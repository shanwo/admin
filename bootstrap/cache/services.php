<?php return array (
  'providers' => 
  array (
    0 => 'Illuminate\\Auth\\AuthServiceProvider',
    1 => 'Illuminate\\Database\\DatabaseServiceProvider',
    2 => 'Illuminate\\Encryption\\EncryptionServiceProvider',
    3 => 'Illuminate\\Filesystem\\FilesystemServiceProvider',
    4 => 'Illuminate\\Hashing\\HashServiceProvider',
    5 => 'Illuminate\\Redis\\RedisServiceProvider',
    6 => 'Illuminate\\Translation\\TranslationServiceProvider',
    7 => 'Illuminate\\Validation\\ValidationServiceProvider',
    8 => 'Illuminate\\View\\ViewServiceProvider',
    9 => 'Fideloper\\Proxy\\TrustedProxyServiceProvider',
    10 => 'Fruitcake\\Cors\\CorsServiceProvider',
    11 => 'Laravel\\Tinker\\TinkerServiceProvider',
    12 => 'Carbon\\Laravel\\ServiceProvider',
    13 => 'Overtrue\\LaravelWeChat\\ServiceProvider',
    14 => 'App\\Providers\\AppServiceProvider',
    15 => 'App\\Providers\\EventServiceProvider',
    16 => 'App\\Providers\\RouteServiceProvider',
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
    'hash' => 'Illuminate\\Hashing\\HashServiceProvider',
    'hash.driver' => 'Illuminate\\Hashing\\HashServiceProvider',
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
    'Illuminate\\Hashing\\HashServiceProvider' => 
    array (
    ),
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