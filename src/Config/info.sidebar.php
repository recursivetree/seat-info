<?php
return [
    'info' => [
        'name'          => 'Info Module',
        'icon'          => 'fas fa-info',
        'route_segment' => 'info',
        'entries'       => [
            [   // List
                'name'  => 'Articles',
                'icon'  => 'fas fa-list',
                'route' => 'info.list',
            ],
            [   // Create pages
                'name'  => 'New',
                'icon'  => 'fas fa-pen',
                'route' => 'info.create',
                'permission' => 'info.create_article',
            ],
            [   // Manage Pages
                'name'  => "Personal",
                'icon'  => 'fas fa-user-edit',
                'route' => 'info.manage',
                'permission' => 'info.create_article',
            ],
            [   // About Page
                'name'  => 'About',
                'icon'  => 'fas fa-info',
                'route' => 'info.about',
            ]
        ]
    ]
];