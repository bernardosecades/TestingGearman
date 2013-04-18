#!/usr/bin/php
<?php

$gmw = new GearmanWorker();

$gmw->addServer();
$gmw->addFunction("check_proxy", "save_log_proxy");


while(1) {
    $gmw->work();
}

function save_log_proxy($job, $data=NULL)
{
    list($line_proxy) = unserialize($job->workload());

    $arr_proxy = explode(':', $line_proxy);
    $ip = trim($arr_proxy[0]);
    $port = trim($arr_proxy[1]);

    $timeout = 5;
    $flag = 0;
    @$fp = fsockopen($ip, $port, $errno, $errstr, $timeout);


    // use CURL to connect to; http://torvpn.com/myip.html   and use proxy and make sure you cant see; 79.98.220.45

    if(!empty($fp)) {
        $flag = 1;
        fclose($fp);
    }

    if ($flag) {
        _log_success("$ip:$port");
    } else {
        _log_error("$ip:$port");
    }

    echo "Proxy IP: $ip Port: $port Flag = $flag \n";

}

function _log_error($data)
{
    _log($data, 'log_error');
}

function _log_success($data)
{
    _log($data, 'log_success');
}

function _log($data, $file_name)
{
    $fp = fopen(__DIR__ . "/log/$file_name","a+");
    fwrite($fp, $data . "\n");
}
?>