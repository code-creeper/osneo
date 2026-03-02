<?php


return [
    'api_key' => env('LEXOFFICE_API'),

    'defaults' => [
        'title' => 'Angebot Wartungsvertrag',
        'introduction' => 'We offer you:',
        'remarks' => 'We look forward to receiving your order and guarantee flawless execution.',
        'payment_terms' => [
            'label' => 'Zahlbar innerhalb von 7 Tagen ab Rechnungsstellung',
            'duration' => 7,
        ]
    ],

    'events' => [
        'voucher.created',
        'voucher.changed',
        'voucher.deleted',

        'invoice.created',
        'invoice.changed',
        'invoice.deleted',

        'payment.changed',
    ],

    'debugging' => true
];
