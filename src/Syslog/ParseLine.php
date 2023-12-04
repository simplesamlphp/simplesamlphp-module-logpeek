<?php

declare(strict_types=1);

namespace SimpleSAML\Module\logpeek\Syslog;

use function getdate;
use function intval;
use function sprintf;
use function sscanf;
use function strtotime;

class ParseLine
{
    /**
     * @param int $time
     * @param string $logLine
     * @return bool
     */
    public static function isOlderThan(int $time, string $logLine): bool
    {
        return true;
    }


    /**
     * @param string $logLine
     * @param int|null $year
     * @return int|false
     */
    public static function getUnixTime(string $logLine, int $year = null)
    {
        // I can read month and day and time from the file.
        // but I will assume year is current year retured by time().
        // Unless month and day in the file is bigger than current month and day,
        // I will then assume previous year.
        // A better approach would be to get the year from last modification time (mtime) of the
        // file this record is taken from. But that requires knowledge about the file.
        if ($year === null) {
            $now = getdate();
            $year = intval($now['year']);
        }
        list($month, $day, $hour, $minute, $second) = sscanf($logLine, "%s %d %d:%d:%d ");
        $time = sprintf("%d %s %d %d:%d:%d", $day, $month, $year, $hour, $minute, $second);
        return strtotime($time);
    }
}
