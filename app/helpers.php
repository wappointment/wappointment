<?php
declare(strict_types=1);

if (!function_exists('dd')) {
    /**
     * Dump the passed variables and end the script.
     *
     * @param mixed ...$vars
     * @return never
     */
    function dd(...$vars): never
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);
        array_shift($backtrace); // Remove dd() itself
        
        header('Content-Type: text/html; charset=utf-8');
        
        require WAPPOINTMENT_PATH . 'app/Views/debug/dd.php';
        
        exit(1);
    }
}

if (!function_exists('dump')) {
    /**
     * Dump the passed variables without ending the script.
     *
     * @param mixed ...$vars
     * @return void
     */
    function dump(...$vars): void
    {
        foreach ($vars as $var) {
            require WAPPOINTMENT_PATH . 'app/Views/debug/dump.php';
        }
    }
}
