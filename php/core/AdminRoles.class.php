<?php

namespace ManyThings\Core;

class AdminRoles extends ModelAdmin
{
    protected $admin = 'roles';
    protected $titleCol = 'name';

    protected $meta = [
        'caption' => 'Roles',
        'sortname' => 'id',
        'sortorder' => 'asc',
        'edit' => true,
        'add' => true,
        'fields' => [
            'id' => [
                'name' => 'Role ID'
            ],
            'name' => [
                'name' => 'Name',
                'width' => '700',
                'wheretype' => 'like'
            ]
        ],
        'relations' => [
            'AdminPermissions' => [
                'fields' => ['section_link', 'view', 'add', 'edit', 'delete', 'export', 'admin', 'nav'],
                'query' => [
                    'where' => "t.view = '1'",
                    'order' => 'type, ord'
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
            'name' => [
                'label' => 'Name'
            ]
        ]
    ];

    public function addAction($data)
    {
        $newId = parent::addAction($data);

        $sections = AdminSections::all();
        foreach ($sections as $section) {
            $newData = [
                'section_id' => $section['id'],
                'role_id' => $newId
            ];

            $item = new AdminPermissions();
            $item->create($newData);
        }

        return $newId;
    }

    public function deleteAction()
    {
        $permissions = AdminPermissions::getResultsBy('role_id', $this->id);
        foreach ($permissions as $permission) {
            $item = new AdminPermissions($permission['id']);
            $item->delete();
        }

        parent::deleteAction();

        return true;
    }
}
