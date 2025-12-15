<?php

date_default_timezone_set('Asia/Kolkata'); 

function time_ago($timestamp) {
    // Convert timestamp to DateTime object
    $datetime1 = new DateTime("now");
    $datetime2 = new DateTime($timestamp);
    $interval = $datetime1->diff($datetime2);

    // Determine if the timestamp is in the past or future
    $isPast = $datetime2 < $datetime1;

    // Build the time ago string
    if ($interval->y >= 1) {
        $time = $interval->y . " year" . ($interval->y > 1 ? "s" : "");
    } elseif ($interval->m >= 1) {
        $time = $interval->m . " month" . ($interval->m > 1 ? "s" : "");
    } elseif ($interval->d >= 1) {
        $time = $interval->d . " day" . ($interval->d > 1 ? "s" : "");
    } elseif ($interval->h >= 1) {
        $time = $interval->h . " hour" . ($interval->h > 1 ? "s" : "");
    } elseif ($interval->i >= 1) {
        $time = $interval->i . " minute" . ($interval->i > 1 ? "s" : "");
    } elseif ($interval->s >= 1) {
        $time = $interval->s . " second" . ($interval->s > 1 ? "s" : "");
    } else {
        return "just now";
    }

    return $isPast ? "$time ago" : "in $time";
}

?>
