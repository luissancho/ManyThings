<?php

namespace ManyThings\Core\Dal;

class UsersDal extends ModelDal
{
    protected $source = 'users';

    protected $activeFilter = [
        'field' => 'active',
        'op' => 'eq',
        'value' => 1
    ];
}
