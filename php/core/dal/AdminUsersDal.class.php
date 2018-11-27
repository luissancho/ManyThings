<?php

namespace ManyThings\Core\Dal;

class AdminUsersDal extends ModelDal
{
    protected $source = 'admin_users';

    protected $relations = [
        'Users' => [
            'type' => 'belongs_to',
            'local_key' => 'user_id',
            'alias' => 'u'
        ]
    ];
}
