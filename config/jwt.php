<?php

return [
    'secret' => env('JWT_SECRET', ''),
    'issuer' => env('JWT_ISSUER', 'http://tcc-auth-service:8003'),
    'audience' => env('JWT_AUDIENCE', 'tcc-api-gateway'),
    'ttl_minutes' => env('JWT_TTL_MINUTES', 15),
];
