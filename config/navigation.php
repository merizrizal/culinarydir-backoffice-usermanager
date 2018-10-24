<?php
return [
    'params' => [
        'navigation' => [
            'userManager' => [
                'label' => 'User Management',
                'iconClass' => 'fa fa-users',
                'navigation' => [
                    'user' => [
                        'label' => 'User',
                        'url' => ['usermanager/user/index'],
                        'isDirect' => false,
                    ],
                    'userLevel' => [
                        'label' => 'User Level',
                        'url' => ['usermanager/user-level/index'],
                        'isDirect' => false,
                    ],
                ]
            ],
            'person' => [
                'label' => 'Person',
                'iconClass' => 'fa fa-user',
                'url' => ['usermanager/person/index'],
                'isDirect' => false,
            ],
        ]
    ]
];