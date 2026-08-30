<?php

namespace App\Filament\SuperAdmin\Resources\Payments;

use App\Filament\SuperAdmin\Resources\Payments\Pages\CreatePayment;
use App\Filament\SuperAdmin\Resources\Payments\Pages\EditPayment;
use App\Filament\SuperAdmin\Resources\Payments\Pages\ListPayments;
use App\Filament\SuperAdmin\Resources\Payments\Schemas\PaymentForm;
use App\Filament\SuperAdmin\Resources\Payments\Tables\PaymentsTable;
use App\Models\billing\Payment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Billing Management';

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return PaymentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PaymentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPayments::route('/'),
            'create' => CreatePayment::route('/create'),
            'edit' => EditPayment::route('/{record}/edit'),
        ];
    }
}
