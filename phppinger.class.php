<?php

/**
* PHPPinger -> library to check if an host is up or down
* 
* @link https://github.com/nicolabricot/PHPPinger
* @author Nicolas Devenet (https://github.com/nicolabricot)
*
* @copyright 2014 Nicolas Devevenet
* @license https://github.com/nicolabricot/PHPPinger/blob/master/LICENSE
* @note This program is distributed in the hope that it will be useful - WITHOUT
* ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
* FITNESS FOR A PARTICULAR PURPOSE.
*/

class Host {
    private $host;
    private $port;
    private $label;
    private $online;

    const TIMEOUT_DEFAULT = 5;
    
    public function __construct($host, $port, $label = NULL) {
        $this->host = $host;
        $this->port = $port;
        $this->label = $label != NULL ? $label : $host;
        $this->online = FALSE;
    }
    
    public function host() {
        return $this->host;
    }
    public function port() {
        return $this->port;
    }
    public function label() {
        return $this->label;
    }
    
    public function check() {
        if (!empty($this->host)) {
            if ($socket = @fsockopen($this->host, $this->port, $errno, $errstr, self::TIMEOUT_DEFAULT)) {
                fclose($socket);
                $this->online = TRUE;
                return TRUE;
            }
        }
        $this->online = FALSE;
        return FALSE;
    }
    
    public function online() {
        return $this->online;
    }
}

abstract class BasicEnum {
    private static $constCache = NULL;

    private static function getConstants() {
        if (self::$constCache === NULL) {
            $reflect = new ReflectionClass(get_called_class());
            self::$constCache = $reflect->getConstants();
        }

        return self::$constCache;
    }

    public static function isValidName($name, $strict = false) {
        $constants = self::getConstants();

        if ($strict) {
            return array_key_exists($name, $constants);
        }

        $keys = array_map('strtolower', array_keys($constants));
        return in_array(strtolower($name), $keys);
    }

    public static function isValidValue($value) {
        $values = array_values(self::getConstants());
        return in_array($value, $values, $strict = true);
    }
}

abstract class NotificationLevel extends BasicEnum {
    const NONE = 0;
    const PROBLEMS = 1;
    const REPORT = 2;
}

class PHP_Pinger {
    
    private $hosts;
    private $hosts_offline;

    protected $callback;
    protected $callback_preRun;
    protected $callback_postRun;
    
    protected $notification;
    
    public function __construct() {
        $this->hosts = array();
        $this->hosts_offline = array();
        $this->notification['level'] = NotificationLevel::NONE;
        $this->notification['preffix'] = 'Status';
        return $this;
    }
    
    public function addHost($host, $port = 80, $label = NULL) {
        $this->hosts[] = new Host($host, $port, $label);
        return $this;
    }

    public function registerCallback($callback) {
        $this->callback = $callback;
        return $this;
    }
    public function registerPreRun($callback) {
        $this->callback_preRun = $callback;
        return $this;
    }
    public function registerPostRun($callback) {
        $this->callback_postRun = $callback;
        return $this;
    }
    
    public function enableNotification($notification_level = NotificationLevel::PROBLEMS) {
        if (NotificationLevel::isValidValue($notification_level)) {
            $this->notification['level'] = $notification_level;
        }
        return $this;
    }
    public function disableNotification() {
        $this->notification['level'] = NotificationLevel::NONE;
        return $this;
    }
    public function setNotificationTo($emails) {
        $this->notification['emails'] = $emails;
        return $this;
    }
    public function setNotificationPreffix($preffix) {
        $this->notification['preffix'] = $preffix;
        return $this;
    }
    public function setNotificationSignature($signature) {
        $this->notification['signature'] = $signature;
        return $this;
    }
    
    private function sendNotification($subject, $message) {
        if ($this->notification['level'] != NotificationLevel::NONE) {
            $header = 'MIME-Version: 1.0'.PHP_EOL;
            $header .= 'Content-type: text/plain; charset=utf-8'.PHP_EOL;
            $header .= "X-Priority: 2".PHP_EOL;
            mail(
                implode(',', $this->notification['emails']),
                '['.$this->notification['preffix'].'] '.$subject,
                $message.(!empty($this->notification['signature']) ? PHP_EOL.$this->notification['signature'] : ''),
                $header
            );
        }
    }
    private function sendNotificationProblems() {
        $message = 'Hello

You have some hosts which are offline:
';
            foreach ($this->hosts_offline as $host) {
                $message .= '    * '.$host->host().':'.$host->port().PHP_EOL;
            }
            $message .= '
Keep you in touch.';
        $this->sendNotification(
            count($this->hosts_offline).' offline host'.(count($this->hosts_offline)>1 ? 's' : ''), 
            $message
        );
    }
    private function sendNotificationReport() {
        $message = 'Hello

Results of last status:
';
            foreach ($this->hosts as $host) {
                $message .= '    * '.$host->host().':'.$host->port().' > '.($host->online() ? 'up' : 'down').PHP_EOL;
            }
            $message .= '
Keep you in touch.';
        $this->sendNotification(
            'Report for '.count($this->hosts).' host'.(count($this->hosts)>1 ? 's' : ''), 
            $message
        );
    }
    private function notify() {
        switch ($this->notification['level']) {
            case NotificationLevel::PROBLEMS:
                if (!empty($this->hosts_offline)) { $this->sendNotificationProblems(); }
                break;
            case NotificationLevel::REPORT:
                $this->sendNotificationReport();
                break;
            default:
                break;
        }
    }
    
    public function run() {
        if (! empty($this->hosts)) {
            // PreRun
            $this->hosts_offline = array();
            if (is_callable($this->callback_preRun)) {
                call_user_func($this->callback_preRun);
            }
            // Run
            foreach($this->hosts as $host) {
                $host->check();
                if (!$host->online()) { $this->hosts_offline[] = $host; }
                if (is_callable($this->callback)) {
                    call_user_func($this->callback, $host->online(), $host->host(), $host->port(), $host->label());
                }
            }
            // PostRun
            $this->notify();
            if (is_callable($this->callback_postRun)) {
                call_user_func($this->callback_postRun);
            }
        }
    }
}

?>