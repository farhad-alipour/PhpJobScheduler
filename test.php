<?php

use main_core\JobRunner;

require __DIR__ . '/vendor/autoload.php';

$job_runner = new JobRunner();

$job_runner->setPath("tasks/");

$job_runner->execute();
