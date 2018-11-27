<?php

namespace ManyThings\Core\Dal;

class SessionsDal extends ModelDal
{
    protected $source = 'sessions';
    protected $updateCol = 'time';

    public function expireSessions($timeout)
    {
        $sql = "DELETE FROM sessions
                WHERE time < '" . $timeout . "'";

        return $this->sqlDelete($sql);
    }
}
