<?php

namespace App\Domains\Billing\Gateways;

use App\Domains\Billing\Contracts\PaymentGatewayContract;
use App\Enums\SubscriptionPayment\StatusPaymentEnum;
use App\Models\billing\Payment;

class MockGateway implements PaymentGatewayContract
{
    /**
     * Controls what the next charge() will return.
     * Set via MockGateway::setNextResult() in tests.
     */
    private static ?string $nextResult = null;

    public static function setNextResult(?string $result): void
    {
        self::$nextResult = $result;
    }

    public static function reset(): void
    {
        self::$nextResult = null;
    }

    public function charge(Payment $payment, array $params): array
    {
        $result = self::$nextResult ?? StatusPaymentEnum::PAID->value;
        self::$nextResult = null;

        return [
            'gateway_reference' => 'mock_' . uniqid(),
            'redirect_url' => null,
            'status' => $result,
        ];
    }

    public function verifyWebhook(object $payload, array $headers): bool
    {
        return true;
    }

    public function getStatus(Payment $payment): string
    {
        return $payment->status->value;
    }

    public function isOnline(): bool
    {
        return true;
    }

    public function name(): string
    {
        return 'mock';
    }
}
