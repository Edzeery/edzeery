<?php

namespace App\Domains\Billing\Contracts;

use App\Models\billing\Payment;

interface PaymentGatewayContract
{
    /**
     * Initiate a charge through the gateway.
     * Returns the gateway's reference ID and a redirect URL (if applicable).
     *
     * @return array{gateway_reference: string|null, redirect_url: string|null, status: string}
     */
    public function charge(Payment $payment, array $params): array;

    /**
     * Verify and process an incoming webhook payload.
     * Returns true if the webhook was valid and processed.
     */
    public function verifyWebhook(object $payload, array $headers): bool;

    /**
     * Check the current status of a payment with the gateway.
     */
    public function getStatus(Payment $payment): string;

    /**
     * Whether this gateway handles payments online (redirect/webhook) vs offline (manual).
     */
    public function isOnline(): bool;

    /**
     * Human-readable gateway name.
     */
    public function name(): string;
}
