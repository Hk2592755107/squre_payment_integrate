<?php

namespace App\Services;

use Square\Environments;
use Square\SquareClient;
use Square\Payments\Requests\CreatePaymentRequest;
use Square\Types\Money;
use Square\Types\Currency;
use Square\Exceptions\ApiException;
use Illuminate\Support\Facades\Log;

class SquarePaymentService
{
    protected SquareClient $client;

    public function __construct()
    {
        $this->client = new SquareClient(
            token: config('square.access_token'),
            options: [
                'baseUrl' => Environments::Sandbox->value,
            ]
        );
    }

    public function processPayment(string $sourceId, float $amount): array
    {
        try {
            $idempotencyKey = uniqid('sq_', true);

            $paymentRequest = new CreatePaymentRequest([
                'sourceId' => $sourceId,
                'idempotencyKey' => $idempotencyKey,
                'amountMoney' => new Money([
                    'amount' => (int) round($amount * 100),
                    'currency' => Currency::Usd->value,
                ]),
                'locationId' => config('square.location_id'),
            ]);

            $response = $this->client->payments->create($paymentRequest);

            $payment = $response->getPayment();

            return [
                'success' => true,
                'payment' => $payment,
                'idempotency_key' => $idempotencyKey,
            ];

        } catch (ApiException $e) {
            Log::error('Square API error: ', $e->getErrors());

            return [
                'success' => false,
                'errors' => $e->getErrors(),
            ];
        } catch (\Exception $e) {
            Log::error('Square payment error: ' . $e->getMessage());

            return [
                'success' => false,
                'errors' => [$e->getMessage()],
            ];
        }
    }
}
