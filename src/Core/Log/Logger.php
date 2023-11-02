<?php
namespace Venancio\Fade\Core\Log;

final class Logger
{
    private static ?self $instance = null;
    private  $fileLogPath = null;
    private  $fileLog = null;

    private function createLogDirectory(): void
    {
        $logDirectory = dirname($this->fileLogPath);
        if (!is_dir($logDirectory)) {
            if (!mkdir($logDirectory, 0777, true)) {
                throw new \RuntimeException("Failed to create log directory: {$logDirectory}");
            }
        }
    }
    private function __construct()
    {
        $this->fileLogPath = 'logs/fade/router.log';
        $this->createLogDirectory();
        $this->fileLog = fopen($this->fileLogPath, 'a+'); // Corrija esta linha
    }

    public static function getInstance(): ?Logger
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function register(\Exception $exception): void
    {
        $date = new \DateTime();
        $date = $date->format('Y-m-d H:i:s');
        $message = "[Error: {$date} - {$exception->getMessage()}\nFile: {$exception->getFile()} - Line {$exception->getLine()}\n{$exception->getTraceAsString()}]\n";
        fwrite($this->fileLog, $message); // Corrija esta linha
    }
}
