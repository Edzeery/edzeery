<?php

return [
    'order' => [
        'dispatch' => [
            'carrier' => 'Confier une commande à une société de livraison afin qu\'elle la ramasse et la suive. C\'est l\'action qui envoie réellement la commande à l\'API du transporteur. Distincte de order.dispatch_validate, qui ne fait que comparer l\'envoi au registre de remise du transporteur sans rien envoyer, et de order.dispatch.rider, qui remet le colis à un livreur plutôt qu\'à une société. À accorder lorsqu\'un membre doit expédier vers des transporteurs sans détenir l\'intégralité de order.manage.',
            'rider' => 'Confier une commande à un livreur, ou la lui retirer, afin qu\'il la ramasse et la livre. Distincte de order.assign, qui transfère une commande entre les membres de votre propre équipe — à accorder lorsqu\'un membre doit remettre des colis à des livreurs sans réaffecter le travail au sein de l\'équipe.',
        ],
        'dispatch_validate' => 'Comparer un envoi au registre de remise du transporteur avant ou après l\'expédition — nombre de colis, poids et montant du paiement à la livraison. Cela n\'envoie pas la commande au transporteur ; l\'envoi relève de order.dispatch.carrier ou de order.manage.',
        'edit' => [
            'geography' => 'Modifier la destination de livraison et le transporteur de n\'importe quelle commande — wilaya, commune, adresse, type de livraison, société de livraison, livreur, bureau et l\'option d\'expédition depuis l\'entrepôt du transporteur. À l\'échelle du magasin : elle couvre toutes les commandes du magasin. C\'est l\'alternative étroite à order.manage pour corriger les détails de livraison.',
            'identity' => 'Modifier les informations d\'identification du client sur n\'importe quelle commande — nom du client, numéros de téléphone et notes internes. À l\'échelle du magasin : elle couvre toutes les commandes, pas seulement celles de votre périmètre de visibilité. C\'est l\'alternative étroite à order.manage pour les corrections d\'identité.',
            'products' => 'Modifier les produits, les quantités, le poids, le type d\'expédition et la remise d\'une commande, sur n\'importe quelle commande. À l\'échelle du magasin : elle couvre toutes les commandes. Le prix reste régi séparément par order.edit.price ainsi que par le paramètre allow_price_edit du magasin — cette permission ne permet pas à elle seule de modifier les prix.',
        ],
        'manage' => 'Faire avancer n\'importe quelle commande dans tout le processus — préparation, expédition, livraison et retour — et modifier n\'importe quel champ de n\'importe quelle commande, ainsi que les actions groupées, le panneau des paramètres de commande et la file de distribution. À l\'échelle du magasin : elle couvre toutes les commandes du magasin. Pour une attRIBUTION plus restreinte, voir order.status.manage.own, les permissions order.edit.* et les permissions order.dispatch.*.',
        'status' => [
            'manage' => [
                'own' => 'Faire avancer les commandes que vous pouvez voir dans tout le processus, sans gestion des commandes à l\'échelle du magasin. Couvre chaque transition de statut sur ces commandes — pas seulement celles liées à l\'expédition : confirmation d\'une nouvelle commande, résultats d\'appel (pas de réponse, mauvais numéro, rupture de stock), report, duplication, annulation, ainsi que préparation, expédition, livraison et retour. Strictement limitée à votre périmètre de visibilité : vos propres commandes assignées, plus celles de vos employés supervisés si vous supervisez quelqu\'un. C\'est l\'alternative étroite à order.manage pour le personnel qui gère son propre flux de commandes de bout en bout.',
            ],
        ],
    ],
    'store' => [
        'billing' => [
            'manage' => 'Consulter et gérer les abonnements et moyens de paiement du magasin.',
        ],
        'team' => [
            'manage' => 'Gérer toute l\'équipe du magasin — ajouter, modifier, retirer ou réaffecter n\'importe quel membre, quel que soit son superviseur. C\'est la permission d\'administration d\'équipe à l\'échelle du magasin (non limitée), distincte de team.manage.own qui ne couvre que les employés supervisés par un manager.',
        ],
        'settings' => [
            'sensitive' => 'Ouvrir le département des paramètres du magasin depuis la barre latérale et gérer ses paramètres essentiels (identité visuelle, informations du magasin, règles commerciales, valeurs d\'inventaire par défaut et langues). Réservée au propriétaire par défaut — masquée aux admins et managers sauf octroi explicite.',
        ],
    ],
];
