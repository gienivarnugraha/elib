<?php

return [

  /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

  'paths' => ['*'],

  'allowed_methods' => ['*'],

  'allowed_origins' => ['http://elib.acs.id'],
  //'allowed_origins' => ['http://elib.acs.id'],

  'supports_credentials' => true,

  'allowed_origins_patterns' => [],

  'allowed_headers' => ['*'],

  'exposed_headers' => [],

  'max_age' => 3600,

  //'allowed_origins' => ['*'],
  //'allowed_headers' => ['Content-Type, X-XSRF-TOKEN, Origin, Authorization, X-Requested-With, X-Socket-Id'],

];
