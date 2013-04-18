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
    list($line_proxy, $url_check) = unserialize($job->workload());

    $arr_proxy = explode(':', $line_proxy);
    $ip = trim($arr_proxy[0]);
    $port = trim($arr_proxy[1]);

    echo "--> Url $url_check Ip $ip Port $port \n";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url_check);
    curl_setopt($ch, CURLOPT_PROXY,  $ip . ':' . $port);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_NOBODY, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8); // Seconds

    $headers = curl_exec($ch);

    echo $headers . "\n";

    $http_code = null;

    if(!curl_errno($ch))
    {
        $info = curl_getinfo($ch);
        $http_code = $info['http_code'];
        echo 'Took ' . $info['total_time'] . ' seconds to send a request to ' . $info['url'] . " CODE: {$info['http_code']} \n";
    }

    curl_close($ch);

    // use CURL to connect to; http://torvpn.com/myip.html   and use proxy and make sure you cant see; 79.98.220.45
    $proxy_block = false;

    if (strpos($headers, '79.98.220.45') !== false) {
        $proxy_block = true;
    }

    if(200 == $http_code && !$proxy_block) {
        _log_success("$ip:$port");
    } else if ($http_code != 200 && !$proxy_block) {
        _log_error("$ip:$port");
    } else if ($proxy_block) {
        _log_proxy_block("$ip:$port");
    }

}

function _log_error($data)
{
    _log($data, 'log_error');
}

function _log_success($data)
{
    _log($data, 'log_success');
}

function _log_proxy_block($data)
{
    _log($data, 'log_block');
}

function _log($data, $file_name)
{
    $fp = fopen(__DIR__ . "/log/$file_name","a+");
    fwrite($fp, $data . "\n");
}
?>