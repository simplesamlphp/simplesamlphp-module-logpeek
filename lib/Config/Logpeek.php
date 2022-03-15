<?php

declare(strict_types=1);

namespace SimpleSAML\Module\logpeek\Config;

use SimpleSAML\Assert\Assert;
use SimpleSAML\Configuration;
use SimpleSAML\Utils;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

/**
 * Configuration for the module logpeek.
 */

class Logpeek {
    /** @var string */
    public const DEFAULT_CONFIGFILE = 'module_logpeek.yml';

    /** @var int */
    public const MAX_BLOCKSIZE = 8192;

    /** @var int */
    public const DEFAULT_BLOCKSIZE = 8192;

    /** @var int */
    public const DEFAULT_LINES = 500;

    /** @var string */
    private string $logFile;

    /** @var int */
    private int $lines;

    /** @var int */
    private int $blockSize;


    /**
     * @param string|null $logFile
     * @param int $blockSize
     * @param int $lines
     */
    public function __construct(?string $logFile, ?int $blockSize = null, ?int $lines = null)
    {
        if ($logFile === null) {
            $config = Configuration::getInstance();
            $loggingDir = $config->getPathValue('loggingdir', 'log/');
            $logFile = $loggingDir . $config->getString('logging.logfile', 'simplesamlphp.log');
        }

        $this->setLogFile($logFile);
        $this->setBlockSize($blockSize ?? self::DEFAULT_BLOCKSIZE);
        $this->setLines($lines ?? self::DEFAULT_LINES);
    }


    /**
     * @return string
     */
    public function getLogFile(): string
    {
        return $this->logFile;
    }


    /**
     * @param string $logFile
     * @return void
     */
    protected function setLogFile(string $logFile): void
    {
        $this->logFile = $logFile;
    }


    /**
     * @return int
     */
    public function getLines(): int
    {
        return $this->lines;
    }


    /**
     * @param int $lines
     * @return void
     */
    protected function setLines(int $lines): void
    {
        Assert::positiveInteger($lines);
        $this->lines = $lines;
    }


    /**
     * @return int
     */
    public function getBlockSize(): int
    {
        return $this->blockSize;
    }


    /**
     * @param int $blockSize
     * @return void
     */
    protected function setBlockSize(int $blockSize): void
    {
        Assert::range($blockSize, 0, self::MAX_BLOCKSIZE);
        $this->blockSize = $blockSize;
    }


    /**
     * @param string $file
     * @return self
     */
    public static function fromPhp(string $configFile = 'module_logpeek.php'): self
    {
        $configUtils = new Utils\Config();
        $configDir = $configUtils->getConfigDir();
        include($configDir . '/' . $configFile);

        return static::fromArray($config ?? []);
    }


    /**
     * @param string $file
     * @return self
     */
    public static function fromYaml(string $configFile = self::DEFAULT_CONFIGFILE): self
    {
        $configUtils = new Utils\Config();
        $configDir = $configUtils->getConfigDir();
        $yamlConfig = Yaml::parse(file_get_contents($configDir . '/' . $configFile));

        return static::fromArray($yamlConfig ?? []);
    }


    /**
     * @param array $config
     * @return self
     */
    public static function fromArray(array $config): self
    {
        return new self($config['logFile'], $config['blockSize'], $config['lines']);
    }
};
