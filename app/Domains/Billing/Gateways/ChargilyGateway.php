<?php

namespace App\Domains\Billing\Gateways;

use App\Domains\Billing\Contracts\PaymentGatewayContract;
use App\Models\billing\Payment;
use RuntimeException;

class ChargilyGateway implements PaymentGatewayContract
{
    public function __construct(
        protected string $apiKey = '',
        protected string $secretKey = '',
        protected string $mode = 'test',
    ) {}

    public function charge(Payment $payment, array $params): array
    {
        // TODO: Implement when test credentials are available.
        // Will call Chargily API to create a checkout session.
        throw new RuntimeException('Chargily gateway not yet configured. Use MockGateway for testing.');
    }

    public function verifyWebhook(object $payload, array $headers): bool
    {
        // TODO: Implement HMAC-SHA256 signature verification against Chargily webhook_secret.
        return false;
    }

    public function getStatus(Payment $payment): string
    {
        // TODO: Implement Chargily API status check.
        return $payment->status->value;
    }

    public function isOnline(): bool
    {
        return true;
    }

    public function name(): string
    {
        return 'chargily';
    }
}
