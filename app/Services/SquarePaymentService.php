<?php

namespace App\Services;

use Square\Environments;
use Square\Legacy\Exceptions\ApiException;
use Square\SquareClient;
use Square\Payments\Requests\CreatePaymentRequest;
use Square\Types\Money;
use Square\Types\Currency;
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
                'errors' => null,
            ];

        } catch (ApiException $e) {

            return [
                'success' => false,
                'payment' => null,
                'idempotency_key' => null,
                'errors' => $e->getErrors(), // Square ki API errors
            ];

        } catch (\Exception $e) {

            return [
                'success' => false,
                'payment' => null,
                'idempotency_key' => $idempotencyKey,
                'errors' => [$e->getMessage()],
            ];
        }
    }}
