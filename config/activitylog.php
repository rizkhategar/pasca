<?php

use App\Models\ActivityLog;

return [
    'enabled' => env('ACTIVITY_LOGGER_ENABLED', true),

    'delete_records_older_than_days' => 365,

    'default_log_name' => 'system',

    'default_auth_driver' => null,

    'subject_returns_soft_deleted_models' => false,

    'activity_model' => ActivityLog::class,

    'table_name' => env('ACTIVITY_LOGGER_TABLE_NAME', 'activity_logs'),

    'database_connection' => env('ACTIVITY_LOGGER_DB_CONNECTION'),
];
