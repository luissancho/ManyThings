<?php

namespace ManyThings\Core\Dal;

class AdminLogsDal extends ModelDal
{
    protected $source = 'admin_logs';
    protected $createCol = 'time';

    protected $relations = [
        'AdminUsers' => [
            'type' => 'belongs_to',
            'local_key' => 'user_id',
            'alias' => 'u'
        ]
    ];
}
