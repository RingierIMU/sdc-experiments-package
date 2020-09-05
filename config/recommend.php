<?php

return [
    'enabled' => true,
    'api_url' => 'https://37piploi08.execute-api.eu-west-1.amazonaws.com/v1/',
    /* Obtain and place your API key below */
    'api_key' => '',
    'event_path' => 'event',
    'recommendations_path' => 'recommendations',
    'interactions_queue' => null,
    'experiments' => [
        'recommend' => [
            'control' => 'personalize',
            'test' => 'alice',
        ],
    ],
    'default_recipe' => 'related_items',
];
