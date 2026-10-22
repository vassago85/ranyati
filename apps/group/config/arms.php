<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Featured spots
    |--------------------------------------------------------------------------
    |
    | How many live listings may be pinned to the top of the public stock
    | list at once. Staff are not unfeatured automatically if the count is
    | already higher than this.
    |
    */

    'max_featured' => (int) env('ARMS_MAX_FEATURED', 6),

    /*
    |--------------------------------------------------------------------------
    | Stale listing age
    |--------------------------------------------------------------------------
    |
    | Active listings older than this many days show a "Listed N+ days"
    | badge on the admin list so old stock is easy to spot.
    |
    */

    'stale_after_days' => (int) env('ARMS_STALE_AFTER_DAYS', 60),

    /*
    |--------------------------------------------------------------------------
    | New arrival badge
    |--------------------------------------------------------------------------
    |
    | Public cards show "New" for this many days after the listing is added.
    |
    */

    'new_badge_days' => (int) env('ARMS_NEW_BADGE_DAYS', 14),

];
