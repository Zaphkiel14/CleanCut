<?php

if (!function_exists('timeAgo')) {
    /**
     * Convert a datetime to a human-readable "time ago" format
     *
     * @param string $datetime
     * @return string
     */
    function timeAgo($datetime)
    {
        if (empty($datetime)) {
            return 'Unknown';
        }

        $time = time() - strtotime($datetime);

        if ($time < 1) {
            return 'just now';
        }

        $condition = [
            12 * 30 * 24 * 60 * 60 => 'year',
            30 * 24 * 60 * 60       => 'month',
            24 * 60 * 60            => 'day',
            60 * 60                 => 'hour',
            60                      => 'minute',
            1                       => 'second'
        ];

        foreach ($condition as $secs => $str) {
            $d = $time / $secs;

            if ($d >= 1) {
                $t = round($d);
                return $t . ' ' . $str . ($t > 1 ? 's' : '') . ' ago';
            }
        }

        return 'just now';
    }
}

if (!function_exists('formatDateTime')) {
    /**
     * Format a datetime string
     *
     * @param string $datetime
     * @param string $format
     * @return string
     */
    function formatDateTime($datetime, $format = 'M d, Y g:i A')
    {
        if (empty($datetime)) {
            return 'N/A';
        }

        return date($format, strtotime($datetime));
    }
}

if (!function_exists('isToday')) {
    /**
     * Check if a date is today
     *
     * @param string $date
     * @return bool
     */
    function isToday($date)
    {
        return date('Y-m-d', strtotime($date)) === date('Y-m-d');
    }
}

if (!function_exists('isYesterday')) {
    /**
     * Check if a date is yesterday
     *
     * @param string $date
     * @return bool
     */
    function isYesterday($date)
    {
        return date('Y-m-d', strtotime($date)) === date('Y-m-d', strtotime('-1 day'));
    }
}
