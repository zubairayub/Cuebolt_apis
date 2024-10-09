<?php

if (!function_exists('format_date')) {
    /**
     * Format a date to a readable format.
     *
     * @param string $date
     * @return string
     */
    function format_date($date)
    {
        return \Carbon\Carbon::parse($date)->toFormattedDateString(); // Customize as needed
    }

    function format_date_time($date)
    {
        return \Carbon\Carbon::parse($date)->toDateTimeString(); // Customize as needed
    }

    function custom_date_time($date)
    {
        return \Carbon\Carbon::parse($date)->format('Y-m-d H:i:s'); // Customize as needed
    }
}
