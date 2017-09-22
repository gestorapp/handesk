<?php

use Carbon\Carbon;

/**
 * App Global definitions
 * App version will upgrade the app when it notices a change.
 * Will run automatized migrations...
 */
if (!defined('APP_NAME')) {
    define('APP_NAME', env('APP_NAME', 'Handesk'));
    define('SITE_SLOGAN', '');
    define('APP_VERSION', '0.0.1');
    define('RELEASES_URL', '#?-trello-landing');
}

/**
 * @param $array
 * @param $withNull
 * @return mixed
 */
function createSelectArray($array, $withNull = false)
{
    if (!$array) {
        return [];
    }
    $values = $array->pluck('name', 'id')->toArray();
    if ($withNull) {
        return ['' => '--'] + $values;
    }

    return $values;
}

/**
 * @param $object
 */
function nameOrDash($object)
{
    return ($object && $object->name) ? $object->name : '--';
}

/**
 * @param $icon
 */
function icon($icon)
{
    return FA::icon($icon);
}

/**
 * @param $email
 * @param $size
 */
function gravatar($email, $size = 30)
{
    $email = md5(strtolower(trim($email)));
    //$gravatarURL = "https://www.gravatar.com/avatar/" . $email."?s=".$size."&d=mm";
    $defaultImage = urlencode('https://raw.githubusercontent.com/BadChoice/handesk/master/public/images/default-avatar.png');
    $gravatarURL = 'https://www.gravatar.com/avatar/'.$email.'?s='.$size."&default={$defaultImage}";

    return '<img id = '.$email.''.$size.' class="gravatar" src="'.$gravatarURL.'" width="'.$size.'">';
}

/**
 * @param $minutes
 */
function toTime($minutes)
{
    $minutes_per_day = (Carbon::HOURS_PER_DAY * Carbon::MINUTES_PER_HOUR);
    $days = floor($minutes / ($minutes_per_day));
    $hours = floor(($minutes - $days * ($minutes_per_day)) / Carbon::MINUTES_PER_HOUR);
    $mins = (int) ($minutes - ($days * ($minutes_per_day)) - ($hours * 60));

    return "{$days} Days {$hours} Hours {$mins} Mins";
}

/**
 * @param $value
 * @param $inverse
 */
function toPercentage($value, $inverse = false)
{
    return ($inverse ? 1 - $value : $value) * 100;
}
