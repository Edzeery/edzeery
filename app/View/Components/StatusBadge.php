<?php

namespace App\View\Components;

use BackedEnum;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class StatusBadge extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public BackedEnum|string|null $status,
        public bool $dark = true,
        public ?string $domain = null,
        public ?string $set = null,
        public bool $iconOnly = false,
        public ?string $storeId = null,
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.status-badge');
    }
}
