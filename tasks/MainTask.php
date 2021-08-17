<?php
    use main_core\Schedule;

    $job = new Schedule();
    $x =12;
    $job->job(function() use ($x) {
        $myfile = fopen("../../test_file.txt", "w");
        $txt = date("H:i:s") . "\n";
        fwrite($myfile, $txt);
        fclose($myfile);
    })->;