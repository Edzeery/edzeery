<?php

namespace Edzeery\LaravelStatusKit\View\Components;

use BackedEnum;
use Illuminate\View\Component;

class StatusBadge extends Component
{
    public function __construct(
        public BackedEnum $status,
        public bool $dark = false,
    ) {}

    public function render()
    {
        return view('status-kit::components.status-badge');
    }
}
