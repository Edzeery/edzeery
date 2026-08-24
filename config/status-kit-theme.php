<?php

/**
 * إعدادات "الفريموورك" المسؤول عن شكل الألوان/البادج.
 *
 * الحزمة تدعم فريمووركين:
 * - bootstrap : يستعمل كلاسات Bootstrap 5.1+ الجاهزة (text-bg-*) المبنية على variant
 * - tailwind  : يستعمل كلاسات light/dark الموجودة يدويًا داخل كل حالة في config/statuses.php
 *
 * غيّر 'default_framework' حسب مشروعك، أو مرّر framework صراحة عند الاستدعاء:
 *   Status::for('payment','paid')->color(framework: 'tailwind')
 */

return [

    'default_framework' => 'bootstrap', // bootstrap | tailwind

    // ==========================================================
    // خريطة variant → كلاس Bootstrap 5 (badge/text/bg موحّد بكلاس واحد)
    // متوفرة افتراضيًا من Bootstrap 5.1+ بلا أي CSS إضافي.
    // ==========================================================
    'bootstrap_variants' => [
        'success' => 'text-bg-success',
        'warning' => 'text-bg-warning',
        'danger' => 'text-bg-danger',
        'info' => 'text-bg-info',
        'gray' => 'text-bg-secondary',
    ],

    // ==========================================================
    // الكلاسات الأساسية لعنصر البادج (badge()) حسب الفريموورك
    // ==========================================================
    'badge_base' => [
        'bootstrap' => 'badge d-inline-flex align-items-center gap-1',
        'tailwind' => 'status-badge inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium',
    ],

    // ==========================================================
    // كلاسات CSS لمكوّن <x-status-select> حسب الفريموورك
    // ==========================================================
    'select_classes' => [
        'bootstrap' => [
            'container' => '',
            'wrapper' => 'd-inline-flex align-items-center',
            'trigger' => 'form-control d-flex align-items-center justify-content-between',
            'trigger_sm' => 'form-control form-control-sm d-flex align-items-center justify-content-between',
            'trigger_lg' => 'form-control d-flex align-items-center justify-content-between',
            'input' => 'form-control form-control-sm',
            'menu' => 'list-unstyled mb-0',
            'option' => 'd-flex align-items-center gap-2',
            'text_truncate' => 'text-truncate',
            'text_muted' => 'text-body-secondary',
            'check_icon' => 'bi bi-check-lg',
            'hidden_input' => '',
            'overflow' => 'overflow-hidden',
            'gap_small' => 'gap-2',
            'p_1_pb_2' => 'px-1 pb-2',
            'px_2_py_1' => 'px-2 py-1',
            'small' => 'small',
            'ms_2' => 'ms-2',
            'mb_0' => 'mb-0',
            'flex_grow' => 'flex-grow-1',
        ],
        'tailwind' => [
            'container' => '',
            'wrapper' => 'inline-flex items-center',
            'trigger' => 'flex items-center justify-between w-full text-start bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm text-gray-900 dark:text-gray-100',
            'trigger_sm' => 'flex items-center justify-between w-full text-start bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md px-2 py-1 text-xs text-gray-900 dark:text-gray-100',
            'trigger_lg' => 'flex items-center justify-between w-full text-start bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-3 text-base text-gray-900 dark:text-gray-100',
            'input' => 'w-full border border-gray-300 dark:border-gray-600 rounded-md px-2 py-1 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100',
            'menu' => 'list-none mb-0',
            'option' => 'flex items-center gap-2',
            'text_truncate' => 'truncate',
            'text_muted' => 'text-gray-400 dark:text-gray-500',
            'check_icon' => 'bi bi-check-lg',
            'hidden_input' => '',
            'overflow' => 'overflow-hidden',
            'gap_small' => 'gap-2',
            'p_1_pb_2' => 'px-1 pb-2',
            'px_2_py_1' => 'px-2 py-1',
            'small' => 'text-xs',
            'ms_2' => 'ms-2',
            'mb_0' => 'mb-0',
            'flex_grow' => 'flex-grow-1',
        ],
    ],

    // ==========================================================
    // إعدادات مكوّن <x-status-select> (Custom Dropdown بـ Alpine.js)
    // ==========================================================
    'select' => [
        'max_height' => '16rem', // ارتفاع أقصى للائحة مع تمرير (scroll) تلقائي
        'z_index' => 50,      // z-index لوحة الاختيار (عدّلها إذا تعارضت مع navbar/modal فمشروعك)
        'default_set' => null,    // مجموعة الأيقونات الافتراضية للـ select؛ null = تتبع status-kit-icons.default_set
    ],

    // ==========================================================
    // إعدادات عامة
    // ==========================================================

    // مسار مجلد Heroicons SVG المخصص (null = يستخدم المجلد الداخلي للحزمة)
    'heroicon_dir' => null,

    // هل يتم تفعيل كاش ملفات SVG في الذاكرة (يُنصح بتعطيله في بيئة التطوير)
    'svg_cache' => true,

    // اللغة الافتراضية للترجمة إذا لم تُحدد (null = يستخدم app()->getLocale())
    'fallback_locale' => null,

];
