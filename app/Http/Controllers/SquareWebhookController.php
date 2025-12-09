<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\SquareWebhookService;

class SquareWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        $signature = $request->header('x-square-signature');
        $body = $request->getContent();

        // Verify webhook signature
        if (!SquareWebhookService::verifyWebhook($signature, $body)) {
            Log::warning('Invalid webhook signature');
            abort(403, 'Invalid signature');
        }

        $event = json_decode($body, true);

        Log::info('Square Webhook Received: ' . $event['type']);

        // Handle different webhook events
        switch ($event['type']) {
            case 'payment.updated':
                $this->handlePaymentUpdate($event);
                break;

            case 'payment.created':
                $this->handlePaymentCreated($event);
                break;

            case 'refund.updated':
                $this->handleRefundUpdate($event);
                break;
        }

        return response()->json(['success' => true]);
    }

    private function handlePaymentUpdate($event)
    {
        $payment = $event['data']['object']['payment'];

        // Update payment status in your database
        Payment::where('square_payment_id', $payment['id'])
            ->update(['status' => $payment['status']]);

        // Additional business logic here
    }

    private function handlePaymentCreated($event)
    {
        // Handle new payment creation
    }

    private function handleRefundUpdate($event)
    {
        // Handle refund updates
    }
}
