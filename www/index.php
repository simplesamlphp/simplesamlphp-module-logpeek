<?php

/**
 * @param \SimpleSAML\Module\logpeek\File\ReverseRead $objFile
 * @param string $tag
 * @param int $cut
 * @return array
 */
function logFilter(\SimpleSAML\Module\logpeek\File\ReverseRead $objFile, string $tag, int $cut): array
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


$config = \SimpleSAML\Configuration::getInstance();
$session = \SimpleSAML\Session::getSessionFromRequest();

\SimpleSAML\Utils\Auth::requireAdmin();

$logpeekconfig = \SimpleSAML\Configuration::getConfig('module_logpeek.php');
$logfile = $logpeekconfig->getValue('logfile', '/var/simplesamlphp.log');
$blockSize = $logpeekconfig->getValue('blocksz', 8192);

$myLog = new \SimpleSAML\Module\logpeek\File\ReverseRead($logfile, $blockSize);


$results = [];
if (isset($_REQUEST['tag'])) {
    $results = logFilter($myLog, $_REQUEST['tag'], $logpeekconfig->getValue('lines', 500));
}


$fileModYear = intval(date("Y", $myLog->getFileMtime()));
$firstLine = $myLog->getFirstLine();
$firstTimeEpoch = \SimpleSAML\Module\logpeek\Syslog\ParseLine::getUnixTime($firstLine ?: '', $fileModYear);
$lastLine = $myLog->getLastLine();
$lastTimeEpoch = \SimpleSAML\Module\logpeek\Syslog\ParseLine::getUnixTime($lastLine ?: '', $fileModYear);
$fileSize = $myLog->getFileSize();

$t = new \SimpleSAML\XHTML\Template($config, 'logpeek:logpeek.twig');
$t->data['results'] = $results;
$t->data['trackid'] = $session->getTrackID();
$t->data['timestart'] = date(DATE_RFC822, $firstTimeEpoch ?: time());
$t->data['endtime'] = date(DATE_RFC822, $lastTimeEpoch ?: time());
$t->data['filesize'] = $fileSize;

$t->send();
