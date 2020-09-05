<?php

namespace Ringierimu\Recommend\Tests\Unit;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Ringierimu\Recommend\Tests\TestCase;
use Ringierimu\Recommend\Tests\Traits\Guzzle;

class SendEventTest extends TestCase
{
    use Guzzle;

    public function testSuccess()
    {
        $this
            ->setGuzzleResponse(
                config('recommend.api_url') . config('recommend.event_path'),
                'tests/fixtures/events/success.json'
            );

        Log::shouldReceive('debug')
            ->withSomeOfArgs('Recommend send_recommendation_interaction error')
            ->never();

        $entity = (object) ['id' => 1];
        send_recommendation_interaction($entity, 'listing_view');
    }

    public function testFailure()
    {
        $this
            ->setGuzzleResponse(
                config('recommend.api_url') . config('recommend.event_path'),
                'tests/fixtures/events/failure.json',
                404
            );

        Log::shouldReceive('debug')
            ->withSomeOfArgs('Recommend send_recommendation_interaction error')
            ->once();

        $entity = (object) ['id' => 1];
        send_recommendation_interaction($entity, 'listing_view');
    }

    public function testNoSendWhenDisabled()
    {
        config(['recommend.enabled' => false]);
        Queue::fake();

        $this
            ->setGuzzleResponse(
                config('recommend.api_url') . config('recommend.event_path'),
                'tests/fixtures/events/success.json'
            );

        Queue::assertNothingPushed();

        $entity = (object) ['id' => 1];
        send_recommendation_interaction($entity, 'listing_view');
    }
}
