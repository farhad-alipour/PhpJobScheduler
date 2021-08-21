<?php

namespace main_core;


class JobRunner
{
    protected string $path = "tasks/";

    protected string $timezone = "Asia/Tehran";

    protected array $execute_results = [];

    /**
     * JobRunner constructor.
     */
    public function __construct()
    {
        $now = time();
        $minute = (string) jdate("i",$now,"",$this->timezone,"en");
        $hour = (string) jdate("H",$now,"",$this->timezone,"en");
        $day = (string) jdate("d",$now,"",$this->timezone,"en");
        $month = (string) jdate("m",$now,"",$this->timezone,"en");
        $year = (string) jdate("Y",$now,"",$this->timezone,"en");
        $GLOBALS['ScheduleRunTime'] = jmktime($hour,$minute,"00",$month,$day,$year);
    }

    public function execute() : void
    {
        foreach(glob($this->path . "*[CronJob].php") as $file){
            ob_start();
            include_once($file);
            $i = count($this->execute_results);
            $this->execute_results[$i]['filename'] = $file;
            $this->execute_results[$i]['output'] = ob_get_contents();
            ob_end_clean();
        }
    }

    /**
     * @param $timezone
     * @return $this
     */
    public function setTimezone($timezone): JobRunner
    {
        $timezone_list = timezone_identifiers_list();
        if(in_array($timezone,$timezone_list))
            $this->timezone = $timezone;
        return $this;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    /**
     * @return array
     */
    public function getExecuteResults(): array
    {
        return $this->execute_results;
    }

    public function setRunTime($timestamp): void
    {
        $minute = (string) jdate("i",$timestamp,"",$this->timezone,"en");
        $hour = (string) jdate("H",$timestamp,"",$this->timezone,"en");
        $day = (string) jdate("d",$timestamp,"",$this->timezone,"en");
        $month = (string) jdate("m",$timestamp,"",$this->timezone,"en");
        $year = (string) jdate("Y",$timestamp,"",$this->timezone,"en");
        $GLOBALS['ScheduleRunTime'] = jmktime($hour,$minute,"00",$month,$day,$year);
    }

}