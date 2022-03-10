<?php

declare(strict_types=1);

namespace SimpleSAML\Module\logpeek\Config;

use E_USER_NOTICE;
use SimpleSAML\Assert\Assert;
use SimpleSAML\Configuration;
use SimpleSAML\Utils;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

/**
 * Configuration for the module logpeek.
 */

class Logpeek {
    /** @var int */
    public const MAX_BLOCKSIZE = 8192;

    /** @var int */
    public const DEFAULT_BLOCKSIZE = 8192;

    /** @var int */
    public const DEFAULT_LINES = 500;

    /** @var string */
    private string $logFile;

    /** @var int */
    private int $lines = self::DEFAULT_LINES;

    /** @var int */
    private int $blockSize = self::DEFAULT_BLOCKSIZE;


    /**
     * @param string $defaultConfigFile
     * @throws \Symfony\Component\Yaml\Exception\ParseException
     */
    public function __construct(string $defaultConfigFile = 'module_logpeek.yml')
    {
        $configUtils = new Utils\Config();
        $configDir = $configUtils->getConfigDir();
        $yamlConfig = Yaml::parse(file_get_contents($configDir . '/' . $defaultConfigFile)) ?? [];
        if (isset($yamlConfig['logFile'])) {
            $this->setLogfile($yamlConfig['logFile']);
        } else {
            $config = Configuration::getInstance();
            $loggingDir = $config->getPathValue('loggingdir', 'log/');
            $this->setLogfile($loggingDir . $config->getString('logging.logfile', 'simplesamlphp.log'));
        }

        if (isset($yamlConfig['lines'])) {
            $this->setLines($yamlConfig['lines']);
        }

        if (isset($yamlConfig['blockSize'])) {
            $this->setBlockSize($yamlConfig['blockSize']);
        }
    }


    /**
     * @return string
     */
    public function getLogFile(): string {
        return $this->logFile;
    }


    /**
     * @param string $logFile
     * @return void
     */
    protected function setLogFile(string $logFile): void {
        $this->logFile = $logFile;
    }


    /**
     * @return int
     */
    public function getLines(): int {
        return $this->lines;
    }


    /**
     * @param int $lines
     * @return void
     */
    protected function setLines(int $lines): void {
        Assert::positiveInteger($lines);
        $this->lines = $lines;
    }


    /**
     * @return int
     */
    public function getBlockSize(): int {
        return $this->blockSize;
    }


    /**
     * @param int $blockSize
     * @return void
     */
    protected function setBlockSize(int $blockSize): void {
        Assert::range($blockSize, 0, self::MAX_BLOCKSIZE);
        $this->blockSize = $blockSize;
    }
};
