<?php

/*
| Mensajes de validación en español.
| Laravel trae estos mensajes solo en inglés; aquí se traducen los que usa el sistema.
| :attribute se reemplaza por el nombre del campo (la etiqueta del formulario).
*/

return [
    'accepted'  => 'El campo :attribute debe ser aceptado.',
    'array'     => 'El campo :attribute debe ser una lista.',
    'boolean'   => 'El campo :attribute debe ser verdadero o falso.',
    'date'      => 'El campo :attribute debe ser una fecha válida.',
    'decimal'   => 'El campo :attribute debe tener :decimal decimales.',
    'email'     => 'El campo :attribute debe ser un correo electrónico válido.',
    'exists'    => 'El valor seleccionado en :attribute no es válido.',
    'gt'        => [
        'numeric' => 'El campo :attribute debe ser mayor que :value.',
        'string'  => 'El campo :attribute debe tener más de :value caracteres.',
        'array'   => 'El campo :attribute debe tener más de :value elementos.',
    ],
    'gte'       => [
        'numeric' => 'El campo :attribute debe ser mayor o igual que :value.',
        'string'  => 'El campo :attribute debe tener al menos :value caracteres.',
        'array'   => 'El campo :attribute debe tener :value elementos o más.',
    ],
    'in'        => 'El valor seleccionado en :attribute no es válido.',
    'integer'   => 'El campo :attribute debe ser un número entero.',
    'max'       => [
        'numeric' => 'El campo :attribute no debe ser mayor que :max.',
        'string'  => 'El campo :attribute no debe tener más de :max caracteres.',
        'array'   => 'El campo :attribute no debe tener más de :max elementos.',
    ],
    'min'       => [
        'numeric' => 'El campo :attribute debe ser al menos :min.',
        'string'  => 'El campo :attribute debe tener al menos :min caracteres.',
        'array'   => 'El campo :attribute debe tener al menos :min elementos.',
    ],
    'numeric'   => 'El campo :attribute debe ser un número.',
    'regex'     => 'El formato del campo :attribute no es válido.',
    'required'  => 'El campo :attribute es obligatorio.',
    'string'    => 'El campo :attribute debe ser texto.',
    'unique'    => 'El valor del campo :attribute ya está registrado.',

    'custom'     => [],
    'attributes' => [],
];
