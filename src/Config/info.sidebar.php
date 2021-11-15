<?php
return [
    'info' => [
        'name'          => 'Info Module',
        'icon'          => 'fas fa-exchange',
        'route_segment' => 'info',
        'entries'       => [
            [   // Edit pages
                'name'  => 'Edit',
                'icon'  => 'fas fa-pen',
                'route' => 'info.edit'
            ],
            [   // List
                'name'  => 'List',
                'icon'  => 'fas fa-list',
                'route' => 'info.list'
            ],
            [   // Manage Pages
                'name'  => 'Manage',
                'icon'  => 'fas fa-cogs',
                'route' => 'info.manage'
            ]
        ]
    ]
];