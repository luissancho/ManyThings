<?php

namespace ManyThings\Core;

class AdminPermissions extends ModelAdmin
{
    protected $admin = 'permissions';

    protected $meta = [
        'caption' => 'Permissions',
        'sortname' => 'id',
        'sortorder' => 'desc',
        'edit' => false,
        'add' => false,
        'fields' => [
            'section_link' => [
                'width' => '200',
                'name' => 'Section',
                'transformer' => [
                    'type' => 'method',
                    'name' => 'getSectionLink'
                ]
            ],
            'role_link' => [
                'width' => '200',
                'name' => 'Role',
                'transformer' => [
                    'type' => 'method',
                    'name' => 'getRoleLink'
                ]
            ],
            'view' => [
                'name' => 'View',
                'transformer' => [
                    'type' => 'formatter',
                    'filter' => 'permission'
                ]
            ],
            'add' => [
                'name' => 'Add',
                'transformer' => [
                    'type' => 'formatter',
                    'filter' => 'permission'
                ]
            ],
            'edit' => [
                'name' => 'Edit',
                'transformer' => [
                    'type' => 'formatter',
                    'filter' => 'permission'
                ]
            ],
            'delete' => [
                'name' => 'Delete',
                'transformer' => [
                    'type' => 'formatter',
                    'filter' => 'permission'
                ]
            ],
            'export' => [
                'name' => 'Export',
                'transformer' => [
                    'type' => 'formatter',
                    'filter' => 'permission'
                ]
            ],
            'admin' => [
                'name' => 'Admin',
                'transformer' => [
                    'type' => 'formatter',
                    'filter' => 'permission'
                ]
            ],
            'nav' => [
                'name' => 'Nav',
                'transformer' => [
                    'type' => 'formatter',
                    'filter' => 'permission'
                ]
            ]
        ]
    ];

    protected $sectionTypes = [
        'admin' => 'Admin',
        'model' => 'Model',
        'controller' => 'Controller',
        'dashboard' => 'Dashboard'
    ];

    protected function permission($value)
    {
        $html = ($value) ? '<span class="icon-ok">&nbsp;</span>' : '&nbsp;';

        return $html;
    }

    protected function getSectionLink($data)
    {
        return '<a href="' . DOMPATH . '/admin/s/sections/details/' . $data['section_id'] . '/">' . $data['AdminSections']['name'] . ' (' . $this->sectionTypes[$data['AdminSections']['type']] . ')</a>';
    }

    protected function getRoleLink($data)
    {
        return '<a href="' . DOMPATH . '/admin/s/roles/details/' . $data['role_id'] . '/">' . $data['AdminRoles']['name'] . '</a>';
    }

    public static function getResultsByRole($roleId)
    {
        $permissions = [];
        $results = self::getResultsBy('role_id', $roleId);

        foreach ($results as $result) {
            $permissions[$result['section_id']] = $result;
        }

        return $permissions;
    }

    public static function getResultsBySection($sectionId)
    {
        $permissions = [];
        $results = self::getResultsBy('section_id', $sectionId);

        foreach ($results as $result) {
            $permissions[$result['role_id']] = $result;
        }

        return $permissions;
    }
}
