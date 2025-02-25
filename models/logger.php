<?php
class Logger {
    private $logFile;

    public function __construct($logFile = '../logs/app.log') {
        $this->logFile = $logFile;
    }

    public function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "$timestamp - $message" . PHP_EOL;
        file_put_contents($this->logFile, $logMessage, FILE_APPEND);
    }
}
?>
