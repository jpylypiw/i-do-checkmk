<?php
/**
 * This class contains some useful functions.
 *
 * PHP Version 7
 *
 * @category Helpers
 * @package  I_Do_Checkmk
 * @author   Jochen Pylypiw <jochen@pylypiw.com>
 * @license  GPL 3.0
 * @link     https://github.com/KingJP/i-do-checkmk
 */

namespace I_Do_Checkmk;

if (! class_exists('Tools')) {

    /**
     * Class Tools
     *
     * @category Helpers
     * @package  I_Do_Checkmk
     * @author   Jochen Pylypiw <jochen@pylypiw.com>
     * @license  GPL 3.0
     * @link     https://github.com/KingJP/i-do-checkmk
     */
    class Tools
    {

        /**
         * Test if a string is in Json Format
         *
         * @param string $string the string to test
         *
         * @return bool
         */
        public static function isJson(string $string)
        {
            json_decode($string);
            return (json_last_error() == JSON_ERROR_NONE);
        }

        /**
         * Remove unnecessary characters from string
         *
         * @param string $hostname hostname to clean
         *
         * @return mixed|string
         */
        public static function cleanHostname(string $hostname)
        {
            if (isset(parse_url($hostname)['host'])) {
                $hostname = parse_url($hostname)['host'];
            } else {
                $hostname = trim($hostname, '/');
                $hostname = preg_replace(
                    array(
                        '#^http?://#',
                        '#^https?://#',
                        '#^www.#', '#^//#',
                        '#^ftp?://#'
                    ), '',
                    $hostname
                );
            }
            return $hostname;
        }

    }
}