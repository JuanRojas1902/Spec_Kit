<?php
declare(strict_types=1);

// Campos que ya espera el enrutador V2 en sus formularios.
return [
    'objetivo_desarrollo_sostenible' => [
        'clave' => 'id',
        'campos' => [
            'id' => ['etiqueta' => 'ID', 'tipo' => 'number', 'obligatorio' => true],
            'nombre' => ['etiqueta' => 'Nombre', 'tipo' => 'text', 'obligatorio' => true],
            'categoria' => ['etiqueta' => 'Categoría', 'tipo' => 'text', 'obligatorio' => false],
        ],
    ],
    'area_aplicacion' => [
        'clave' => 'id',
        'campos' => [
            'id' => ['etiqueta' => 'ID', 'tipo' => 'number', 'obligatorio' => true],
            'nombre' => ['etiqueta' => 'Nombre', 'tipo' => 'text', 'obligatorio' => true],
        ],
    ],
    'termino_clave' => [
        'clave' => 'termino',
        'campos' => [
            'termino' => ['etiqueta' => 'Término', 'tipo' => 'text', 'obligatorio' => true],
            'termino_ingles' => ['etiqueta' => 'Término en inglés', 'tipo' => 'text', 'obligatorio' => false],
        ],
    ],
    'universidad' => [
        'clave' => 'id',
        'campos' => [
            'id' => ['etiqueta' => 'ID', 'tipo' => 'number', 'obligatorio' => true],
            'nombre' => ['etiqueta' => 'Nombre', 'tipo' => 'text', 'obligatorio' => true],
            'tipo' => ['etiqueta' => 'Tipo', 'tipo' => 'text', 'obligatorio' => false],
            'ciudad' => ['etiqueta' => 'Ciudad', 'tipo' => 'text', 'obligatorio' => false],
        ],
    ],
    'linea_investigacion' => [
        'clave' => 'id',
        'campos' => [
            'nombre' => ['etiqueta' => 'Nombre', 'tipo' => 'text', 'obligatorio' => true],
            'descripcion' => ['etiqueta' => 'Descripción', 'tipo' => 'textarea', 'obligatorio' => false],
        ],
    ],
];
