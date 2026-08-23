<?php

namespace App\Http\Controllers\Api\Webhooks;

use App\Domains\Billing\Events\PaymentSucceeded;
use App\Domains\Billing\Gateways\ChargilyGateway;
use App\Enums\SubscriptionPayment\StatusPaymentEnum;
use App\Http\Controllers\Controller;
use App\Models\billing\Payment;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ChargilyWebhookController extends Controller
{
    public function __invoke(Request $request, ChargilyGateway $gateway): Response
    {
        $payload = $request->json()->object();

        $valid = $payload && $gateway->verifyWebhook($payload, [
            'signature' => (string) $request->header('signature', ''),
            'raw_body' => (string) $request->getContent(),
        ]);

        if (! $valid) {
            return response('Invalid signature.', Response::HTTP_FORBIDDEN);
        }

        $checkout = $payload->data ?? null;

        if (! $checkout) {
            return response('Malformed payload.', Response::HTTP_BAD_REQUEST);
        }

        $payment = Payment::where('meta->chargily_checkout_id', $checkout->id ?? null)->first()
            ?? Payment::where('transaction_id', $checkout->id ?? null)->first();

        if (! $payment) {
            return response('Payment not found.', Response::HTTP_NOT_FOUND);
        }

        $status = match ($payload->type ?? '') {
            'checkout.paid' => StatusPaymentEnum::PAID,
            'checkout.failed' => StatusPaymentEnum::FAILED,
            default => null,
        };

        if (! $status || $payment->status === $status) {
            return response('Event ignored.', Response::HTTP_OK);
        }

        $payment->update([
            'status' => $status,
            'paid_at' => $status === StatusPaymentEnum::PAID ? now() : $payment->paid_at,
            'transaction_id' => $checkout->invoice_id ?? $payment->transaction_id,
            'meta' => array_merge($payment->meta ?? [], [
                'chargily_checkout_id' => $checkout->id ?? null,
                'chargily_event' => $payload->type,
            ]),
        ]);

        if ($status === StatusPaymentEnum::PAID) {
            event(new PaymentSucceeded($payment));
        }

        return response('OK', Response::HTTP_OK);
    }
}
