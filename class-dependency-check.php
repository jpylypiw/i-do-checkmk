<?php
/**
 * Created by PhpStorm.
 * User: joche
 * Date: 06.05.2017
 * Time: 13:09
 */

namespace i_do_checkmk;

if ( ! class_exists( 'Dependency_Check' )) {

    class Dependency_Check
    {

        public function check_all_dependencies() {
            $this->check_curl();
            $this->check_json();
        }

        public function check_curl() {
            if (! function_exists('curl_init') ||
                ! function_exists('curl_setopt') ||
                ! function_exists('curl_exec') ||
                ! function_exists('curl_close')) {
                $this->error_exit ('Curl not available. Please install PHP Curl Extension.');
            }
        }

        public function check_json () {
            if (! function_exists('json_decode')) {
                $this->error_exit ('You are using a very old PHP version. Please update your PHP version to use json_decode.' . PHP_EOL . 'Check PHP Documentation: http://php.net/manual/de/function.json-decode.php');
            }

            if (! function_exists('json_encode')) {
                $this->error_exit ('You are using a very old PHP version. Please update your PHP version to use json_encode.' . PHP_EOL . 'Check PHP Documentation: http://php.net/manual/de/function.json-encode.php');
            }
        }

        private function error_exit($error) {
            die ('ERROR: i-do-checkmk Dependency Check Failed.' . PHP_EOL . 'Message: ' . $error);
        }

    }

}