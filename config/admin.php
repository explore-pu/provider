<?php

return [

    /*
    |--------------------------------------------------------------------------
    | name
    |--------------------------------------------------------------------------
    |
    | This value is the name of laravel-admin, This setting is displayed on the
    | login page.
    |
    */
    'name' => 'Elegant-Admin',

    /*
    |--------------------------------------------------------------------------
    | logo
    |--------------------------------------------------------------------------
    |
    | The logo of all admin pages. You can also set it as an image by using a
    | `img` tag, eg '<img src="http://logo-url" alt="Admin logo">'.
    |
    */
    'logo' => '<b>Elegant</b> Admin',

    /*
    |--------------------------------------------------------------------------
    | mini logo
    |--------------------------------------------------------------------------
    |
    | The logo of all admin pages when the sidebar menu is collapsed. You can
    | also set it as an image by using a `img` tag, eg
    | '<img src="http://logo-url" alt="Admin logo">'.
    |
    */
    'logo-mini' => '<b>EA</b>',

    /*
    |--------------------------------------------------------------------------
    | bootstrap setting
    |--------------------------------------------------------------------------
    |
    | This value is the path of bootstrap file.
    |
    */
    'bootstrap' => app_path('Admin/bootstrap.php'),

    /*
    |--------------------------------------------------------------------------
    | route settings
    |--------------------------------------------------------------------------
    |
    | The routing configuration of the admin page, including the path prefix,
    | the controller namespace, and the default middleware. If you want to
    | access through the root path, just set the prefix to empty string.
    |
    */
    'route' => [

        'prefix' => env('ADMIN_ROUTE_PREFIX', 'admin'),

        'namespace' => 'App\\Admin\\Controllers',

        'middleware' => ['web', 'admin'],
    ],

    /*
    |--------------------------------------------------------------------------
    | install directory
    |--------------------------------------------------------------------------
    |
    | The installation directory of the controller and routing configuration
    | files of the administration page. The default is `app/Admin`, which must
    | be set before running `artisan admin::install` to take effect.
    |
    */
    'directory' => app_path('Admin'),

    /*
    |--------------------------------------------------------------------------
    | html title
    |--------------------------------------------------------------------------
    |
    | Html title for all pages.
    |
    */
    'title' => 'Admin',

    /*
    |--------------------------------------------------------------------------
    | Access via `https`
    |--------------------------------------------------------------------------
    |
    | If your page is going to be accessed via https, set it to `true`.
    |
    */
    'https' => env('ADMIN_HTTPS', false),

    /*
    |--------------------------------------------------------------------------
    | auth setting
    |--------------------------------------------------------------------------
    |
    | Authentication settings for all admin pages. Include an authentication
    | guard and a user provider setting of authentication driver.
    |
    | You can specify a controller for `login` `logout` and other auth routes.
    |
    */
    'auth' => [

        'auth_controller' => App\Admin\Controllers\AuthController::class,

        'guard' => 'admin',

        'guards' => [
            'admin' => [
                'driver' => 'session',
                'provider' => 'admin',
            ],
        ],

        'providers' => [
            'admin' => [
                'driver' => 'eloquent',
                'model' => Elegant\Admin\Auth\Database\User::class,
            ],
        ],

        // Add "remember me" to login form
        'remember' => true,

        // Redirect to the specified URI when user is not authorized.
        'redirect_to' => 'login',

        // The URIs that should be excluded from authorization.
        'excepts' => [
            "login",
            "logout",
            "_handle_form_",
            "_handle_action_",
            "_handle_selectable_",
            "_handle_renderable_",
        ],

        // consolidation of routing permissions
        'merge' => [
            'self_setting_put' => 'self_setting',
            'store' => 'create',
            'update' => 'edit',
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | upload setting
    |--------------------------------------------------------------------------
    |
    | File system configuration for form upload files and images, including
    | disk and upload path.
    |
    */
    'upload' => [

        // Disk in `config/filesystem.php`.
        'disk' => 'admin',

        // Image and file upload path under the disk above.
        'directory' => [
            'image' => 'images',
            'file' => 'files',
        ],

        // Global configuration file returns the complete url
        'back_full_url' => false,

        // Global configuration file use unique name
        'unique_name' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | database settings
    |--------------------------------------------------------------------------
    |
    | Here are database settings for builtin model & tables.
    |
    */
    'database' => [

        // Database connection for following tables.
        'connection' => '',

        // User table, model and controller
        'users_table' => 'admin_users',
        'users_model' => Elegant\Admin\Auth\Database\User::class,
        //users_controller' => App\Admin\Controllers\AdminUserController::class,
        // Limit the maximum number of administrator roles that can be selected, default is 0, 0 means no limit
        //'users_maximum_roles' => '1',

        // Role table, model and controller
        'roles_table' => 'admin_roles',
        'roles_model' => Elegant\Admin\Auth\Database\Role::class,
        //'roles_controller' => App\Admin\Controllers\AdminRoleController::class,

        // Menu table, model and controller
        'menus_table' => 'admin_menus',
        'menus_model' => Elegant\Admin\Auth\Database\Menu::class,
        //'menus_controller' => App\Admin\Controllers\AdminMenuController::class,

        // Log table, model and controller
        'logs_table' => 'admin_logs',
        'logs_model' => Elegant\Admin\Auth\Database\Log::class,
        //'logs_controller' => App\Admin\Controllers\AdminLogController::class,

        // Pivot table for table above.
        'user_roles_table' => 'admin_user_roles',
    ],

    /*
    |--------------------------------------------------------------------------
    | User operation log setting
    |--------------------------------------------------------------------------
    |
    | By setting this option to open or close operation log in Elegant-Admin.
    |
    */
    'operation_log' => [

        'enable' => true,

        /*
         * Only logging allowed methods in the list
         */
        'allowed_methods' => ['GET', 'HEAD', 'POST', 'PUT', 'DELETE', 'CONNECT', 'OPTIONS', 'TRACE', 'PATCH'],

        /**
         * replace sensitive field data entered in the log
         */
        'desensitization' => [
            'password',
            'password_confirmation',
        ],

        /*
         * Routes that will not log to database.
         *
         * All method to path like: admin/auth/logs
         * or specific method to path like: get:admin/auth/logs.
         */
        'except' => [
            'admin_logs',
            'admin_logs/*',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Indicates whether to check route permission.
    |--------------------------------------------------------------------------
    */
    'check_permissions' => true,

    /*
    |--------------------------------------------------------------------------
    | Indicates whether to check menu roles.
    |--------------------------------------------------------------------------
    */
    'check_menus' => true,

    /*
    |--------------------------------------------------------------------------
    | User default avatar
    |--------------------------------------------------------------------------
    |
    | Set a default avatar for newly created users.
    |
    */
    'default_avatar' => '/vendor/elegant-admin/AdminLTE/dist/img/user2-160x160.jpg',

    /*
    |--------------------------------------------------------------------------
    | Admin map field provider
    |--------------------------------------------------------------------------
    |
    | Supported: "tencent", "google", "yandex".
    |
    */
    'map_provider' => 'google',

    /*
    |--------------------------------------------------------------------------
    | Application Skin
    |--------------------------------------------------------------------------
    |
    | This value is the skin of admin pages.
    | @see https://adminlte.io/docs/2.4/layout
    |
    | Supported:
    |    "skin-blue", "skin-blue-light", "skin-yellow", "skin-yellow-light",
    |    "skin-green", "skin-green-light", "skin-purple", "skin-purple-light",
    |    "skin-red", "skin-red-light", "skin-black", "skin-black-light".
    |
    */
    'skin' => 'skin-black-light',

    /*
    |--------------------------------------------------------------------------
    | Application layout
    |--------------------------------------------------------------------------
    |
    | This value is the layout of admin pages.
    | @see https://adminlte.io/docs/2.4/layout
    |
    | Supported: "fixed", "layout-boxed", "layout-top-nav", "sidebar-collapse", "sidebar-mini".
    |
    */
    'layout' => ['fixed', 'sidebar-mini'],

    /*
    |--------------------------------------------------------------------------
    | Login page background image
    |--------------------------------------------------------------------------
    |
    | This value is used to set the background image of login page.
    |
    */
    'login_background_image' => '',

    /*
    |--------------------------------------------------------------------------
    | Show version at footer
    |--------------------------------------------------------------------------
    |
    | Whether to display the version number of at the footer of each page
    |
    */
    'show_version' => true,

    /*
    |--------------------------------------------------------------------------
    | Show environment at footer
    |--------------------------------------------------------------------------
    |
    | Whether to display the environment at the footer of each page
    |
    */
    'show_environment' => true,

    /*
    |--------------------------------------------------------------------------
    | Menu bind to permission
    |--------------------------------------------------------------------------
    |
    | whether enable menu bind to a permission
    */
    'menu_bind_permission' => true,

    /*
    |--------------------------------------------------------------------------
    | Enable default breadcrumb
    |--------------------------------------------------------------------------
    |
    | Whether enable default breadcrumb for every page content.
    */
    'enable_default_breadcrumb' => true,

    /*
    |--------------------------------------------------------------------------
    | Enable/Disable assets minify
    |--------------------------------------------------------------------------
    */
    'minify_assets' => [

        // Assets will not be minified.
        'excepts' => [

        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Enable/Disable sidebar menu search
    |--------------------------------------------------------------------------
    */
    'enable_menu_search' => true,

    /*
    |--------------------------------------------------------------------------
    | Alert message that will displayed on top of the page.
    |--------------------------------------------------------------------------
    */
    'top_alert' => '',

    /*
    |--------------------------------------------------------------------------
    | The global Grid action display class.
    |--------------------------------------------------------------------------
    */
    'grid_action_class' => \Elegant\Admin\Grid\Displayers\DropdownActions::class,

    /*
    |--------------------------------------------------------------------------
    | Extension Directory
    |--------------------------------------------------------------------------
    |
    | When you use command `php artisan admin:extend` to generate extensions,
    | the extension files will be generated in this directory.
    */
    'extension_dir' => app_path('Admin/Extensions'),

    /*
    |--------------------------------------------------------------------------
    | Settings for extensions.
    |--------------------------------------------------------------------------
    |
    | You can find all available extensions here
    | https://github.com/laravel-admin-extensions.
    |
    */
    'extensions' => [
        // Editor configuration
        'editor' => [
            'config' => [
                'lang' => 'zh-CN',
                'height' => 300,
//                'toolbar' => [
//                    ['name' => 'clipboard', 'items' => ['Undo', 'Redo', 'Cut', 'Copy']],
//                    ['name' => 'editing', 'items' => [ 'Scayt' ]],
//                    ['name' => 'basicstyles', 'items' => [ 'Bold', 'Italic', 'Strike', '-', 'RemoveFormat' ]],
//                    ['name' => 'paragraph', 'items' => [ 'NumberedList', 'BulletedList', '-', 'Blockquote' ]],
//                    ['name' => 'links', 'items' => [ 'Link', 'Unlink', 'Anchor' ]],
//                    ['name' => 'insert', 'items' => ['Image', 'Table', 'HorizontalRule']],
//                    ['name' => 'styles', 'items' => [ 'Styles', 'Format' ]],
//                    ['name' => 'document', 'items' => [ 'Source' ]],
//                    ['name' => 'tools', 'items' => [ 'Maximize' ]]
//                ],
//                'filebrowserImageBrowseUrl' => '/file/browse',
//                'filebrowserImageUploadUrl' => '/editor/upload',
            ]
        ],
    ],
];
