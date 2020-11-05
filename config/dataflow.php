<?php

/** @var string 配置文件基本目录 $basePath */
$basePath = app_path('DataFlow/Config');

/** @var array 所有子目录 $allConfigPath */
$allConfigPath = [
    // ENV 配置项可用于设置该任务的起停 => 任务配置文件
    'DATA_FLOW_CONFIG_TEST'     => '/example.php',
    'DATA_FLOW_CONFIG_TEST_TOW' => '/example_2.php',

];

/** @var array 获取所有有效的配置 $allValidConfig */
/** 同名脚本 后出现的会覆盖掉前面 请留意 */
return collect($allConfigPath)->filter(function ($file, $env) {

    if (env($env, false) == true) return $file;

})->mapWithKeys(function ($config, $key) use ($basePath) {

    return [$key => include_once $basePath.$config ?? []];

})->filter(function ($config, $item) {

    if (!empty($config)) return $config;

})->collapse()->toArray();

