<?php
/**
 * The class contains the supported methods of i-doit API.
 *
 * PHP Version 7
 *
 * @category I-Doit
 * @package  I_Do_Checkmk
 * @author   Jochen Pylypiw <jochen@pylypiw.com>
 * @license  GPL 3.0
 * @link     https://github.com/KingJP/i-do-checkmk
 */

namespace I_Do_Checkmk;

if (! class_exists('IdoitApiMethods')) {

    /**
     * Class IdoitApiMethods
     *
     * @category I-Doit
     * @package  I_Do_Checkmk
     * @author   Jochen Pylypiw <jochen@pylypiw.com>
     * @license  GPL 3.0
     * @link     https://github.com/KingJP/i-do-checkmk
     */
    class IdoitApiMethods
    {
        /**
         * @param IdoitApi $api
         *
         * @return null
         */
        public static function createSession(IdoitApi $api)
        {
            if ($api->sendRequest('idoit.login'))
            {
                $headers = $api->getHeadersOfLastRequest();

                if (isset($headers['X-RPC-Auth-Session']))
                {
                    return $headers['X-RPC-Auth-Session'];
                }
            }
            return null;
        }

        public static function closeSession(IdoitApi $api)
        {
            if ($api->sendRequest('idoit.logout'))
            {
                return true;
            }
            return false;
        }

        public static function idoitVersion(IdoitApi $api) {
            return $api->sendRequest('idoit.version');
        }


    }
}