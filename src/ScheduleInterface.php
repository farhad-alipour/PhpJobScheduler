<?php

namespace darcob_jobscheduler;

use Closure;

interface ScheduleInterface
{
    public function job(closure $function);

    public function onErrorListener(closure $function, bool $output_error = false);

    public function preventOverlapping(int $max_minute);

    public function addJob();

    public function setTimezone($timezone);

    public function checkIsTrueSchedule();

    public function getExecuteExamples(int $count);

    public function getNextExecuteTime();

    public function getErrorLists();


    public function everyMinute(int $n_minute);

    public function everyHour(int $n_hour);

    public function everyDay(string $hour, string $minute);

    public function everyWeek(array $days_of_week, string $hour, string $minute);

    public function everyMonth(array $days, string $hour, string $minute);

    public function everyYear(array $months, array $days, string $hour, string $minute);

    public function onDateAndTime(string $year, string $month, string $day, string $hour, string $minute);


    public function fromDateAndTime(string $from_year, string $from_month, string $from_day, string $from_hour, string $from_minute);

    public function toDateAndTime(string $to_year, string $to_month, string $to_day, string $to_hour, string $to_minute);

    public function between(string $start_hour, string $start_minute, string $end_hour, string $end_minute, string $start_year = null, string $start_month = null, string $start_day = null, string $end_year = null, string $end_month = null, string $end_day = null);

}