<?php

namespace ManyThings\Core;

class AdminSections extends ModelAdmin
{
    protected $admin = 'sections';
    protected $titleCol = 'name';

    protected $meta = [
        'caption' => 'Sections',
        'sortname' => 'id',
        'sortorder' => 'asc',
        'edit' => true,
        'add' => true,
        'fields' => [
            'id' => [
                'name' => 'Sec. ID'
            ],
            'name' => [
                'name' => 'Name',
                'width' => '200',
                'wheretype' => 'like'
            ],
            'ref' => [
                'name' => 'Ref.',
                'width' => '200',
                'wheretype' => 'like'
            ],
            'type' => [
                'name' => 'Type',
                'width' => '100',
                'stype' => 'select',
                'transformer' => [
                    'type' => 'array',
                    'name' => 'types'
                ]
            ],
            'class' => [
                'name' => 'Class Name',
                'width' => '200',
                'wheretype' => 'like'
            ],
            'tab' => [
                'name' => 'Tab',
                'width' => '200',
                'wheretype' => 'like'
            ],
            'ord' => [
                'name' => 'Ord.',
                'width' => '60',
                'search' => false
            ]
        ],
        'relations' => [
            'AdminPermissions' => [
                'fields' => ['role_link', 'view', 'add', 'edit', 'delete', 'export', 'admin', 'nav']
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
            ],
            'ref' => [
                'label' => 'Ref.',
                'add' => false
            ],
            'type' => [
                'label' => 'Type',
                'type' => 'select',
                'options' => [
                    'type' => 'array',
                    'name' => 'types'
                ]
            ],
            'class' => [
                'label' => 'Class Name'
            ],
            'tab' => [
                'label' => 'Tab'
            ],
            'ord' => [
                'label' => 'Ord.',
                'add' => false
            ]
        ]
    ];

    protected $types = [
        'admin' => 'Admin',
        'model' => 'Model',
        'controller' => 'Controller',
        'dashboard' => 'Dashboard'
    ];

    public function addAction($data)
    {
        $data['ref'] = Utils::makeLink($data['name']);
        $data['ord'] = self::getDal()->getLastOrd($data['tab']) + 1;

        $newId = parent::addAction($data);

        $roles = AdminRoles::all();
        foreach ($roles as $role) {
            $item = new AdminPermissions();
            $item->create([
                'section_id' => $newId,
                'role_id' => $role['id']
            ]);
        }
        self::getDal()->setAdminPermissions($newId);

        return $newId;
    }

    public function update($values)
    {
        $data = $this->data;
        $values = $this->getUpdateData($values);

        if ($values['tab'] != $data['tab']) {
            $values['ord'] = self::getDal()->getLastOrd($values['tab']) + 1;
            self::getDal()->moveOrds($data['tab'], -1, $data['ord'] + 1, self::getDal()->getLastOrd($data['tab']) + 1);
        } else {
            if (intval($values['ord']) < intval($data['ord'])) {
                self::getDal()->moveOrds($values['tab'], 1, $values['ord'], $data['ord']);
            } elseif (intval($values['ord']) > intval($data['ord'])) {
                self::getDal()->moveOrds($values['tab'], -1, $data['ord'] + 1, $values['ord'] + 1);
            }
        }

        return parent::update($values);
    }

    public function deleteAction()
    {
        $permissions = AdminPermissions::getResultsBy('section_id', $this->id);
        foreach ($permissions as $permission) {
            $item = new AdminPermissions($permission['id']);
            $item->delete();
        }

        parent::deleteAction();

        return true;
    }
}
