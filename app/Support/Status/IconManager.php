<?php

namespace App\Support\Status;

class IconManager
{
    public static function get(string $name, ?string $set = null, ?string $classes = null): string
    {
        return \getIconHtml($name, $set, $classes);
    }
}
