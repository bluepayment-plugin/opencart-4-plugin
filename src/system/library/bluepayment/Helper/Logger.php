<?php

namespace Opencart\System\Library\BluePayment\Helper;

require_once DIR_EXTENSION . 'bluepayment/system/library/bluemedia-sdk-php/index.php';

use Opencart\System\Engine\Registry;
use Monolog\Logger as MonologLogger;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;

final class Logger
{
    private const LOG_NAME = 'Autopay';
    private const LOG_FILENAME = '/bluepayment.log';
    private const LOG_FILE_PATH = DIR_LOGS . self::LOG_FILENAME;
    private const MAX_LOG_FILES = 30;

    private $registry;
    private $logger;

    public function __construct(Registry $registry)
    {
        $this->registry = $registry;

        $this->initRegistry();
        $this->initLogger();
    }

    public function log(string $level, string $message, array $context = []): void
    {
        $this->logger->log($level, $message, $context);
    }

    public function getRecentLog(): array
    {
        return $this->logFileToArray($this->getRecentLogPath());
    }

    public function refreshLog(): void
    {
        $result = $this->logFileToArray($this->getRequestedFilePath());

        $this->registry->get('response')->addHeader('Content-Type: application/json');
        $this->registry->get('response')->setOutput(json_encode($result));
    }

    public function download(): void
    {
        $file = $this->getRequestedFilePath();

        if (file_exists($file) && filesize($file) > 0) {
            $this->registry->get('response')->addheader('Pragma: public');
            $this->registry->get('response')->addheader('Expires: 0');
            $this->registry->get('response')->addheader('Content-Description: File Transfer');
            $this->registry->get('response')->addheader('Content-Type: application/octet-stream');
            $this->registry->get('response')
                ->addheader(
                    'Content-Disposition: attachment; filename="' .
                    $this->getLogFileName($file)
                );
            $this->registry->get('response')->addheader('Content-Transfer-Encoding: binary');

            $this->registry->get('response')
                ->setOutput(file_get_contents($file, FILE_USE_INCLUDE_PATH, null));
        } else {
            $this->registry->get('session')->data['message_warning'] = $this->registry->get('language')->get('log_file_download_error');

            $this->registry->get('response')
                ->redirect(
                    $this->registry->get('url')->link(
                        'extension/bluepayment/payment/bluepayment',
                        'user_token=' . $this->registry->get('session')->data['user_token'],
                        true
                    )
                );
        }
    }

    private function getRecentLogPath(): string
    {
        $files = $this->getFileList();

        if (is_array($files)) {
            return end($files);
        }

        return $files;
    }

    public function getFormattedFileList(): ?array
    {
        $result = [];

        foreach ($this->getFileList() as $file) {
            $result[$this->getLogFileName($file)] = $file;
        }

        return $result;
    }

    private function getFileList(): ?array
    {
        return glob(DIR_LOGS . '/bluepayment*.log');
    }

    private function getLogFileName(string $file_path): string
    {
        $file_path_parts = explode('/', $file_path);

        return end($file_path_parts);
    }

    private function initLogger(): void
    {
        $this->logger = new MonologLogger(self::LOG_NAME);

        $formatter = new LineFormatter(LineFormatter::SIMPLE_FORMAT, LineFormatter::SIMPLE_DATE);
        $formatter->includeStacktraces(true);

        $handler = new RotatingFileHandler(self::LOG_FILE_PATH, self::MAX_LOG_FILES, MonologLogger::INFO);
        $handler->setFormatter($formatter);

        $this->logger->pushHandler($handler);
    }

    private function initRegistry(): void
    {
        $this->registry->get('load')->library('BluePayment/Dictionary/BluepaymentDictionary');
		$this->registry->get('load')->language('extension/bluepayment/payment/bluepayment');
		$this->registry->get('load')->language('tool/log');
    }

    private function getRequestedFilePath(): string
    {
        return isset($this->registry->get('request')->get['selected_log_file'])
            ? $this->registry->get('request')->get['selected_log_file']
            : $this->getRecentLogPath();
    }

    private function logFileToArray(string $file): array
    {
        $result['logs'] = [];
        if (file_exists($file) && filesize($file) > 0) {
            foreach (file($file, FILE_USE_INCLUDE_PATH, null) as $line) {
                $result['logs'][] = $line;
            }
        } else {
            $result['error_warning'] = $this->registry->get('language')->get('log_file_not_found');
        }

        return $result;
    }
}
