<?php

namespace App\Livewire\Concerns;

use Illuminate\Support\Facades\Validator;

/**
 * Shared inline-edit state machine for Livewire (class or Volt) components.
 *
 * Keeps the editing lifecycle (start / save / cancel), per-field validation,
 * store-scoped permission checks, optimistic rollback and audit logging in one
 * reusable trait so feature phases can attach inline editing to any Livewire
 * component without duplicating the boilerplate.
 *
 * Usage (Volt function-based component):
 *
 *     use App\Livewire\Concerns\HasInlineEdit;
 *     uses([HasInlineEdit::class]);
 *
 *     $saveName = function () {
 *         return $this->saveEdit([
 *             'field'       => 'name',
 *             'permission'  => StorePermissionEnum::PRODUCT_UPDATE->value,
 *             'rules'       => ['value' => ['required', 'min:2']],
 *             'subject'     => fn (mixed $id) => Product::where('store_id', currentStoreId())->findOrFail($id),
 *             'apply'       => fn ($model, $value) => tap($model)->update(['name' => $value]),
 *             'label'       => 'product name',
 *             'audit_event' => 'product_name_updated',
 *         ]);
 *     };
 *
 * The blade side drives the lifecycle by calling the wire methods and reading
 * $editingField / $editingId / $editingValue / $editingError.
 */
trait HasInlineEdit
{
    public ?string $editingField = null;

    public mixed $editingId = null;

    public mixed $editingValue = null;

    public ?string $editingError = null;

    public mixed $editingSnapshot = null;

    protected bool $editingSaving = false;

    /**
     * Begin editing a field. Caches the current display value so cancel can
     * restore it and the consumer can prefill the input.
     */
    public function startEdit(string $field, mixed $recordId = null, mixed $currentValue = null): void
    {
        $this->editingField = $field;
        $this->editingId = $recordId;
        $this->editingSnapshot = $currentValue;
        $this->editingValue = $currentValue;
        $this->editingError = null;
        $this->editingSaving = false;
    }

    public function cancelEdit(): void
    {
        $this->editingField = null;
        $this->editingId = null;
        $this->editingValue = $this->editingSnapshot;
        $this->editingError = null;
        $this->editingSaving = false;
    }

    /**
     * Resolve per-field validation rules (which may be a closure).
     */
    protected function inlineEditRules(array $config): array
    {
        if (is_callable($config['rules'] ?? null)) {
            return ($config['rules'])($config['field'], $this->editingValue, $this->editingId);
        }

        return $config['rules'] ?? [];
    }

    /**
     * Persist the current editing session.
     *
     * Flow: authorize -> validate -> persist -> audit. On validation failure the
     * change is NOT applied, an error is surfaced and the value is left for the
     * consumer to restore; a validation_failed audit entry is recorded so
     * failed attempts remain traceable.
     */
    public function saveEdit(array $config): void
    {
        $permission = $config['permission'] ?? null;

        if ($permission !== null) {
            abort_unless(canStore($permission), 403);
        }

        $this->editingError = null;
        $this->editingSaving = true;

        try {
            $value = $this->editingValue;

            $validator = Validator::make(
                ['value' => $value],
                $this->inlineEditRules($config),
                $config['messages'] ?? [],
            );

            if ($validator->fails()) {
                $this->editingError = $validator->errors()->first('value');

                $this->writeInlineAudit($config, $value, 'validation_failed');

                return;
            }

            $subject = $this->resolveInlineSubject($config);

            if (is_callable($config['apply'] ?? null)) {
                ($config['apply'])($subject, $value, $this->editingId);
            }

            $this->writeInlineAudit($config, $value, 'applied');

            $this->resetEditingState();
        } finally {
            $this->editingSaving = false;
        }
    }

    /**
     * Resolve the audit subject for the current editing session.
     */
    protected function resolveInlineSubject(array $config)
    {
        if (is_callable($config['subject'] ?? null)) {
            return ($config['subject'])($this->editingId);
        }

        return null;
    }

    protected function resetEditingState(): void
    {
        $this->editingField = null;
        $this->editingId = null;
        $this->editingValue = null;
        $this->editingSnapshot = null;
        $this->editingError = null;
        $this->editingSaving = false;
    }

    /**
     * Record one audit entry for an inline-edit attempt.
     */
    protected function writeInlineAudit(array $config, mixed $value, string $stage): void
    {
        $label = $config['label'] ?? $config['field'];
        $event = $config['audit_event'] ?? 'inline_edit';

        $builder = activity(config('activitylog.default_log_name', 'default'))
            ->event($stage === 'applied' ? $event : $event.'_'.$stage)
            ->withProperties([
                'field' => $config['field'],
                'value' => $value,
                'record_id' => $this->editingId,
                'stage' => $stage,
            ]);

        $user = auth()->user();
        if ($user !== null) {
            $builder->by($user);
        }

        $subject = $this->resolveInlineSubject($config);
        if ($subject !== null) {
            $builder->on($subject);
        }

        $builder->log(ucfirst($stage).' '.$label);
    }
}
