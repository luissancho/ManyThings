<?php

namespace ManyThings\Core\Dal;

class AdminPermissionsDal extends ModelDal
{
    protected $source = 'admin_permissions';

    protected $relations = [
        'AdminSections' => [
            'type' => 'belongs_to',
            'local_key' => 'section_id',
            'alias' => 's'
        ],
        'AdminRoles' => [
            'type' => 'belongs_to',
            'local_key' => 'role_id',
            'alias' => 'r'
        ]
    ];
}
