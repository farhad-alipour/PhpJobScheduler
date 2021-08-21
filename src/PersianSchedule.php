<?php

namespace darcob_jobscheduler;

use Closure;
use Throwable;

class PersianSchedule implements ScheduleInterface
{
    protected string $unique_name = "";

    protected string $description = "";

    protected ?Closure $job_function = null;

    protected ?closure $on_error_callback = null;

    protected array $schedule_errors = [];

    protected string $timezone = "Asia/Tehran";

    protected bool $output_error_status = false;

    protected bool $error_handler_status = false;

    protected bool $prevent_overlapping_status = false;

    protected int $prevent_overlapping_max_minute = 60;

    protected string $schedule_type = "";

    protected array $schedule_type_parameters = [];

    protected int $run_time = 0;

    protected int $between_start_hour = -1;

    protected int $between_start_minute = -1;

    protected int $between_end_hour = -1;

    protected int $between_end_minute = -1;

    protected int $between_start_timestamp = -1;

    protected int $between_end_timestamp = -1;

    protected int $from_timestamp = -1;

    protected int $to_timestamp = -1;

    /**
     * Schedule constructor.
     */
    public function __construct($run_time = null)
    {
        if(!is_null($run_time))
            $this->run_time = $run_time;
        else{
            if(!isset($GLOBALS['ScheduleRunTime']))
                $this->addScheduleError("global variable 'ScheduleRunTime' is not set , constructor");
            elseif(!is_numeric($GLOBALS['ScheduleRunTime']))
                $this->addScheduleError("global variable 'ScheduleRunTime' is not integer , constructor");
            else
                $this->run_time = $GLOBALS['ScheduleRunTime'];
        }
    }

    /**
     * @return string
     */
    public function getUniqueName(): string
    {
        return $this->unique_name;
    }

    /**
     * @param string $unique_name
     * @return PersianSchedule
     */
    public function setUniqueName(string $unique_name): PersianSchedule
    {
        $this->unique_name = $unique_name;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return PersianSchedule
     */
    public function setDescription(string $description): PersianSchedule
    {
        $this->description = $description;
        return $this;
    }

    protected function getExecuteTime(){
        return $this->run_time;
    }

    /**
     * @param closure $function
     * @return $this
     */
    public function job(Closure $function): PersianSchedule
    {
        if(is_callable($function))
            $this->job_function = $function;
        else
            $this->addScheduleError("given parameter 'function' is not a function , method : job");
        return $this;
    }

    /**
     * @param closure $function
     * @param bool $output_error
     * @return $this
     */
    public function onErrorListener(Closure $function, bool $output_error = false): PersianSchedule
    {
        $this->output_error_status = true;
        if(is_callable($function))
            $this->on_error_callback = $function;
        else
            $this->addScheduleError("given parameter 'function' is not a function , method : onErrorListener");
        return $this;
    }

    /**
     * *
     * @param int $max_minute
     * @return void
     */
    public function preventOverlapping(int $max_minute = 60): void
    {
        if($this->unique_name != ""){
            $this->prevent_overlapping_status = true;
            $this->prevent_overlapping_max_minute = $max_minute;
        }
        else
            $this->addScheduleError("before calling 'preventOverlapping' method, you must first set a unique name , call setUniqueName()");
    }


    protected function lock(string $process): void
    {
        $base_path = dirname(__FILE__);
        $name = $base_path . "/lockfiles/" . $this->unique_name . ".txt";

        if($process == "lock") {
            $file = fopen($name, "w");
            fwrite($file,$this->run_time);
            fclose($file);
        }
        elseif ($process == "unlock"){
            if(file_exists($name)){
                unlink($name);
            }
        }
    }

    protected function lockStatus(): bool
    {
        $base_path = dirname(__FILE__);
        $name = $base_path . "/lockfiles/" . $this->unique_name . ".txt";

        if($this->prevent_overlapping_status){
            if(file_exists($name)){
                $file = fopen($name, "r");
                $read = fgets($file);
                if(is_numeric($read)){
                    $read = (int) $read;
                    $difference = $this->run_time - $read;
                    if($difference >= (60*$this->prevent_overlapping_max_minute)){
                        unlink($name);
                        return false;
                    }
                    else{
                        return true;
                    }
                }
                unlink($name);
                return false;
            }
            else
                return false;
        }
        else
            return false;
    }

    /**
     * @return void
     */
    public function addJob()
    {
        if(empty($this->schedule_errors) && $this->schedule_type != ""){
            $first = $this->getNextExecuteTime();
            if(!empty($first)){
                $display = $first[0]['display'];
                $first = $first[0]['timestamp'];
                if($first == $this->run_time){
                    if($this->job_function != null){

                        if(!$this->lockStatus()){

                            //fix bug in php 8 and php 7.4
                            set_error_handler(function ($errno, $errstr, $errfile, $errline){
                                if($this->on_error_callback != null && $this->error_handler_status == false){
                                    $this->error_handler_status = true;
                                    call_user_func($this->on_error_callback);

                                    if($this->output_error_status)
                                        echo "Error Message : " . $errstr . "\n" . "in file : " . $errfile . "\n" . "in line : " . $errline;

                                    if($this->prevent_overlapping_status)
                                        $this->lock("unlock");
                                }
                            });

                            try{
                                if($this->prevent_overlapping_status)
                                    $this->lock("lock");

                                call_user_func($this->job_function);

                                if($this->prevent_overlapping_status)
                                    $this->lock("unlock");
                            }
                            catch(Throwable $e){
                                //fix bug in php 8 and php 7.4
                                trigger_error($e->getMessage());
                            }
                        }
                        else
                            $this->addScheduleError("job could not be run because the overlap mechanism has stopped running");
                    }
                    else
                        $this->addScheduleError("run job error , your job is null");
                }
                else{
                    $this->addScheduleError($this->unique_name . " (job) - run in : " . $display);
                }
            }
            else
                $this->addScheduleError("run job error , schedule runs is empty , method : addJob");
        }
        else
            $this->addScheduleError("add job error , your job has an error or may not have defined it correctly");
    }

    /**
     * @param $timezone
     * @return $this
     */
    public function setTimezone($timezone): PersianSchedule
    {
        $timezone_list = timezone_identifiers_list();
        if(in_array($timezone,$timezone_list))
            $this->timezone = $timezone;
        else
            $this->addScheduleError("given parameter 'timezone' is not a standard timezone . more help -> https://www.php.net/manual/en/timezones.php , method : timezone");
        return $this;
    }

    /**
     * @return bool
     */
    public function checkIsTrueSchedule(): bool
    {
        if(empty($this->schedule_errors) && $this->schedule_type != "")
            return true;
        else
            return false;
    }

    /**
     * @param int $count
     * @return array
     */
    public function getExecuteExamples(int $count): array
    {
        if(isset($this->schedule_type_parameters[0]))
            $param_0 = $this->schedule_type_parameters[0];

        if(isset($this->schedule_type_parameters[1]))
            $param_1 = $this->schedule_type_parameters[1];

        if(isset($this->schedule_type_parameters[2]))
            $param_2 = $this->schedule_type_parameters[2];

        if(isset($this->schedule_type_parameters[3]))
            $param_3 = $this->schedule_type_parameters[3];

        if(isset($this->schedule_type_parameters[4]))
            $param_4 = $this->schedule_type_parameters[4];

        $arr = array();

        switch ($this->schedule_type){
            case "everyYear":
                $arr = $this->getYearlyRunSchedules($param_0,$param_1,$param_2,$param_3,$count);
                break;
            case "everyMonth":
                $arr = $this->getMonthlyRunSchedules($param_0,$param_1,$param_2,$count);
                break;
            case "everyWeek":
                $arr = $this->getWeeklyRunSchedules($param_0,$param_1,$param_2,$count);
                break;
            case "everyDay":
                $arr = $this->getDailyRunSchedules($param_0,$param_1,$count);
                break;
            case "everyHour":
                $arr = $this->getHourlyRunSchedules($param_0,$count);
                break;
            case "everyMinute":
                $arr = $this->getMinuteRunSchedules($param_0,$count);
                break;
            case "onDateAndTime":
                $timestamp = jmktime($param_3,$param_4,"00",$param_1,$param_2,$param_0,"",$this->timezone);
                $arr[0]['display'] = jdate("Y/m/d - H:i",$timestamp,"",$this->timezone,"en");
                $arr[0]['timestamp'] = $timestamp;
                break;
            default:
                $this->addScheduleError("schedule_type is empty , You must first call other methods example : everyMinute or everyDay or etc.");
        }
        return $arr;
    }

    /**
     * @return array
     */
    public function getNextExecuteTime(): array
    {
        $new_arr = array();
        if($this->schedule_type != ""){
            $arr = $this->getExecuteExamples(1);
            if(!empty($arr)){
                $new_arr[0] = $arr[0];
            }
        }
        return $new_arr;
    }

    /**
     * @return array
     */
    public function getErrorLists(): array
    {
        return $this->schedule_errors;
    }

    private function getParamFromRunTime($get)
    {
        if($this->run_time != 0){
            switch ($get){
                case "minute":
                    $param = "i";
                    break;
                case "hour":
                    $param = "H";
                    break;
                case "day":
                    $param = "d";
                    break;
                case "month":
                    $param = "m";
                    break;
                case "year":
                    $param = "Y";
                    break;
                case "dayofweek":
                    $param = "w";
                    break;
                case "last_day_of_month":
                    $param = "t";
                    break;
                default:
                    $this->addScheduleError("given parameter 'get' in method 'getParamFromRunTime' is incorrect");
                    return false;
            }
            return jdate($param,$this->run_time,"",$this->timezone,"en");
        }
        else{
            $this->addScheduleError("run_time not set");
            return false;
        }
    }

    private function getFirstNearestDayOfWeekForRun(array $days_of_week_array, $now)
    {
        if(!empty($days_of_week_array) && ($now >= 0 && $now <= 6)){
            for ($i = $now; $i <= 6; $i++) {
                if (in_array($i, $days_of_week_array))
                    return $i;
            }
            for ($i = 0; $i <= $now; $i++) {
                if (in_array($i, $days_of_week_array))
                    return $i;
            }
            return false;
        }
        else
            return false;
    }

    private function getFirstNearestDayForRun(array $days_array, $now)
    {
        if(!empty($days_array) && ($now >= 1 && $now <= 31)){
            for ($i = $now; $i <= 31; $i++) {
                if (in_array($i, $days_array))
                    return $i;
            }
            for ($i = 1; $i <= $now; $i++) {
                if (in_array($i,$days_array))
                    return $i;
            }
            return false;
        }
        else
            return false;
    }

    private function getMonthlyRunSchedules(array $days , $hour , $minute , $count): array
    {
        if(!empty($days) && (max($days) <= 31 && min($days) >= 1)){
            sort($days);
            $temp_year = (string) $this->getParamFromRunTime("year");
            $temp_month = (string) $this->getParamFromRunTime("month");
            $schedule_run = array();
            if($count <= 0)
                $count = 10;
            $limit = 0;
            while (count($schedule_run) <= $count){
                foreach ($days as $day){
                    if(jcheckdate($temp_month,$day,$temp_year)){
                        $timestamp = jmktime($hour,$minute,"00",$temp_month,$day,$temp_year,"",$this->timezone);
                        if($timestamp >= $this->run_time){

                            if($this->from_timestamp != -1){
                                if($this->from_timestamp > $timestamp)
                                    continue;
                            }

                            if($this->to_timestamp != -1){
                                if($this->to_timestamp < $timestamp)
                                    break 2;
                            }

                            if($this->between_start_timestamp != -1 && $this->between_end_timestamp != -1){
                                if($this->between_end_timestamp < $timestamp)
                                    break 2;
                                if($this->between_start_timestamp > $timestamp || $this->between_end_timestamp < $timestamp)
                                    continue;
                            }
                            elseif($this->between_start_hour != -1 && $this->between_start_minute != -1 && $this->between_end_hour != -1 && $this->between_end_minute != -1){
                                $f_start_hour = (string) $this->between_start_hour;
                                $f_end_hour = (string) $this->between_end_hour;
                                $f_start_minute = (string) $this->between_start_minute;
                                $f_end_minute = (string) $this->between_end_minute;
                                $f_day = (string) jdate("d",$timestamp,"",$this->timezone,"en");
                                $f_month = (string) jdate("m",$timestamp,"",$this->timezone,"en");
                                $f_year = (string) jdate("Y",$timestamp,"",$this->timezone,"en");

                                $f_start_timestamp = jmktime($f_start_hour,$f_start_minute,"00",$f_month,$f_day,$f_year,"",$this->timezone);
                                $f_end_timestamp = jmktime($f_end_hour,$f_end_minute,"00",$f_month,$f_day,$f_year,"",$this->timezone);

                                if($f_start_timestamp >= $f_end_timestamp){
                                    $this->addScheduleError("start_timestamp is more or equal then end_timestamp , method getMonthlyRunSchedules");
                                    break 2;
                                }

                                if($limit == 2000)
                                    break 2;

                                if($f_start_timestamp > $timestamp || $f_end_timestamp < $timestamp){
                                    $limit++;
                                    continue;
                                }
                            }

                            $element = count($schedule_run);
                            $schedule_run[$element]['display'] = jdate("Y/m/d - H:i:s",$timestamp,"",$this->timezone,"en");
                            $schedule_run[$element]['timestamp'] = $timestamp;
                        }
                    }
                }
                if($temp_month == 12){
                    $temp_month = 1;
                    $temp_year++;
                }
                else
                    $temp_month++;
            }
            return $schedule_run;
        }
        else{
            $this->addScheduleError("given parameter 'days' is empty array or is out range array(1,..,31) , method getMonthlyRunSchedules");
            return array();
        }
    }

    private function getWeeklyRunSchedules(array $days_of_week , $hour , $minute , $count): array
    {
        if(!empty($days_of_week) && (max($days_of_week) <= 6 && min($days_of_week) >= 0)){
            sort($days_of_week);
            $schedule_run = array();
            if($count <= 0)
                $count = 10;
            $timestamp = $this->run_time;
            $limit = 0;
            while (count($schedule_run) <= $count){
                $number_of_week = (int) jdate("w",$timestamp,"",$this->timezone,"en");
                if(in_array($number_of_week,$days_of_week)){
                    $temp_year = (string) jdate("Y",$timestamp,"",$this->timezone,"en");
                    $temp_month = (string) jdate("m",$timestamp,"",$this->timezone,"en");
                    $temp_day = (string) jdate("d",$timestamp,"",$this->timezone,"en");
                    $s_timestamp = jmktime($hour,$minute,"00",$temp_month,$temp_day,$temp_year,"",$this->timezone);
                    $timestamp = $s_timestamp;
                    if($s_timestamp >= $this->run_time){

                        if($this->from_timestamp != -1){
                            if($this->from_timestamp > $timestamp) {
                                $timestamp = $timestamp + (60*60*24);
                                continue;
                            }
                        }

                        if($this->to_timestamp != -1){
                            if($this->to_timestamp < $timestamp)
                                break;
                        }

                        if($this->between_start_timestamp != -1 && $this->between_end_timestamp != -1){
                            if($this->between_end_timestamp < $timestamp)
                                break;
                            if($this->between_start_timestamp > $timestamp || $this->between_end_timestamp < $timestamp) {

                                $timestamp = $timestamp + (60*60*24);
                                continue;
                            }
                        }
                        elseif($this->between_start_hour != -1 && $this->between_start_minute != -1 && $this->between_end_hour != -1 && $this->between_end_minute != -1){
                            $f_start_hour = (string) $this->between_start_hour;
                            $f_end_hour = (string) $this->between_end_hour;
                            $f_start_minute = (string) $this->between_start_minute;
                            $f_end_minute = (string) $this->between_end_minute;
                            $f_day = (string) jdate("d",$timestamp,"",$this->timezone,"en");
                            $f_month = (string) jdate("m",$timestamp,"",$this->timezone,"en");
                            $f_year = (string) jdate("Y",$timestamp,"",$this->timezone,"en");

                            $f_start_timestamp = jmktime($f_start_hour,$f_start_minute,"00",$f_month,$f_day,$f_year,"",$this->timezone);
                            $f_end_timestamp = jmktime($f_end_hour,$f_end_minute,"00",$f_month,$f_day,$f_year,"",$this->timezone);

                            if($f_start_timestamp >= $f_end_timestamp){
                                $this->addScheduleError("start_timestamp is more or equal then end_timestamp , method getWeeklyRunSchedules");
                                break;
                            }

                            if($limit == 2000)
                                break;

                            if($f_start_timestamp > $timestamp || $f_end_timestamp < $timestamp){
                                $timestamp = $timestamp + (60*60*24);
                                $limit++;
                                continue;
                            }
                        }

                        $element = count($schedule_run);
                        $schedule_run[$element]['display'] = jdate("Y/m/d (l) - H:i:s",$s_timestamp,"",$this->timezone,"en");
                        $schedule_run[$element]['timestamp'] = $s_timestamp;
                    }
                }
                $timestamp = $timestamp + (60*60*24);
            }
            return $schedule_run;
        }
        else{
            $this->addScheduleError("given parameter 'days_of_week' is empty array or parameter 'days_of_week' is out range array(0,1,2,3,4,5,6) , method getWeeklyRunSchedules");
            return array();
        }
    }

    private function getDailyRunSchedules($hour , $minute , $count): array
    {
        $schedule_run = array();
        if($count <= 0)
            $count = 10;
        $timestamp = $this->run_time;
        $limit = 0;
        while (count($schedule_run) <= $count){
            $temp_year = (string) jdate("Y",$timestamp,"",$this->timezone,"en");
            $temp_month = (string) jdate("m",$timestamp,"",$this->timezone,"en");
            $temp_day = (string) jdate("d",$timestamp,"",$this->timezone,"en");
            $s_timestamp = jmktime($hour,$minute,"00",$temp_month,$temp_day,$temp_year,"",$this->timezone);
            $timestamp = $s_timestamp;
            if($s_timestamp >= $this->run_time){

                if($this->from_timestamp != -1){
                    if($this->from_timestamp > $timestamp) {
                        $timestamp = $timestamp + (60*60*24);
                        continue;
                    }
                }

                if($this->to_timestamp != -1){
                    if($this->to_timestamp < $timestamp)
                        break;
                }

                if($this->between_start_timestamp != -1 && $this->between_end_timestamp != -1){
                    if($this->between_end_timestamp < $timestamp)
                        break;
                    if($this->between_start_timestamp > $timestamp || $this->between_end_timestamp < $timestamp) {
                        $timestamp = $timestamp + (60*60*24);
                        continue;
                    }
                }
                elseif($this->between_start_hour != -1 && $this->between_start_minute != -1 && $this->between_end_hour != -1 && $this->between_end_minute != -1){
                    $f_start_hour = (string) $this->between_start_hour;
                    $f_end_hour = (string) $this->between_end_hour;
                    $f_start_minute = (string) $this->between_start_minute;
                    $f_end_minute = (string) $this->between_end_minute;
                    $f_day = (string) jdate("d",$timestamp,"",$this->timezone,"en");
                    $f_month = (string) jdate("m",$timestamp,"",$this->timezone,"en");
                    $f_year = (string) jdate("Y",$timestamp,"",$this->timezone,"en");

                    $f_start_timestamp = jmktime($f_start_hour,$f_start_minute,"00",$f_month,$f_day,$f_year,"",$this->timezone);
                    $f_end_timestamp = jmktime($f_end_hour,$f_end_minute,"00",$f_month,$f_day,$f_year,"",$this->timezone);

                    if($f_start_timestamp >= $f_end_timestamp){
                        $this->addScheduleError("start_timestamp is more or equal then end_timestamp , method getDailyRunSchedules");
                        break;
                    }

                    if($limit == 2000)
                        break;

                    if($f_start_timestamp > $timestamp || $f_end_timestamp < $timestamp){
                        $timestamp = $timestamp + (60*60*24);
                        $limit++;
                        continue;
                    }
                }

                $element = count($schedule_run);
                $schedule_run[$element]['display'] = jdate("Y/m/d - H:i:s",$s_timestamp,"",$this->timezone,"en");
                $schedule_run[$element]['timestamp'] = $s_timestamp;
            }
            $timestamp = $timestamp + (60*60*24);
        }
        return $schedule_run;
    }

    private function getYearlyRunSchedules(array $months, array $days , $hour , $minute , $count): array
    {
        if(!empty($months) && !empty($days) && (max($months) <= 12 && min($months) >= 1) && (max($days) <= 31 && min($days) >= 1)){
            sort($months);
            sort($days);
            $temp_year = (string) $this->getParamFromRunTime("year");
            $schedule_run = array();
            if($count <= 0)
                $count = 10;
            $limit = 0;
            while (count($schedule_run) <= $count){

                foreach ($months as $month){
                    foreach ($days as $day){
                        if(jcheckdate($month,$day,$temp_year)){
                            $timestamp = jmktime($hour,$minute,"00",$month,$day,$temp_year,"",$this->timezone);
                            if($timestamp >= $this->run_time){

                                if($this->from_timestamp != -1){
                                    if($this->from_timestamp > $timestamp)
                                        continue;
                                }

                                if($this->to_timestamp != -1){
                                    if($this->to_timestamp < $timestamp)
                                        break 3;
                                }

                                if($this->between_start_timestamp != -1 && $this->between_end_timestamp != -1){
                                    if($this->between_end_timestamp < $timestamp)
                                        break 3;
                                    if($this->between_start_timestamp > $timestamp || $this->between_end_timestamp < $timestamp)
                                        continue;
                                }
                                elseif($this->between_start_hour != -1 && $this->between_start_minute != -1 && $this->between_end_hour != -1 && $this->between_end_minute != -1){
                                    $f_start_hour = (string) $this->between_start_hour;
                                    $f_end_hour = (string) $this->between_end_hour;
                                    $f_start_minute = (string) $this->between_start_minute;
                                    $f_end_minute = (string) $this->between_end_minute;
                                    $f_day = (string) jdate("d",$timestamp,"",$this->timezone,"en");
                                    $f_month = (string) jdate("m",$timestamp,"",$this->timezone,"en");
                                    $f_year = (string) jdate("Y",$timestamp,"",$this->timezone,"en");

                                    $f_start_timestamp = jmktime($f_start_hour,$f_start_minute,"00",$f_month,$f_day,$f_year,"",$this->timezone);
                                    $f_end_timestamp = jmktime($f_end_hour,$f_end_minute,"00",$f_month,$f_day,$f_year,"",$this->timezone);

                                    if($f_start_timestamp >= $f_end_timestamp){
                                        $this->addScheduleError("start_timestamp is more or equal then end_timestamp , method getYearlyRunSchedules");
                                        break 3;
                                    }

                                    if($limit == 2000)
                                        break 3;

                                    if($f_start_timestamp > $timestamp || $f_end_timestamp < $timestamp){
                                        $limit++;
                                        continue;
                                    }
                                }

                                $element = count($schedule_run);
                                $schedule_run[$element]['display'] = jdate("Y/m/d - H:i:s",$timestamp,"",$this->timezone,"en");
                                $schedule_run[$element]['timestamp'] = $timestamp;
                            }
                        }
                    }
                }
                $temp_year++;
            }
            return $schedule_run;
        }
        else{
            $this->addScheduleError("given parameters 'days' or 'months' is empty array or is out range array(1,..,31) , array(1,..,12) , method getYearlyRunSchedules");
            return array();
        }
    }

    private function getHourlyRunSchedules(int $hour, int $count): array
    {
        if($hour < 1){
            $this->addScheduleError("given parameter 'hour' is less then 1 , method getHourlyRunSchedules");
            return array();
        }
        $schedule_run = array();
        if($count <= 0)
            $count = 10;

        $const_timestamp = jmktime("00","00","00","01","01","1400","",$this->timezone);

        if($const_timestamp > $this->run_time)
            $this->addScheduleError("const_timestamp is more then run_timestamp , method : getHourlyRunSchedules");

        $second_timestamp = $const_timestamp + (60*60*$hour);
        $distance = $second_timestamp - $const_timestamp;
        $run_timestamp = $this->run_time;
        $x = floor(($run_timestamp - $const_timestamp) / $distance);
        $start_timestamp = ($x * $distance) + $const_timestamp;
        $timestamp = (int) $start_timestamp;
        $limit = 0;

        while (count($schedule_run) <= $count){
            if($timestamp >= $this->run_time){

                if($this->from_timestamp != -1){
                    if($this->from_timestamp > $timestamp) {
                        $timestamp = (int)($timestamp + $distance);
                        continue;
                    }
                }

                if($this->to_timestamp != -1){
                    if($this->to_timestamp < $timestamp)
                        break;
                }

                if($this->between_start_timestamp != -1 && $this->between_end_timestamp != -1){
                    if($this->between_end_timestamp < $timestamp)
                        break;
                    if($this->between_start_timestamp > $timestamp || $this->between_end_timestamp < $timestamp) {
                        $timestamp = (int)($timestamp + $distance);
                        continue;
                    }
                }
                elseif($this->between_start_hour != -1 && $this->between_start_minute != -1 && $this->between_end_hour != -1 && $this->between_end_minute != -1){
                    $f_start_hour = (string) $this->between_start_hour;
                    $f_end_hour = (string) $this->between_end_hour;
                    $f_start_minute = (string) $this->between_start_minute;
                    $f_end_minute = (string) $this->between_end_minute;
                    $f_day = (string) jdate("d",$timestamp,"",$this->timezone,"en");
                    $f_month = (string) jdate("m",$timestamp,"",$this->timezone,"en");
                    $f_year = (string) jdate("Y",$timestamp,"",$this->timezone,"en");

                    $f_start_timestamp = jmktime($f_start_hour,$f_start_minute,"00",$f_month,$f_day,$f_year,"",$this->timezone);
                    $f_end_timestamp = jmktime($f_end_hour,$f_end_minute,"00",$f_month,$f_day,$f_year,"",$this->timezone);

                    if($f_start_timestamp >= $f_end_timestamp){
                        $this->addScheduleError("start_timestamp is more or equal then end_timestamp , method getHourlyRunSchedules");
                        break;
                    }

                    if($limit == 2000)
                        break;

                    if($f_start_timestamp > $timestamp || $f_end_timestamp < $timestamp){
                        $timestamp = (int)($timestamp + $distance);
                        $limit++;
                        continue;
                    }
                }

                $element = count($schedule_run);
                $schedule_run[$element]['display'] = jdate("Y/m/d - H:i:s",$timestamp,"",$this->timezone,"en");
                $schedule_run[$element]['timestamp'] = $timestamp;
            }
            $timestamp = (int)($timestamp + $distance);
        }
        return $schedule_run;
    }

    private function getMinuteRunSchedules(int $minute, int $count): array
    {
        if($minute < 1){
            $this->addScheduleError("given parameter 'minute' is less then 1 , method getMinuteRunSchedules");
            return array();
        }
        $schedule_run = array();
        if($count <= 0)
            $count = 10;

        $const_timestamp = jmktime("00","00","00","01","01","1400","",$this->timezone);

        if($const_timestamp > $this->run_time)
            $this->addScheduleError("const_timestamp is more then run_timestamp , method : getMinuteRunSchedules");

        $second_timestamp = $const_timestamp + (60*$minute);
        $distance = $second_timestamp - $const_timestamp;
        $run_timestamp = $this->run_time;
        $x = floor(($run_timestamp - $const_timestamp) / $distance);
        $start_timestamp = ($x * $distance) + $const_timestamp;
        $timestamp = (int) $start_timestamp;
        $limit = 0;

        while(count($schedule_run) <= $count){
            if($timestamp >= $this->run_time){

                if($this->from_timestamp != -1){
                    if($this->from_timestamp > $timestamp) {
                        $timestamp = (int)($timestamp + $distance);
                        continue;
                    }
                }

                if($this->to_timestamp != -1){
                    if($this->to_timestamp < $timestamp)
                        break;
                }

                if($this->between_start_timestamp != -1 && $this->between_end_timestamp != -1){
                    if($this->between_end_timestamp < $timestamp)
                        break;
                    if($this->between_start_timestamp > $timestamp || $this->between_end_timestamp < $timestamp) {
                        $timestamp = (int)($timestamp + $distance);
                        continue;
                    }
                }
                elseif($this->between_start_hour != -1 && $this->between_start_minute != -1 && $this->between_end_hour != -1 && $this->between_end_minute != -1){
                    $f_start_hour = (string) $this->between_start_hour;
                    $f_end_hour = (string) $this->between_end_hour;
                    $f_start_minute = (string) $this->between_start_minute;
                    $f_end_minute = (string) $this->between_end_minute;
                    $f_day = (string) jdate("d",$timestamp,"",$this->timezone,"en");
                    $f_month = (string) jdate("m",$timestamp,"",$this->timezone,"en");
                    $f_year = (string) jdate("Y",$timestamp,"",$this->timezone,"en");

                    $f_start_timestamp = jmktime($f_start_hour,$f_start_minute,"00",$f_month,$f_day,$f_year,"",$this->timezone);
                    $f_end_timestamp = jmktime($f_end_hour,$f_end_minute,"00",$f_month,$f_day,$f_year,"",$this->timezone);

                    if($f_start_timestamp >= $f_end_timestamp){
                        $this->addScheduleError("start_timestamp is more or equal then end_timestamp , method getWeeklyRunSchedules");
                        break;
                    }

                    if($limit == 2000)
                        break;

                    if($f_start_timestamp > $timestamp || $f_end_timestamp < $timestamp){
                        $timestamp = (int)($timestamp + $distance);
                        $limit++;
                        continue;
                    }
                }

                $element = count($schedule_run);
                $schedule_run[$element]['display'] = jdate("Y/m/d - H:i:s",$timestamp,"",$this->timezone,"en");
                $schedule_run[$element]['timestamp'] = $timestamp;
            }
            $timestamp = (int)($timestamp + $distance);
        }
        return $schedule_run;
    }

    /**
     * @param $hour
     * @param $method
     * @param $parameter
     * @return bool
     */
    private function checkHourAndSetError($hour,$method,$parameter): bool
    {
        if(!is_numeric($hour))
            $this->addScheduleError("given parameter '". $parameter ."' is not integer , method : ". $method);
        elseif(strlen($hour) != 2)
            $this->addScheduleError("given parameter '". $parameter ."' length is less than 2 . Examples -> 12,23,00,05 , method : ". $method);
        elseif($hour < 0 || $hour > 23)
            $this->addScheduleError("given parameter '". $parameter ."' is not between 00 and 23 , method : ". $method);
        else
            return true;
        return false;
    }

    /**
     * @param $minute
     * @param $method
     * @param $parameter
     * @return bool
     */
    private function checkMinuteAndSetError($minute,$method,$parameter): bool
    {
        if(!is_numeric($minute))
            $this->addScheduleError("given parameter '". $parameter ."' is not integer , method : ". $method);
        elseif(strlen($minute) != 2)
            $this->addScheduleError("given parameter '". $parameter ."' length is less or more than 2 . Examples -> 53,30,09,03 , method : ". $method);
        elseif($minute < 0 || $minute > 59)
            $this->addScheduleError("given parameter '". $parameter ."' is not between 00 and 59 , method : ". $method);
        else
            return true;
        return false;
    }

    /**
     * @param $day
     * @param $method
     * @param $parameter
     * @return bool
     */
    private function checkDayAndSetError($day,$method,$parameter): bool
    {
        if(!is_numeric($day))
            $this->addScheduleError("given parameter '". $parameter ."' is not integer , method : ". $method);
        elseif(strlen($day) != 2)
            $this->addScheduleError("given parameter '". $parameter ."' length is less or more than 2 . Examples -> 05,29,09,11 , method : ". $method);
        elseif($day < 1 || $day > 31)
            $this->addScheduleError("given parameter '". $parameter ."' is not between 01 and 31 , method : ". $method);
        else
            return true;
        return false;
    }

    /**
     * @param $month
     * @param $method
     * @param $parameter
     * @return bool
     */
    private function checkMonthAndSetError($month,$method,$parameter): bool
    {
        if(!is_numeric($month))
            $this->addScheduleError("given parameter '". $parameter ."' is not integer , method : " . $method);
        elseif(strlen($month) != 2)
            $this->addScheduleError("given parameter '". $parameter ."' length is less or more than 2 . Examples -> 05,03,09,12 , method : ". $method);
        elseif($month < 1 || $month > 12)
            $this->addScheduleError("given parameter '". $parameter ."' is not between 01 and 12 , method : ". $method);
        else
            return true;
        return false;
    }

    /**
     * @param $year
     * @param $method
     * @param $parameter
     * @return bool
     */
    private function checkYearAndSetError($year,$method,$parameter): bool
    {
        if(!is_numeric($year))
            $this->addScheduleError("given parameter '". $parameter ."' is not integer , method : " . $method);
        elseif(strlen($year) != 4)
            $this->addScheduleError("given parameter '". $parameter ."' length is less or more than 4 . Examples -> 2025,2018,1398,1405 , method : ". $method);
        elseif($year < 1400)
            $this->addScheduleError("given parameter '". $parameter ."' is less then 1400 , method : ". $method);
        else
            return true;
        return false;
    }

    private function addScheduleError(string $message)
    {
        $this->schedule_errors[count($this->schedule_errors)] = $message;
    }

    /**
     * @param int $n_minute
     * @return $this
     */
    public function everyMinute(int $n_minute): PersianSchedule
    {
        if(!is_numeric($n_minute)){
            $this->addScheduleError("given parameter 'hour' is not integer , method everyHour");
            return $this;
        }

        $schedule_runs = $this->getMinuteRunSchedules($n_minute,1);

        if(!empty($schedule_runs)){
            if($this->schedule_type == ""){
                $this->schedule_type = "everyMinute";
                $this->schedule_type_parameters = array($n_minute);
            }
            else
                $this->addScheduleError("can not use two '".$this->schedule_type."' and 'everyMinute' methods at the a job");
        }
        else
            $this->addScheduleError("schedule runs is empty , method : everyDay");
        return $this;
    }

    /**
     * @param int $n_hour
     * @return $this
     */
    public function everyHour(int $n_hour): PersianSchedule
    {
        if(!is_numeric($n_hour)){
            $this->addScheduleError("given parameter 'hour' is not integer , method everyHour");
            return $this;
        }

        $schedule_runs = $this->getHourlyRunSchedules($n_hour,1);

        if(!empty($schedule_runs)){
            if($this->schedule_type == ""){
                $this->schedule_type = "everyHour";
                $this->schedule_type_parameters = array($n_hour);
            }
            else
                $this->addScheduleError("can not use two '".$this->schedule_type."' and 'everyHour' methods at the a job");
        }
        else
            $this->addScheduleError("schedule runs is empty , method : everyDay");
        return $this;
    }

    /**
     * @param string $hour
     * @param string $minute
     * @return $this
     */
    public function everyDay(string $hour, string $minute): PersianSchedule
    {
       if($this->checkHourAndSetError($hour,"everyDay","hour") &&
           $this->checkMinuteAndSetError($minute,"everyDay","minute"))
       {
           $schedule_runs = $this->getDailyRunSchedules($hour,$minute,1);

           if(!empty($schedule_runs)){
               if($this->schedule_type == ""){
                   $this->schedule_type = "everyDay";
                   $this->schedule_type_parameters = array($hour,$minute);
               }
               else
                   $this->addScheduleError("can not use two '".$this->schedule_type."' and 'everyDay' methods at the a job");
           }
           else
               $this->addScheduleError("schedule runs is empty , method : everyDay");
       }
       return $this;
    }

    /**
     * @param array $days_of_week
     * @param string $hour
     * @param string $minute
     * @return $this
     */
    public function everyWeek(array $days_of_week, string $hour, string $minute): PersianSchedule
    {
        if($this->checkHourAndSetError($hour," everyWeek","hour") &&
            $this->checkMinuteAndSetError($minute," everyWeek","minute"))
        {
            $schedule_runs = $this->getWeeklyRunSchedules($days_of_week,$hour,$minute,1);

            if(!empty($schedule_runs)){
                if($this->schedule_type == ""){
                    $this->schedule_type = "everyWeek";
                    $this->schedule_type_parameters = array($days_of_week,$hour,$minute);
                }
                else
                    $this->addScheduleError("can not use two '".$this->schedule_type."' and 'everyWeek' methods at the a job");
            }
            else
                $this->addScheduleError("schedule runs is empty , method : everyWeek");
        }
        return $this;
    }

    /**
     * @param array $days
     * @param string $hour
     * @param string $minute
     * @return $this
     */
    public function everyMonth(array $days, string $hour, string $minute): PersianSchedule
    {
        if($this->checkHourAndSetError($hour," everyMonth","hour") &&
            $this->checkMinuteAndSetError($minute," everyMonth","minute"))
        {
            $schedule_runs = $this->getMonthlyRunSchedules($days,$hour,$minute,1);

            if(!empty($schedule_runs)){
                if($this->schedule_type == ""){
                    $this->schedule_type = "everyMonth";
                    $this->schedule_type_parameters = array($days,$hour,$minute);
                }
                else
                    $this->addScheduleError("can not use two '".$this->schedule_type."' and 'everyMonth' methods at the a job");
            }
            else
                $this->addScheduleError("schedule runs is empty , method : everyMonth");
        }
        return $this;
    }

    /**
     * @param array $months
     * @param array $days
     * @param string $hour
     * @param string $minute
     * @return $this
     */
    public function everyYear(array $months, array $days, string $hour, string $minute): PersianSchedule
    {
        if($this->checkHourAndSetError($hour,"everyYear","hour") &&
            $this->checkMinuteAndSetError($minute,"everyYear","minute"))
        {
            $schedule_runs = $this->getYearlyRunSchedules($months,$days,$hour,$minute,1);

            if(!empty($schedule_runs)){
                if($this->schedule_type == ""){
                    $this->schedule_type = "everyYear";
                    $this->schedule_type_parameters = array($months,$days,$hour,$minute);
                }
                else
                    $this->addScheduleError("can not use two '".$this->schedule_type."' and 'everyYear' methods at the a job");
            }
            else
                $this->addScheduleError("schedule runs is empty , method : everyYear");
        }
        return $this;
    }

    /**
     * @param string $year
     * @param string $month
     * @param string $day
     * @param string $hour
     * @param string $minute
     * @return $this
     */
    public function onDateAndTime(string $year, string $month, string $day, string $hour, string $minute): PersianSchedule
    {
        if($this->checkHourAndSetError($hour,"onDateAndTime","hour") &&
            $this->checkMinuteAndSetError($minute,"onDateAndTime","minute") &&
            $this->checkYearAndSetError($year,"onDateAndTime","year") &&
            $this->checkMonthAndSetError($month,"onDateAndTime","month") &&
            $this->checkDayAndSetError($day,"onDateAndTime","day"))
        {
            if($this->schedule_type == ""){
                $this->schedule_type = "onDateAndTime";
                $this->schedule_type_parameters = array($year,$month,$day,$hour,$minute);
            }
            else
                $this->addScheduleError("can not use two '".$this->schedule_type."' and 'onDateAndTime' methods at the a job");
        }
        return $this;
    }

    /**
     * @param string $from_year
     * @param string $from_month
     * @param string $from_day
     * @param string $from_hour
     * @param string $from_minute
     * @return $this
     */
    public function fromDateAndTime(string $from_year, string $from_month, string $from_day, string $from_hour, string $from_minute): PersianSchedule
    {
        if($this->checkHourAndSetError($from_hour,"fromDateAndTime","from_hour") &&
            $this->checkMinuteAndSetError($from_minute,"fromDateAndTime","from_minute") &&
            $this->checkYearAndSetError($from_year,"fromDateAndTime","from_year") &&
            $this->checkMonthAndSetError($from_month,"fromDateAndTime","from_month") &&
            $this->checkDayAndSetError($from_day,"fromDateAndTime","from_day"))
        {
            if(jcheckdate($from_month,$from_day,$from_year))
                $this->from_timestamp = jmktime($from_hour,$from_minute,"00",$from_month,$from_day,$from_year,"",$this->timezone);
            else
                $this->addScheduleError("invalid date , method : fromDateAndTime");
        }
        return $this;
    }

    /**
     * @param string $to_year
     * @param string $to_month
     * @param string $to_day
     * @param string $to_hour
     * @param string $to_minute
     * @return $this
     */
    public function toDateAndTime(string $to_year, string $to_month, string $to_day, string $to_hour, string $to_minute): PersianSchedule
    {
        if($this->checkHourAndSetError($to_hour,"toDateAndTime","to_hour") &&
            $this->checkMinuteAndSetError($to_minute,"toDateAndTime","to_minute") &&
            $this->checkYearAndSetError($to_year,"toDateAndTime","to_year") &&
            $this->checkMonthAndSetError($to_month,"toDateAndTime","to_month") &&
            $this->checkDayAndSetError($to_day,"toDateAndTime","to_day"))
        {
            if(jcheckdate($to_month,$to_day,$to_year))
                $this->to_timestamp = jmktime($to_hour,$to_minute,"00",$to_month,$to_day,$to_year,"",$this->timezone);
            else
                $this->addScheduleError("invalid date , method : toDateAndTime");
        }
        return $this;
    }

    /**
     * @param string $start_hour
     * @param string $start_minute
     * @param string $end_hour
     * @param string $end_minute
     * @param string|null $start_year
     * @param string|null $start_month
     * @param string|null $start_day
     * @param string|null $end_year
     * @param string|null $end_month
     * @param string|null $end_day
     * @return $this
     */
    public function between(string $start_hour, string $start_minute, string $end_hour, string $end_minute, string $start_year = null, string $start_month = null, string $start_day = null, string $end_year = null, string $end_month = null, string $end_day = null): PersianSchedule
    {
        if($this->checkHourAndSetError($start_hour,"between","start_hour") &&
            $this->checkMinuteAndSetError($start_minute,"between","start_minute") &&
            $this->checkHourAndSetError($end_hour,"between","end_hour") &&
            $this->checkMinuteAndSetError($end_minute,"between","end_minute"))
        {
            $this->between_start_hour = (int) $start_hour;
            $this->between_start_minute = (int) $start_minute;
            $this->between_end_hour = (int) $end_hour;
            $this->between_end_minute = (int) $end_minute;

            if(!is_null($start_year) && !is_null($start_month) && !is_null($start_day) &&
                !is_null($end_year) && !is_null($end_month) && !is_null($end_day) &&
                $this->checkYearAndSetError($start_year,"between","start_year") &&
                $this->checkYearAndSetError($end_year,"between","end_year") &&
                $this->checkMonthAndSetError($start_month,"between","start_month") &&
                $this->checkMonthAndSetError($end_month,"between","end_month") &&
                $this->checkDayAndSetError($start_day,"between","start_day") &&
                $this->checkDayAndSetError($end_day,"between","end_day"))
            {
                if(!jcheckdate($start_month,$start_day,$start_year))
                    $this->addScheduleError("invalid date (1) , method : between");
                elseif(!jcheckdate($end_month,$end_day,$end_year))
                    $this->addScheduleError("invalid date (2) , method : between");
                else{
                    $this->between_start_timestamp = jmktime($start_hour,$start_minute,"00",$start_month,$start_day,$start_year,"",$this->timezone);
                    $this->between_end_timestamp = jmktime($end_hour,$end_minute,"00",$end_month,$end_day,$end_year,"",$this->timezone);
                }
            }
        }
        return $this;
    }

}