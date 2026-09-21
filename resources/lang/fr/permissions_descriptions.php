<?php

return [
    'order' => [
        'manage' => 'Faire avancer les commandes dans tout le processus: préparation, expédition, livraison et retour.',
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
