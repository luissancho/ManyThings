<?php

namespace ManyThings\Core;

class AdminUsers extends ModelAdmin
{
    protected $admin = 'admins';
    protected $titleCol = 'Users.username';

    protected $meta = [
        'caption' => 'Admins',
        'sortname' => 'id',
        'sortorder' => 'asc',
        'edit' => true,
        'add' => true,
        'fields' => [
            'id' => [
                'name' => 'User ID'
            ],
            'Users.username' => [
                'name' => 'Name',
                'width' => '300',
                'wheretype' => 'like'
            ],
            'Users.email' => [
                'name' => 'Email',
                'width' => '300',
                'wheretype' => 'like'
            ],
            'Users.timezone' => [
                'name' => 'Timezone',
                'width' => '140',
                'wheretype' => 'like'
            ],
            'role_id' => [
                'name' => 'Role',
                'width' => '120',
                'stype' => 'select',
                'transformer' => [
                    'type' => 'model',
                    'name' => 'AdminRoles',
                    'column_name' => 'name'
                ]
            ]
        ],
        'actions' => [
            'delete' => [
                'name' => 'Delete',
                'input' => 'confirm'
            ]
        ]
    ];

    protected $form = [
        'message' => '',
        'fields' => [
            'Users.username' => [
                'label' => 'Name'
            ],
            'Users.email' => [
                'label' => 'Email'
            ],
            'Users.password_add' => [
                'label' => 'Password',
                'type' => 'password',
                'edit' => false
            ],
            'Users.password_edit' => [
                'label' => 'New Password (blank to keep current)',
                'type' => 'password',
                'add' => false
            ],
            'role_id' => [
                'label' => 'Role',
                'type' => 'select',
                'options' => [
                    'type' => 'model',
                    'name' => 'AdminRoles',
                    'column_name' => 'name'
                ]
            ],
            'Users.timezone' => [
                'label' => 'Timezone'
            ]
        ]
    ];

    public function create($values)
    {
        $user = new Users();
        $values['user_id'] = $user->create([
            'email' => $values['Users__email'],
            'username' => $values['Users__username'],
            'password' => $values['Users__password_add'],
            'timezone' => $values['Users__timezone'],
            'active' => true
        ]);

        parent::create([
            'user_id' => $values['user_id'],
            'role_id' => $values['role_id'],
            'level' => $values['role_id'] == 1 || $values['role_id'] == 2 ? 3 : 2
        ]);

        return $values['user_id'];
    }

    public function update($values)
    {
        $values['level'] = $values['role_id'] == 1 || $values['role_id'] == 2 ? 3 : 2;
        $values['timezone'] = $values['Users__timezone'];
        $values['email'] = $values['Users__email'];
        $values['username'] = $values['Users__username'];
        if (!empty($values['Users__password_edit'])) {
            $values['password'] = $values['Users__password_edit'];
        }

        $user = new Users($this->data['user_id']);
        $userChanged = $user->update($values);

        $changed = parent::update($values);

        return array_merge($userChanged, $changed);
    }
}
