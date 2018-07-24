<?php
return [
    'mainMenu'=> [
        'label' => 'Main Menu',
        'iconClass' => 'fa fa-home',
        'url' => [''],
        'isDirect' => true,
    ],
    'usermanager' => [
        'label' => 'Membership',
        'iconClass' => 'fa fa-star',
        'navigation' => [
            'allRegistry' => [
                'label' => 'All Registry',
                'url' => ['marketing/registry-business/index'],
                'isDirect' => false,
            ],
            'myRegistry' => [
                'label' => 'My Registry',
                'url' => ['marketing/registry-business/index', 'type' => 'my'],
                'isDirect' => false,
            ],
        ]
    ],
];