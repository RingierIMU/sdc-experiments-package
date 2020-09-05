<?php

namespace Ringierimu\Recommend\Tests;

use GuzzleHttp\Client as GuzzleClient;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Ringierimu\Recommend\Tests\Mocks\GuzzleClient as MockGuzzleClient;

abstract class TestCase extends BaseTestCase
{

    protected function setUp(): void
    {
        parent::setUp();

        app()->bind(GuzzleClient::class, MockGuzzleClient::class);
    }

    protected function getEnvironmentSetUp($app)
    {
        $app->setBasePath(__DIR__ . '/..');
        config(
            [
                'recommend' => require __DIR__ . '/../config/recommend.php',
            ]
        );
    }
}
