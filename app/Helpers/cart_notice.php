<?php

use App\Domains\Cart\Services\CartService;

if (! function_exists('limit_notice_payload')) {
    /**
     * Build the payload for the storefront edz-notice channel from the last
     * silent cart adjustment (min bump / max clamp), or null when untouched.
     */
    function limit_notice_payload(CartService $cartService): ?array
    {
        $notice = $cartService->takeNotice();

        if (! $notice) {
            return null;
        }

        return [
            'title' => __("storefront.{$notice['key']}", [
                'min' => $notice['limit'],
                'max' => $notice['limit'],
            ]),
            'tone' => 'warning',
        ];
    }
}
