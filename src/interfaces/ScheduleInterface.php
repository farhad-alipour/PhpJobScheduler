<?php

namespace main_core;

interface ScheduleInterface
{
    public function job($function);

    public function after($function);

    public function before($function);

    public function when();

    public function addJob();

    public function setTimezone($timezone);

    public function checkIsTrueSchedule();

    public function getExecuteExamples(int $count);

    public function getNextExecuteTime($output_type = "timestamp");

    public function getErrorLists();


    public function everyMinute($minute);

    public function everyHour($hour);

    public function hour($hour);

    public function minute($minute = 00);

    public function day();

    public function month();


    public function onDateAndTime($timestamp);

    public function daily();

    public function monthly();

    public function yearly();

    public function weekdays(array $day);

    public function fromDateAndTime($timestamp);

    public function toDateAndTime($timestamp);

    public function between($start_hour,$start_minute,$end_hour,$end_minute);

}