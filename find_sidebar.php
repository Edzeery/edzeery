<?php
$files = glob('storage/framework/views/*.php');
foreach ($files as $f) {
    $c = file_get_contents($f);
    if (str_contains($c, 'storefront-settings')) {
        echo $f . PHP_EOL;
    }
}
