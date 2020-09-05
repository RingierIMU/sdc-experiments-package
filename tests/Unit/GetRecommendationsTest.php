<?php

namespace Ringierimu\Recommend\Tests\Unit;

use Illuminate\Support\Facades\Log;
use Ringierimu\Recommend\Tests\TestCase;
use Ringierimu\Recommend\Tests\Traits\Guzzle;

class GetRecommendationsTest extends TestCase
{
    use Guzzle;

    public function testSuccess()
    {
        $this
            ->setGuzzleResponse(
                config('recommend.api_url') . config('recommend.recommendations_path'),
                'tests/fixtures/recommendations/success.json'
            );

        Log::shouldReceive('debug')
            ->withSomeOfArgs('Recommend get_recommendations error')
            ->never();

        $entity = (object) ['id' => 1];
        $this->assertEquals(
            get_recommendations($entity),
            ['1234', '9876']
        );
    }

    public function testFailure()
    {
        $this
            ->setGuzzleResponse(
                config('recommend.api_url') . config('recommend.recommendations_path'),
                'tests/fixtures/recommendations/failure.json',
                500
            );

        Log::shouldReceive('debug')
            ->withSomeOfArgs('Recommend get_recommendations error')
            ->once();

        $entity = (object) ['id' => 1];
        get_recommendations($entity);
    }

    public function testNoResultsWhenDisabled()
    {
        config(['recommend.enabled' => false]);

        $this
            ->setGuzzleResponse(
                config('recommend.api_url') . config('recommend.recommendations_path'),
                'tests/fixtures/recommendations/success.json'
            );

        $entity = (object) ['id' => 1];
        $this->assertEquals(
            get_recommendations($entity),
            []
        );
    }
}
