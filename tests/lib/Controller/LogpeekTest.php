<?php

declare(strict_types=1);

namespace SimpleSAML\Test\Module\logpeek\Controller;

use PHPUnit\Framework\TestCase;
use SimpleSAML\Configuration;
use SimpleSAML\Logger;
use SimpleSAML\Module\logpeek\Controller;
use SimpleSAML\Session;
use SimpleSAML\Utils;
use Symfony\Component\HttpFoundation\Request;

use function tmpfile;

/**
 * Set of tests for the controllers in the "logpeek" module.
 *
 * @covers \SimpleSAML\Module\logpeek\Controller\Logpeek
 */
class LogpeekTest extends TestCase
{
    /** @var \SimpleSAML\Configuration */
    protected Configuration $config;

    /** @var \SimpleSAML\Session */
    protected Session $session;

    /** @var \SimpleSAML\Utils\Auth */
    protected Utils\Auth $authUtils;


    /**
     * Set up for each test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $tmpfile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'simplesamlphp.log';
        touch($tmpfile);

        $this->config = Configuration::loadFromArray(
            [
                'module.enable' => ['logpeek' => true, 'loggingdir' => dirname($tmpfile)],
            ],
            '[ARRAY]',
            'simplesaml'
        );

        // Log some random stuff to the tmpfile
        Logger::warning("some");
        Logger::warning("test");
        Logger::warning("data");

        $this->session = Session::getSessionFromRequest();

        $this->authUtils = new class () extends Utils\Auth {
            public function requireAdmin(): void
            {
                // stub
            }
        };

        Configuration::setPreLoadedConfig(
            Configuration::loadFromArray(
                [
                    'logfile' => $tmpfile,
                    'lines'   => 1500,

                    // Read block size. 8192 is max, limited by fread.
                    'blocksz' => 8192,
                ],
                '[ARRAY]',
                'simplesaml'
            ),
            'module_logpeek.php',
            'simplesaml'
        );
    }


    /**
     */
    public function testMain(): void
    {
        $request = Request::create(
            '/',
            'GET'
        );

        $c = new Controller\Logpeek($this->config, $this->session);
        $c->setAuthUtils($this->authUtils);
        $response = $c->main($request);

        $this->assertTrue($response->isSuccessful());
    }


    /**
     */
    public function testMainWithTag(): void
    {
        $request = Request::create(
            '/',
            'GET',
            ['tag' => $this->session->getTrackID()]
        );

        $c = new Controller\Logpeek($this->config, $this->session);
        $c->setAuthUtils($this->authUtils);
        $response = $c->main($request);

        $this->assertTrue($response->isSuccessful());
    }
}
