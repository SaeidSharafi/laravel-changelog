<?php

/*
 * You can place your custom package configuration in here.
 */
return [
    'current_version' => '1.0.0',
    'use_html'        => true,
    'changelog_path'  => [
        'md' => [
            'fallback' => null,
            'en'       => null,
        ],
        'yaml' => [
            'fallback' => null,
            'en'       => null,
        ],
        'json' => [
            'fallback' => null,
            'en'       => null,
        ],
    ],
    'routes' => [
        'enabled'    => true,
        'prefix'     => 'changelog',
        'middleware' => ['web', 'auth'],
    ],
];
