<?php

namespace App\Domains\Order\Exceptions;

use DomainException;

class OrderIncompleteException extends DomainException
{
    public array $missing;

    public function __construct(string $message = '', array $missing = [])
    {
        parent::__construct($message);

        $this->missing = $missing;
    }

    public static function fromMissing(array $missing): self
    {
        $labels = array_column($missing, 'label');

        return new static(
            'Order is incomplete: ' . implode(', ', $labels),
            array_values($missing),
        );
    }

    public function labels(): array
    {
        return array_column($this->missing, 'label');
    }
}