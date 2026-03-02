<?php

return [

    'taggable_models' => [
        'App\Models\Leave' => 'Leaves',
    ],

    //todo::remove_me
    'leave_categories' => [
        'offsite_work' => 'Offsite Work',
        'paid' => 'Paid Leave',
        'unpaid' => 'Unpaid Leave',
    ],

    'employment_types' => [
        'weekly' => 'Full Time',
        'hourly' => 'Hourly',
    ],

    'vehicle_conditions' => [
        'clean' => 'Clean',
        'slightly_dirty' => 'Slightly Dirty',
        'extremely_dirty' => 'Extremely Dirty',
    ],

    'tank_levels' => [
        'empty' => 'Empty',
        'quarter' => '¼',
        'half' => '½',
        'three_quarter' => '¾',
        'full' => 'Full',
    ],

    'emission_stickers' => [
        'none' => 'None',
        '2' => 'Red (2)',
        '3' => 'Yellow(3)',
        '4' => 'Green(4)',
    ],

    'yes_no' => [
        'yes' => 'Yes',
        'no' => 'No',
    ],

    'document_types' => [
        "R" => 'Rechnung',
        "LFS" => 'Lieferschein',
        "AUF" => 'Auftragsbestätigung',
        "ANG" => 'Angebot',
        "RMA" => 'Reklamation',
        "REGIE" => 'Regiebericht',
        "BAUT" => 'Bautagebuch',
        "VERTBAU" => 'Wartungsvertrag',
        "VERTLEA" => 'Leasingvertrag',
        "VERTVERSICH" => 'Versicherung',
        "VERTSONST" => 'Vertrag-Sonstiges',
        "INFO" => 'Infopost',
        "SONST" => 'Sonstiges',
        "GUS" => 'Gutschriften',
        "POS" => 'Provisionsabrechnung',
        "STORNO" => 'Rechnung-Stornieren',
        "KASSE" => 'Kasse',
        "VERTWARTUNG" => 'Wartungsvertrag',
        "MAHNUNG" => 'Mahnung',
        "AVIS" => 'Zahlungsavis',
        "KRANK" => 'Krankmeldung',
        "KDD" => 'Kundendokument',
    ]
];
