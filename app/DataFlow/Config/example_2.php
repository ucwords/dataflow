<?php

return [
    'test_job_2' => [
        'source_connection' => 'dl_hongbao_222',
        'source_table' => 'user_join',
        'target_connection' => 'dl_hongbao_222',
        'target_table' => 'user_remain',
        'source_limit' => 30000,
        'source_limit_field' => [
            'field' => 'id',
            'default_value' => 0
        ],
        'source_where' => [],
        'source_group' => [],
        'no_clean' => true,
        'target_fields' => [
            'game_user_id' => 'user_id',
            'game_id' => [
                'source_field' => null,
                'alias' => 'game_id',
                'judge' => 'game_id|<|game_id|game_id',
                'callback' => 'defaultNumber|0'
            ],
            'game_origin_id' => [
                'source_field' => null,
                'alias' => 'game_origin_id',
                'callback' => 'fixedValue|347',
                'attribute' => true
            ],
            'server_id' => [
                'attribute' => true
            ],
            'role_id' => [
                'attribute' => true
            ],
            'role_name',
            'server_zone_id' => [
                'source_field' => 'server_zone',
                'attribute' => true
            ],
            'role_level' => [
                'judge' => 'role_level|<|role_level|role_level',
            ],
            'last_sync_time' => [
                'source_field' => null,
                'alias' => 'last_sync_time',
                'callback' => 'fixedValue|2020-03-01 00:00:00',
                'judge' => 'last_sync_time|<|last_sync_time|last_sync_time',
            ],
        ],
        'middleware' => ['App\EData\Middleware\Game' => 'fillMxServerName'],
        'write_type' => 'insertUpdate'
    ]
];
