<?php
return [
    'info' => [
        'name'          => 'Info Module',
        'icon'          => 'fas fa-info',
        'route_segment' => 'info',
        'entries'       => [
            [   // List
                'name'  => 'Start',
                'icon'  => 'fas fa-home',
                'route' => 'info.home',
            ],
            [   // List
                'name'  => 'Articles',
                'icon'  => 'fas fa-list',
                'route' => 'info.list',
            ],
            [   // Edit pages
                'name'  => 'New',
                'icon'  => 'fas fa-pen',
                'route' => 'info.create',
                'permission' => 'info.manage_article',
            ],
            [   // Manage Pages
                'name'  => 'Manage',
                'icon'  => 'fas fa-cogs',
                'route' => 'info.manage',
                'permission' => 'info.manage_article',
            ],
            [   // Manage Pages
                'name'  => 'About',
                'icon'  => 'fas fa-info',
                'route' => 'info.about',
            ]
        ]
    ]
];