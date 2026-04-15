<?php

return [
    'auto_backup' => env('BACKUP_AUTO_ENABLED', true),
    'backup_frequency' => env('BACKUP_FREQUENCY', 'weekly'),
    'retention_days' => env('BACKUP_RETENTION_DAYS', 30),
];
