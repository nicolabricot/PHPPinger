<?php

// we need to load the PHP Pinger file
require '../phppinger.class.php';

/* FIRST EXAMPLE */
// define the callback function which is executed when pinging a host
/* $online: boolean to TRUE if $host has been reached
 * $host: hostname or IP to be checked (google.fr, www.google.fr, 8.8.8.8, ...)
 * $port: number port used to open the socket - Default value: 80
 * $label: sweety name for $host:$port - Default value: $host */
function myCallback($online, $host, $port, $label) {
    echo '<li><abbr title="', $host, ':', $port, '"><span>', $label, '</span></abbr> &rsaquo; ',
    ($online ? 'online' : '<strong>offline</strong>'), '</li>', PHP_EOL;
}
// define the callback function which is exectued before the application run
function myPreRun() {
    echo '<!-- preRun --><ul><!-- /preRun -->', PHP_EOL;
}
// define the callback function which is exectued after the application run
function myPostRun() {
    echo '<!-- postRun --></ul><!-- /postRun -->', PHP_EOL;
}

// create a new application
$app = new PHP_Pinger();
// add host to be tested
/* addHost($host [, $port [, $label]])
 * $host: hostname or IP to be checked (google.fr, www.google.fr, 8.8.8.8, ...)
 * $port: number port used to open the socket - Default value: 80
 * $label: sweety name for $host:$port - Default value: $host */
$app->addHost('nicolabricot.com')
    ->addHost('github.com', 443, 'GitHub &lsaquo;3')
    ->addHost('help.github.com', 443, 'Help over HTTPS')
    ->addHost('ftp.github.com', 21);
    
// set callbacks (by default nothing is displayed)
$app->registerPreRun('myPreRun')
    ->registerCallback('myCallback')
    ->registerPostRun('myPostRun');

/* SECOND EXAMPLE */
$other_example = new PHP_Pinger();
$other_example->addHost('google.fr', 80, 'Google FR')
              ->addHost('www.google.com', 443, 'Google SSL')
              ->addHost('404.google.com')
              ->addHost('example.com', 22, 'SSH example')
              ->registerCallback('myOtherCallback')
              ->addHost('example.com', 80, 'Good example');
// define an other callback function which is executed when pinging a host
function myOtherCallback($online, $host, $port, $label) {
    echo '<tr class="', ($online ? 'on' : 'off'), '"><td>', $label, '</td>',
        '<td><code>', $host, ':', $port, '</code></td>',
        '<td>', ($online ? 'online' : '<strong>offline</strong>'), '</td></tr>',
        PHP_EOL;
}

?><!doctype html>
<html>
<head>
    <title>Example HTML &middot; PHP Pinger</title>
    <style>
        body { width: 700px; margin: auto; padding: 20px 0; font-family: Lato, "Helvetica Neue", Helvetica, Arial, sans-serif; }
        a { color: #005EA5; text-decoration: none; }
        a:hover, a:active { color: #2E8ACA; border-bottom: 2px solid #2E8ACA; }
        a:focus, a:active { background-color: #FFBF47; outline: 3px solid #FFBF47; border: none; }
        h1 { border-bottom: 2px #005EA5 dashed; padding-bottom: 10px; }
        h2 { margin: 30px 0 10px; border-top: 1px solid #ccc; padding-top: 20px;}
        pre, code { font-family: "Source Code Pro", Consolas, "Courrier New", Courrier, monospace; font-size: .95em; }
        abbr { cursor: help; display: inline-block; min-width: 130px; border-bottom: none; }
        table { width: 100%; border-collapse: collapse; }
        th { border-bottom: 1px solid #888; text-align: left; }
        td, th { padding: 5px 8px; }
        tr.on { background-color: rgba(0,255,0,0.1); }
        tr.off { background-color: rgba(255,0,0,0.1); }
        tr.on td + td + td { color: green; }
        tr.off td + td + td { color: red; }
    </style>
    <link href="//fonts.googleapis.com/css?family=Lato:400,400italic,700|Source+Sans+Code:400" rel="stylesheet" />
    <link rel="shortcut icon" type="image/png" href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABoAAAAaCAYAAACpSkzOAAABFElEQVRIib3WURGDMBBF0UhAQiW8uTFQCZUQCZWAAyQgBQlIqAQktD9hhqbJEmjazOxfyCG7C4lzzjlgAJYYg/vFAAbv/XMbJUyS4vwpeWYGBkkXC1oy0JIAl8zi2QD6UxAQcnN2YpbUVacOuB8E3rBsndJmAMIXiJ3GbU1OpCsbew0ytkCsDnaSulZIsVYRujaGnn9JXRFai9cSK+2mB8aGmF2jVlhV17XAJKnYDN77uRE2FZEITcn2T2HmbtIdncWAYCLWQrXYOi87JHU1b7uHAXcLUS5d63cQa2Y2CDCaf+p4D8geCWkK4pF+A/p1UUnXj9O0UJPSuWO35tFRuDM8qt7yIBQSZNnt/2+wzZ0h/MJ4Aa9TefuF5N03AAAAAElFTkSuQmCC" />
</head>
<body>

<h1><a href="https://nicolabricot.github.io/PHPPinger">PHP Pinger</a> <br />Example HTML</h1>

<p>You can browse the <a href="https://nicolabricot.github.io/PHPPinger/documentation.html">documentation</a> or see the source of these page.</p>

<h2>First example</h2>
<p>Following enumeration is defined with callback functions (callback, preRun and postRun).</p>
<!-- run result -->
<?php
    // launch $app ping tests
    $app->run();
?>
<!-- /run result -->

<h2>Second example</h2>
<p>Only the main callback function is defined which format each line of the table.</p>
<table>
<tr><th>Label</th><th>host:port</th><th>Result</th></tr>
<!-- run result -->
<?php
    // launch the other example
    $other_example->run();
?>
<!-- /run result -->
</table>

</body>
</html>