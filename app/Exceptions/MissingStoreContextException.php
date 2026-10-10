<?php

namespace App\Exceptions;

use RuntimeException;

class MissingStoreContextException extends RuntimeException
{
    public static function forModel(string $model): self
    {
        return new self(sprintf(
            'Cannot create [%s] without a store context. Set a store_id explicitly '
            .'or wrap the write in StoreContext::runAs($store, fn).',
            $model
        ));
    }
}
