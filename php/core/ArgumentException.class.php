<?php

namespace ManyThings\Core;

class ArgumentException extends \Exception
{
    public $errorList;

    public function __construct($e)
    {
        if ($e instanceof ErrorList) {
            $this->errorList = $e;
        } elseif (is_array($e)) {
            $this->errorList = new ErrorList();
            foreach ($e as $key => $val) {
                $this->errorList->newItem($key, $val);
            }
        } else {
            $this->errorList = new ErrorList();
            $this->errorList->newItem('', $e);
        }

        $message = Utils::arrayToText($this->getErrors());

        parent::__construct($message);
    }

    public function getErrors()
    {
        return $this->errorList->getList(ErrorList::FORMAT_ASSOC);
    }
}
