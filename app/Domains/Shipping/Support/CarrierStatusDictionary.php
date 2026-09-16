<?php

namespace App\Domains\Shipping\Support;

use App\Enums\Store\OrderTrackingStatus;

/**
 * قاموس حالات شركات التوصيل: القيمة الخام (كما ترد من API/Webhook) → المفتاح
 * الداخلي (OrderTrackingStatus). مصدر واحد لكل من:
 *  - سطر التزامن (NoestTrackingSyncService) — كل قيمة في القاموس لها مفتاح دائم،
 *    فلا حاجة لـ fallback عام مثل IN_TRANSIT.
 *  - شاشة «حالات تتبع الشركة» في صفحة الحالات (جدول raw ⇦ المفتاح ⇦ التسمية).
 *
 * القيم الخام منسوخة حرفيًا من وثائق التكاملات:
 *  - NOEST: docs/توثيق_واجهة_برمجة_تطبيقات_NOEST_v2.3.md (جدول event_key).
 *  - Ecotrack: docs/توثيق_واجهة_برمجة_تطبيقات_Ecotrack_v1.md (11 activity + 19 status).
 *  - Yalidine: docs/Yalidine API Integration Documentation — Edzeery.md (36 history statuses).
 */
final class CarrierStatusDictionary
{
    /** @var array<string, array<string, string>> carrier code => raw status => internal key */
    private const RAW_TO_STATUS = [
        'noest' => [
            'upload' => OrderTrackingStatus::SHIPPED->value,
            'customer_validation' => OrderTrackingStatus::SHIPPED->value,
            'validation_collect_colis' => OrderTrackingStatus::IN_TRANSIT->value,
            'validation_reception_admin' => OrderTrackingStatus::IN_TRANSIT->value,
            'validation_reception' => OrderTrackingStatus::IN_TRANSIT->value,
            'sent_to_redispatch' => OrderTrackingStatus::IN_TRANSIT->value,
            'annulation_dispatch_retour' => OrderTrackingStatus::IN_TRANSIT->value,
            'cancel_return_dispatched_to_partenaire' => OrderTrackingStatus::IN_TRANSIT->value,
            'fdr_activated' => OrderTrackingStatus::OUT_FOR_DELIVERY->value,
            'nouvel_tentative_asked_by_customer' => OrderTrackingStatus::OUT_FOR_DELIVERY->value,
            'return_redispatched_to_livraison' => OrderTrackingStatus::OUT_FOR_DELIVERY->value,
            'mise_a_jour' => OrderTrackingStatus::FAILED_ATTEMPT->value,
            'return_asked_by_customer' => OrderTrackingStatus::RETURNING->value,
            'return_asked_by_hub' => OrderTrackingStatus::RETURNING->value,
            'return_dispatched_to_warehouse' => OrderTrackingStatus::RETURNING->value,
            'retour_dispatched_to_partenaires' => OrderTrackingStatus::RETURNED->value,
            'return_dispatched_to_partenaire' => OrderTrackingStatus::RETURNED->value,
            'colis_retour_transmit_to_partner' => OrderTrackingStatus::RETURNED->value,
            'livraison_echoue_recu' => OrderTrackingStatus::RETURNED->value,
            'return_validated_by_partener' => OrderTrackingStatus::RETURNED->value,
            'pickedup' => OrderTrackingStatus::RETURNED->value,
            'valid_return_pickup' => OrderTrackingStatus::RETURNED->value,
            'pickup_picked_recu' => OrderTrackingStatus::RETURNED->value,
            'livre' => OrderTrackingStatus::DELIVERED->value,
            'livred' => OrderTrackingStatus::DELIVERED->value,
            // Key جديد من الوثيقة (لم يكن في NoestTrackingMapper القديم).
            'colis_suspendu' => OrderTrackingStatus::ON_HOLD->value,
            'colis_pickup_transmit_to_partner' => OrderTrackingStatus::RETURNED->value,
            'echange_valide' => OrderTrackingStatus::DELIVERED->value,
            'echange_valid_by_hub' => OrderTrackingStatus::DELIVERED->value,
            'verssement_admin_cust' => OrderTrackingStatus::DELIVERED->value,
            'validation_reception_cash_by_partener' => OrderTrackingStatus::DELIVERED->value,
            'verssement_admin_cust_canceled' => OrderTrackingStatus::ON_HOLD->value,
            'verssement_hub_cust_canceled' => OrderTrackingStatus::ON_HOLD->value,
            'ask_to_delete_by_admin' => OrderTrackingStatus::CANCELLED->value,
            'ask_to_delete_by_hub' => OrderTrackingStatus::CANCELLED->value,
        ],
        'ecotrack' => [
            // مفردة activity في get/tracking/info (11 قيمة).
            'order_information_received_by_carrier' => OrderTrackingStatus::SHIPPED->value,
            'picked' => OrderTrackingStatus::IN_TRANSIT->value,
            'accepted_by_carrier' => OrderTrackingStatus::IN_TRANSIT->value,
            'dispatched_to_driver' => OrderTrackingStatus::OUT_FOR_DELIVERY->value,
            'attempt_delivery' => OrderTrackingStatus::FAILED_ATTEMPT->value,
            'return_asked' => OrderTrackingStatus::RETURNING->value,
            'return_in_transit' => OrderTrackingStatus::RETURNING->value,
            'Return_received' => OrderTrackingStatus::RETURNED->value,
            'livred' => OrderTrackingStatus::DELIVERED->value,
            'encaissed' => OrderTrackingStatus::DELIVERED->value,
            'payed' => OrderTrackingStatus::DELIVERED->value,
            // مفردة status في get/orders/status (19 قيمة — بلا القيمة "all" الفلترية).
            'prete_a_expedier' => OrderTrackingStatus::SHIPPED->value,
            'en_ramassage' => OrderTrackingStatus::IN_TRANSIT->value,
            'en_preparation_stock' => OrderTrackingStatus::SHIPPED->value,
            'vers_hub' => OrderTrackingStatus::IN_TRANSIT->value,
            'en_hub' => OrderTrackingStatus::IN_TRANSIT->value,
            'vers_wilaya' => OrderTrackingStatus::IN_TRANSIT->value,
            'en_preparation' => OrderTrackingStatus::IN_TRANSIT->value,
            'en_livraison' => OrderTrackingStatus::OUT_FOR_DELIVERY->value,
            'suspendu' => OrderTrackingStatus::ON_HOLD->value,
            'livre_non_encaisse' => OrderTrackingStatus::DELIVERED->value,
            'encaisse_non_paye' => OrderTrackingStatus::DELIVERED->value,
            'paiements_prets' => OrderTrackingStatus::DELIVERED->value,
            'paye_et_archive' => OrderTrackingStatus::DELIVERED->value,
            'retour_chez_livreur' => OrderTrackingStatus::RETURNING->value,
            'retour_transit_entrepot' => OrderTrackingStatus::RETURNING->value,
            'retour_en_traitement' => OrderTrackingStatus::RETURNING->value,
            'retour_recu' => OrderTrackingStatus::RETURNED->value,
            'retour_archive' => OrderTrackingStatus::RETURNED->value,
            'annule' => OrderTrackingStatus::CANCELLED->value,
        ],
        'yalidine' => [
            'Pas encore expédié' => OrderTrackingStatus::SHIPPED->value,
            'A vérifier' => OrderTrackingStatus::SHIPPED->value,
            'En préparation' => OrderTrackingStatus::SHIPPED->value,
            'Pas encore ramassé' => OrderTrackingStatus::SHIPPED->value,
            'Prêt à expédier' => OrderTrackingStatus::SHIPPED->value,
            'En passation' => OrderTrackingStatus::IN_TRANSIT->value,
            'Ramassé' => OrderTrackingStatus::IN_TRANSIT->value,
            'Transfert' => OrderTrackingStatus::IN_TRANSIT->value,
            'Expédié' => OrderTrackingStatus::IN_TRANSIT->value,
            'Centre' => OrderTrackingStatus::IN_TRANSIT->value,
            'En localisation' => OrderTrackingStatus::IN_TRANSIT->value,
            'Vers Wilaya' => OrderTrackingStatus::IN_TRANSIT->value,
            'En transit' => OrderTrackingStatus::IN_TRANSIT->value,
            'Reçu à Wilaya' => OrderTrackingStatus::IN_TRANSIT->value,
            'Débloqué' => OrderTrackingStatus::IN_TRANSIT->value,
            'Alerte résolue' => OrderTrackingStatus::IN_TRANSIT->value,
            'En attente du client' => OrderTrackingStatus::OUT_FOR_DELIVERY->value,
            'Prêt pour livreur' => OrderTrackingStatus::OUT_FOR_DELIVERY->value,
            'Sorti en livraison' => OrderTrackingStatus::OUT_FOR_DELIVERY->value,
            'En attente' => OrderTrackingStatus::OUT_FOR_DELIVERY->value,
            'Bloqué' => OrderTrackingStatus::ON_HOLD->value,
            'En alerte' => OrderTrackingStatus::ON_HOLD->value,
            'Tentative échouée' => OrderTrackingStatus::FAILED_ATTEMPT->value,
            'Echèc livraison' => OrderTrackingStatus::FAILED_ATTEMPT->value,
            'Livré' => OrderTrackingStatus::DELIVERED->value,
            'Retour vers centre' => OrderTrackingStatus::RETURNING->value,
            'Retourné au centre' => OrderTrackingStatus::RETURNING->value,
            'Retour transfert' => OrderTrackingStatus::RETURNING->value,
            'Retour groupé' => OrderTrackingStatus::RETURNING->value,
            'Retour à retirer' => OrderTrackingStatus::RETURNING->value,
            'Retour non retiré' => OrderTrackingStatus::RETURNED->value,
            'Retour vers vendeur' => OrderTrackingStatus::RETURNING->value,
            'Retourné au vendeur' => OrderTrackingStatus::RETURNED->value,
            'Colis abandonné' => OrderTrackingStatus::LOST->value,
            'Annulé' => OrderTrackingStatus::CANCELLED->value,
            'Echange échoué' => OrderTrackingStatus::RETURNED->value,
        ],
    ];

    /** @var array<string, string> carrier code => merchant_panel translation key for its name */
    private const CARRIER_NAME_KEY = [
        'noest' => 'merchant_panel.carrier_noest',
        'ecotrack' => 'merchant_panel.carrier_ecotrack',
        'yalidine' => 'merchant_panel.carrier_yalidine',
    ];

    /** كل أكواد الشركات الموثّقة بالقاموس. */
    public static function carriers(): array
    {
        return array_keys(self::RAW_TO_STATUS);
    }

    /**
     * خيارات الشركات لشاشة الحالات.
     *
     * @return array<int, array{value: string, label: string}>
     */
    public static function carrierOptions(): array
    {
        $options = [];

        foreach (self::carriers() as $code) {
            $options[] = [
                'value' => $code,
                'label' => __(self::CARRIER_NAME_KEY[$code] ?? 'merchant_panel.carrier_'.$code),
            ];
        }

        return $options;
    }

    /**
     * المفتاح الداخلي لقيمة خام (null للمهام الإدارية/المالية التي لم تغيّر حالة
     * الشحنة — مثل تعديل السعر — حيث نُبقي الحالة السابقة دون رجعة).
     */
    public static function statusFor(string $carrier, ?string $raw): ?OrderTrackingStatus
    {
        if ($raw === null || $raw === '') {
            return null;
        }

        $key = self::RAW_TO_STATUS[$carrier][$raw] ?? null;

        return $key !== null ? OrderTrackingStatus::tryFrom($key) : null;
    }

    /**
     * القيم الخام لكل شركة مع مفاتيحها الداخلية، بترتيب الوثيقة.
     *
     * @return array<int, array{raw: string, status: OrderTrackingStatus}>
     */
    public static function list(string $carrier): array
    {
        $rows = [];

        foreach (self::RAW_TO_STATUS[$carrier] ?? [] as $raw => $key) {
            $status = OrderTrackingStatus::tryFrom($key);
            if ($status === null) {
                continue;
            }

            $rows[] = ['raw' => $raw, 'status' => $status];
        }

        return $rows;
    }

    /**
     * كل القيم الخام لشركة تطابق حالة داخلية معيّنة (يساعد على إيجاد تاريخ
     * الحدث عند بلوغ حالة نهائية في سطر التزامن).
     *
     * @return array<int, string>
     */
    public static function keysFor(string $carrier, OrderTrackingStatus $status): array
    {
        $keys = [];

        foreach (self::RAW_TO_STATUS[$carrier] ?? [] as $raw => $key) {
            if ($key === $status->value) {
                $keys[] = $raw;
            }
        }

        return $keys;
    }
}
