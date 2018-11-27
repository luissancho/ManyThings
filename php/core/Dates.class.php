<?php

namespace ManyThings\Core;

use DateInterval;
use DateTime;
use DateTimeZone;

class Dates extends DateTime
{
    const FORMAT_DEFAULT = 'Y-m-d H:i:s';

    public function __construct($time = null, $format = null, $timezone = null)
    {
        $config = DI::getDI()->config->date;

        if (is_string($timezone)) {
            $timezone = new DateTimeZone($timezone);
        } elseif (!$timezone) {
            $timezone = new DateTimeZone($config->timezone);
        }

        $date = self::getDate($time, $format, $timezone);

        parent::__construct($date, $timezone);
    }

    public static function isValid($time = null, $format = null, $timezone = null)
    {
        $config = DI::getDI()->config->date;

        if (is_string($timezone)) {
            $timezone = new DateTimeZone($timezone);
        } elseif (!$timezone) {
            $timezone = new DateTimeZone($config->timezone);
        }

        return self::getDate($time, $format, $timezone) !== false;
    }

    protected static function getDate($time = null, $format = null, $timezone = null)
    {
        $date = null;

        if (is_object($time) && $time instanceof DateTime) {
            $date = $time;
        } elseif (is_numeric($time)) {
            $date = self::createFromFormat('U', $time, $timezone);
        } elseif (is_string($time)) {
            if ($format) {
                $date = self::createFromFormat($format, $time, $timezone);
            } else {
                $date = self::createFromString($time);
            }
        } elseif (!$time) {
            $date = date_create('now', $timezone);
        }

        if ($date) {
            return $date->format(self::FORMAT_DEFAULT);
        }

        return false;
    }

    protected static function createFromString($time)
    {
        if ($date = self::parseSql($time)) {
            return $date;
        }

        if ($date = self::parseCustom($time)) {
            return $date;
        }

        if ($date = date_create($time)) {
            return $date;
        }

        return false;
    }

    protected static function parseSql($time)
    {
        $format = 'Y-m-d';
        if ($date = self::createFromFormat($format, $time)) {
            return $date->setTime(0, 0, 0);
        }

        $format .= ' H:i:s';
        if ($date = self::createFromFormat($format, $time)) {
            return $date;
        }

        return false;
    }

    protected static function parseCustom($time)
    {
        $config = DI::getDI()->config->date;

        $format = $config->format_date;
        if ($date = self::createFromFormat($format, $time)) {
            return $date->setTime(0, 0, 0);
        }

        $format .= ' ' . $config->format_time;
        if ($date = self::createFromFormat($format, $time)) {
            return $date;
        }

        return false;
    }

    public function formatSql()
    {
        return $this->format('Y-m-d H:i:s');
    }

    public function formatIso()
    {
        return $this->format('c');
    }

    public function formatZulu()
    {
        return $this->format('Y-m-d\TH:i:s.000\Z');
    }

    public function formatString($hms = false)
    {
        $config = DI::getDI()->config->date;

        $format = $config->format_date;
        if ($hms) {
            $format .= ' ' . $config->format_time;
        }

        return $this->format($format);
    }

    public function formatLong()
    {
        $config = DI::getDI()->config->date;

        return $this->format($config->format_long);
    }

    public function setToday()
    {
        return $this->setTime(0, 0, 0);
    }

    public function setWeek()
    {
        return $this->move('-P' . $this->format('w') . 'D')->setToday();
    }

    public function setMonth()
    {
        return $this->setDate($this->format('Y'), $this->format('n'), 1)->setToday();
    }

    public static function create($time = null, $format = null, $timezone = null)
    {
        return new self($time, $format, $timezone);
    }

    public static function now()
    {
        return self::create();
    }

    public static function today()
    {
        return self::create()->setToday();
    }

    public static function month()
    {
        return self::create()->setMonth();
    }

    public static function timestampToday()
    {
        return self::today()->getTimestamp();
    }

    public static function timestampDate($time = null)
    {
        return self::create($time)->getTimestamp();
    }

    public static function sqlNow()
    {
        return self::now()->formatSql();
    }

    public static function sqlToday()
    {
        return self::today()->formatSql();
    }

    public static function sqlDate($time = null)
    {
        return self::create($time)->formatSql();
    }

    public static function stringNow()
    {
        return self::now()->formatString(true);
    }

    public static function stringToday()
    {
        return self::today()->formatString();
    }

    public static function stringDate($time = null, $hms = false)
    {
        return self::create($time)->formatString($hms);
    }

    public function isBetween($from, $to)
    {
        return $this >= $from && $this <= $to;
    }

    public function setTimezone($timezone)
    {
        return parent::setTimezone(new DateTimeZone($timezone));
    }

    public function copy($interval = null)
    {
        $date = self::create($this, null, $this->getTimezone());

        if ($interval) {
            $date->move($interval);
        }

        return $date;
    }

    public function move($interval)
    {
        // -PT5M | +P4H | P3D | P2M | P1Y
        $op = 'add';

        $first = substr($interval, 0, 1);
        switch ($first) {
            case '+':
                $interval = substr($interval, 1);
                break;
            case '-':
                $op = 'sub';
                $interval = substr($interval, 1);
                break;
        }

        return $this->{$op}(new DateInterval($interval));
    }

    public function diffExp($time = null)
    {
        if (!is_object($time)) {
            $time = self::create($time, null, $this->getTimezone());
        }

        $diff = $this->diff($time)->format('%a,%h,%i,%s');
        list($days, $hours, $mins, $secs) = explode(',', $diff);

        return [
            'days' => $days,
            'hours' => $hours,
            'mins' => $mins,
            'secs' => $secs
        ];
    }

    public function diffString($time = null)
    {
        $diff = $this->diffExp($time);

        $sDiff = '';
        if ($diff['days'] > 0) {
            $sDiff .= ($diff['days'] > 1) ? $diff['days'] . ' ' . _T('days') : '1 ' . _T('day');
        } elseif ($diff['hours'] > 0) {
            $sDiff .= ($diff['hours'] > 1) ? $diff['hours'] . ' ' . _T('hours') : '1 ' . _T('hour');
        } elseif ($diff['mins'] > 0) {
            $sDiff .= ($diff['mins'] > 1) ? $diff['mins'] . ' ' . _T('mins') : '1 ' . _T('min');
        } else {
            $sDiff .= _T('seconds');
        }

        return $sDiff;
    }

    public static function getDateIntervals($date, $period, $total)
    {
        $time = self::timestampDate($date);
        $sqlIntervals = [];

        if ($period == 'year') {
            $firstYear = date('y', $time) - $total + 1;
            $firstDate = date('U', mktime(0, 0, 0, 1, 1, $firstYear));

            $lastYear = date('y', $time);
            $lastDate = date('U', mktime(0, 0, 0, 1, 1, $lastYear));

            for ($i = $firstYear, $date1 = 0; $date1 < $lastDate; $i++) {
                $date1 = date('U', mktime(0, 0, 0, 1, 1, $i));
                $date2 = date('U', mktime(0, 0, 0, 1, 1, $i + 1));
                $sqlIntervals[date('y', $date1)] = [$date1, $date2];
            }
        } elseif ($period == 'month') {
            $firstMonth = date('m', $time) - $total + 1;
            $firstDate = date('U', mktime(0, 0, 0, $firstMonth, 1, date('y', $time)));

            $lastMonth = date('m', $time);
            $lastDate = date('U', mktime(0, 0, 0, $lastMonth, 1, date('y', $time)));

            for ($i = $firstMonth, $date1 = 0; $date1 < $lastDate; $i++) {
                $date1 = date('U', mktime(0, 0, 0, $i, 1, date('y', $time)));
                $date2 = date('U', mktime(0, 0, 0, $i + 1, 1, date('y', $time)));
                $sqlIntervals[date('m', $date1) . '_' . date('y', $date1)] = [$date1, $date2];
            }
        } elseif ($period == 'week') {
            $firstDay = date('d', $time) - (7 * ($total - 1));
            $firstDate = date('U', mktime(0, 0, 0, date('m', $time), $firstDay, date('y', $time)));
            if (date('N', $firstDate) != 1) {
                $firstDay = $firstDay - date('N', $firstDate) + 1;
                $firstDate = date('U', mktime(0, 0, 0, date('m', $time), $firstDay, date('y', $time)));
            }

            $lastDay = date('d', $time);
            $lastDate = date('U', mktime(0, 0, 0, date('m', $time), $lastDay, date('y', $time)));
            if (date('N', $lastDate) != 1) {
                $lastDay = $lastDay - date('N', $lastDate) + 1;
                $lastDate = date('U', mktime(0, 0, 0, date('m', $time), $lastDay, date('y', $time)));
            }

            for ($i = $firstDay, $date1 = 0; $date1 < $lastDate; $i += 7) {
                $date1 = date('U', mktime(0, 0, 0, date('m', $time), $i, date('y', $time)));
                $date2 = date('U', mktime(0, 0, 0, date('m', $time), $i + 7, date('y', $time)));
                $sqlIntervals[date('d', $date1) . '_' . date('m', $date1)] = [$date1, $date2];
            }
        } elseif ($period == 'day') {
            $firstDay = date('d', $time) - $total + 1;
            $firstDate = date('U', mktime(0, 0, 0, date('m', $time), $firstDay, date('y', $time)));

            $lastDay = date('d', $time);
            $lastDate = date('U', mktime(0, 0, 0, date('m', $time), $lastDay, date('y', $time)));

            for ($i = $firstDay, $date1 = 0; $date1 < $lastDate; $i++) {
                $date1 = date('U', mktime(0, 0, 0, date('m', $time), $i, date('y', $time)));
                $date2 = date('U', mktime(0, 0, 0, date('m', $time), $i + 1, date('y', $time)));
                $sqlIntervals[date('d', $date1) . '_' . date('m', $date1)] = [$date1, $date2];
            }
        }

        return $sqlIntervals;
    }

    public static function getDateRangeIntervals($dateFrom, $dateTo, $period = '')
    {
        $timeFrom = self::timestampDate($dateFrom);
        $timeTo = self::timestampDate($dateTo);
        $sqlIntervals = [];

        if ($period == 'year') {
            $firstYear = date('y', $timeFrom);
            $lastDate = date('U', mktime(0, 0, 0, 1, 1, date('y', $timeTo)));

            for ($i = $firstYear, $date1 = 0; $date1 < $lastDate; $i++) {
                $date1 = date('U', mktime(0, 0, 0, 1, 1, $i));
                if ($date1 < $timeFrom) {
                    $date1 = date('U', mktime(0, 0, 0, date('m', $timeFrom), date('d', $timeFrom), date('y', $timeFrom)));
                }
                $date2 = date('U', mktime(0, 0, 0, 1, 1, $i + 1));
                if ($date2 > $timeTo) {
                    $date2 = date('U', mktime(0, 0, 0, date('m', $timeTo), date('d', $timeTo) + 1, date('y', $timeTo)));
                }

                $sqlIntervals[date('Y', $date1)] = [$date1, $date2];
            }
        } elseif ($period == 'month') {
            $firstMonth = date('m', $timeFrom);
            $lastDate = date('U', mktime(0, 0, 0, date('m', $timeTo), 1, date('y', $timeTo)));

            for ($i = $firstMonth, $date1 = 0; $date1 < $lastDate; $i++) {
                $date1 = date('U', mktime(0, 0, 0, $i, 1, date('y', $timeFrom)));
                if ($date1 < $timeFrom) {
                    $date1 = date('U', mktime(0, 0, 0, date('m', $timeFrom), date('d', $timeFrom), date('y', $timeFrom)));
                }
                $date2 = date('U', mktime(0, 0, 0, $i + 1, 1, date('y', $timeFrom)));
                if ($date2 > $timeTo) {
                    $date2 = date('U', mktime(0, 0, 0, date('m', $timeTo), date('d', $timeTo) + 1, date('y', $timeTo)));
                }

                $sqlIntervals[date('m', $date1) . '_' . date('y', $date1)] = [$date1, $date2];
            }
        } elseif ($period == 'week') {
            $firstDay = date('d', $timeFrom);
            if (date('N', mktime(0, 0, 0, date('m', $timeFrom), $firstDay, date('y', $timeFrom))) != 1) {
                $firstDay = $firstDay - date('N', $timeFrom) + 1;
            }
            $lastDate = date('U', mktime(0, 0, 0, date('m', $timeTo), date('d', $timeTo), date('y', $timeTo)));
            if (date('N', $lastDate) != 1) {
                $lastDate = date('U', mktime(0, 0, 0, date('m', $timeTo), date('d', $timeTo) - date('N', $lastDate) + 1, date('y', $timeTo)));
            }

            for ($i = $firstDay, $date1 = 0; $date1 < $lastDate; $i += 7) {
                $date1 = $date0 = date('U', mktime(0, 0, 0, date('m', $timeFrom), $i, date('y', $timeFrom)));
                if ($date1 < $timeFrom) {
                    $date1 = $timeFrom;
                }
                $date2 = date('U', mktime(0, 0, 0, date('m', $timeFrom), $i + 7, date('y', $timeFrom)));
                if ($date2 > $timeTo) {
                    $date2 = date('U', mktime(0, 0, 0, date('m', $timeTo), date('d', $timeTo) + 1, date('y', $timeTo)));
                }

                $sqlIntervals[date('d', $date0) . '_' . date('m', $date1)] = [$date1, $date2];
            }
        } elseif ($period == 'day') {
            $firstDay = date('d', $timeFrom);
            $lastDate = date('U', mktime(0, 0, 0, date('m', $timeTo), date('d', $timeTo), date('y', $timeTo)));

            for ($i = $firstDay, $date1 = 0, $date2 = 0; $date1 < $lastDate; $i++) {
                $date1 = date('U', mktime(0, 0, 0, date('m', $timeFrom), $i, date('y', $timeFrom)));
                $date2 = date('U', mktime(0, 0, 0, date('m', $timeFrom), $i + 1, date('y', $timeFrom)));

                $sqlIntervals[date('d', $date1) . '_' . date('m', $date1)] = [$date1, $date2];
            }
        } else {
            $date1 = date('U', mktime(0, 0, 0, date('m', $timeFrom), date('d', $timeFrom), date('y', $timeFrom)));
            $date2 = date('U', mktime(0, 0, 0, date('m', $timeTo), date('d', $timeTo) + 1, date('y', $timeTo)));

            $sqlIntervals[] = [$date1, $date2];
        }

        return $sqlIntervals;
    }

    public static function getDateRangeInterval($dateFrom, $dateTo)
    {
        $timeFrom = self::timestampDate($dateFrom);
        $timeTo = self::timestampDate($dateTo);

        $date1 = date('U', mktime(0, 0, 0, date('m', $timeFrom), date('d', $timeFrom), date('y', $timeFrom)));
        $date2 = date('U', mktime(0, 0, 0, date('m', $timeTo), date('d', $timeTo) + 1, date('y', $timeTo)));

        $sqlInterval = [$date1, $date2];

        return $sqlInterval;
    }
}
