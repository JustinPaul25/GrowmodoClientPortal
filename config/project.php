<?php

use function PHPSTORM_META\map;

return [
    'user_status' => [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'pending' => 'Pending',
        'suspended' => 'Suspended',
        'banned' => 'Banned',
    ],
    'organization_status' => [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'pending' => 'Pending',
        'suspended' => 'Suspended',
    ],
    'subscriptions' => [
        'interval_types' => [
            'month' => 'Month',
            'year' => 'Year',
        ]
    ],

    'questionable_types' => [
        "checkbox",
        "radio",
        "select",
        "select_brand",
        "select_platform",
        "textfield",
        "url",
        "textarea1",
        "textarea2",
        "upload_single",
        "upload_multiple",
    ],



    'keys_to_lowercase' => [
        'email', 'username'
    ]


];