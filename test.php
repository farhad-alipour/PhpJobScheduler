<?php

use darcob_jobscheduler\JobRunner;

require __DIR__ . '/vendor/autoload.php';

$job_runner = new JobRunner();

$job_runner->setPath("tasks/");

$job_runner->setRunTime(jmktime("11","30","00","01","20","1401"));

$job_runner->execute();

$r = $job_runner->getExecuteResults();
echo $r[0]['output'];