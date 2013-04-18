#!/usr/bin/php
<?php

$path_file = __DIR__ . '/tmp/proxy.txt';

$gmc= new GearmanClient();

$gmc->addServer();
$gmc->setCompleteCallback("show_message");

$arr_proxy = file($path_file);
$url_check = 'http://torvpn.com/myip.html';

foreach ($arr_proxy as $line_proxy) {
    $gmc->addTask("check_proxy", serialize(array($line_proxy, $url_check)));
}

$gmc->runTasks();


function show_message($task)
{
    //list($line_proxy) = unserialize($task->data());
    echo "Add task (Job: " . $task->jobHandle() . ") \n";
}
?>