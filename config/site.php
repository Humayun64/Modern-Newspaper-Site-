<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Search engine indexing
    |--------------------------------------------------------------------------
    |
    | Separate from APP_ENV on purpose. A staging copy needs production mode —
    | real error handling, no debug screen — while still being invisible to
    | Google. Without this switch, staging gets indexed and competes with the
    | live domain for the same headlines.
    |
    | Set APP_INDEXABLE=false on the staging subdomain.
    | Set APP_INDEXABLE=true only on the real domain.
    |
    */

    'indexable' => filter_var(
        env('APP_INDEXABLE', env('APP_ENV') === 'production'),
        FILTER_VALIDATE_BOOLEAN
    ),

];
