<?php
return [
    'info' => [
        'name'          => 'Info Module',
        'icon'          => 'fas fa-books',
        'route_segment' => 'info',
        'entries'       => [
            [   // List
                'name'  => 'Articles',
                'icon'  => 'fas fa-list',
                'route' => 'info.list'
            ],
            [   // Edit pages
                'name'  => 'New',
                'icon'  => 'fas fa-pen',
                'route' => 'info.create'
            ],
            [   // Manage Pages
                'name'  => 'Manage',
                'icon'  => 'fas fa-cogs',
                'route' => 'info.manage'
            ]
        ]
    ]
];