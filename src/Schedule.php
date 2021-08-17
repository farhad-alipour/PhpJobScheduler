<?php

namespace main_core;

class Schedule implements ScheduleInterface
{

    protected $job_function;

    protected $after_callback;

    protected $before_callback;

    protected array $schedule_errors = [];

    protected string $timezone = "Asia/Tehran";

    protected int $run_time = 0;

    protected bool $job_status = false;

    protected int $day = -1;

    protected int $month = -1;

    protected int $hour = -1;

    protected  int $minute = -1;


    /**
     * Schedule constructor.
     */
    public function __construct()
    {
        if(!isset($GLOBALS['ScheduleRunTime']))
            $this->addScheduleError("global variable 'ScheduleRunTime' is not set , constructor");
        elseif(!is_numeric($GLOBALS['ScheduleRunTime']))
            $this->addScheduleError("global variable 'ScheduleRunTime' is not integer , constructor");
        else
            $this->run_time = $GLOBALS['ScheduleRunTime'];
    }

    protected function getExecuteTime(){

    }

    /**
     * @param $function
     * @return $this
     */
    public function job($function): Schedule
    {
        if(is_callable($function))
            $this->job_function = $function;
        else
            $this->addScheduleError("given parameter 'job' is not a function , method : job");
        return $this;
    }

    /**
     * @param $function
     * @return $this
     */
    public function after($function): Schedule
    {
        if(is_callable($function))
            $this->after_callback = $function;
        else
            $this->addScheduleError("given parameter 'job' is not a function , method : after");
        return $this;
    }

    /**
     * @param $function
     * @return $this
     */
    public function before($function): Schedule
    {
        if(is_callable($function))
            $this->before_callback = $function;
        else
            $this->addScheduleError("given parameter 'job' is not a function , method : before");
        return $this;
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
     * @return $this
     */
    public function setTimezone($timezone): Schedule
    {
        $timezone_list = timezone_identifiers_list();
        if(in_array($timezone,$timezone_list))
            $this->timezone = $timezone;
        else
            $this->addScheduleError("given parameter 'timezone' is not a standard timezone . more help -> https://www.php.net/manual/en/timezones.php , method : timezone");
        return $this;
    }

    /**
     * @return mixed
     */
    public function checkIsTrueSchedule()
    {
        /*if(empty($this->schedule_errors))
            return true;
        else
            return false;*/
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
     * @return array
     */
    public function getErrorLists(): array
    {
        return array_reverse($this->schedule_errors);
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
     * @return $this
     */
    public function hour($hour): Schedule
    {
        if(!is_numeric($hour))
            $this->addScheduleError("given parameter 'hour' is not integer , method : hour");
        elseif(strlen($hour) != 2)
            $this->addScheduleError("given parameter 'hour' length is less than 2 . Examples -> 12,23,00,05 , method : hour");
        elseif($hour < 0 || $hour > 23)
            $this->addScheduleError("given parameter 'hour' is not between 00 and 23 , method : hour");
        else
            $this->hour = $hour;
        return $this;
    }

    /**
     * @param int $minute
     * @return $this
     */
    public function minute($minute = 00): Schedule
    {
        if(!is_numeric($minute))
            $this->addScheduleError("given parameter 'minute' is not integer , method : minute");
        elseif(strlen($minute) != 2)
            $this->addScheduleError("given parameter 'minute' length is less than 2 . Examples -> 53,30,09,03 , method : minute");
        elseif($minute < 0 || $minute > 59)
            $this->addScheduleError("given parameter 'minute' is not between 00 and 59 , method : minute");
        else
            $this->minute = $minute;
        return $this;
    }

    /**
     * @return $this
     */
    public function day($day): Schedule
    {
        if(!is_numeric($day))
            $this->addScheduleError("given parameter 'day' is not integer , method : day");
        elseif(strlen($day) != 2)
            $this->addScheduleError("given parameter 'day' length is less than 2 . Examples -> 05,29,09,11 , method : day");
        elseif($day < 1 || $day > 31)
            $this->addScheduleError("given parameter 'day' is not between 01 and 31 , method : day");
        else
            $this->day = $day;
        return $this;
    }

    /**
     * @return $this
     */
    public function month($month): Schedule
    {
        if(!is_numeric($month))
            $this->addScheduleError("given parameter 'month' is not integer , method : month");
        elseif(strlen($month) != 2)
            $this->addScheduleError("given parameter 'month' length is less than 2 . Examples -> 05,03,09,12 , method : month");
        elseif($month < 1 || $month > 12)
            $this->addScheduleError("given parameter 'month' is not between 01 and 12 , method : month");
        else
            $this->month = $month;
        return $this;
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

    private function addScheduleError(string $message)
    {
        $this->schedule_errors[count($this->schedule_errors)] = $message;
    }
}