<?php
return [
    /*
    |--------------------------------------------------------------------------
    | ACL Feature
    |--------------------------------------------------------------------------
    |
    | If set to true, file access will be restricted to the users who created the files.
    |
    */
    'acl_enabled' => true,

    /*
    |--------------------------------------------------------------------------
    | Disk Configuration
    |--------------------------------------------------------------------------
    |
    | The default disk to use for the file manager.
    | This disk should be configured in config/filesystems.php
    |
    */
    'disk' => env('FILEMANAGER_DISK', 'filemanager'),

    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the API endpoints for file management operations.
    |
    */
    'api' => [
        'enabled' => true,
        'prefix' => 'files/v1',
        'middleware' => ['api', 'auth:sanctum'],
        'rate_limit' => '100,1',
        'max_file_size' => 102400,
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt', 'zip', 'xls', 'xlsx', 'ppt', 'pptx', 'dwg', 'dxf'],
        'chunk_size' => 1048576,
    ],

    /*
    |--------------------------------------------------------------------------
    | Folder Configuration
    |--------------------------------------------------------------------------
    |
    | Configure folder creation and management settings.
    |
    */
    'folders' => [
        'max_depth' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Callbacks
    |--------------------------------------------------------------------------
    |
    | Custom callbacks for extending functionality.
    |
    */
    'callbacks' => [
        'before_upload' => null,
        'after_upload' => App\Callbacks\AfterFileUpload::class,
        'access_check' => null,
    ],
];
