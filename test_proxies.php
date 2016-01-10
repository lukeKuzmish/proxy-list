<?php
define('DEFAULT_PROXY_FILE', 'inputproxies.txt');
define('DEFAULT_PROXY_OUTPUT_FILE', 'successful_proxies.txt');
define('PROXY_TIMEOUT', 10);

if (isset($argv[1])) {
    $inputProxyFile = $argv[1];
}
else {
    $inputProxyFile = DEFAULT_PROXY_FILE;
}

if (isset($argv[2])) {
    $outputProxyFile = $argv[1];
}
else {
    $outputProxyFile = DEFAULT_PROXY_OUTPUT_FILE;
}


if (!file_exists($inputProxyFile)) {
    echo "\nThe input file ({$inputProxyFile}) does not exist! Exiting.\n";
    exit;
}

require_once __DIR__ . "/class.curl.php";

$urlToTest = 'http://dynupdate.no-ip.com/ip.php';
$c = new Curl($urlToTest);
$ch = $c->getCH();
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,0); 
curl_setopt($ch, CURLOPT_TIMEOUT, 10); //timeout in seconds

$proxyListStr = file_get_contents($inputProxyFile);

$proxyListStr = str_replace("\r", '', $proxyListStr);

$proxies = explode("\n", $proxyListStr);

$successful = array();

foreach($proxies as $i => $proxy) {
    echo "\n{$i} line\n";
    if (trim($proxy) == '') {
        echo "\nline empty!\n";
        continue;
    }
    echo "\nTrying proxy: {$proxy}\n";
    $c->setProxy($proxy);
    $r = $c->getRequest();
    if ($r != '') {
        echo "Success!";
        $successful[] = $proxy;
    }
    else {
        echo "Request failed :-(";
    }
    echo "\n\n";
}

if (count($successful) > 0) {
    $str = implode($successful, "\n");
    if (file_put_contents($outputProxyFile, $str)) {
        echo "\nSuccessfully saved successful proxy list!\n";
    }
    else {
        echo "\nThere was a problem saving the proxy list.  Contents:\n$str\n";
    }
}