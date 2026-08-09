<?php

namespace App\View\Components;

use BackedEnum;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class RoleBadge extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public BackedEnum|string|null $role,
        public bool $dark = false,
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.role-badge');
    }
}
