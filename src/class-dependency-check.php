<?php

namespace i_do_checkmk;

if ( ! class_exists( 'Dependency_Check' )) {

    class Dependency_Check
    {

        public function check_all_dependencies() {
            $result = $this->check_curl();

            if ($result === true)
                $result = $this->check_json();

            return $result;
        }

        public function check_curl() {
            if (! function_exists('curl_init') ||
                ! function_exists('curl_setopt') ||
                ! function_exists('curl_exec') ||
                ! function_exists('curl_close')) {
                return $this->error ('Curl not available. Please install PHP Curl Extension.');
            }
            return true;
        }

        public function check_json () {
            if (! function_exists('json_decode')) {
                return $this->error ('You are using a very old PHP version. Please update your PHP version to use json_decode.' . PHP_EOL . 'Check PHP Documentation: http://php.net/manual/de/function.json-decode.php');
            }

            if (! function_exists('json_encode')) {
                return $this->error ('You are using a very old PHP version. Please update your PHP version to use json_encode.' . PHP_EOL . 'Check PHP Documentation: http://php.net/manual/de/function.json-encode.php');
            }
            return true;
        }

        private function error($error) {
            throw new \Exception('ERROR: i-do-checkmk Dependency Check Failed.' . PHP_EOL . 'Message: ' . $error);
            return false;
        }

    }

}