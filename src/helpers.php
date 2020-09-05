<?php

use Ringierimu\Recommend\SendEvent;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

if (! function_exists('send_recommendation_interaction')) {
    /**
     * @param $entity
     * @param string $eventType
     */
    function send_recommendation_interaction(
        $entity,
        string $eventType
    ) {
        if (! config('recommend.enabled')) {
            return;
        }

        $sendEvent = new SendEvent($entity, $eventType);

        if (function_exists('dispatch_after_response')) {
            dispatch_after_response($sendEvent);
        } else {
            dispatch($sendEvent);
        }
    }
}

if (! function_exists('get_recommendations')) {
    /**
     * @param $entity
     * @param $recipe
     *
     * @return array
     */
    function get_recommendations(
        $entity,
        string $recipe = null
    ): array {
        if (! config('recommend.enabled')) {
            return [];
        }

        $client = resolve(Client::class);
        $domainCode = app()->has('domain_code')
            ? resolve('domain_code')
            : null;

        try {
            $response = $client
                ->request(
                    'GET',
                    sprintf(
                        '%s%s?%s',
                        config('recommend.api_url'),
                        config('recommend.recommendations_path'),
                        http_build_query(
                            [
                                'engine' => get_engine(),
                                'recipe' => $recipe ?? config('recommend.default_recipe', 'related_items'),
                                'itemId' => $entity->id,
                                'context' => array_merge(
                                    $domainCode
                                        ? [
                                            'domain' => $domainCode,
                                        ]
                                        : [],
                                    [
                                        'sessionHash' => md5(
                                            sprintf(
                                                '%s%s',
                                                $domainCode
                                                    ? $domainCode . '_'
                                                    : '',
                                                Session::getId()
                                            )
                                        ),
                                    ],
                                ),
                            ],
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

            return json_decode($response->getBody()) ?: [];
        } catch (Exception $e) {
            Log::debug(
                'Recommend get_recommendations error',
                [
                    'entityId' => $entity->id,
                    'exception' => $e,
                ]
            );

            return [];
        }
    }
}

if (!function_exists('get_engine')) {
    /**
     * @return string
     */
    function get_engine(): string {
        return config('recommend.experiments.recommend.' . experiment_group('experiment_recommend'));
    }
}

if (!function_exists('experiment_group')) {
    /**
     * Return what user group the current
     * user is for a given experiment
     *
     * @param string $experimentName
     *
     * @return string
     */
    function experiment_group(
        string $experimentName
    ): string {
        return is_control($experimentName)
            ? 'control'
            : 'test';
    }
}

if (!function_exists('is_control')) {
    /**
     * Check if the current user is in the
     * control group for a given experiment
     *
     * @param string $experimentName
     *
     * @return bool
     */
    function is_control(
        string $experimentName
    ): bool {
        $experiments = $_COOKIE['experiments'] ?? '';
        $experiment = array_get(
            (array) json_decode($experiments),
            $experimentName
        );

        if (mb_strstr($experiment, 'test')) {
            return false;
        }

        return true;
    }
}
