<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SquarePaymentService;
use App\Models\Payment;

class SquarePaymentController extends Controller
{
    protected SquarePaymentService $square;

    public function __construct(SquarePaymentService $square)
    {
        $this->square = $square;
    }

    public function showForm()
    {
        return view('payment', [
            'app_id' => env('SQUARE_APPLICATION_ID'),
            'location_id' => env('SQUARE_LOCATION_ID'),
        ]);
    }

    public function process(Request $request)
    {
        try {
            $data = $request->json()->all();

            $sourceId = $data['sourceId'] ?? null;
            $amount = $data['amount'] ?? null;
            $customerEmail = $data['customer_email'] ?? null;
            $orderId = $data['order_id'] ?? null;

            \Log::info('Payment request received', [
                'sourceId' => $sourceId,
                'amount' => $amount,
                'ip' => $request->ip()
            ]);

            if (!$sourceId || !$amount) {
                return response()->json([
                    'success' => false,
                    'errors' => ['sourceId or amount missing']
                ], 422);
            }

            // Call Square service
            $result = $this->square->processPayment($sourceId, $amount);

            if ($result['success']) {

                $payment = $result['payment'];

                $record = Payment::create([
                    'square_payment_id' => $payment->getId(),
                    'amount' => $amount,
                    'status' => $payment->getStatus(),
                    'customer_email' => $customerEmail,
                    'order_id' => $orderId,
                    'location_id' => config('square.location_id'),
                    'source_type' => $payment->getSourceType(),
                    'request_data' => $data,
                    'payment_data' => $payment,
                    'idempotency_key' => $result['idempotency_key'],
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Payment successful',
                    'data' => ['payment_record' => $record]
                ]);
            }
            return response()->json([
                'success' => false,
                'errors' => $result['errors']
            ], 400);

        } catch (\Exception $e) {

            \Log::error('Square payment error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'errors' => [$e->getMessage()]
            ], 500);
        }
    }
}

