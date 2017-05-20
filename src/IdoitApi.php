<?php
/**
 * This class contains the interface to i-doit.
 *
 * You can find the full documentation of i-doit JSON-RPC API here:
 *
 * German:
 * https://kb.i-doit.com/pages/viewpage.action?pageId=7831613
 *
 * English:
 * https://kb.i-doit.com/pages/viewpage.action?pageId=37355644
 *
 * i-doit enables external access to the IT documentation via an application programming interface (API).
 * With the API data can be read, created, updated and deleted.
 * Thus the API offers similar functions like the Web GUI but the additional benefit is that you can automate them without any problems.
 */

namespace I_Do_Checkmk;

require('Tools.php');
require('IdoitApiMethods.php');

if (! class_exists('IdoitApi')) {

    /**
     * Class IdoitApi
     * @package I_Do_Checkmk
     */
    class IdoitApi
    {
        private $HOSTNAME;

        private $API_KEY;

        private $API_URL;

        private $USERNAME;

        private $PASSWORD;

        private $REQUEST_ID;

        private $SESSION_ID;

        private $HEADERS = array();

        /**
         * IdoitApi constructor.
         * @param $hostname
         * @param $api_key
         * @param string $username
         * @param string $password
         * @param bool $ssl
         * @throws \Exception
         */
        public function __construct(
            $hostname,
            $api_key,
            $username = '',
            $password = '',
            $ssl = false
        ) {
            $this->HOSTNAME = $hostname;
            $this->API_KEY = $api_key;
            $this->USERNAME = $username;
            $this->PASSWORD = $password;
            $this->REQUEST_ID = 0;

            $hostname = Tools::cleanHostname($hostname);
            $this->API_URL = ($ssl === true ? 'https://' : 'http://') . $hostname . '/src/jsonrpc.php';

            $session = IdoitApiMethods::createSession($this);
            if (isset($session) && $session != '') {
                $this->SESSION_ID = $session;
            }

            if (is_object(IdoitApiMethods::idoitVersion($this))) {
                return true;
            }
            throw new \Exception('Unable connecting to i-doit server. Please watch error log for detailed information.');
        }

        /**
         *
         */
        public function __destruct() {
            return IdoitApiMethods::closeSession($this);
        }

        /**
         * @param $method
         * @param $params
         * @return array
         * @throws \Exception
         */
        private function createRequest($method, $params)
        {
            // check
            if (!is_scalar($method))
            {
                throw new \Exception('Method name has no scalar value');
            }

            // check
            if (!is_array($params))
            {
                throw new \Exception('Params must be given as array');
            }

            $currentId = ++$this->REQUEST_ID;

            // prepares the prepare
            $request = array(
                'method'  => $method,
                'params'  => $params,
                'id'      => $currentId,
                'version' => '2.0'
            );

            return $request;
        }

        /**
         * @param $method
         * @param array $params
         * @return mixed
         * @throws \Exception
         */
        public function sendRequest($method, $params = array()) {
            $response = '';
            $curl_rq = null;
            $params = array('apikey' => $this->API_KEY) + $params;
            $request = json_encode($this->createRequest($method, $params));

            $header = array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($request)
            );

            if ($this->USERNAME != '' && !$this->SESSION_ID)
            {
                $header[] = 'Authorization: Basic ' . base64_encode($this->USERNAME . ':' . $this->PASSWORD);
            }

            if ($this->SESSION_ID)
            {
                $header[] = 'X-RPC-Auth-Session: ' . $this->SESSION_ID;
            }

            // perform the HTTP POST
            $opts = array(
                'http' => array(
                    'method'  => 'POST',
                    'header'  => implode("\r\n", $header),
                    'content' => $request
                ),
                'ssl' => array(
                    'verify_peer'   => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                    'ciphers' => 'ALL:!AES:!3DES:!RC4:@STRENGTH', // OK:LOW
                )
            );

            $context = stream_context_create($opts);
            $stream = null;

            try {
                if ($stream = fopen($this->API_URL, 'r', false, $context)) {

                    while ($row = fgets($stream))
                    {
                        $response .= trim($row) . "\n";
                    }

                    if (isset($http_response_header) && is_array($http_response_header))
                    {
                        foreach ($http_response_header as $header)
                        {
                            $tmp = explode(': ', $header);
                            if (isset($tmp[1]))
                            {
                                $this->HEADERS[$tmp[0]] = $tmp[1];
                            }
                        }
                    }
                }
                else {
                    throw new \Exception('Unable to connect to ' . $this->API_URL, array());
                }
            }
            catch (\Exception $exception) {
                throw $exception;
            }
            finally {
                if ($stream != null)
                    fclose($stream);
            }

            $response = mb_convert_encoding($response, 'UTF-8');
            return $this->validateResponse($response);
        }

        /**
         * @param $response
         * @return mixed
         * @throws \Exception
         */
        private function validateResponse($response) {
            json_decode($response);

            if (json_last_error() === JSON_ERROR_NONE) {
                $response = json_decode($response);

                if (isset($response->error) && !is_null($response->error)) {
                    throw new \Exception('Request error: ' . $response->error->message . ' (' . @$response->error->data->error . ')');
                }

                return $response->result;
            }

            throw new \Exception('i-doit server response is not in json format. got following server answer: ' . PHP_EOL . $response);
        }

        /**
         * @return array
         */
        public function getHeadersOfLastRequest()
        {
            return $this->HEADERS;
        }
    }
}