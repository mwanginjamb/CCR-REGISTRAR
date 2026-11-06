<?php

// Connection settings come from .env (loaded by yiithings/yii2-dotenv);
// see .env.example for the keys.
return [
    'class' => 'yii\db\Connection',
    'dsn' => sprintf(
        'mysql:host=%s;dbname=%s;port=%s',
        env('DB_HOST', 'localhost'),
        env('DB_NAME', 'ccr'),
        env('DB_PORT', '3306')
    ),
    'username' => env('DB_USER', 'root'),
    'password' => env('DB_PASSWORD', ''),
    'charset' => 'utf8',

    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];
