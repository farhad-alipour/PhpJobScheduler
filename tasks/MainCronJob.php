<?php
use darcob_jobscheduler\PersianSchedule;

$job = new PersianSchedule();

$job->setUniqueName("TestTask-1");

$x = 0;
$job->job(function () use ($x){

    echo "my job";
});

$job->onErrorListener(function (){
    echo "Error";
});

//$job->between("09","30","15","00");
//$job->between("10","30","18","30","1400","08","12","1403","10","13");

//$job->fromDateAndTime("1405","10","20","11","00");
//$job->toDateAndTime("1405","12","20","11","30");

$job->everyYear(array(1,2),array(20,25),"11","30");

$job->preventOverlapping(1);

$job->addJob();

//var_dump($job->getNextExecuteTime());

var_dump($job->getExecuteExamples(500));

echo "<br><br> <h2 style='color: darkred'>Error lists :</h2>";
var_dump($job->getErrorLists());