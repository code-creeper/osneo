<?php

return [
    /*
     * required properties:
     * name: used on forms for label
     *
     * available properties:
     * type: int|array|string etc default: mixed
     * default: default value default: null
     * rules: validation rules default: nullable
     * */

    'allowed_document_types' => [
        'name' => 'Allowed Document Types',
        'type' => 'array',
        'default' => [],
    ],

    'leave_increment_start_year' => [
        'name' => 'Leave Increment Start Year',
        'rules' => 'required'
    ],

    'leave_increment_per_year' => [
        'name' => 'Leave Increment Per Year',
        'default' => 0.5,
    ],
];
