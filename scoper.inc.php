<?php

declare(strict_types=1);

use Isolated\Symfony\Component\Finder\Finder;

return [
    // The prefix configuration. If a non null value will be used, a random prefix will be generated.
    'prefix' => 'WappoVendor',

    // By default when running php-scoper add-prefix, it will prefix all relevant code found in the current working
    // directory. You can however define which files should be scoped by defining a collection of Finders in the
    // following configuration key.
    //
    // For more see: https://github.com/humbug/php-scoper#finders-and-paths
    'finders' => [
        Finder::create()->files()->in('app'),
        Finder::create()
            ->files()
            ->ignoreVCS(true)
            ->notName('/LICENSE|.*\\.md|.*\\.dist|Docker|METADATA-VERSION.txt|README\\.markdown|Makefile|UPGRADE_TO_2_1|UPGRADE_TO_2_2|phpstan\\.neon|build\\.xml|phpunit\\.xml|build\\.properties|composer\\.json|composer\\.lock/')
            ->exclude([
                // 'doctrine',
                // 'egulias',
                // 'giggsey',
                // 'guzzlehttp',
                // 'illuminate',
                // 'nesbot',
                // 'phpcompatibility',
                // 'rakit',
                // 'sabre',
                // 'phpcompatibility',
                // 'squizlabs',
                // 'symfony',
                'doc',
                'docs',
                'test',
                'Test',
                'test_old',
                'Tests',
                'tests',
                'vendor-bin',
                'bin',
                'vobject/resources'
            ])
            ->in('vendor'),
        Finder::create()->append([
            'composer.json',
        ]),
    ],

    // Whitelists a list of files. Unlike the other whitelist related features, this one is about completely leaving
    // a file untouched.
    // Paths are relative to the configuration file unless if they are already absolute
    'files-whitelist' => [
        'app/required.php',
        'app/Transports/WpMailPatched.php',
        'app/Services/IcsGenerator.php',
        'app/Services/Permissions.php',
    ],

    // When scoping PHP files, there will be scenarios where some of the code being scoped indirectly references the
    // original namespace. These will include, for example, strings or string manipulations. PHP-Scoper has limited
    // support for prefixing such strings. To circumvent that, you can define patchers to manipulate the file to your
    // heart contents.
    //
    // For more see: https://github.com/humbug/php-scoper#patchers
    'patchers' => [
        function (string $filePath, string $prefix, string $contents): string {
            // Change the contents here.
            $search = [
                'tap(',
                'collect(',
                'class_uses_recursive(',
                'class_basename(',
                'last(',
                'value(',
                'blank(',
                'filled(',
                'data_get(',
                'transform(',
                'windows_os(',
                'head(',
            ];
            $find_replace = [
                'findpattern' => [],
                'replace' => [],
            ];
            foreach ($search as $searchFunction) {
                $find_replace['findpattern'][] = str_replace('REP', str_replace('(', '\(', $searchFunction), '/(?<!function |->|\w|::|\$)REP/');
                $find_replace['replace'][] = '\WappointmentLv::' . $searchFunction;
            }

            $files_search = [
                '/vendor/illuminate/database/',
                '/vendor/illuminate/filesystem/',
                '/vendor/illuminate/http/',
                '/vendor/illuminate/session/',
                '/vendor/illuminate/support/'
            ];
            if (strpos($filePath, '/vendor/illuminate/support/helpers.php') !== false) {
                echo "\nFULL REPLACE $filePath  \n";
                return '<?php

if (!function_exists("dd")) {
    function dd(...$args)
    {
        http_response_code(500);

        if(defined("REST_REQUEST") && REST_REQUEST === true){
            echo  json_encode(["args"=>$args, "backtrace" => debug_backtrace(FALSE, 2)]);
        }else{
            foreach ($args as $x) {
                (new \WappoVendor\Illuminate\Support\Debug\Dumper)->dump($x);
            }
        }

        die(1);
    }
}

if (!function_exists("dds")) {
    function dds($content, $filename = "default")
    {
        $dirlog = WAPPOINTMENT_PATH . "logs";
        if(!is_dir($dirlog)){
            mkdir($dirlog, 0755);
        }
        file_put_contents($dirlog . DIRECTORY_SEPARATOR . time() . $filename, $content);
    }
}';
            }
            if (strpos($filePath, '/app/') !== false) {
                $contents = str_replace([
                    '\WappoVendor\WappointmentValidationException',
                    '\WappoVendor\WappointmentException',
                    '\WappoVendor\WappointmentLv',
                    '\WappoVendor\WP_Widget',
                    '\WappoVendor\WP_Error',
                    '\WappoVendor\WP_Ajax_Upgrader_Skin',
                    '\WappoVendor\Plugin_Upgrader',
                    '\WappoVendor\WP_Filesystem_Base',
                    '\WappoVendor\WappoSwift_',
                    '\WappoVendor\__',
                    '\WappoVendor\is_plugin_active',
                    '\WappoVendor\get_plugins',
                    '\WappoVendor\activate_plugin',
                    '\WappoVendor\deactivate_plugin',
                ], [
                    '\WappointmentValidationException',
                    '\WappointmentException',
                    '\WappointmentLv',
                    '\WP_Widget',
                    '\WP_Error',
                    '\WP_Ajax_Upgrader_Skin',
                    '\Plugin_Upgrader',
                    '\WP_Filesystem_Base',
                    '\WappoSwift_',
                    '__',
                    '\is_plugin_active',
                    '\get_plugins',
                    '\activate_plugin',
                    '\deactivate_plugin',

                ], $contents);
            }


            foreach ($files_search as $file_name) {
                if (strpos($filePath, $file_name) !== false) {
                    echo "\nFound $filePath  replace \n";
                    return preg_replace(
                        $find_replace['findpattern'],
                        $find_replace['replace'],
                        $contents
                    );
                }
            }


            return $contents;
        },
    ],

    // PHP-Scoper's goal is to make sure that all code for a project lies in a distinct PHP namespace. However, you
    // may want to share a common API between the bundled code of your PHAR and the consumer code. For example if
    // you have a PHPUnit PHAR with isolated code, you still want the PHAR to be able to understand the
    // PHPUnit\Framework\TestCase class.
    //
    // A way to achieve this is by specifying a list of classes to not prefix with the following configuration key. Note
    // that this does not work with functions or constants neither with classes belonging to the global namespace.
    //
    // Fore more see https://github.com/humbug/php-scoper#whitelist
    'whitelist' => [
        // 'PHPUnit\Framework\TestCase',   // A specific class
        // 'PHPUnit\Framework\*',          // The whole namespace
        // '*',                            // Everything
        'Wappointment\*',                            // Everything
        /*        '\WappointmentLv',
        '\WappointmentException',
        '\WP_Widget',*/
        'true',
        'false',
    ],

    // If `true` then the user defined constants belonging to the global namespace will not be prefixed.
    //
    // For more see https://github.com/humbug/php-scoper#constants--constants--functions-from-the-global-namespace
    'whitelist-global-constants' => true,

    // If `true` then the user defined classes belonging to the global namespace will not be prefixed.
    //
    // For more see https://github.com/humbug/php-scoper#constants--constants--functions-from-the-global-namespace
    'whitelist-global-classes' => true,

    // If `true` then the user defined functions belonging to the global namespace will not be prefixed.
    //
    // For more see https://github.com/humbug/php-scoper#constants--constants--functions-from-the-global-namespace
    'whitelist-global-functions' => true,
];
