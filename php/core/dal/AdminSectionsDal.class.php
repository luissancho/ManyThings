<?php

namespace ManyThings\Core\Dal;

class AdminSectionsDal extends ModelDal
{
    protected $source = 'admin_sections';

    protected $relations = [
        'AdminPermissions' => [
            'type' => 'has_many',
            'foreign_key' => 'section_id'
        ]
    ];

    public function getLastOrd($tab)
    {
        $sql = 'SELECT MAX(t.ord)
    			FROM ' . $this->getTableSql() . "
    			WHERE t.tab = '" . $this->sqlEscape($tab) . "'";

        return $this->sqlGetVar($sql);
    }

    public function moveOrds($tab, $int, $from, $to)
    {
        $sql = 'UPDATE ' . $this->getTableSql() . '
                SET
                    t.ord = t.ord + (' . $int . ")
                WHERE t.tab = '" . $this->sqlEscape($tab) . "'
                    AND t.ord >= " . $from . '
                    AND t.ord < ' . $to;

        return $this->sqlUpdate($sql);
    }

    public function setAdminPermissions($sectionId)
    {
        $sql = "UPDATE admin_permissions
                SET
                    `view` = '1',
                    `edit` = '1',
                    `add` = '1',
                    `delete` = '1',
                    `export` = '1',
                    `admin` = '1',
                    `nav` = '1'
                WHERE role_id = 1
                  AND section_id = " . $sectionId;

        return $this->sqlUpdate($sql);
    }
}
