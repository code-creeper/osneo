<?php

use Carbon\Carbon;

function carbon($time): Carbon
{
    return new Carbon($time);
}

function hrToMins($hours, $minutes = 0)
{
    return $minutes + ($hours * 60);
}

function durationInputToMinutes($duration, $abs = true): mixed
{
    $durationIsNegative = str($duration)->startsWith('-');

    $duration = str($duration)->remove('-')->explode(':');

    $hours = str($duration[0] ?? 0)->toInteger();
    $minutes = str($duration[1] ?? 0)->toInteger();

    if ($abs || ! $durationIsNegative) {
        return hrToMins($hours, $minutes);
    }

    return 0 - hrToMins($hours, $minutes);
}

function getAttendanceTypeFromDuration($duration): string
{
    return str($duration)->startsWith('-') ? 'break' : 'attendance';
}

function minutesToDurationInput($minutes): mixed
{
    if ( ! $minutes) {
        return $minutes;
    }

    if (str($minutes)->startsWith('-')) {
        return "-".formatMins($minutes);
    }

    return formatMins($minutes);
}

function formatHrs($hours, $descriptive = false): string
{
    $minutes = $hours * 60;

    return formatMins($minutes, $descriptive);
}

function formatMins($mins, $descriptive = false): string
{
    if ( ! $mins) {
        return '-';
    }

    $minutes = sprintf('%02d',
        fmod($mins, 60)
    );

    $h = $mins / 60;
    if ($h > 0) {
        $sign = '';
        $hours = floor($h);
    } else {
        $sign = '-';
        $hours = ceil($h);
    }

    $hours = abs($hours);
    $hours = sprintf('%02d', $hours);

    if ( ! $descriptive) {
        return "$hours:".sprintf('%02d', abs($minutes));
    }

    $string = $sign."{$hours} ".__('hr');
    if ($minutes > 0) {
        $string .= " {$minutes} ".__('min');
    } elseif ($minutes < 0) {
        $string .= " ".abs($minutes).__('min');
    }

    return $string;
}

function ceilDecimal($number, $decimal = 2): float|int
{
    return ceil($number * pow(10, $decimal)) / pow(10, $decimal);
}

function getGitBranch($short = true): string
{
    $file = base_path().'/.git/HEAD';
    if (File::missing($file)) {
        return 'No ref';
    }
    $head = File::get($file);

    if ($short) {
        $explode = explode('/', $head);

        return trim(end($explode));
    }

    return trim($head);
}

function getGitCommit($short = true): string
{
    if (getGitBranch() == 'No ref') {
        return '';
    }

    $file = base_path().'/.git/'.substr(getGitBranch(false), 5);
    if (File::missing($file)) {
        return '';
    }
    $commit = File::get($file);

    if ($short) {
        return substr($commit, 0, 7);
    }

    return $commit;
}

function prepareTreeData($node, $filter, $filters, $currentLevel = []): string
{
    $icon = 'fas fa-folder text-warning';

    $opened = $status = isNodeOpened($node, $filter, $filters, $currentLevel);

    if ($node == \App\Models\Document::$trashDir) {
        $icon = '/images/recycle_bin_alt.png';
    }

    if ($node == \App\Models\Document::$sortedDir) {
        $opened = true;
    }

    return '{"opened":'.$opened.', "selected": '.$status.', "icon": "'.$icon.'"}';
}

function isNodeOpened($node, $filter, $filters, $currentLevel = []): string
{
    if (in_array($filter, ['doc_type', 'year', 'month'])) {
        if (isset($currentLevel['source'], $filters['source'])) {
            $diffSrc = $currentLevel['source'] !== $filters['source'];
            if ($filter == 'doc_type' && $diffSrc) {
                return 'false';
            }

            if ($filter == 'year' && isset($filters['doc_type'])) {
                $diffDoc = $currentLevel['doc_type'] !== $filters['doc_type'];
                if ($diffDoc || $diffSrc) {
                    return 'false';
                }
            }

            if ($filter == 'month' && isset($filters['doc_type'], $filters['year'])) {
                $diffDoc = $currentLevel['doc_type'] !== $filters['doc_type'];
                $diffYear = $currentLevel['year'] != $filters['year'];
                if ($diffDoc || $diffSrc || $diffYear) {
                    return 'false';
                }
            }
        }
    }

    return ($filters[$filter] ?? null) == $node ? 'true' : 'false';
}

function dateToCarbon($date, $format = 'Y-m-d', $defaultDate = null)
{
    if ( ! $date) {
        return $defaultDate;
    }

    if (is_a($date, Carbon::class)) {
        return $date;
    }

    if ($format == 'Y-m-d') {
        return Carbon::parse($date);
    }

    return Carbon::createFromFormat($format, $date);
}
