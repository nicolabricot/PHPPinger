<?php

require '../phppinger.class.php';

define ('KEY', 'mySecretKeyToAvoidToBeSpamed');

// if we reach the page via a browser
// for example: http://domain.tld/status/cron.php?key=mySecretKeyToAvoidToBeSpamed
if (isset($_GET['key']) && $_GET['key'] == KEY) {
    executePing();
}

// if we use the php command line (in a shell)
// for exemple with a cron task: 'php cron.php mySecretKeyToAvoidToBeSpamed'
else if (isset($argv[1]) && $argv[1] == KEY) {
    executePing();
}

else {
    header('HTTP/1.1 403 Forbidden', true, 403);
    die('403 Forbidden');
}

// do the job
function executePing() {

    // Run a first instance
    $app = new PHP_Pinger();
    // set e-mail destinators with an array
    $app->setNotificationTo(array('nicolas@devenet.info', 'contact@nicolabricot.com'));
    // you can change the subjects preffix
    $app->setNotificationPreffix('Network Monitoring');
    // you can add a signature at the end of the sent e-mail
    $app->setNotificationSignature('--'.PHP_EOL.'The Status CRON Notifier');
    // now enable notification (available types are: NONE, PROBLEMS, REPORT)
    $app->enableNotification(NotificationLevel::REPORT);
    // add hosts to be checked
    $app->addHost('nicolabricot.com')
        ->addHost('github.com', 443, 'GitHub &lsaquo;3')
        ->addHost('help.github.com', 443, 'Help over HTTPS')
        ->addHost('ftp.github.com', 21);
    // let's go!
    $app->run();

    // An other instance to check some hosts
    $cat = new PHP_Pinger();
    $cat->enableNotification(NotificationLevel::PROBLEMS)
        ->setNotificationTo(array('nicolas@devenet.info'))
        ->addHost('google.fr', 80, 'Google FR')
        ->addHost('www.google.com', 443, 'Google SSL')
        ->addHost('404.google.com')
        ->addHost('example.com', 8080, 'Bad example')
        ->addHost('example.com', 80, 'Good example')
        ->run();

    // Callback functions can be also used
}

?>