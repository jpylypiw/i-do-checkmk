<?php

namespace I_Do_Checkmk;

if ( ! class_exists( 'DependencyCheck' )) {

    /**
     * Class DependencyCheck
     * @package I_Do_Checkmk
     */
    class DependencyCheck
    {

        /**
         * @return bool
         */
        public function checkAllDependencies() {
            $result = $this->checkCurl();

            if ($result === true) {
                $result = $this->checkJson();
            }

            return $result;
        }

        /**
         * @return bool
         */
        public function checkCurl() {
            if (! function_exists('curl_init') ||
                ! function_exists('curl_setopt') ||
                ! function_exists('curl_exec') ||
                ! function_exists('curl_close')) {
                return $this->throwException ('Curl not available. Please install PHP Curl Extension.');
            }
            return true;
        }

        /**
         * @return bool
         */
        public function checkJson() {
            if (! function_exists('json_decode')) {
                return $this->throwException ('You are using a very old PHP version. Please update your PHP version to use json_decode.' . PHP_EOL . 'Check PHP Documentation: http://php.net/manual/de/function.json-decode.php');
            }

            if (! function_exists('json_encode')) {
                return $this->throwException ('You are using a very old PHP version. Please update your PHP version to use json_encode.' . PHP_EOL . 'Check PHP Documentation: http://php.net/manual/de/function.json-encode.php');
            }
            return true;
        }

        /**
         * @param $error
         * @return bool
         * @throws \Exception
         */
        private function throwException($error) {
            throw new \Exception('ERROR: i-do-checkmk Dependency Check Failed.' . PHP_EOL . 'Message: ' . $error);
            return false;
        }

    }

}