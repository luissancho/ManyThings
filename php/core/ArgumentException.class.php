<?php

namespace ManyThings\Core;

class ArgumentException extends \Exception
{
    public $errorList;

    public function __construct($list)
    {
        $this->errorList = $list;

        $message = Utils::arrayToText($this->getErrors());

        parent::__construct($message);
    }

    public function getErrors()
    {
        return $this->errorList->getList(ErrorList::FORMAT_ASSOC);
    }
}
