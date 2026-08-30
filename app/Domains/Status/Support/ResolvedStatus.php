<?php

namespace App\Domains\Status\Support;

use App\Models\Status as StatusModel;
use Edzeery\MyStatusKit\DTO\StatusResult;
use Illuminate\Support\Str;

/**
 * نتيجة موحّدة لحالة النظام (سواء من جدول statuses أو من مكتبة status-kit).
 *
 * المصدر db = صف من جدول statuses (خاصة بمتجر أو نظامية),
 * المصدر kit = fallback من config/status-kit-statuses.php.
 */
final class ResolvedStatus
{
    public function __construct(
        public readonly string $domain,
        public readonly string $key,
        public readonly string $label,
        public readonly string $variant,
        public readonly string $light,
        public readonly string $dark,
        public readonly string $hex = '#9ca3af',
        public readonly ?string $icon = null,
        public readonly string $displayMode = 'badge',
        public readonly string $source = 'kit',
        public readonly ?string $storeId = null,
    ) {}

    public static function fromKit(string $domain, string $key, StatusResult $result): self
    {
        return new self(
            domain: $domain,
            key: $key,
            label: $result->label(),
            variant: $result->variant(),
            light: $result->lightClass(),
            dark: $result->darkClass(),
            hex: $result->hex(),
            icon: $result->toArray()['icon'] ?? null,
            displayMode: 'badge',
            source: 'kit',
        );
    }

    public static function fromModel(StatusModel $model): self
    {
        $variant = $model->color ?: 'gray';
        $style = self::styleFor($variant);
        $isSystem = (bool) $model->is_system && empty($model->store_id);
        $label = $isSystem ? self::systemLabel($model) : $model->label;

        return new self(
            domain: $model->type,
            key: $model->key,
            label: $label,
            variant: $variant,
            light: $style['light'],
            dark: $style['dark'],
            hex: $style['hex'],
            icon: $model->icon,
            displayMode: $model->display_mode ?? 'badge',
            source: 'db',
            storeId: $model->store_id,
        );
    }

    /**
     * الصف النظامي يعرض الترجمة من status-kit إن وُجدت (يحافظ على i18n
     * عبر اللغات الأربع حتى مع وجود الصف في جدول statuses)،
     * وإلا يتراجع لـ label مخزون الصف.
     */
    private static function systemLabel(StatusModel $model): string
    {
        $key = "status-kit::statuses.{$model->type}.{$model->key}";
        $translated = __($key);

        return $translated === $key ? $model->label : $translated;
    }

    public static function fallback(string $domain, string $key): self
    {
        $style = self::styleFor('gray');

        return new self(
            domain: $domain,
            key: $key,
            label: Str::headline($key),
            variant: 'gray',
            light: $style['light'],
            dark: $style['dark'],
            hex: $style['hex'],
            icon: 'default',
            displayMode: 'badge',
            source: 'kit',
        );
    }

    /** كلاسات العرض (light مع dark اختيارياً). */
    public function classes(bool $dark = true): string
    {
        return trim($this->light.($dark && $this->dark ? ' '.$this->dark : ''));
    }

    /** كلاسات عنصر البادج كاملة (أساس الفريموورك + لون + إضافات) — مرآة StatusResult::badgeClasses. */
    public function badgeClasses(?string $extraClasses = null, ?string $framework = null): string
    {
        $framework = $framework ?? config('status-kit-theme.default_framework', 'bootstrap');
        $base = config("status-kit-theme.badge_base.{$framework}", '');

        return trim($base.' '.$this->classes().' '.($extraClasses ?? ''));
    }

    /** HTML الأيقونة (عبر vendor status-kit). */
    public function renderIcon(?string $set = null, ?string $classes = null): string
    {
        return \icon($this->icon ?? 'default', $set, $classes);
    }

    /** اسم لون Filament المكافئ للـ variant. */
    public function filamentColor(): string
    {
        return match ($this->variant) {
            'success' => 'success',
            'warning' => 'warning',
            'danger' => 'danger',
            'info' => 'info',
            default => 'gray',
        };
    }

    /** اسم أيقونة Filament (heroicon-o-*). */
    public function filamentIcon(): string
    {
        $iconKey = $this->heroiconKey();

        return $iconKey ? 'heroicon-o-'.$iconKey : 'heroicon-o-x-circle';
    }

    /** مفتاح الأيقونة ضمن مجموعة heroicon في status-kit-icons. */
    public function heroiconKey(): ?string
    {
        if (! $this->icon) {
            return null;
        }

        return config("status-kit-icons.heroicon.{$this->icon}", $this->icon);
    }

    public function toArray(): array
    {
        return [
            'domain' => $this->domain,
            'status' => $this->key,
            'value' => $this->key,
            'label' => $this->label,
            'variant' => $this->variant,
            'color' => $this->classes(false),
            'color_dark' => $this->classes(true),
            'hex' => $this->hex,
            'icon' => $this->icon,
            'display_mode' => $this->displayMode,
            'source' => $this->source,
            'store_id' => $this->storeId,
        ];
    }

    /** استخراج كلاسات light/dark/hex لاسم لون (variant) من "general". */
    private static function styleFor(string $variant): array
    {
        $item = config("status-kit-statuses.general.{$variant}", []);

        if (empty($item)) {
            $item = config('status-kit-statuses.general.gray', []);
        }

        return [
            'light' => $item['light'] ?? 'text-gray-700 bg-gray-100',
            'dark' => $item['dark'] ?? 'dark:text-gray-300 dark:bg-gray-900/40',
            'hex' => $item['hex'] ?? '#9ca3af',
        ];
    }
}
