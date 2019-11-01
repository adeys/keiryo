<?php

namespace Keiryo\Tests\DataMapper\Fixtures\Mapping;

use Keiryo\DataMapper\Persistence\ArrayPersister;
use Keiryo\Tests\DataMapper\Fixtures\Entity\User;

return [
    User::class => [
        'persisterClass' => ArrayPersister::class,
        'table' => 'users',
        'id' => 'id',
        'fields' => [
            'id' => [
                'type' => 'int'
            ],
            'name' => [
                'column' => 'username'
            ],
            'email',
            'password',
            'joinedAt' => [
                'type' => 'datetime',
                'column' => 'joined_at'
            ]
        ]
    ]
];
