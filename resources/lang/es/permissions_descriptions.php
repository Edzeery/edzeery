<?php

return [
    'order' => [
        'dispatch' => [
            'carrier' => 'Entregar un pedido a una empresa de transporte para que lo recoja y lo siga. Esta es la acción que realmente envía el pedido a la API del transportista. Distinto de order.dispatch_validate, que solo compara el envío con el registro de entrega del transportista y no envía nada, y de order.dispatch.rider, que entrega el paquete a un repartidor en lugar de a una empresa. Concédelo cuando un miembro deba enviar a transportistas sin tener todo order.manage.',
            'rider' => 'Entregar un pedido a un repartidor, o recuperarlo de él, para que el repartidor lo recoja y lo entregue. Distinto de order.assign, que mueve un pedido entre los miembros de tu propio equipo: concédelo cuando un miembro deba colocar paquetes con repartidores sin reasignar trabajo dentro del equipo.',
        ],
        'dispatch_validate' => 'Comparar un envío con el registro de entrega del transportista antes o después del despacho: número de paquetes, peso e importe del pago contra entrega. Esto no envía el pedido al transportista; el envío corresponde a order.dispatch.carrier o a order.manage.',
        'edit' => [
            'geography' => 'Editar el destino de entrega y el transportista de cualquier pedido — wilaya, comuna, dirección, tipo de entrega, empresa de transporte, repartidor, oficina y la opción de envío desde el almacén del transportista. A nivel de tienda: abarca todos los pedidos de la tienda. Esta es la alternativa más estrecha a order.manage para corregir los datos de entrega.',
            'identity' => 'Editar los datos identificativos del cliente en cualquier pedido — nombre del cliente, números de teléfono y notas internas. A nivel de tienda: abarca todos los pedidos de la tienda, no solo los de tu ámbito de visibilidad. Esta es la alternativa más estrecha a order.manage para correcciones de identidad.',
            'products' => 'Editar los productos, las cantidades, el peso, el tipo de envío y el descuento de cualquier pedido. A nivel de tienda: abarca todos los pedidos de la tienda. El precio sigue rigiéndose por separado mediante order.edit.price junto con el ajuste allow_price_edit de la tienda: este permiso por sí solo no permite cambiar los precios.',
        ],
        'manage' => 'Mover cualquier pedido por todo el flujo —preparación, envío, entrega y devolución— y editar cualquier campo de cualquier pedido, además de las acciones masivas, el panel de ajustes de pedidos y la cola de distribución. A nivel de tienda: abarca todos los pedidos de la tienda. Para una concesión más estrecha, consulta order.status.manage.own, los permisos order.edit.* y los permisos order.dispatch.*.',
        'status' => [
            'manage' => [
                'own' => 'Mover por todo el flujo los pedidos que puedes ver, sin gestión de pedidos a nivel de tienda. Cubre todas las transiciones de estado de esos pedidos, no solo las de envío: confirmar un pedido nuevo, resultados de llamada (sin respuesta, número equivocado, sin stock), aplazamiento, duplicación y cancelación, así como preparación, envío, entrega y devolución. Estrictamente limitado a tu ámbito de visibilidad: tus pedidos asignados, más los de tu personal supervisado si supervisas a alguien. Esta es la alternativa más estrecha a order.manage para el personal que gestiona de principio a fin su propio flujo de pedidos.',
            ],
        ],
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
