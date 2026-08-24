<?php

/**
 * ملف موحّد لكل حالات النظام (ألوان + hex + أيقونة عامة + variant).
 *
 * ── قسم _shared ──
 * الحالات المشتركة تُعرَّف مرة واحدة هنا. أي نطاق يمكنه:
 *   • استدعاؤها كـ string:           'active'
 *   • استدعاؤها مع تعديل الأيقونة:  'paid' => ['icon' => 'paid']
 *   • تعريف حالة خاصة بالنطاق:       'checkout_paid' => ['variant' => ..., ...]
 *
 * ── الأقسام العامة ──
 * - variant : success | warning | danger | info | gray
 * - light   : كلاسات Tailwind للوضع الفاتح
 * - dark    : كلاسات Tailwind للوضع الداكن
 * - hex     : اللون الأساسي كـ hex
 * - icon    : مفتاح عام يُترجم عبر config/icons.php
 *
 * التسميات النصية (Labels) موجودة في lang/{ar,en,fr}/statuses.php
 */

return [

    // ══════════════════════════════════════════════════════════════
    // الحالات المشتركة — تُعرَّف مرة واحدة وتُستعار عبر النطاقات
    // ══════════════════════════════════════════════════════════════
    '_shared' => [
        'active' => ['variant' => 'success', 'light' => 'text-green-700 bg-green-100',   'dark' => 'dark:text-green-300 dark:bg-green-900/40',  'hex' => '#16a34a', 'icon' => 'active'],
        'inactive' => ['variant' => 'gray',    'light' => 'text-gray-700 bg-gray-100',      'dark' => 'dark:text-gray-300 dark:bg-gray-800',        'hex' => '#9ca3af', 'icon' => 'inactive'],
        'pending' => ['variant' => 'warning', 'light' => 'text-yellow-700 bg-yellow-100',  'dark' => 'dark:text-yellow-300 dark:bg-yellow-900/40', 'hex' => '#facc15', 'icon' => 'pending'],
        'approved' => ['variant' => 'success', 'light' => 'text-green-700 bg-green-100',   'dark' => 'dark:text-green-300 dark:bg-green-900/40',  'hex' => '#16a34a', 'icon' => 'approved'],
        'rejected' => ['variant' => 'danger',  'light' => 'text-red-700 bg-red-100',        'dark' => 'dark:text-red-300 dark:bg-red-900/40',       'hex' => '#dc2626', 'icon' => 'rejected'],
        'suspended' => ['variant' => 'danger',  'light' => 'text-red-700 bg-red-100',        'dark' => 'dark:text-red-300 dark:bg-red-900/40',       'hex' => '#dc2626', 'icon' => 'suspended'],
        'blocked' => ['variant' => 'danger',  'light' => 'text-red-700 bg-red-100',        'dark' => 'dark:text-red-300 dark:bg-red-900/40',       'hex' => '#dc2626', 'icon' => 'blocked'],
        'banned' => ['variant' => 'danger',  'light' => 'text-red-800 bg-red-200',        'dark' => 'dark:text-red-400 dark:bg-red-950',          'hex' => '#b91c1c', 'icon' => 'banned'],
        'draft' => ['variant' => 'info',    'light' => 'text-blue-700 bg-blue-100',      'dark' => 'dark:text-blue-300 dark:bg-blue-900/40',     'hex' => '#2563eb', 'icon' => 'draft'],
        'completed' => ['variant' => 'success', 'light' => 'text-green-700 bg-green-100',   'dark' => 'dark:text-green-300 dark:bg-green-900/40',  'hex' => '#16a34a', 'icon' => 'completed'],
        'cancelled' => ['variant' => 'danger',  'light' => 'text-red-700 bg-red-100',        'dark' => 'dark:text-red-300 dark:bg-red-900/40',       'hex' => '#dc2626', 'icon' => 'cancelled'],
        'canceled' => ['variant' => 'gray',    'light' => 'text-gray-700 bg-gray-100',      'dark' => 'dark:text-gray-300 dark:bg-gray-800',        'hex' => '#9ca3af', 'icon' => 'canceled'],
        'failed' => ['variant' => 'danger',  'light' => 'text-red-700 bg-red-100',        'dark' => 'dark:text-red-300 dark:bg-red-900/40',       'hex' => '#dc2626', 'icon' => 'failed'],
        'paid' => ['variant' => 'success', 'light' => 'text-green-700 bg-green-100',   'dark' => 'dark:text-green-300 dark:bg-green-900/40',  'hex' => '#16a34a', 'icon' => 'paid'],
        'unpaid' => ['variant' => 'danger',  'light' => 'text-red-700 bg-red-100',        'dark' => 'dark:text-red-300 dark:bg-red-900/40',       'hex' => '#dc2626', 'icon' => 'unpaid'],
        'refunded' => ['variant' => 'info',    'light' => 'text-blue-700 bg-blue-100',      'dark' => 'dark:text-blue-300 dark:bg-blue-900/40',     'hex' => '#2563eb', 'icon' => 'refunded'],
        'expired' => ['variant' => 'gray',    'light' => 'text-gray-700 bg-gray-100',      'dark' => 'dark:text-gray-300 dark:bg-gray-800',        'hex' => '#9ca3af', 'icon' => 'expired'],
        'processing' => ['variant' => 'info',    'light' => 'text-blue-700 bg-blue-100',      'dark' => 'dark:text-blue-300 dark:bg-blue-900/40',     'hex' => '#2563eb', 'icon' => 'processing'],
        'closed' => ['variant' => 'gray',    'light' => 'text-gray-700 bg-gray-100',      'dark' => 'dark:text-gray-300 dark:bg-gray-800',        'hex' => '#9ca3af', 'icon' => 'closed'],
        'online' => ['variant' => 'success', 'light' => 'text-green-700 bg-green-100',   'dark' => 'dark:text-green-300 dark:bg-green-900/40',  'hex' => '#16a34a', 'icon' => 'online'],
        'offline' => ['variant' => 'gray',    'light' => 'text-gray-700 bg-gray-100',      'dark' => 'dark:text-gray-300 dark:bg-gray-800',        'hex' => '#9ca3af', 'icon' => 'offline'],
        'verified' => ['variant' => 'success', 'light' => 'text-green-700 bg-green-100',   'dark' => 'dark:text-green-300 dark:bg-green-900/40',  'hex' => '#16a34a', 'icon' => 'verified'],
        'unverified' => ['variant' => 'gray',    'light' => 'text-gray-700 bg-gray-100',      'dark' => 'dark:text-gray-300 dark:bg-gray-800',        'hex' => '#9ca3af', 'icon' => 'unverified'],
        'available' => ['variant' => 'success', 'light' => 'text-green-700 bg-green-100',   'dark' => 'dark:text-green-300 dark:bg-green-900/40',  'hex' => '#16a34a', 'icon' => 'available'],
        'unavailable' => ['variant' => 'gray',    'light' => 'text-gray-700 bg-gray-100',      'dark' => 'dark:text-gray-300 dark:bg-gray-800',        'hex' => '#9ca3af', 'icon' => 'unavailable'],
        'deleted' => ['variant' => 'danger',  'light' => 'text-red-700 bg-red-100',        'dark' => 'dark:text-red-300 dark:bg-red-900/40',       'hex' => '#dc2626', 'icon' => 'deleted'],
        'restored' => ['variant' => 'success', 'light' => 'text-green-700 bg-green-100',   'dark' => 'dark:text-green-300 dark:bg-green-900/40',  'hex' => '#16a34a', 'icon' => 'restored'],
        'published' => ['variant' => 'success', 'light' => 'text-green-700 bg-green-100',   'dark' => 'dark:text-green-300 dark:bg-green-900/40',  'hex' => '#16a34a', 'icon' => 'published'],
        'unpublished' => ['variant' => 'gray',    'light' => 'text-gray-700 bg-gray-100',      'dark' => 'dark:text-gray-300 dark:bg-gray-800',        'hex' => '#9ca3af', 'icon' => 'unpublished'],
        'archived' => ['variant' => 'gray',    'light' => 'text-gray-700 bg-gray-100',      'dark' => 'dark:text-gray-300 dark:bg-gray-800',        'hex' => '#9ca3af', 'icon' => 'archived'],
        'expand' => ['variant' => 'info',    'light' => 'text-blue-700 bg-blue-100',      'dark' => 'dark:text-blue-300 dark:bg-blue-900/40',     'hex' => '#2563eb', 'icon' => 'expand'],
        'collapse' => ['variant' => 'info',    'light' => 'text-blue-700 bg-blue-100',      'dark' => 'dark:text-blue-300 dark:bg-blue-900/40',     'hex' => '#2563eb', 'icon' => 'collapse'],
        'default' => ['variant' => 'gray',    'light' => 'text-gray-700 bg-gray-100',      'dark' => 'dark:text-gray-300 dark:bg-gray-800',        'hex' => '#9ca3af', 'icon' => 'default'],
    ],

    // ══════════════════════════════════════════════════════════════
    // نطاقات الدفع
    // ══════════════════════════════════════════════════════════════
    'payment' => [
        'paid' => 'paid',
        'pending' => 'pending',
        'completed' => 'completed',
        'failed' => 'failed',
        'refunded' => 'refunded',
        'canceled' => 'canceled',
        'checkout_paid' => ['icon' => 'paid'],
        'checkout_pending' => ['icon' => 'pending'],
        'checkout_failed' => ['icon' => 'failed'],
        'checkout_canceled' => ['variant' => 'danger', 'light' => 'text-red-700 bg-red-100', 'dark' => 'dark:text-red-300 dark:bg-red-900/40', 'hex' => '#dc2626', 'icon' => 'canceled'],
        'checkout_expired' => ['icon' => 'expired'],
    ],

    // ══════════════════════════════════════════════════════════════
    // الاشتراكات
    // ══════════════════════════════════════════════════════════════
    'subscription' => [
        'active' => 'active',
        'pending' => 'pending',
        'trialing' => ['variant' => 'info', 'light' => 'text-blue-700 bg-blue-100', 'dark' => 'dark:text-blue-300 dark:bg-blue-900/40', 'hex' => '#2563eb', 'icon' => 'active'],
        'past_due' => ['variant' => 'warning', 'light' => 'text-orange-700 bg-orange-100', 'dark' => 'dark:text-orange-300 dark:bg-orange-900/40', 'hex' => '#ea580c', 'icon' => 'pending'],
        'expired' => 'expired',
        'canceled' => 'canceled',
        'suspended' => 'suspended',
    ],

    // ══════════════════════════════════════════════════════════════
    // المستخدمون
    // ══════════════════════════════════════════════════════════════
    'user' => [
        'active' => 'active',
        'inactive' => 'inactive',
        'pending' => 'pending',
        'suspended' => 'suspended',
        'banned' => 'banned',
        'email_unverified' => ['variant' => 'gray', 'light' => 'text-gray-700 bg-gray-100', 'dark' => 'dark:text-gray-300 dark:bg-gray-800', 'hex' => '#9ca3af', 'icon' => 'email_unverified'],
        'online' => 'online',
        'offline' => 'offline',
    ],

    // ══════════════════════════════════════════════════════════════
    // التحقق من البريد
    // ══════════════════════════════════════════════════════════════
    'email_verification' => [
        'email_verified' => ['icon' => 'verified'],
        'email_unverified' => ['variant' => 'gray', 'light' => 'text-gray-700 bg-gray-100', 'dark' => 'dark:text-gray-300 dark:bg-gray-800', 'hex' => '#9ca3af', 'icon' => 'email_unverified'],
    ],

    // ══════════════════════════════════════════════════════════════
    // الاتصال
    // ══════════════════════════════════════════════════════════════
    'online_status' => [
        'online' => 'online',
        'offline' => 'offline',
    ],

    // ══════════════════════════════════════════════════════════════
    // المتاجر
    // ══════════════════════════════════════════════════════════════
    'stores' => [
        'active' => 'active',
        'pending' => 'pending',
        'suspended' => 'suspended',
        'closed' => 'closed',
        'draft' => 'draft',
        'blocked' => 'blocked',
        'approved' => 'approved',
        'rejected' => 'rejected',
    ],

    // ══════════════════════════════════════════════════════════════
    // الطلبات
    // ══════════════════════════════════════════════════════════════
    'order' => [
        'pending' => 'pending',
        'processing' => 'processing',
        'completed' => 'completed',
        'canceled' => 'canceled',
        'refunded' => 'refunded',
        'confirmed' => ['icon' => 'completed'],
        'preparing' => ['variant' => 'info', 'light' => 'text-blue-700 bg-blue-100', 'dark' => 'dark:text-blue-300 dark:bg-blue-900/40', 'hex' => '#2563eb', 'icon' => 'processing'],
        'shipped' => ['variant' => 'info', 'light' => 'text-blue-700 bg-blue-100', 'dark' => 'dark:text-blue-300 dark:bg-blue-900/40', 'hex' => '#2563eb', 'icon' => 'send'],
        'delivered' => ['icon' => 'completed'],
        'returned' => ['variant' => 'warning', 'light' => 'text-yellow-700 bg-yellow-100', 'dark' => 'dark:text-yellow-300 dark:bg-yellow-900/40', 'hex' => '#facc15', 'icon' => 'pending'],
        'in_transit' => ['variant' => 'info', 'light' => 'text-blue-700 bg-blue-100', 'dark' => 'dark:text-blue-300 dark:bg-blue-900/40', 'hex' => '#2563eb', 'icon' => 'processing'],
        'on_hold' => ['variant' => 'warning', 'light' => 'text-yellow-700 bg-yellow-100', 'dark' => 'dark:text-yellow-300 dark:bg-yellow-900/40', 'hex' => '#facc15', 'icon' => 'pending'],
        'out_for_delivery' => ['variant' => 'info', 'light' => 'text-blue-700 bg-blue-100', 'dark' => 'dark:text-blue-300 dark:bg-blue-900/40', 'hex' => '#2563eb', 'icon' => 'active'],
    ],

    // ══════════════════════════════════════════════════════════════
    // المنتجات
    // ══════════════════════════════════════════════════════════════
    'product' => [
        'active' => 'active',
        'inactive' => 'inactive',
        'out_of_stock' => ['variant' => 'warning', 'light' => 'text-yellow-700 bg-yellow-100', 'dark' => 'dark:text-yellow-300 dark:bg-yellow-900/40', 'hex' => '#facc15', 'icon' => 'out_of_stock'],
        'discontinued' => ['variant' => 'danger', 'light' => 'text-red-700 bg-red-100', 'dark' => 'dark:text-red-300 dark:bg-red-900/40', 'hex' => '#dc2626', 'icon' => 'discontinued'],
    ],

    // ══════════════════════════════════════════════════════════════
    // الأدوار
    // ══════════════════════════════════════════════════════════════
    'role' => [
        'super_admin' => ['variant' => 'danger', 'light' => 'text-red-700 bg-red-100', 'dark' => 'dark:text-red-400 dark:bg-red-950', 'hex' => '#991b1b', 'icon' => 'shield-exclamation'],
        'admin' => ['variant' => 'warning', 'light' => 'text-yellow-700 bg-yellow-100', 'dark' => 'dark:text-yellow-400 dark:bg-yellow-950', 'hex' => '#f59e0b', 'icon' => 'shield-check'],
        'support_agent' => ['variant' => 'info', 'light' => 'text-blue-700 bg-blue-100', 'dark' => 'dark:text-blue-400 dark:bg-blue-950', 'hex' => '#2563eb', 'icon' => 'chat-bubble'],
        'tech_support' => ['variant' => 'gray', 'light' => 'text-gray-600 bg-gray-100', 'dark' => 'dark:text-gray-400 dark:bg-gray-900', 'hex' => '#6b7280', 'icon' => 'wrench-screwdriver'],
        'merchant' => ['variant' => 'success', 'light' => 'text-green-700 bg-green-100', 'dark' => 'dark:text-green-400 dark:bg-green-950', 'hex' => '#16a34a', 'icon' => 'building-storefront'],
        'user' => ['variant' => 'info', 'light' => 'text-blue-600 bg-blue-100', 'dark' => 'dark:text-blue-400 dark:bg-blue-950', 'hex' => '#3b82f6', 'icon' => 'user-circle'],
        'owner' => ['variant' => 'success', 'light' => 'text-green-700 bg-green-100', 'dark' => 'dark:text-green-400 dark:bg-green-950', 'hex' => '#16a34a', 'icon' => 'shield-check'],
        'manager' => ['variant' => 'warning', 'light' => 'text-yellow-700 bg-yellow-100', 'dark' => 'dark:text-yellow-400 dark:bg-yellow-950', 'hex' => '#ca8a04', 'icon' => 'clipboard-check'],
        'staff' => ['variant' => 'gray', 'light' => 'text-gray-600 bg-gray-100', 'dark' => 'dark:text-gray-400 dark:bg-gray-900', 'hex' => '#9ca3af', 'icon' => 'user'],
        'guest' => ['variant' => 'info', 'light' => 'text-blue-600 bg-blue-100', 'dark' => 'dark:text-blue-400 dark:bg-blue-950', 'hex' => '#3b82f6', 'icon' => 'user'],
        'moderator' => ['variant' => 'danger', 'light' => 'text-red-700 bg-red-100', 'dark' => 'dark:text-red-400 dark:bg-red-950', 'hex' => '#ef4444', 'icon' => 'shield-exclamation'],
        'editor' => ['variant' => 'info', 'light' => 'text-blue-700 bg-blue-100', 'dark' => 'dark:text-blue-400 dark:bg-blue-950', 'hex' => '#2563eb', 'icon' => 'pencil-square'],
        'support_team' => ['variant' => 'info', 'light' => 'text-blue-700 bg-blue-100', 'dark' => 'dark:text-blue-400 dark:bg-blue-950', 'hex' => '#2563eb', 'icon' => 'headset'],
        'customer' => ['variant' => 'success', 'light' => 'text-green-700 bg-green-100', 'dark' => 'dark:text-green-400 dark:bg-green-950', 'hex' => '#16a34a', 'icon' => 'user-check'],
        'billing_manager' => ['variant' => 'warning', 'light' => 'text-yellow-700 bg-yellow-100', 'dark' => 'dark:text-yellow-400 dark:bg-yellow-950', 'hex' => '#f59e0b', 'icon' => 'credit-card'],
        'technical_team' => ['variant' => 'info', 'light' => 'text-blue-700 bg-blue-100', 'dark' => 'dark:text-blue-400 dark:bg-blue-950', 'hex' => '#2563eb', 'icon' => 'wrench-screwdriver'],
        'qa_team' => ['variant' => 'gray', 'light' => 'text-gray-600 bg-gray-100', 'dark' => 'dark:text-gray-400 dark:bg-gray-900', 'hex' => '#6b7280', 'icon' => 'beaker'],
        'platform_manager' => ['variant' => 'success', 'light' => 'text-green-700 bg-green-100', 'dark' => 'dark:text-green-400 dark:bg-green-950', 'hex' => '#16a34a', 'icon' => 'server'],
        'deputy_super_admin' => ['variant' => 'danger', 'light' => 'text-red-700 bg-red-100', 'dark' => 'dark:text-red-400 dark:bg-red-950', 'hex' => '#991b1b', 'icon' => 'shield-exclamation'],
    ],

    // ══════════════════════════════════════════════════════════════
    // الفواتير
    // ══════════════════════════════════════════════════════════════
    'invoice' => [
        'draft' => 'draft',
        'paid' => 'paid',
        'overdue' => ['variant' => 'danger', 'light' => 'text-red-700 bg-red-100', 'dark' => 'dark:text-red-300 dark:bg-red-900/40', 'hex' => '#dc2626', 'icon' => 'pending'],
        'cancelled' => 'cancelled',
    ],

    // ══════════════════════════════════════════════════════════════
    // الإشعارات
    // ══════════════════════════════════════════════════════════════
    'notification' => [
        'new_user' => ['variant' => 'info', 'light' => 'text-blue-700 bg-blue-100', 'dark' => 'dark:text-blue-300 dark:bg-blue-900/40', 'hex' => '#2563eb', 'icon' => 'new_user'],
        'new_payment' => ['variant' => 'success', 'light' => 'text-green-700 bg-green-100', 'dark' => 'dark:text-green-300 dark:bg-green-900/40', 'hex' => '#16a34a', 'icon' => 'new_payment'],
        'subscription_activated' => ['variant' => 'info', 'light' => 'text-purple-700 bg-purple-100', 'dark' => 'dark:text-purple-300 dark:bg-purple-900/40', 'hex' => '#9333ea', 'icon' => 'subscription_activated'],
        'backup_completed' => ['variant' => 'gray', 'light' => 'text-gray-700 bg-gray-100', 'dark' => 'dark:text-gray-300 dark:bg-gray-800', 'hex' => '#6b7280', 'icon' => 'backup_completed'],
        'system_alert' => ['variant' => 'warning', 'light' => 'text-yellow-700 bg-yellow-100', 'dark' => 'dark:text-yellow-300 dark:bg-yellow-900/40', 'hex' => '#d97706', 'icon' => 'system_alert'],
        'delete' => ['variant' => 'danger', 'light' => 'text-red-700 bg-red-100', 'dark' => 'dark:text-red-300 dark:bg-red-900/40', 'hex' => '#dc2626', 'icon' => 'delete'],
    ],

    // ══════════════════════════════════════════════════════════════
    // الأهداف
    // ══════════════════════════════════════════════════════════════
    'goal' => [
        'in_progress' => ['variant' => 'info', 'light' => 'text-blue-700 bg-blue-100', 'dark' => 'dark:text-blue-300 dark:bg-blue-900/40', 'hex' => '#2563eb', 'icon' => 'pending'],
        'active' => 'active',
        'completed' => 'completed',
        'cancelled' => 'cancelled',
    ],

    // ══════════════════════════════════════════════════════════════
    // الديون
    // ══════════════════════════════════════════════════════════════
    'debt' => [
        'active' => 'active',
        'partial' => ['variant' => 'warning', 'light' => 'text-yellow-700 bg-yellow-100', 'dark' => 'dark:text-yellow-300 dark:bg-yellow-900/40', 'hex' => '#f59e0b', 'icon' => 'pending'],
        'paid' => 'paid',
        'overdue' => ['variant' => 'danger', 'light' => 'text-red-700 bg-red-100', 'dark' => 'dark:text-red-300 dark:bg-red-900/40', 'hex' => '#dc2626', 'icon' => 'failed'],
    ],

    'debt_type' => [
        'owed' => ['variant' => 'warning', 'light' => 'text-yellow-700 bg-yellow-100', 'dark' => 'dark:text-yellow-300 dark:bg-yellow-900/40', 'hex' => '#f59e0b', 'icon' => 'pending'],
        'owing' => ['variant' => 'info', 'light' => 'text-blue-700 bg-blue-100', 'dark' => 'dark:text-blue-300 dark:bg-blue-900/40', 'hex' => '#2563eb', 'icon' => 'active'],
    ],

    // ══════════════════════════════════════════════════════════════
    // الأصول
    // ══════════════════════════════════════════════════════════════
    'asset' => [
        'cash' => ['icon' => 'active'],
        'bank_account' => ['variant' => 'info', 'light' => 'text-blue-700 bg-blue-100', 'dark' => 'dark:text-blue-300 dark:bg-blue-900/40', 'hex' => '#2563eb', 'icon' => 'wallet'],
        'ccp' => ['variant' => 'info', 'light' => 'text-blue-700 bg-blue-100', 'dark' => 'dark:text-blue-300 dark:bg-blue-900/40', 'hex' => '#3b82f6', 'icon' => 'wallet'],
        'gold' => ['variant' => 'warning', 'light' => 'text-yellow-700 bg-yellow-100', 'dark' => 'dark:text-yellow-300 dark:bg-yellow-900/40', 'hex' => '#f59e0b', 'icon' => 'gold'],
        'silver' => ['variant' => 'gray', 'light' => 'text-gray-700 bg-gray-100', 'dark' => 'dark:text-gray-300 dark:bg-gray-800', 'hex' => '#9ca3af', 'icon' => 'default'],
        'real_estate' => ['variant' => 'success', 'light' => 'text-green-700 bg-green-100', 'dark' => 'dark:text-green-300 dark:bg-green-900/40', 'hex' => '#22c55e', 'icon' => 'home'],
        'stocks' => ['variant' => 'info', 'light' => 'text-blue-700 bg-blue-100', 'dark' => 'dark:text-blue-300 dark:bg-blue-900/40', 'hex' => '#8b5cf6', 'icon' => 'chart'],
        'crypto' => ['variant' => 'warning', 'light' => 'text-orange-700 bg-orange-100', 'dark' => 'dark:text-orange-300 dark:bg-orange-900/40', 'hex' => '#f97316', 'icon' => 'pending'],
        'other' => ['variant' => 'gray', 'light' => 'text-gray-700 bg-gray-100', 'dark' => 'dark:text-gray-300 dark:bg-gray-800', 'hex' => '#64748b', 'icon' => 'default'],
    ],

    // ══════════════════════════════════════════════════════════════
    // الميزانيات
    // ══════════════════════════════════════════════════════════════
    'budget' => [
        'active' => 'active',
        'inactive' => 'inactive',
        'draft' => 'draft',
        'approved' => 'approved',
        'rejected' => 'rejected',
        'closed' => 'closed',
        'overspent' => ['variant' => 'danger', 'light' => 'text-red-700 bg-red-100', 'dark' => 'dark:text-red-300 dark:bg-red-900/40', 'hex' => '#dc2626', 'icon' => 'failed'],
        'on_track' => ['icon' => 'completed'],
        'at_risk' => ['variant' => 'warning', 'light' => 'text-yellow-700 bg-yellow-100', 'dark' => 'dark:text-yellow-300 dark:bg-yellow-900/40', 'hex' => '#facc15', 'icon' => 'warning'],
    ],

    // ══════════════════════════════════════════════════════════════
    // المصروفات
    // ══════════════════════════════════════════════════════════════
    'expense' => [
        'draft' => 'draft',
        'pending' => 'pending',
        'approved' => 'approved',
        'rejected' => 'rejected',
        'paid' => 'paid',
        'partially_paid' => ['variant' => 'warning', 'light' => 'text-yellow-700 bg-yellow-100', 'dark' => 'dark:text-yellow-300 dark:bg-yellow-900/40', 'hex' => '#facc15', 'icon' => 'pending'],
        'overdue' => ['variant' => 'danger', 'light' => 'text-red-700 bg-red-100', 'dark' => 'dark:text-red-300 dark:bg-red-900/40', 'hex' => '#dc2626', 'icon' => 'failed'],
        'cancelled' => 'cancelled',
        'refunded' => 'refunded',
    ],

    // ══════════════════════════════════════════════════════════════
    // الإيرادات
    // ══════════════════════════════════════════════════════════════
    'income' => [
        'pending' => 'pending',
        'confirmed' => ['icon' => 'completed'],
        'received' => ['icon' => 'paid'],
        'partial' => ['variant' => 'warning', 'light' => 'text-yellow-700 bg-yellow-100', 'dark' => 'dark:text-yellow-300 dark:bg-yellow-900/40', 'hex' => '#facc15', 'icon' => 'pending'],
        'overdue' => ['variant' => 'danger', 'light' => 'text-red-700 bg-red-100', 'dark' => 'dark:text-red-300 dark:bg-red-900/40', 'hex' => '#dc2626', 'icon' => 'failed'],
        'cancelled' => 'cancelled',
        'refunded' => 'refunded',
    ],

    // ══════════════════════════════════════════════════════════════
    // المعاملات المالية
    // ══════════════════════════════════════════════════════════════
    'transaction' => [
        'pending' => 'pending',
        'processing' => 'processing',
        'completed' => 'completed',
        'failed' => 'failed',
        'cancelled' => 'cancelled',
        'reversed' => ['variant' => 'info', 'light' => 'text-blue-700 bg-blue-100', 'dark' => 'dark:text-blue-300 dark:bg-blue-900/40', 'hex' => '#2563eb', 'icon' => 'refunded'],
        'disputed' => ['variant' => 'danger', 'light' => 'text-red-700 bg-red-100', 'dark' => 'dark:text-red-300 dark:bg-red-900/40', 'hex' => '#dc2626', 'icon' => 'failed'],
        'refunded' => 'refunded',
    ],

    // ══════════════════════════════════════════════════════════════
    // الحسابات المالية
    // ══════════════════════════════════════════════════════════════
    'account' => [
        'active' => 'active',
        'inactive' => 'inactive',
        'frozen' => ['variant' => 'info', 'light' => 'text-blue-700 bg-blue-100', 'dark' => 'dark:text-blue-300 dark:bg-blue-900/40', 'hex' => '#2563eb', 'icon' => 'locked'],
        'suspended' => 'suspended',
        'closed' => 'closed',
        'pending_verification' => ['variant' => 'warning', 'light' => 'text-yellow-700 bg-yellow-100', 'dark' => 'dark:text-yellow-300 dark:bg-yellow-900/40', 'hex' => '#facc15', 'icon' => 'pending'],
        'overdrawn' => ['variant' => 'danger', 'light' => 'text-red-700 bg-red-100', 'dark' => 'dark:text-red-300 dark:bg-red-900/40', 'hex' => '#dc2626', 'icon' => 'failed'],
    ],

    // ══════════════════════════════════════════════════════════════
    // الدومين العام — بادجات + أيقونات قابلة لإعادة الاستعمال
    // ══════════════════════════════════════════════════════════════
    'general' => [
        // ── حالات مشتركة (من _shared) ──
        'active' => 'active', 'inactive' => 'inactive', 'pending' => 'pending',
        'approved' => 'approved', 'rejected' => 'rejected', 'suspended' => 'suspended',
        'blocked' => 'blocked', 'banned' => 'banned', 'draft' => 'draft',
        'completed' => 'completed', 'cancelled' => 'cancelled', 'canceled' => 'canceled',
        'failed' => 'failed', 'paid' => 'paid', 'unpaid' => 'unpaid',
        'refunded' => 'refunded', 'expired' => 'expired', 'processing' => 'processing',
        'closed' => 'closed', 'online' => 'online', 'offline' => 'offline',
        'archived' => 'archived', 'deleted' => 'deleted',

        // ── ألوان عامة ──
        'info' => ['variant' => 'info',    'light' => 'text-blue-700 bg-blue-100', 'dark' => 'dark:text-blue-300 dark:bg-blue-900/40', 'hex' => '#2563eb', 'icon' => 'info'],
        'success' => ['variant' => 'success', 'light' => 'text-green-700 bg-green-100', 'dark' => 'dark:text-green-300 dark:bg-green-900/40', 'hex' => '#16a34a', 'icon' => 'success'],
        'warning' => ['variant' => 'warning', 'light' => 'text-yellow-700 bg-yellow-100', 'dark' => 'dark:text-yellow-300 dark:bg-yellow-900/40', 'hex' => '#facc15', 'icon' => 'warning'],
        'danger' => ['variant' => 'danger',  'light' => 'text-red-700 bg-red-100', 'dark' => 'dark:text-red-300 dark:bg-red-900/40', 'hex' => '#dc2626', 'icon' => 'failed'],
        'gray' => ['variant' => 'gray',    'light' => 'text-gray-700 bg-gray-100', 'dark' => 'dark:text-gray-300 dark:bg-gray-800', 'hex' => '#9ca3af', 'icon' => 'default'],

        // ── بادجات جاهزة ──
        'featured' => ['variant' => 'warning', 'light' => 'text-yellow-700 bg-yellow-100', 'dark' => 'dark:text-yellow-300 dark:bg-yellow-900/40', 'hex' => '#facc15', 'icon' => 'featured'],
        'new' => ['variant' => 'info',    'light' => 'text-blue-700 bg-blue-100', 'dark' => 'dark:text-blue-300 dark:bg-blue-900/40', 'hex' => '#2563eb', 'icon' => 'new'],
        'popular' => ['variant' => 'danger',  'light' => 'text-red-700 bg-red-100', 'dark' => 'dark:text-red-300 dark:bg-red-900/40', 'hex' => '#dc2626', 'icon' => 'popular'],
        'verified' => ['icon' => 'verified'],
        'recommended' => ['variant' => 'info',    'light' => 'text-blue-700 bg-blue-100', 'dark' => 'dark:text-blue-300 dark:bg-blue-900/40', 'hex' => '#2563eb', 'icon' => 'recommended'],
        'limited' => ['variant' => 'warning', 'light' => 'text-yellow-700 bg-yellow-100', 'dark' => 'dark:text-yellow-300 dark:bg-yellow-900/40', 'hex' => '#facc15', 'icon' => 'limited'],

        // ── نعم/لا، تشغيل/إيقاف ──
        'yes' => ['variant' => 'success', 'light' => 'text-green-700 bg-green-100', 'dark' => 'dark:text-green-300 dark:bg-green-900/40', 'hex' => '#16a34a', 'icon' => 'yes'],
        'no' => ['variant' => 'danger',  'light' => 'text-red-700 bg-red-100', 'dark' => 'dark:text-red-300 dark:bg-red-900/40', 'hex' => '#dc2626', 'icon' => 'no'],
        'on' => ['icon' => 'on'],
        'off' => ['icon' => 'off'],
        'enable' => ['icon' => 'enable'],
        'disable' => ['icon' => 'disable'],

        // ── فتح/إغلاق/حفظ/إضافة/إزالة ──
        'open' => ['variant' => 'info',    'light' => 'text-blue-700 bg-blue-100', 'dark' => 'dark:text-blue-300 dark:bg-blue-900/40', 'hex' => '#2563eb', 'icon' => 'open'],
        'close' => ['icon' => 'close'],
        'save' => ['icon' => 'save'],
        'add' => ['icon' => 'add'],
        'remove' => ['icon' => 'remove'],

        // ── عرض/بريد/هاتف/تقويم/مجلد/صورة/رسالة ──
        'view' => ['icon' => 'view'],
        'email' => ['icon' => 'email'],
        'phone' => ['icon' => 'phone'],
        'calendar' => ['icon' => 'calendar'],
        'folder' => ['variant' => 'warning', 'light' => 'text-yellow-700 bg-yellow-100', 'dark' => 'dark:text-yellow-300 dark:bg-yellow-900/40', 'hex' => '#facc15', 'icon' => 'folder'],
        'image' => ['icon' => 'image'],
        'message' => ['icon' => 'message'],
        'send' => ['icon' => 'send'],
        'share' => ['icon' => 'share'],

        // ── سلة/نجمة/قلب/علم ──
        'cart' => ['icon' => 'cart'],
        'star' => ['variant' => 'warning', 'light' => 'text-yellow-700 bg-yellow-100', 'dark' => 'dark:text-yellow-300 dark:bg-yellow-900/40', 'hex' => '#facc15', 'icon' => 'star'],
        'heart' => ['icon' => 'heart'],
        'flag' => ['icon' => 'flag'],

        // ── تحقق/خطأ/إضافة/طرح ──
        'check' => ['icon' => 'check'],
        'cross' => ['icon' => 'cross'],
        'plus' => ['icon' => 'plus'],
        'minus' => ['icon' => 'minus'],

        // ── مفتاح/قفل/فتح/رابط/مساعدة/تحديث ──
        'key' => ['variant' => 'warning', 'light' => 'text-yellow-700 bg-yellow-100', 'dark' => 'dark:text-yellow-300 dark:bg-yellow-900/40', 'hex' => '#facc15', 'icon' => 'key'],
        'lock' => ['icon' => 'lock'],
        'unlock' => ['icon' => 'unlock'],
        'link' => ['icon' => 'link'],
        'help' => ['icon' => 'help'],
        'refresh' => ['icon' => 'refresh'],

        // ── عين/إخفاء ──
        'eye' => ['icon' => 'eye'],
        'eye-off' => ['icon' => 'eye-off'],

        // ── الوسائط ──
        'play' => ['icon' => 'play'],
        'pause' => ['variant' => 'warning', 'light' => 'text-yellow-700 bg-yellow-100', 'dark' => 'dark:text-yellow-300 dark:bg-yellow-900/40', 'hex' => '#facc15', 'icon' => 'pause'],
        'stop' => ['icon' => 'stop'],
        'rewind' => ['icon' => 'rewind'],
        'forward' => ['icon' => 'forward'],
        'next' => ['icon' => 'next'],
        'prev' => ['icon' => 'prev'],
        'repeat' => ['icon' => 'repeat'],
        'shuffle' => ['icon' => 'shuffle'],
        'volume' => ['icon' => 'volume'],
        'volume-mute' => ['icon' => 'volume-mute'],
        'play-circle' => ['icon' => 'play-circle'],
        'pause-circle' => ['variant' => 'warning', 'light' => 'text-yellow-700 bg-yellow-100', 'dark' => 'dark:text-yellow-300 dark:bg-yellow-900/40', 'hex' => '#facc15', 'icon' => 'pause-circle'],

        // ── دورة حياة ──
        'beta' => ['variant' => 'info', 'light' => 'text-blue-700 bg-blue-100', 'dark' => 'dark:text-blue-300 dark:bg-blue-900/40', 'hex' => '#2563eb', 'icon' => 'beta'],
        'deprecated' => ['icon' => 'deprecated'],
        'locked' => ['icon' => 'locked'],
        'unlocked' => ['icon' => 'unlocked'],
        'highlighted' => ['variant' => 'warning', 'light' => 'text-yellow-700 bg-yellow-100', 'dark' => 'dark:text-yellow-300 dark:bg-yellow-900/40', 'hex' => '#facc15', 'icon' => 'highlighted'],
        'trending' => ['icon' => 'trending'],

        // ── حالات إضافية (v1.1.4) ──
        'test' => ['variant' => 'info',    'light' => 'text-blue-700 bg-blue-100', 'dark' => 'dark:text-blue-300 dark:bg-blue-900/40', 'hex' => '#2563eb', 'icon' => 'info'],
        'live' => ['icon' => 'active'],
        'sandbox' => ['variant' => 'warning', 'light' => 'text-yellow-700 bg-yellow-100', 'dark' => 'dark:text-yellow-300 dark:bg-yellow-900/40', 'hex' => '#facc15', 'icon' => 'pending'],
        'enabled' => ['icon' => 'enable'],
        'disabled' => ['icon' => 'disable'],
        'visible' => ['icon' => 'eye'],
        'hidden' => ['icon' => 'eye-off'],
        'restored' => ['icon' => 'restored'],
        'under_review' => ['variant' => 'warning', 'light' => 'text-yellow-700 bg-yellow-100', 'dark' => 'dark:text-yellow-300 dark:bg-yellow-900/40', 'hex' => '#facc15', 'icon' => 'pending'],
        'unverified' => ['icon' => 'unverified'],
        'published' => ['icon' => 'published'],
        'unpublished' => ['icon' => 'unpublished'],
        'reviewed' => ['icon' => 'approved'],
        'submitted' => ['variant' => 'info', 'light' => 'text-blue-700 bg-blue-100', 'dark' => 'dark:text-blue-300 dark:bg-blue-900/40', 'hex' => '#2563eb', 'icon' => 'send'],
        'partially_paid' => ['variant' => 'warning', 'light' => 'text-yellow-700 bg-yellow-100', 'dark' => 'dark:text-yellow-300 dark:bg-yellow-900/40', 'hex' => '#facc15', 'icon' => 'pending'],
        'partially_refunded' => ['variant' => 'info', 'light' => 'text-blue-700 bg-blue-100', 'dark' => 'dark:text-blue-300 dark:bg-blue-900/40', 'hex' => '#2563eb', 'icon' => 'refunded'],
        'processing_payment' => ['icon' => 'processing'],
        'awaiting_payment' => ['variant' => 'warning', 'light' => 'text-yellow-700 bg-yellow-100', 'dark' => 'dark:text-yellow-300 dark:bg-yellow-900/40', 'hex' => '#facc15', 'icon' => 'pending'],
        'chargeback' => ['icon' => 'failed'],
        'disputed' => ['icon' => 'failed'],
        'confirmed' => ['icon' => 'completed'],
        'preparing' => ['icon' => 'processing'],
        'shipped' => ['variant' => 'info', 'light' => 'text-blue-700 bg-blue-100', 'dark' => 'dark:text-blue-300 dark:bg-blue-900/40', 'hex' => '#2563eb', 'icon' => 'send'],
        'delivered' => ['icon' => 'completed'],
        'returned' => ['variant' => 'warning', 'light' => 'text-yellow-700 bg-yellow-100', 'dark' => 'dark:text-yellow-300 dark:bg-yellow-900/40', 'hex' => '#facc15', 'icon' => 'pending'],
        'in_transit' => ['icon' => 'processing'],
        'on_hold' => ['variant' => 'warning', 'light' => 'text-yellow-700 bg-yellow-100', 'dark' => 'dark:text-yellow-300 dark:bg-yellow-900/40', 'hex' => '#facc15', 'icon' => 'pending'],
        'out_for_delivery' => ['icon' => 'active'],
        'available' => ['icon' => 'available'],
        'unavailable' => ['icon' => 'unavailable'],
        'critical' => ['icon' => 'failed'],
        'upcoming' => ['variant' => 'info', 'light' => 'text-blue-700 bg-blue-100', 'dark' => 'dark:text-blue-300 dark:bg-blue-900/40', 'hex' => '#2563eb', 'icon' => 'calendar'],
        'ongoing' => ['icon' => 'active'],
        'ended' => ['icon' => 'expired'],
        'paused' => ['variant' => 'warning', 'light' => 'text-yellow-700 bg-yellow-100', 'dark' => 'dark:text-yellow-300 dark:bg-yellow-900/40', 'hex' => '#facc15', 'icon' => 'pause'],
        'resumed' => ['icon' => 'play'],
        'scheduled' => ['icon' => 'calendar'],
        'delayed' => ['variant' => 'warning', 'light' => 'text-yellow-700 bg-yellow-100', 'dark' => 'dark:text-yellow-300 dark:bg-yellow-900/40', 'hex' => '#facc15', 'icon' => 'pending'],
        'trial' => ['variant' => 'info', 'light' => 'text-blue-700 bg-blue-100', 'dark' => 'dark:text-blue-300 dark:bg-blue-900/40', 'hex' => '#2563eb', 'icon' => 'beta'],
        'active_subscription' => ['icon' => 'active'],
        'expired_subscription' => ['icon' => 'expired'],
        'renewed' => ['icon' => 'restored'],
        'upgraded' => ['icon' => 'success'],
        'downgraded' => ['variant' => 'warning', 'light' => 'text-yellow-700 bg-yellow-100', 'dark' => 'dark:text-yellow-300 dark:bg-yellow-900/40', 'hex' => '#facc15', 'icon' => 'warning'],
        'confirmed_email' => ['icon' => 'verified'],
        'unconfirmed_email' => ['icon' => 'unverified'],
        'password_reset' => ['variant' => 'info', 'light' => 'text-blue-700 bg-blue-100', 'dark' => 'dark:text-blue-300 dark:bg-blue-900/40', 'hex' => '#2563eb', 'icon' => 'key'],
        'expanded' => ['icon' => 'expand'],
        'collapsed' => ['icon' => 'collapse'],
        'unknown' => ['icon' => 'default'],
    ],

];
