<?php

declare(strict_types=1);

namespace SimpleSAML\Test\Module\logpeek\Controller;

use Exception;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Configuration;
use SimpleSAML\Logger;
use SimpleSAML\Module\logpeek\Controller;
use SimpleSAML\Session;
use SimpleSAML\Utils;
use Symfony\Component\HttpFoundation\Request;

use function dirname;
use function file_get_contents;
use function sprintf;
use function sys_get_temp_dir;
use function unlink;

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

    /** @var string */
    protected string $tmpfile;


    /**
     * Set up for each test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->session = Session::getSessionFromRequest();

        $this->tmpfile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'simplesamlphp.log';

        $this->config = Configuration::loadFromArray(
            [
                'module.enable' => ['logpeek' => true],
                'loggingdir' => dirname($this->tmpfile),
                'logging.handler' => 'file',
            ],
            '[ARRAY]',
            'simplesaml'
        );

        $tag = $this->session->getTrackID();
        file_put_contents(
            $this->tmpfile,
            [
                sprintf("Aug 17 19:21:51 SimpleSAMLphp WARNING [%s] some" . PHP_EOL, $tag),
                sprintf("Aug 17 19:21:52 SimpleSAMLphp WARNING [%s] test" . PHP_EOL, $tag),
                sprintf("Aug 17 19:21:53 SimpleSAMLphp WARNING [%s] data" . PHP_EOL, $tag),
            ]
        );

        $this->authUtils = new class () extends Utils\Auth {
            public function requireAdmin(): void
            {
                // stub
            }
        };

        Configuration::setPreLoadedConfig(
            Configuration::loadFromArray(
                [
                    'logfile' => $this->tmpfile,
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
     * Tear down after each test.
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unlink($this->tmpfile);
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


    /**
     */
    public function testMainWithInvalidTag(): void
    {
        $request = Request::create(
            '/',
            'GET',
            ['tag' => 'WRONG']
        );

        $c = new Controller\Logpeek($this->config, $this->session);
        $c->setAuthUtils($this->authUtils);

        $this->expectException(Exception::class);
        $c->main($request);
    }
}
