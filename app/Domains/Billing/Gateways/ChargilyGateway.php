<?php

namespace App\Domains\Billing\Gateways;

use App\Domains\Billing\Contracts\PaymentGatewayContract;
use App\Enums\SubscriptionPayment\StatusPaymentEnum;
use App\Models\billing\Payment;
use Illuminate\Support\Facades\Http;
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
        if ($this->secretKey === '') {
            throw new RuntimeException('Chargily gateway is not configured.');
        }

        $payload = array_filter([
            'amount' => round((float) $payment->amount, 2),
            'currency' => strtolower($payment->currency ?: 'dzd'),
            'description' => $params['description'] ?? null,
            'success_url' => $params['success_url'] ?? null,
            'failure_url' => $params['failure_url'] ?? null,
            'webhook_url' => $params['webhook_url'] ?? route('api.webhooks.chargily'),
            'customer_name' => $params['customer_name'] ?? null,
            'customer_email' => $params['customer_email'] ?? null,
            'metadata' => ['payment_id' => $payment->id],
        ], fn ($value) => $value !== null);

        $response = Http::withToken($this->secretKey)
            ->acceptJson()
            ->post($this->baseUrl() . '/checkouts', $payload);

        if ($response->failed()) {
            throw new RuntimeException('Chargily checkout failed: ' . $response->body());
        }

        $checkout = $response->json();

        return [
            'gateway_reference' => $checkout['id'] ?? null,
            'redirect_url' => $checkout['checkout_url'] ?? null,
            'status' => StatusPaymentEnum::PENDING->value,
        ];
    }

    public function verifyWebhook(object $payload, array $headers): bool
    {
        $signature = $headers['signature'] ?? '';
        $rawBody = $headers['raw_body'] ?? '';

        if ($signature === '' || $rawBody === '' || $this->secretKey === '') {
            return false;
        }

        return hash_equals(
            hash_hmac('sha256', $rawBody, $this->secretKey),
            (string) $signature
        );
    }

    public function getStatus(Payment $payment): string
    {
        $checkoutId = $payment->meta['chargily_checkout_id'] ?? null;

        if (! $checkoutId || $this->secretKey === '') {
            return $payment->status->value;
        }

        $response = Http::withToken($this->secretKey)
            ->acceptJson()
            ->get($this->baseUrl() . '/checkouts/' . $checkoutId);

        if ($response->failed()) {
            return $payment->status->value;
        }

        return match ($response->json('status')) {
            'paid' => StatusPaymentEnum::PAID->value,
            'failed' => StatusPaymentEnum::FAILED->value,
            'canceled' => StatusPaymentEnum::CANCELED->value,
            default => $payment->status->value,
        };
    }

    protected function baseUrl(): string
    {
        return $this->mode === 'live'
            ? 'https://pay.chargily.net/api/v2'
            : 'https://pay.chargily.net/test/api/v2';
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
