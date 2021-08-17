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


    public function everyMinute($minute , $from_year = null ,$from_month = null , $from_day = null , $from_hour = null , $from_minute = null);

    public function everyHour($hour , $from_year = null ,$from_month = null , $from_day = null , $from_hour = null , $from_minute = null);

    public function everyDay($hour = null , $minute = null);

    public function everyWeek(array $days , $hour = null , $minute = null);

    public function everyMonth(array $days , $hour = null , $minute = null);

    public function everyYear(array $monthsAndDays , $hour = null , $minute = null);


    public function onDateAndTime($timestamp);

    public function daily();

    public function monthly();

    public function yearly();

    public function weekdays(array $day);

    public function fromDateAndTime($timestamp);

    public function toDateAndTime($timestamp);

    public function between($start_hour,$start_minute,$end_hour,$end_minute);

}