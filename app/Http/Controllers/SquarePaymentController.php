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
            $amount   = $data['amount'] ?? null;

            if (!$sourceId || !$amount) {
                return response()->json([
                    'success' => false,
                    'errors' => ['sourceId or amount missing']
                ], 422);
            }

            $result = $this->square->processPayment($sourceId, $amount);
            $payment = $result['payment'] ?? null;
            $errors  = $result['errors'] ?? null;
            $status = $payment ? $payment->getStatus() : Payment::STATUS_FAILED;

            \Log::info('Square Raw Response', $result);


            \Log::info('Square Errors', [
                'errors' => $errors,
            ]);

            $record = Payment::create([
                'square_payment_id' => $payment ? $payment->getId() : null,
                'amount'            => $amount,
                'status'            => $status,
                'customer_email'    => $data['customer_email'] ?? null,
                'order_id'          => $payment ? $payment->getOrderId() : null,
                'payment_data'      => $payment ?: null,
                'request_data'      => $data,
                'idempotency_key'   => $result['idempotency_key'] ?? null,
                'location_id'       => $payment ? $payment->getLocationId() : null,
                'source_type'       => $payment ? $payment->getSourceType() : null,
                'error_message' => json_decode(json_encode($errors), true),
            ]);

            \Log::info('Payment DB Record Stored', [
                'record' => $record->toArray(),
            ]);

            return response()->json([
                'success' => $result['success'],
                'data' => [
                    'payment_record' => $record,
                ]
            ]);

        } catch (\Exception $e) {

            \Log::error('Square payment fatal error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'errors' => [$e->getMessage()]
            ], 500);
        }
    }
}

