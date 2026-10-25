<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'manypaycash' => [
        'url' => env('MANYPAYCASH_API_URL', 'https://www.manypaycash.com/api/support/partenaire'),
        'users_url' => env('MANYPAYCASH_USERS_API_URL', 'https://www.manypaycash.com/api/support/getUsers'),
        'search_by_external_url' => env('MANYPAYCASH_SEARCH_TR_BY_EXTERNAL_URL', 'https://www.manypaycash.com/api/support/searchTrByExternalId'),
        'search_by_internal_url' => env('MANYPAYCASH_SEARCH_TR_BY_INTERNAL_URL', 'https://www.manypaycash.com/api/support/searchTrByInternalId'),
        'api_key' => env('MANYPAYCASH_API_KEY', 'VWhxZzJiLW5uUGNTbThFLW5DemNSZkJfZ3dTeHNWRmNMUk96aVpjRzIxNTc2MTk4NjE'),
        'customers_url' => env(
            'MANYPAYCASH_CUSTOMERS_URL',
            'https://www.manypaycash.com/api/support/customers'
        ),

        'search_by_partner_url' => env(
            'MANYPAYCASH_SEARCH_BY_PARTNER_URL',
            'https://www.manypaycash.com/api/support/searchTrByPartnerId'
        ),

        'customer_transactions_url' => env(
            'MANYPAYCASH_CUSTOMER_TRANSACTIONS_URL',
            'https://www.manypaycash.com/api/support/customerTransactions'
        ),
    ],

];
