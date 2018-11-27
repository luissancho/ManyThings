<?php

namespace ManyThings\Core;

class AdminLogs extends ModelAdmin
{
    const ACTION_CREATE = 'Create';
    const ACTION_UPDATE = 'Update';
    const ACTION_DELETE = 'Delete';

    protected $admin = 'logs';

    protected $meta = [
        'caption' => 'Logs',
        'fields' => [
            'id' => [
                'name' => 'Log ID'
            ],
            'time' => [
                'width' => '120',
                'search' => false,
                'formatter' => 'date',
                'format' => 'd/m/y H:i',
                'transformer' => [
                    'type' => 'formatter',
                    'filter' => 'date'
                ]
            ],
            'user_id' => [
                'name' => 'User ID',
                'show_in_detail' => false
            ],
            'user_name' => [
                'name' => 'Name',
                'width' => '140',
                'transformer' => [
                    'type' => 'method',
                    'name' => 'getUserName'
                ],
                'show_in_detail' => false
            ],
            'class' => [
                'width' => '140'
            ],
            'item_id' => [
                'name' => 'Item ID',
                'width' => '180',
                'transformer' => [
                    'type' => 'method',
                    'name' => 'getItemLink'
                ]
            ],
            'action' => [
                'width' => '140'
            ],
            'log' => [
                'width' => '400',
                'align' => 'left',
                'show_in_detail' => false
            ],
            'view' => [
                'name' => 'Log View',
                'width' => '100',
                'transformer' => [
                    'type' => 'method',
                    'name' => 'view'
                ],
                'show_in_list' => false,
                'show_in_detail' => false
            ]
        ],
        'blocks' => [
            'Log' => [
                'log' => [
                    'name' => '',
                    'width' => '400',
                    'align' => 'left',
                    'transformer' => [
                        'type' => 'formatter',
                        'name' => 'json'
                    ]
                ]
            ]
        ],
        'relations' => [
            'AdminUsers' => [
                'name' => 'User',
                'forward' => true
            ]
        ]
    ];

    protected function date($value)
    {
        return !empty($value) ? Dates::create($value)->setTimezone($this->timezone)->formatString(true) : '-';
    }

    protected function view($data)
    {
        return '<a tabindex="0" role="button" data-toggle="popover" data-container="body" data-placement="left" data-html="true" data-trigger="focus" title="View" data-content="' . Utils::jsonToText($data['log']) . '" style="cursor: pointer;">Show</a>';
    }

    protected function getUserName($data)
    {
        $user = AdminUsers::get($data['AdminUsers']['id']);

        return $user['Users']['username'];
    }

    protected function getItemLink($data)
    {
        $section = AdminSections::getRowBy('class', $data['class']);

        return '<a href="' . DOMPATH . '/admin/s/' . $section['ref'] . '/details/' . $data['item_id'] . '/">' . $data['item_id'] . '</a>';
    }

    public static function createEvent($class, $itemId, $action, $log = [])
    {
        $config = self::getDI()->config;
        $session = self::getDI()->session;

        if (empty($config->app->admin_log)) {
            return 0;
        }

        $event = new self();
        $eventId = $event->create(
        [
            'user_id' => $session->uid,
            'class' => $class,
            'item_id' => $itemId,
            'action' => $action,
            'log' => json_encode($log)
        ]);

        return $eventId;
    }
}
