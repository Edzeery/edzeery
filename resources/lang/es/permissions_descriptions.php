<?php

return [
    'order' => [
        'manage' => 'Mover pedidos por todo el flujo: preparación, envío, entrega y devolución.',
    ],
    'store' => [
        'billing' => [
            'manage' => 'Ver y gestionar las suscripciones y métodos de pago de la tienda.',
        ],
        'team' => [
            'manage' => 'Gestionar todo el equipo de la tienda — añadir, editar, eliminar o reasignar a cualquier miembro, independientemente de quién lo supervisa. Es el permiso de gestión de equipo a nivel de tienda (sin ámbito), distinto de team.manage.own, que solo cubre al personal que un manager supervisa.',
        ],
        'settings' => [
            'sensitive' => 'Abrir el apartado de ajustes de la tienda en la barra lateral y gestionar sus parámetros principales (marca, información de la tienda, reglas de comercio, valores de inventario por defecto e idiomas). Solo del propietario por defecto — oculto a admins y managers salvo concesión explícita.',
        ],
    ],
];
