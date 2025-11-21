<?php

use Spatie\DbDumper\Compressors\GzipCompressor;

return [

    'backup' => [

        'name' => env('BACKUP_NAME', env('APP_NAME', 'Laravel')),

        'source' => [
            'files' => [
                /*
                 * INCLUIR SÓLO LA BASE DE DATOS: 
                 * La lista de archivos de la aplicación se deja VACÍA.
                 */
                'include' => [],

                /*
                 * Estos directorios y archivos serán excluidos del backup.
                 */
                'exclude' => [
                    base_path('vendor'),
                    base_path('node_modules'),
                ],

                'follow_links' => false,
                'ignore_unreadable_directories' => false,
                'relative_path' => null,
            ],

            /*
             * Conexiones de bases de datos que deben ser respaldadas.
             * .
             */
            'databases' => [
                // Cambiado el fallback de 'mariadb' a 'mysql' para alinearse con la solución del dumper.
                env('DB_CONNECTION', 'mysql'),
            ],

            /*
             * COMPRESIÓN DEL DUMP DE LA BASE DE DATOS:
             * Usa Gzip.
             */
            'database_dump_compressor' => GzipCompressor::class,

            'database_dump_file_timestamp_format' => 'Y-m-d-H-i-s',
            'database_dump_filename_base' => 'database',
            
            /*
             * La extensión del archivo dump, ahora .sql.gz por la compresión Gzip.
             */
            'database_dump_file_extension' => 'sql.gz',
        ],

        'destination' => [
            /*
             * Usa la compresión por defecto y nivel 9 (máxima compresión).
             */
            'compression_method' => ZipArchive::CM_DEFAULT,
            'compression_level' => 9,

            /*
             * PREFIJO DE NOMBRE SOLICITADO
             */
            'filename_prefix' => 'Backup_Maquindus_DB_',

            /*
             * El disco personalizado donde se almacenará el backup (definido en .env).
             */
            'disks' => [
                env('BACKUP_DISK_NAME', 'backup_custom_disk'),
            ],
        ],

        'temporary_directory' => storage_path('app/backup-temp'),

        /*
         * Contraseña para cifrar el archivo ZIP (se obtiene de BACKUP_ARCHIVE_PASSWORD en tu .env).
         */
        'encryption' => 'default',
        'tries' => 1,
        'retry_delay' => 0,
    ],

    // --- SECCIÓN DE NOTIFICACIONES ---
    'notifications' => [
        'notifications' => [
            \Spatie\Backup\Notifications\Notifications\BackupHasFailedNotification::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\UnhealthyBackupWasFoundNotification::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\CleanupHasFailedNotification::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\BackupWasSuccessfulNotification::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\HealthyBackupWasFoundNotification::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\CleanupWasSuccessfulNotification::class => ['mail'],
        ],

        'notifiable' => \Spatie\Backup\Notifications\Notifiable::class,

        'mail' => [
            'to' => 'your@example.com',

            'from' => [
                'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
                'name' => env('MAIL_FROM_NAME', 'Example'),
            ],
        ],
    ],

    /*
     * Sección de Monitoreo (Ahora monitorea el disco customizado)
     */
    'monitor_backups' => [
        [
            'name' => env('APP_NAME', 'Laravel'), // <-- Fallback a 'Laravel'
            
            /* * CORREGIDO: Monitorea el disco que definiste en BACKUP_DISK_NAME
             */
            'disks' => [env('BACKUP_DISK_NAME', 'local')], 
            
            'health_checks' => [
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumAgeInDays::class => 1,
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumStorageInMegabytes::class => 5000,
            ],
        ],
    ],

    /*
     * Configuración de la estrategia de limpieza (sin cambios)
     */
    'cleanup' => [
        'strategy' => \Spatie\Backup\Tasks\Cleanup\Strategies\DefaultStrategy::class,

        'default_strategy' => [
            'keep_all_backups_for_days' => 7,
            'keep_daily_backups_for_days' => 30,
            'keep_weekly_backups_for_weeks' => 8,
            'keep_monthly_backups_for_months' => 4,
            'keep_yearly_backups_for_years' => 2,
            'delete_oldest_backups_when_using_more_megabytes_than' => 5000,
        ],
        'tries' => 1,
        'retry_delay' => 0,
    ],
];