<?php

namespace ManyThings\Core;

class ErrorList
{
    const FORMAT_LIST = 'list';
    const FORMAT_ASSOC = 'assoc';
    const FORMAT_MESSAGES = 'messages';

    protected $list;

    public function __construct()
    {
        $this->list = [];
    }

    public function newItem($field, $message)
    {
        $this->list[] =
        [
            'field' => $field,
            'message' => $message
        ];
    }

    public function getList($format = self::FORMAT_LIST)
    {
        $list = [];

        switch ($format) {
            case self::FORMAT_ASSOC:
                foreach ($this->list as $item) {
                    $list[$item['field']] = $item['message'];
                }
                break;
            case self::FORMAT_MESSAGES:
                foreach ($this->list as $item) {
                    $list[] = $item['message'];
                }
                break;
            default:
                $list = $this->list;
        }

        return $list;
    }

    public function hasItems()
    {
        return count($this->list) > 0;
    }
}
