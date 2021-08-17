<?php

declare(strict_types=1);

namespace SimpleSAML\Module\logpeek\Controller;

use Exception;
use SimpleSAML\Assert\Assert;
use SimpleSAML\Configuration;
use SimpleSAML\Module\logpeek\File;
use SimpleSAML\Module\logpeek\Syslog;
use SimpleSAML\Session;
use SimpleSAML\Utils;
use SimpleSAML\XHTML\Template;
use Symfony\Component\HttpFoundation\Request;

use function array_reverse;
use function intval;
use function count;
use function date;
use function preg_match;
use function strstr;

/**
 * Controller class for the logpeek module.
 *
 * This class serves the different views available in the module.
 *
 * @package simplesamlphp/simplesamlphp-module-logpeek
 */
class Logpeek
{
    /** @var \SimpleSAML\Configuration */
    protected Configuration $config;

    /** @var \SimpleSAML\Configuration */
    protected Configuration $moduleConfig;

    /** @var \SimpleSAML\Session */
    protected Session $session;


    /**
     * Controller constructor.
     *
     * It initializes the global configuration and session for the controllers implemented here.
     *
     * @param \SimpleSAML\Configuration $config The configuration to use by the controllers.
     * @param \SimpleSAML\Session $session The session to use by the controllers.
     *
     * @throws \Exception
     */
    public function __construct(
        Configuration $config,
        Session $session
    ) {
        $this->config = $config;
        $this->moduleConfig = Configuration::getConfig('module_logpeek.php');
        $this->session = $session;
    }


    /**
     * Main index controller.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request The current request.
     *
     * @return \SimpleSAML\XHTML\Template
     */
    public function attributes(Request $request): Template
    {
        Utils\Auth::requireAdmin();

        $logfile = $this->moduleConfig->getValue('logfile', '/var/simplesamlphp.log');
        $blockSize = $this->moduleConfig->getValue('blocksz', 8192);

        $myLog = new File\ReverseRead($logfile, $blockSize);

        $results = [];
        if ($request->query->has('tag') === true) {
            /** @psalm-var string $tag */
            $tag = $request->query->get('tag');
            Assert::notNull($tag);

            $results = $this->logFilter($myLog, $tag, $this->moduleConfig->getValue('lines', 500));
        }

        $fileModYear = intval(date("Y", $myLog->getFileMtime()));
        $firstLine = $myLog->getFirstLine();
        $firstTimeEpoch = Syslog\ParseLine::getUnixTime($firstLine ?: '', $fileModYear);
        $lastLine = $myLog->getLastLine();
        $lastTimeEpoch = Syslog\ParseLine::getUnixTime($lastLine ?: '', $fileModYear);
        $fileSize = $myLog->getFileSize();

        $t = new Template($this->config, 'logpeek:logpeek.twig');
        $t->data['results'] = $results;
        $t->data['trackid'] = $this->session->getTrackID();
        $t->data['timestart'] = date(DATE_RFC822, $firstTimeEpoch ?: time());
        $t->data['endtime'] = date(DATE_RFC822, $lastTimeEpoch ?: time());
        $t->data['filesize'] = $fileSize;

        return $t;
    }


    /**
     * @param \SimpleSAML\Module\logpeek\File\ReverseRead $objFile
     * @param string $tag
     * @param int $cut
     * @return array
     */
    private function logFilter(File\ReverseRead $objFile, string $tag, int $cut): array
    {
        if (!preg_match('/^[a-f0-9]{10}$/D', $tag)) {
            throw new Exception('Invalid search tag');
        }

        $i = 0;
        $results = [];
        $line = $objFile->getPreviousLine();
        while ($line !== false && ($i++ < $cut)) {
            if (strstr($line, '[' . $tag . ']')) {
                $results[] = $line;
            }
            $line = $objFile->getPreviousLine();
        }

        $results[] = 'Searched ' . $i . ' lines backward. ' . count($results) . ' lines found.';
        $results = array_reverse($results);
        return $results;
    }
}
