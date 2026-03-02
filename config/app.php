<?php

use Illuminate\Support\Facades\Facade;

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    |
    */

    'name' => env('APP_NAME', 'OSneo'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => (bool) env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your application so that it is used when running Artisan tasks.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    'asset_url' => env('ASSET_URL'),

    /*
    |--------------------------------------------------------------------------
    | Program paths
    |--------------------------------------------------------------------------
    |
    | Program paths to Composer, NPM, GIT and PHP
    |
    */

	'composer' => env('APP_COMPOSER', 'composer'),
	'npm' => env('APP_NPM', 'npm'),
	'git' => env('APP_GIT', 'git'),
	'php' => env('APP_PHP', 'php'),

    /*
    |--------------------------------------------------------------------------
    | Editable environment variables
    |--------------------------------------------------------------------------
    |
    | The environmental variables listed can be edited in the user interface.
    |
    */

	'editable_env' => [
        'APP_NAME', 'APP_LOCALE', 'TIMEZONE', 'APP_CHANNEL', 'APP_DEBUG', 'APP_ENV', 'APP_URL', 'APP_COMPOSER', 'APP_NPM', 'APP_GIT', 'APP_PHP',
    ],

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    |
    */

    'timezone' => env('TIMEZONE', 'Asia/Karachi'),

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    */

	'locale' => env('APP_LOCALE', 'de'),

    /*
    |--------------------------------------------------------------------------
    | Application Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale determines the locale to use when the current one
    | is not available. You may change the value to correspond to any of
    | the language folders that are provided through your application.
    |
    */

    'fallback_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Faker Locale
    |--------------------------------------------------------------------------
    |
    | This locale will be used by the Faker PHP library when generating fake
    | data for your database seeds. For example, this will be used to get
    | localized telephone numbers, street address information and more.
    |
    */

    'faker_locale' => 'de_DE',

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is used by the Illuminate encrypter service and should be set
    | to a random, 32 character string, otherwise these encrypted strings
    | will not be safe. Please do this before deploying an application!
    |
    */

    'key' => env('APP_KEY'),

    'cipher' => 'AES-256-CBC',

    'downloads_folder' => 'downloads',

    /*
    |--------------------------------------------------------------------------
    | System User ID
    |--------------------------------------------------------------------------
    |
    | This ID will be used for logging the actions performed by the system.
    | user with this ID will be displayed in the logs
-    |
    */
    'system_user_id' => env('SYSTEM_USER_ID', 1),

    // leave reason used for creating auto leaves for unexcused absences
    'unexcused_leave_reason_id' => env('UNEXCUSED_LEAVE_REASON_ID', 6),

    'sick_leave_reason_id' => env('SICK_LEAVE_REASON_ID', 1),
    'rejected_sick_leave_reason_id' => env('REJECTED_SICK_LEAVE_REASON_ID', 11),

    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */

    'providers' => [

        /*
         * Laravel Framework Service Providers...
         */
        Illuminate\Auth\AuthServiceProvider::class,
        Illuminate\Broadcasting\BroadcastServiceProvider::class,
        Illuminate\Bus\BusServiceProvider::class,
        Illuminate\Cache\CacheServiceProvider::class,
        Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
        Illuminate\Cookie\CookieServiceProvider::class,
        Illuminate\Database\DatabaseServiceProvider::class,
        Illuminate\Encryption\EncryptionServiceProvider::class,
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        Illuminate\Foundation\Providers\FoundationServiceProvider::class,
        Illuminate\Hashing\HashServiceProvider::class,
        Illuminate\Mail\MailServiceProvider::class,
        Illuminate\Notifications\NotificationServiceProvider::class,
        Illuminate\Pagination\PaginationServiceProvider::class,
        Illuminate\Pipeline\PipelineServiceProvider::class,
        Illuminate\Queue\QueueServiceProvider::class,
        Illuminate\Redis\RedisServiceProvider::class,
        Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
        Illuminate\Session\SessionServiceProvider::class,
        Illuminate\Translation\TranslationServiceProvider::class,
        Illuminate\Validation\ValidationServiceProvider::class,
        Illuminate\View\ViewServiceProvider::class,

        /*
         * Package Service Providers...
         */
        WireElements\Pro\Components\Modal\ModalServiceProvider::class,
        WireElements\Pro\Components\SlideOver\SlideOverServiceProvider::class,
        WireElements\Pro\Components\Insert\InsertServiceProvider::class,

        /*
         * Application Service Providers...
         */
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        App\Providers\BroadcastServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\MacroServiceProvider::class,
        App\Providers\RouteServiceProvider::class,

        \SocialiteProviders\Manager\ServiceProvider::class,
        App\Providers\FakerServiceProvider::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be registered when this application
    | is started. However, feel free to register as many as you wish as
    | the aliases are "lazy" loaded so they don't hinder performance.
    |
    */

    'aliases' => Facade::defaultAliases()->merge([
        'LeavesHelper' => \App\Helpers\LeavesHelper::class,
        'Constants' => \App\Helpers\ConstantHelper::class,
        'GeneralHelper' => \App\Helpers\GeneralHelper::class,
        'DocumentSorter' => \App\Facades\DocumentSorterFacade::class,
        'Lexoffice' => \App\Lexoffice\Facades\LexofficeServiceFacade::class,
        'LexofficeApi' => \App\Lexoffice\Facades\LexofficeApiFacade::class,
        'Creditreform' => \App\Creditreform\Facades\CreditreformServiceFacade::class,
        'CreditreformApi' => \App\Creditreform\Facades\CreditreformApiFacade::class,
    ])->toArray(),
];
