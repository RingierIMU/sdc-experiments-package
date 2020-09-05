<?php

namespace Ringierimu\Recommend;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class SendEvent implements ShouldQueue
{
    use Queueable;

    /**
     * @var int
     */
    public $tries = 1;

    /**
     * @var int
     */
    protected $entityId;

    /**
     * @var string
     */
    protected $sessionHash;

    /**
     * @var string
     */
    protected $eventType;

    /**
     * @var int
     */
    protected $timestamp;

    /**
     * @param $entity
     * @param string $eventType
     *
     */
    public function __construct(
        $entity,
        string $eventType
    ) {
        $queue = config('recommend.interactions_queue');
        if ($queue) {
            $this->onQueue(config('recommend.interactions_queue'));
        }

        $this->entityId = $entity->id;
        $this->sessionHash = md5(
            sprintf(
                '%s%s',
                app()->has('domain_code')
                    ? resolve('domain_code') . '_'
                    : '',
                Session::getId()
            )
        );
        $this->eventType = $eventType;
        $this->timestamp = Carbon::now('UTC')->timestamp;
    }

    /**
     * @throws \Exception
     */
    public function handle()
    {
        $client = resolve(Client::class);

        try {
            $response = $client
                ->request(
                    'POST',
                    sprintf(
                        '%s%s?%s',
                        config('recommend.api_url'),
                        config('recommend.event_path'),
                        http_build_query(
                            array_merge(
                                [
                                    'itemId' => $this->entityId,
                                    'userId' => $this->sessionHash,
                                    'eventType' => $this->eventType,
                                    'timestamp' => $this->timestamp,

                                ],
                                app()->has('domain_code')
                                    ? [
                                        'context' => [
                                            'domain' => resolve('domain_code'),
                                        ],
                                    ]
                                    : [],
                            )
                        )
                    ),
                    [
                        'headers' => [
                            'x-api-key' => config('recommend.api_key'),
                        ],
                    ]
                );

            if ($response->getStatusCode() != 200) {
                throw new Exception($response->getBody());
            }
        } catch (Exception $e) {
            Log::debug(
                'Recommend send_recommendation_interaction error',
                [
                    'entityId' => $this->entityId,
                    'userId' => $this->sessionHash,
                    'exception' => $e,
                ]
            );
        }
    }
}
