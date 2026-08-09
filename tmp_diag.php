<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo '=== TRIGGERS ===' . PHP_EOL;
foreach (DB::select('select trigger_name, event_object_table, action_statement from information_schema.triggers where trigger_schema = ?', [env('DB_DATABASE', 'edzeery')]) as $t) {
    echo $t->trigger_name . ' ON ' . $t->event_object_table . ': ' . substr($t->action_statement, 0, 200) . PHP_EOL;
}

echo PHP_EOL . '=== PRODUCTS TABLE ===' . PHP_EOL;
$create = DB::select('show create table products');
echo $create[0]->{'Create Table'} . PHP_EOL;

echo PHP_EOL . '=== DB LISTENER TEST ===' . PHP_EOL;
DB::listen(function ($q) {
    echo 'LISTEN: ' . substr($q->sql, 0, 80) . ' | bindings=' . json_encode($q->bindings) . PHP_EOL;
});

try {
    DB::table('products')->insert([
        'store_id' => '01kz6ecsmk3ccrzw2rfvx9jqeg',
        'name' => 'DiagTest',
        'slug' => 'diag-test',
        'sku' => 'DIAG-' . time(),
        'type' => 'simple',
        'price' => 5,
    ]);
    echo 'insert ok' . PHP_EOL;
} catch (Throwable $e) {
    echo 'insert failed: ' . $e->getMessage() . PHP_EOL;
}
