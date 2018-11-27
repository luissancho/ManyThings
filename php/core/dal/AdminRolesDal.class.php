<?php

namespace ManyThings\Core\Dal;

class AdminRolesDal extends ModelDal
{
    protected $source = 'admin_roles';

    protected $relations = [
        'AdminPermissions' => [
            'type' => 'has_many',
            'foreign_key' => 'role_id'
        ]
    ];
}
