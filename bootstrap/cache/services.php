<?php return array (
  'providers' => 
  array (
    0 => 'Illuminate\\Auth\\AuthServiceProvider',
    1 => 'Illuminate\\Database\\DatabaseServiceProvider',
    2 => 'Illuminate\\Encryption\\EncryptionServiceProvider',
    3 => 'Illuminate\\Filesystem\\FilesystemServiceProvider',
    4 => 'Illuminate\\Redis\\RedisServiceProvider',
    5 => 'Illuminate\\Translation\\TranslationServiceProvider',
    6 => 'Illuminate\\Validation\\ValidationServiceProvider',
    7 => 'Illuminate\\View\\ViewServiceProvider',
    8 => 'Fideloper\\Proxy\\TrustedProxyServiceProvider',
    9 => 'Fruitcake\\Cors\\CorsServiceProvider',
    10 => 'Laravel\\Tinker\\TinkerServiceProvider',
    11 => 'Carbon\\Laravel\\ServiceProvider',
    12 => 'Overtrue\\LaravelWeChat\\ServiceProvider',
    13 => 'App\\Providers\\AppServiceProvider',
    14 => 'App\\Providers\\EventServiceProvider',
    15 => 'App\\Providers\\RouteServiceProvider',
  ),
  'eager' => 
  array (
    0 => 'Illuminate\\Auth\\AuthServiceProvider',
    1 => 'Illuminate\\Database\\DatabaseServiceProvider',
    2 => 'Illuminate\\Encryption\\EncryptionServiceProvider',
    3 => 'Illuminate\\Filesystem\\FilesystemServiceProvider',
    4 => 'Illuminate\\View\\ViewServiceProvider',
    5 => 'Fideloper\\Proxy\\TrustedProxyServiceProvider',
    6 => 'Fruitcake\\Cors\\CorsServiceProvider',
    7 => 'Carbon\\Laravel\\ServiceProvider',
    8 => 'Overtrue\\LaravelWeChat\\ServiceProvider',
    9 => 'App\\Providers\\AppServiceProvider',
    10 => 'App\\Providers\\EventServiceProvider',
    11 => 'App\\Providers\\RouteServiceProvider',
  ),
  'deferred' => 
  array (
    'redis' => 'Illuminate\\Redis\\RedisServiceProvider',
    'redis.connection' => 'Illuminate\\Redis\\RedisServiceProvider',
    'translator' => 'Illuminate\\Translation\\TranslationServiceProvider',
    'translation.loader' => 'Illuminate\\Translation\\TranslationServiceProvider',
    'validator' => 'Illuminate\\Validation\\ValidationServiceProvider',
    'validation.presence' => 'Illuminate\\Validation\\ValidationServiceProvider',
    'command.tinker' => 'Laravel\\Tinker\\TinkerServiceProvider',
  ),
  'when' => 
  array (
    'Illuminate\\Redis\\RedisServiceProvider' => 
    array (
    ),
    'Illuminate\\Translation\\TranslationServiceProvider' => 
    array (
    ),
    'Illuminate\\Validation\\ValidationServiceProvider' => 
    array (
    ),
    'Laravel\\Tinker\\TinkerServiceProvider' => 
    array (
    ),
  ),
);