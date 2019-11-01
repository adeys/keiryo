<?php

namespace Keiryo\Tests\DataMapper\Fixtures\Mapping;

use Keiryo\DataMapper\Persistence\ArrayPersister;
use Keiryo\Tests\DataMapper\Fixtures\Entity\Comment;

return [
    Comment::class => [
        'persisterClass' => ArrayPersister::class,
        'table' => 'comments',
        'id' => 'id',
        'fields' => [
            'id' => [
                'type' => 'int'
            ],
            'content'
        ]
    ]
];
