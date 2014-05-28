<?php

// load PHP Pinger
require '../phppinger.class.php';

/* callbacks for example */
function callback($is_online, $hostname, $port, $sweety_name) {
    echo '<li>', $hostname, ':', $port, ' is ',
    ($is_online ? 'online' : 'offline'), '</li>';
}
function preRun() { echo '<ul>', PHP_EOL; }
function postRun() { echo '</ul>', PHP_EOL; }

/* instanciate a new Pinger and run it with callbacks and notifications */
$ping = new PHP_Pinger();
$ping->registerCallback('callback')
    ->registerPreRun('preRun')
    ->registerPostRun('postRun')
    ->addHost('nicolabricot.com')
    ->addHost('blog.nicolabricot.com', 80)
    ->addHost('ftp.github.com', 21, 'GitHub FTP')
    ->setNotificationTo(array('email@domain.tld', 'other-dude@domain.tld'))
    ->setNotificationPreffix('Network Monitoring')
    ->setNotificationSignature('--'.PHP_EOL.'By PHP Pinger')
    ->enableNotification(NotificationLevel::REPORT)
    ->run();

?>