<?php

namespace main_core;

class Schedule implements ScheduleInterface
{

    protected $job_function;

    protected $after_callback;

    protected $before_callback;

    protected bool $job_status = false;

    protected int $day = -1;

    protected int $month = -1;

    protected int $hour = -1;

    protected  int $minute = -1;


    protected function getExecuteTime(){

    }

    /**
     * @param $function
     * @return mixed
     */
    public function job($function)
    {
        // TODO: Implement job() method.
    }

    /**
     * @param $function
     * @return mixed
     */
    public function after($function)
    {
        // TODO: Implement after() method.
    }

    /**
     * @param $function
     * @return mixed
     */
    public function before($function)
    {
        // TODO: Implement before() method.
    }

    /**
     * @return mixed
     */
    public function when()
    {
        // TODO: Implement when() method.
    }

    /**
     * @return mixed
     */
    public function addJob()
    {
        // TODO: Implement addJob() method.
    }

    /**
     * @param $timezone
     * @return mixed
     */
    public function setTimezone($timezone)
    {
        // TODO: Implement setTimezone() method.
    }

    /**
     * @return mixed
     */
    public function checkIsTrueSchedule()
    {
        // TODO: Implement checkIsTrueSchedule() method.
    }

    /**
     * @param int $count
     * @return mixed
     */
    public function getExecuteExamples(int $count)
    {
        // TODO: Implement getExecuteExamples() method.
    }

    /**
     * @param string $output_type
     * @return mixed
     */
    public function getNextExecuteTime($output_type = "timestamp")
    {
        // TODO: Implement getNextExecuteTime() method.
    }

    /**
     * @return mixed
     */
    public function getErrorLists()
    {
        // TODO: Implement getErrorLists() method.
    }

    /**
     * @param $minute
     * @return mixed
     */
    public function everyMinute($minute)
    {
        // TODO: Implement everyMinute() method.
    }

    /**
     * @param $hour
     * @return mixed
     */
    public function everyHour($hour)
    {
        // TODO: Implement everyHour() method.
    }

    /**
     * @param $hour
     * @return mixed
     */
    public function hour($hour)
    {
        // TODO: Implement hour() method.
    }

    /**
     * @param int $minute
     * @return mixed
     */
    public function minute($minute = 00)
    {
        // TODO: Implement minute() method.
    }

    /**
     * @return mixed
     */
    public function day()
    {
        // TODO: Implement day() method.
    }

    /**
     * @return mixed
     */
    public function month()
    {
        // TODO: Implement month() method.
    }

    /**
     * @param $timestamp
     * @return mixed
     */
    public function onDateAndTime($timestamp)
    {
        // TODO: Implement onDateAndTime() method.
    }

    /**
     * @return mixed
     */
    public function daily()
    {
        // TODO: Implement daily() method.
    }

    /**
     * @return mixed
     */
    public function monthly()
    {
        // TODO: Implement monthly() method.
    }

    /**
     * @return mixed
     */
    public function yearly()
    {
        // TODO: Implement yearly() method.
    }

    /**
     * @param array $day
     * @return mixed
     */
    public function weekdays(array $day)
    {
        // TODO: Implement weekdays() method.
    }

    /**
     * @param $timestamp
     * @return mixed
     */
    public function fromDateAndTime($timestamp)
    {
        // TODO: Implement fromDateAndTime() method.
    }

    /**
     * @param $timestamp
     * @return mixed
     */
    public function toDateAndTime($timestamp)
    {
        // TODO: Implement toDateAndTime() method.
    }

    /**
     * @param $start_hour
     * @param $start_minute
     * @param $end_hour
     * @param $end_minute
     * @return mixed
     */
    public function between($start_hour, $start_minute, $end_hour, $end_minute)
    {
        // TODO: Implement between() method.
    }
}