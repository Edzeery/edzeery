<?php

namespace App\Filament\Imports;

use App\Models\Products\ProductOptionValue;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class ProductOptionValueImporter extends Importer
{
    protected static ?string $model = ProductOptionValue::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('store')
                ->relationship(),
            ImportColumn::make('product_option_id')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('value')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
        ];
    }

    public function resolveRecord(): ProductOptionValue
    {
        return ProductOptionValue::firstOrNew([
            'value' => $this->data['value'],
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your product option value import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
