<?php
namespace Venancio\Fade\Core\Log;

final class Logger
{

    /**
     * @var self|null $instance The single instance of the Logger class. It's initially set to null.
     */
    private static ?self $instance = null;

    /**
     * @var string|null $fileLogPath The path to the log file. It's initially set to null.
     */
    private $fileLogPath = null;

    /**
     * @var resource|null $fileLog The file resource used for writing log information. It's initially set to null.
     */
    private $fileLog = null;


    /**
     * Constructor for the class.
     *
     * Initializes a new instance of the class and sets up the log file and directory.
     * It creates the log directory if it doesn't exist and opens the log file for appending.
     *
     * @throws \RuntimeException If it fails to create the log directory or open the log file.
     */
    private function __construct()
    {
        $this->createLogDirectory();
        $this->fileLog = fopen($this->fileLogPath, 'a+');
    }

    /**
     * Creates the log directory if it doesn't exist.
     *
     * This method checks if the directory where logs will be stored exists. If the directory doesn't exist,
     * it attempts to create the directory recursively with permissions set to 0777 to ensure logs can be written.
     *
     * @throws \RuntimeException If it fails to create the log directory.
     */
    private function createLogDirectory(): void
    {
        $this->fileLogPath = 'logs/fade/router.log';
        $logDirectory = dirname($this->fileLogPath);
        if (!is_dir($logDirectory)) {
            if (!mkdir($logDirectory, 0777, true)) {
                throw new \RuntimeException("Failed to create log directory: {$logDirectory}");
            }
        }
    }

    /**
     * Get an instance of the Logger class, creating it if it doesn't already exist.
     *
     * This method ensures that there's only one instance of the Logger class throughout the application's lifecycle.
     * It returns the existing instance if it has been created, or creates a new one if it doesn't exist.
     *
     * @return Logger An instance of the Logger class.
     */
    public static function getInstance(): ?Logger
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Register an exception in the log.
     *
     * This method records information about a thrown exception, including its message, file, line number,
     * and stack trace, in the log file. It appends this information to the existing log file content.
     *
     * @param \Throwable $exception The exception to be registered in the log.
     */
    public function register(\Throwable $exception): void
    {
        $date = new \DateTime();
        $date = $date->format('Y-m-d H:i:s');
        $message = "[Error: {$date} - {$exception->getMessage()}\nFile: {$exception->getFile()} - Line {$exception->getLine()}\n{$exception->getTraceAsString()}]\n";
        fwrite($this->fileLog, $message); // Corrija esta linha
    }
}
