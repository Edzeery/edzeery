<?php

namespace App\Filament\Merchant\Resources\Products\Schemas;

use App\Enums\Store\ProductOptionInputType;
use App\Models\Category;
use App\Models\Products\Product;
use App\Models\Products\ProductOption;
use App\Models\Products\ProductOptionValue;
use App\Services\BarcodeService;
use App\Support\SkuGenerator;
use App\Support\VariantPreviewBuilder;
use Dom\Text;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Hidden::make('options_changed')
                ->default(false)
                ->dehydrated(true),
            Wizard::make([


                /* ================= Basic Info ================= */
                Step::make('Basic Info')
                    ->schema([
                        Section::make('Basic info')
                            ->schema([
                                Grid::make(2)->schema([
                                    Select::make('brand_id')
                                        ->relationship('brand', 'name')
                                        ->nullable()
                                        ->searchable()
                                        ->preload(),

                                    Select::make('categories')
                                        ->label('Categories')
                                        ->multiple()
                                        ->relationship('categories', 'name')
                                        ->options(function () {
                                            return Category::all()->mapWithKeys(function ($category) {
                                                return [$category->id => $category->full_name]; // full_name = "Gpu > Nvidia > RTX > Ser 40"
                                            })->toArray();
                                        })
                                        ->searchable()
                                        ->preload()
                                        ->hint('You can select multiple categories (hierarchical structure supported)'),

                                    TextInput::make('unit')
                                        ->label(__('titles.unit'))
                                        ->default('piece')
                                        ->required()
                                        ->dehydrated(true),
                                ]),

                                Grid::make(2)->schema([
                                    TextInput::make('name')
                                        ->required()
                                        ->live(debounce: 1500)
                                        ->unique(
                                            table: 'products',
                                            column: 'name',
                                            ignoreRecord: true,
                                            modifyRuleUsing: fn($rule, callable $get) =>
                                            $rule->where('store_id', $get('store_id'))
                                        )
                                        ->afterStateUpdated(
                                            fn($state, $set) =>
                                            $set('slug', Str::slug($state))
                                        ),

                                    TextInput::make('slug')
                                        ->required()
                                        ->readOnly()
                                    // ->unique('products', 'slug')
                                    ,
                                ]),
                                Textarea::make('short_description')
                                    ->label(__('titles.short_description'))
                                    ->columnSpanFull()
                                    ->dehydrated(true),
                            ]),
                        Section::make('SEO')
                            ->schema([
                                TextInput::make('meta_title')
                                    ->label(__('titles.meta_title'))
                                    ->maxLength(255)
                                    ->dehydrated(true),

                                MarkdownEditor::make('meta_description')
                                    ->label(__('titles.meta_description'))
                                    ->dehydrated(true),
                            ])

                    ])
                    ->columns(2),

                /* ================= Configure product ================= */
                Step::make('Configure product')
                    ->schema([
                        Section::make('Configure product')
                            ->schema([
                                Grid::make(1)->schema([
                                    Toggle::make('has_variants')
                                        ->label('This product has variants')
                                        ->live()
                                        ->reactive()

                                        ->afterStateUpdated(function ($state, callable $get, callable $set, $record) {

                                            if ($state === true) {
                                                // Simple → Variable
                                                $set('options_changed', true);
                                                $set('variants_preview', []);

                                                // 🔓 أعد تمكين auto-generation
                                                $set('auto_generate_sku', false);
                                                $set('auto_generate_barcode', false);
                                            }


                                            if ($state === false) {
                                                // تحوّل Variable → Simple
                                                $set('variants_preview', []);
                                                $set('options', []);
                                            }
                                        })
                                        ->formatStateUsing(fn($record) => $record?->hasVariants() ?? false),
                                ]),

                                Grid::make(2)->schema([

                                    Toggle::make('auto_generate_barcode')
                                        ->label('Auto-generate Barcodes')
                                        ->helperText('Barcodes will be generated automatically')
                                        ->live()
                                        ->reactive()
                                        ->default(false)

                                        // ✅ تعطيل الـ Toggle عند Edit إذا barcode موجود
                                        ->disabled(
                                            fn($record, callable $get) =>
                                            filled($record?->barcode) && $get('has_variants') === false
                                        )
                                        // ✅ عند تحميل الفورم (Edit)
                                        ->afterStateHydrated(function (callable $set, $record) {
                                            if ($record && filled($record->barcode)) {
                                                $set('auto_generate_barcode', false);
                                            }
                                        })
                                        ->dehydrated(true),

                                    TextInput::make('barcode')
                                        ->label('Barcode')
                                        ->placeholder(fn($get) => $get('auto_generate_barcode') ? __('messages.auto_generate') : null)
                                        // 🔒 يُعطّل فقط إذا auto_generate_barcode مفعّل
                                        ->disabled(fn($get) => $get('auto_generate_barcode') === true)

                                        ->helperText(
                                            fn($get) =>
                                            $get('auto_generate_barcode')
                                                ? __('messages.auto_generate_barcode_notice')
                                                : 'Unique barcode for the product'
                                        )

                                        ->unique(
                                            table: 'products',
                                            column: 'barcode',
                                            ignoreRecord: true
                                        )

                                        // ✅ عند Edit: اجلب القيمة من DB فقط
                                        ->afterStateHydrated(function (callable $set, callable $get, $record) {
                                            if (
                                                $record &&
                                                filled($record->barcode) &&
                                                ($get('has_variants') === false)
                                            ) {
                                                $set('auto_generate_barcode', false);
                                            }
                                        })

                                        ->disabled(fn($get) => $get('auto_generate_barcode') === true),

                                    Toggle::make('auto_generate_sku')
                                        ->label('Auto-generate SKUs')
                                        ->helperText('SKUs will be generated automatically')
                                        ->live()
                                        ->default(false)

                                        // ✅ تعطيل الـ Toggle عند Edit إذا sku موجود
                                        ->disabled(
                                            fn($record, callable $get) =>
                                            filled($record?->sku) && $get('has_variants') === false
                                        )


                                        // ✅ عند تحميل الفورم (Edit)
                                        ->afterStateHydrated(
                                            function (callable $set, callable $get, $record) {
                                                if (
                                                    $record &&
                                                    filled($record->sku) &&
                                                    ($get('has_variants') === false)
                                                ) {
                                                    $set('auto_generate_sku', false);
                                                }
                                            }
                                        )
                                        ->dehydrated(true),


                                    TextInput::make('sku')
                                        ->label('Base SKU')
                                        ->required(fn($get) => ! $get('auto_generate_sku'))
                                        ->placeholder(fn($get) => $get('auto_generate_sku') ? __('messages.auto_generate') : null)
                                        ->live(debounce: 500)
                                        ->unique(
                                            table: 'products',
                                            column: 'sku',
                                            ignoreRecord: true
                                        )

                                        // ✅ يُعطّل فقط عند auto_generate_sku
                                        ->disabled(fn($get) => $get('auto_generate_sku') === true)

                                        ->dehydrated(fn($get) => $get('auto_generate_sku') !== true),

                                    Section::make('Status')
                                        ->schema([
                                            Grid::make(2)->schema([
                                                Toggle::make('is_active')
                                                    ->live()
                                                    ->reactive()
                                                    ->default(true),
                                                Toggle::make('is_featured')
                                                    ->live()
                                                    ->reactive()
                                                    ->default(false),
                                            ]),
                                        ]),

                                ]),
                            ]),
                    ]),
                /* =================/ Configure product /================= */

                /* ================= Pricing ================= */
                Step::make('Pricing')
                    ->visible(fn($get) => ! $get('has_variants'))
                    ->schema([
                        Section::make('Pricing')
                            ->visible(fn($get) => ! $get('has_variants'))
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('price')
                                        ->label('Product Price')
                                        ->numeric()
                                        ->required(fn($get) => ! $get('has_variants'))
                                        ->visible(fn($get) => ! $get('has_variants')),
                                    TextInput::make('compare_price')
                                        ->label('Compare Price')
                                        ->numeric()

                                        ->visible(fn($get) => ! $get('has_variants')),

                                    TextInput::make('cost_price')
                                        ->label('Cost Price')
                                        ->numeric()
                                        ->required(fn($get) => ! $get('has_variants'))
                                        ->visible(fn($get) => ! $get('has_variants')),
                                ]),
                                TextInput::make('profit')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->afterStateHydrated(
                                        fn($state, callable $get, callable $set) =>
                                        self::recalculateProfitAndMargin($get, $set)
                                    )
                                    ->visible(fn($get) => ! $get('has_variants')),
                                TextInput::make('margin')
                                    ->suffix('%')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->afterStateHydrated(
                                        fn($state, callable $get, callable $set) =>
                                        self::recalculateProfitAndMargin($get, $set)
                                    )
                                    ->visible(fn($get) => ! $get('has_variants')),
                            ]),
                    ]),

                /* =================/ Pricing /================= */



                /* ================= Options & Values ================= */
                Step::make('Options & Values')
                    ->visible(fn($get) => $get('has_variants'))
                    ->schema([
                        /* ================= Repeater options ================= */
                        Repeater::make('options')
                            ->label('Product Options')
                            ->live()
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set, $get, $record) {

                                // إذا Edit → علّم أن الخيارات تغيرت
                                if ($record) {
                                    $set('options_changed', true);
                                }

                                $set(
                                    'variants_preview',
                                    VariantPreviewBuilder::fromOptions(
                                        collect($state ?? [])
                                            ->filter(
                                                fn($o) => ($o['type'] ?? null) !== ProductOptionInputType::TEXT->value
                                            )
                                            ->toArray()
                                    )
                                );
                            })
                            ->schema([
                                Grid::make(2)->schema([
                                    /* ===== Option ===== */
                                    Select::make('product_option_id')
                                        ->label('Option')
                                        ->options(function (callable $get) {

                                            // الخيار الحالي
                                            $currentOptionId = $get('product_option_id');

                                            // كل الخيارات المختارة في الريبيتر
                                            $selectedOptionIds = collect($get('../../options') ?? [])
                                                ->pluck('product_option_id')
                                                ->filter()
                                                ->unique()
                                                ->reject(fn($id) => $id == $currentOptionId) // 👈 استثناء الحالي
                                                ->toArray();

                                            return ProductOption::query()
                                                ->whereNotIn('id', $selectedOptionIds)
                                                ->pluck('name', 'id');
                                        })
                                        ->required()
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, callable $get, callable $set) {

                                            $option = ProductOption::find($state);

                                            $set('type', $option?->type?->value);

                                            // إعادة تعيين القيم فقط إذا تغيّر الأوبشن فعليًا
                                            if ($get('values')) {
                                                $set('values', []);
                                            }
                                        }),


                                    /* ===== Values ===== */
                                    Section::make('Values')->schema([
                                        Select::make('values')
                                            ->multiple()
                                            ->searchable()
                                            ->reactive()
                                            ->options(
                                                fn($get) =>
                                                ProductOptionValue::query()
                                                    ->where('product_option_id', $get('product_option_id'))
                                                    ->pluck('value', 'id')
                                            )
                                            ->required()
                                            ->visible(
                                                fn($get) =>
                                                in_array(
                                                    $get('type'),
                                                    [
                                                        ProductOptionInputType::SELECT->value,
                                                        ProductOptionInputType::RADIO->value,
                                                        ProductOptionInputType::CHECKBOX->value,
                                                    ],
                                                    true
                                                )
                                            ),
                                    ]),
                                ]),

                                Hidden::make('type'),
                            ])
                            ->addActionLabel('Add Option'),
                    ]),

                /* ================= Variants Preview ================= */
                Step::make('Variants Preview')
                    ->visible(fn($get) => $get('has_variants') && self::canEnableVariants($get))

                    ->schema([
                        // ===== Apply to All =====
                        Grid::make(4)->schema([
                            TextInput::make('apply_all_price')
                                ->label('Price (Apply to all)')->numeric()->live()->dehydrated(false)->default('0.00'),
                            TextInput::make('apply_all_cost_price')
                                ->label('Cost Price (Apply to all)')->numeric()->live()->dehydrated(false)->default('0.00'),
                            TextInput::make('apply_all_stock')
                                ->label('Stock (Apply to all)')->numeric()->dehydrated(false)->default(1),
                            TextInput::make('apply_all_low_stock')
                                ->label('Low Stock Alert (Apply to all)')->numeric()->dehydrated(false)->default(5),
                            TextInput::make('apply_all_weight')
                                ->label('Weight (Apply to all)')->numeric()->dehydrated(false)->default(null),
                            TextInput::make('apply_all_length')
                                ->label('Length (Apply to all)')->numeric()->dehydrated(false)->default(null),
                            TextInput::make('apply_all_width')
                                ->label('Width (Apply to all)')->numeric()->dehydrated(false)->default(null),
                            TextInput::make('apply_all_height')
                                ->label('Height (Apply to all)')->numeric()->dehydrated(false)->default(null),
                            Action::make('apply_all_variants')->label('Apply')->icon('heroicon-o-check-circle')
                                ->color('primary')
                                ->visible(fn($get) => !empty($get('variants_preview')))
                                ->action(function (callable $get, callable $set) {
                                    $variants = $get('variants_preview') ?? [];
                                    foreach ($variants as $i => $variant) {
                                        // ===== Apply all fields =====
                                        $fields = [
                                            'price' => 'apply_all_price',
                                            'cost_price' => 'apply_all_cost_price',
                                            'stock' => 'apply_all_stock',
                                            'low_stock_threshold' => 'apply_all_low_stock',
                                            'weight' => 'apply_all_weight',
                                            'length' => 'apply_all_length',
                                            'width' => 'apply_all_width',
                                            'height' => 'apply_all_height',
                                        ];

                                        foreach ($fields as $field => $inputKey) {
                                            $value = $get($inputKey);
                                            if ($value !== null) {
                                                if (in_array($field, ['stock', 'low_stock_threshold'])) {
                                                    $variants[$i][$field] = max(0, (int)$value);
                                                } else {
                                                    $variants[$i][$field] = $value;
                                                }
                                            }
                                        }

                                        // ===== Recalculate profit/margin =====
                                        $price = $variants[$i]['price'] ?? null;
                                        $cost  = $variants[$i]['cost_price'] ?? null;
                                        if (is_numeric($price) && is_numeric($cost) && $price > 0) {
                                            $variants[$i]['profit'] = $price - $cost;
                                            $variants[$i]['margin'] = round((($price - $cost) / $price) * 100, 2);
                                        } else {
                                            $variants[$i]['profit'] = null;
                                            $variants[$i]['margin'] = null;
                                        }
                                    }
                                    $set('variants_preview', $variants);
                                }),
                        ]),
                        // ===== Reset Variants =====
                        Action::make('reset_variants')
                            ->label('Reset variants')
                            ->icon('heroicon-o-arrow-path')
                            ->color('gray')
                            ->visible(fn($get) => !empty($get('variants_preview')))
                            ->action(fn(callable $get, callable $set) => $set('variants_preview', [])),

                        Repeater::make('variants_preview')->columns(12)->schema([
                            Tabs::make('Variant Details')
                                ->tabs([
                                    Tabs\Tab::make('Basic Info')
                                        ->schema([
                                            TextInput::make('labels')->disabled()->dehydrated(false),
                                            TextInput::make('sku')
                                                ->disabled(fn($get) => $get('../../auto_generate_sku') === true)
                                                ->placeholder(fn($get) => $get('../../auto_generate_sku') ? __('messages.auto_generate') : null)
                                                ->dehydrated(fn($get) => $get('../../auto_generate_sku') !== true)
                                                ->helperText(fn($get) => $get('../../auto_generate_sku') ? __('messages.auto_generate_sku_notice') : null),

                                            TextInput::make('barcode')
                                                ->disabled(fn($get) => $get('../../auto_generate_barcode') === true)
                                                ->placeholder(fn($get) => $get('../../auto_generate_barcode') ? __('messages.auto_generate') : null)
                                                ->helperText(fn($get) => $get('../../auto_generate_barcode') ? __('messages.auto_generate_barcode_notice') : null)
                                                ->disabled(fn($get) => $get('../../auto_generate_barcode') === true),
                                        ]),

                                    Tabs\Tab::make('Pricing')
                                        ->schema([
                                            Grid::make(12)->schema([
                                                TextInput::make('price')->numeric()->required()->live()->columnSpan(4),
                                                TextInput::make('compare_price')->numeric()->nullable()->live()->columnSpan(4),
                                                TextInput::make('cost_price')->numeric()->required()->live()->columnSpan(4),
                                                TextInput::make('profit')->disabled()->dehydrated(true)->columnSpan(4),
                                                TextInput::make('margin')->suffix('%')->disabled()->dehydrated(true)->columnSpan(4),
                                            ]),
                                        ]),

                                    Tabs\Tab::make('Inventory')
                                        ->schema([
                                            Grid::make(2)->schema([
                                                TextInput::make('stock')->numeric()->default(1),
                                                TextInput::make('low_stock_threshold')->label('Low Stock Alert')->numeric()->minValue(0)->default(5),
                                            ]),
                                        ]),

                                    Tabs\Tab::make('Attributes')
                                        ->schema([
                                            Grid::make(12)->schema([
                                                TextInput::make('weight')->numeric()->step(0.001)->nullable()->columnSpan(3),
                                                TextInput::make('length')->numeric()->step(0.01)->nullable()->columnSpan(3),
                                                TextInput::make('width')->numeric()->step(0.01)->nullable()->columnSpan(3),
                                                TextInput::make('height')->numeric()->step(0.01)->nullable()->columnSpan(3),
                                            ]),
                                        ]),

                                    Tabs\Tab::make('Status')
                                        ->schema([
                                            Toggle::make('is_active')
                                                ->default(true),
                                        ]),
                                ])->columnSpanFull(),
                        ]),
                    ])->columnSpanFull(),
                /* ================= Images ================= */
                Step::make('Images')
                    ->schema([

                        FileUpload::make('images')
                            ->disk('public')
                            ->multiple()
                            ->image()
                            ->directory('products')
                            ->reorderable()
                            ->preserveFilenames()
                            ->dehydrated(true),
                        MarkdownEditor::make('description')
                            ->label(__('titles.description'))
                            ->dehydrated(true),
                    ])->columns(2),

            ])->submitAction( 
                Action::make('save')->label('Save')->color('primary')->submit('save')
            )->columnSpanFull(),
        ]);
    }

    protected static function canEnableVariants(callable $get): bool
    {
        $options = collect($get('options') ?? [])
            ->filter(fn($o) => ($o['type'] ?? null) !== ProductOptionInputType::TEXT->value);

        if ($options->isEmpty()) return false;

        foreach ($options as $option) {
            if (empty($option['product_option_id']) || empty($option['values'])) {
                return false;
            }
        }

        return true;
    }


    protected static function generatePreviewVariants(array $options): array
    {
        if (empty($options)) {
            return [];
        }

        // 1️⃣ Collect IDs
        $optionIds = collect($options)
            ->pluck('product_option_id')
            ->filter()
            ->unique()
            ->toArray();

        $valueIds = collect($options)
            ->pluck('values')
            ->flatten()
            ->filter()
            ->unique()
            ->toArray();

        if (empty($optionIds) || empty($valueIds)) {
            return [];
        }

        // 2️⃣ Fetch once
        $optionsCollection = ProductOption::whereIn('id', $optionIds)
            ->get(['id', 'name'])
            ->keyBy('id');

        $valuesCollection = ProductOptionValue::whereIn('id', $valueIds)
            ->get(['id', 'product_option_id', 'value'])
            ->groupBy('product_option_id');

        // 3️⃣ Build options map
        $optionsMap = collect($options)
            ->filter(fn($o) => !empty($o['values']))
            ->map(function ($option) use ($optionsCollection, $valuesCollection) {

                $optionModel = $optionsCollection->get($option['product_option_id']);

                if (! $optionModel) {
                    return null;
                }

                return [
                    'option_name' => $optionModel->name,
                    'values' => $valuesCollection->get($option['product_option_id'], collect()),
                ];
            })
            ->filter()
            ->values();

        if ($optionsMap->isEmpty()) {
            return [];
        }

        // 4️⃣ Cartesian Product
        $combinations = [[]];

        foreach ($optionsMap as $option) {
            $tmp = [];

            foreach ($combinations as $combination) {
                foreach ($option['values'] as $value) {
                    $tmp[] = array_merge($combination, [[
                        'option'   => $option['option_name'],
                        'value'    => $value->value,
                        'value_id' => $value->id,
                    ]]);
                }
            }

            $combinations = $tmp;
        }

        // 5️⃣ Final format
        return collect($combinations)->map(fn($combo) => [
            'labels' => collect($combo)
                ->map(fn($c) => "{$c['option']} : {$c['value']}")
                ->implode(' , '),

            'sku_parts' => collect($combo)
                ->pluck('value')
                ->map(fn($v) => SkuGenerator::normalizePart($v))

                ->toArray(),

            'value_ids' => collect($combo)
                ->pluck('value_id')
                ->toArray(),

            'name' => collect($combo)
                ->map(fn($c) => "{$c['option']} : {$c['value']}")
                ->implode(' / '),

            'sku' => null,
            'price' => null,
            'cost_price' => null,
            'stock' => 1,
            'low_stock_threshold' => 5,
        ])->toArray();
    }


    protected static function recalculateProfitAndMargin(callable $get, callable $set): void
    {
        $price = $get('price');
        $cost  = $get('cost_price');
        if (!is_numeric($price) || !is_numeric($cost) || $price <= 0) {
            $set('profit', null);
            $set('margin', null);
            return;
        }
        $profit = $price - $cost;
        $margin = round(($profit / $price) * 100, 2);
        $set('profit', $profit);
        $set('margin', $margin);
    }
}
