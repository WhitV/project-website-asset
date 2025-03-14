<?php
class Logger {
    private $logFile;

    public function __construct($logFile = '../logs/app.log') {
        $this->logFile = $logFile;
    }

    public function log($message, $functionName = '', $page = '') {
        if (empty($functionName)) {
            $functionName = debug_backtrace()[1]['function'] ?? 'global';
        }
        if (empty($page)) {
            $page = $_SERVER['PHP_SELF'];
        }
        // Set timezone to Thailand
        date_default_timezone_set('Asia/Bangkok');
        $timestamp = date('Y-m-d H:i:s');
        // Simplified log message format
        $logMessage = "$timestamp - $message" . PHP_EOL;
        file_put_contents($this->logFile, $logMessage, FILE_APPEND);
    }
}
?>
