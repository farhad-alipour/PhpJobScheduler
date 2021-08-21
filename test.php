<?php

use darcob_jobscheduler\JobRunner;

require __DIR__ . '/vendor/autoload.php';

$job_runner = new JobRunner();

$job_runner->setPath("tasks/");

$job_runner->execute();