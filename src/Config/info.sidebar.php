<?php
return [
    'info' => [
        'name'          => 'Info Module',
        'icon'          => 'fas fa-info',
        'route_segment' => 'info',
        'permission' => 'info.view_article',
        'entries'       => [
            [   // List
                'name'  => 'Start',
                'icon'  => 'fas fa-home',
                'route' => 'info.home',
                'permission' => 'info.view_article',
            ],
            [   // List
                'name'  => 'Articles',
                'icon'  => 'fas fa-list',
                'route' => 'info.list',
                'permission' => 'info.view_article',
            ],
            [   // Edit pages
                'name'  => 'New',
                'icon'  => 'fas fa-pen',
                'route' => 'info.create',
                'permission' => 'info.edit_article',
            ],
            [   // Manage Pages
                'name'  => 'Manage',
                'icon'  => 'fas fa-cogs',
                'route' => 'info.manage',
                'permission' => 'info.edit_article',
            ]
        ]
    ]
];