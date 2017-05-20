<?php
/**
 * This class contains the interface to Check_MK.
 *
 * You can find the full Documentation of Check_MK JSON Web API here:
 * https://mathias-kettner.de/checkmk_wato_webapi.html
 *
 * Web-API requests are only available for automation users.
 * Please keep in mind that the configured user permissions within WATO, e.g. write permission to a folder, also apply to automation users.
 * You might give the automation user the role Administrator if you want full control.
 */

namespace I_Do_Checkmk;

if (! class_exists('CheckMkApi')) {


    /**
     * Class CheckMkApi
     * @package I_Do_Checkmk
     */
    class CheckMkApi {

        /**
         * Defines the hostname of Check_MK Server.
         *
         * @var string
         */
        private $HOSTNAME;

        /**
         * Contains the Check_MK Server Site ID.
         * This is important for distributed Monitoring.
         *
         * @var string
         */
        private $INSTANCE;

        /**
         * The username of the API User.
         * Please don't use omd user.
         *
         * @var string
         */
        private $USERNAME;

        /**
         * The automatition secret of the api user.
         * Please do not use normal password.
         *
         * @var string
         */
        private $PASSWORD;

        /**
         * Set the API URL of the Check_MK Server.
         * Please do not add http://. This is added automatically.
         *
         * Example: myfancyhostname.com
         *
         * @var string
         */
        private $API_URL;

        /**
         * Check_MK has a special Inventory API url.
         * This is set automatically.
         *
         * @var string
         */
        private $INVENTORY_API_URL;

        /**
         * CheckMkApi constructor.
         * @param $hostname
         * @param $instance
         * @param $username
         * @param $password
         * @param bool $ssl
         * @throws \Exception
         */
        public function __construct($hostname, $instance, $username, $password, $ssl = false)
        {
            $hostname = Tools::cleanHostname($hostname);

            $this->HOSTNAME = $hostname;
            $this->INSTANCE = $instance;
            $this->USERNAME = $username;
            $this->PASSWORD = $password;

            $instance = trim($instance, '/');

            $check_mk_server = ($ssl === true ? 'https://' : 'http://') .  $hostname . '/' . $instance . '/check_mk/';

            $this->API_URL = $check_mk_server . 'webapi.py';
            $this->INVENTORY_API_URL = $check_mk_server . 'host_inv_api.py';

            if (is_object($this->getAllHosts())) {
                return true;
            }
            throw new \Exception('Unable contacting Check_MK API. Please check the hostname und credentials.');
        }

        /**
         * Send API Request to Check_MK Server with CURL.
         *
         * @param $action
         * @param string $attributes
         * @param string $post_data
         * @return bool|mixed
         * @throws \Exception
         */
        private function sendRequest($action, $attributes = '', $post_data = '{}') {
            $response = '';
            $request = null;

            try {
                $request = curl_init();

                if ($action !== 'host_inv_api') {
                    curl_setopt($request, CURLOPT_URL, $this->API_URL . '?action=' . $action . '&_username=' . $this->USERNAME . '&_secret=' . $this->PASSWORD . $attributes);
                    curl_setopt($request, CURLOPT_CUSTOMREQUEST, "POST");
                    curl_setopt($request, CURLOPT_POSTFIELDS, 'request=' . $post_data);
                } else {
                    curl_setopt($request, CURLOPT_URL, $this->INVENTORY_API_URL . '?_username=' . $this->USERNAME . '&_secret=' . $this->PASSWORD . $attributes);
                }

                curl_setopt($request, CURLOPT_RETURNTRANSFER, true);

                $response = curl_exec($request);
            }
            catch (\Exception $exception) {
                throw $exception;
            }
            finally {
                if ($request != null)
                    curl_close($request);
            }

            $response = mb_convert_encoding($response, 'UTF-8');
            return $this->validateResponse($response);
        }

        /**
         * Checks, if the server answer is valid and contains a valid json response.
         * If not we will return false, so the main class can handle this.
         *
         * @param $response
         * @return bool|mixed
         * @throws \Exception
         */
        private function validateResponse($response) {
            json_decode($response);

            if (json_last_error() === JSON_ERROR_NONE) {
                $response = json_decode($response);

                if ($response->result_code === 1) {
                    throw new \Exception('Got Error from Check_MK. Error Message: ' . $response->result);
                    return false;
                }
            }

            return $response;
        }

        /**
         * With the action add_host you can add a new host to WATO.
         *
         * You need to specify the hostname and the folder where the host resides.
         * Additionally, you may add further elements into the attributes dictionary.
         * In the attributes block you can set values like alias and ipaddress and even host tags.
         * Host tags are specified by tag_{groupname} : value
         * If this host is a cluster host you can specify its nodes with the key nodes followed by a list of the node names.
         *
         * $hostname:
         * { "hostname": "winxp_1" }
         *
         * $folder:
         * { "folder": "os/windows" }
         *
         * $attributes:
         * "attributes": { "tag_criticality": "prod", "tag_agent": "cmk-agent", "alias": "Alias of winxp_1", "ipaddress": "127.0.0.1", }
         *
         * $create_folders:
         * Per default, non-existing host folders are created automatically. You can change this behaviour with the additional parameter create_folders=0.
         *
         * @param $hostname
         * @param $folder
         * @param string $attributes
         * @param array $nodes
         * @param int $create_folders
         * @return bool|mixed
         */
        public function addHost($hostname, $folder, $attributes = null, $nodes = array(), $create_folders = 1) {
            if ($attributes == null)
            {
                $attributes = array('site' => $this->INSTANCE);
            }

            $post_data = array(
                'hostname' => $hostname,
                'folder' => $folder,
                'attributes' => $attributes,
                'nodes' => $nodes
           );
            $post_data = json_encode($post_data);

            return $this->sendRequest('add_host', '&create_folders=' . $create_folders, $post_data);
        }

        /**
         * With the action edit_host you can edit an already existing WATO host. You can only change a hosts attributes, but NOT the hosts folder.
         *
         * Since the hostname is unique within WATO you only need to specify the host via the hostname parameter.
         * You are able to update the attributes with new values.
         * Further attributes of this host, which are not mentioned in the attributes block are not modified.
         * If this host is a cluster host you can specify its nodes with the key nodes followed by a list of the node names.
         *
         * Furthermore, via unset_attributes you can unset attributes for this host, so they are no longer explicitly set and can be inherited from parent folders.
         *
         * $hostname:
         * { "hostname": "winxp_1" }
         *
         * $attributes:
         * "attributes": { "site": "testsite2" }
         *
         * $unset_attributes:
         * "unset_attributes": ["tag_criticality"]
         *
         * @param $hostname
         * @param string $attributes
         * @param array $unset_attributes
         * @param array $nodes
         * @return bool|mixed
         */
        public function editHost($hostname, $unset_attributes = array(), $attributes = null, $nodes = array()) {
            if ($attributes == null)
            {
                $attributes = array('site' => $this->INSTANCE);
            }

            $post_data = array(
                'hostname' => $hostname,
                'attributes' => $attributes,
                'unset_attributes' => $unset_attributes,
                'nodes' => $nodes
           );
            $post_data = json_encode($post_data);

            return $this->sendRequest('edit_host', '', $post_data);
        }

        /**
         * With the action delete_host you can delete a host in WATO.
         *
         * $hostname:
         * { "hostname": "winxp_1" }
         *
         * @param $hostname
         * @return bool|mixed
         */
        public function deleteHost($hostname) {
            $post_data = array(
                'hostname' => $hostname
           );
            $post_data = json_encode($post_data);

            return $this->sendRequest('delete_host', '', $post_data);
        }

        /**
         * With the get_host action you can query the attributes of the given host.
         *
         * $hostname:
         * { "hostname": "winxp_1" }
         *
         * $effective_attributes:
         * This request returns only the explicitly set attributes of this host. If you want to have also the inherited attributes from the parent folders you need to add the parameter effective_attributes=1.
         *
         * @param $hostname
         * @param int $effective_attributes
         * @return bool|mixed
         */
        public function getHost($hostname, $effective_attributes = 0) {
            $post_data = array(
                'hostname' => $hostname
           );
            $post_data = json_encode($post_data);

            return $this->sendRequest('get_host', '&effective_attributes=' . $effective_attributes, $post_data);
        }

        /**
         * With the get_all_hosts action you can query the attributes of all hosts managed in WATO.
         *
         * $effective_attributes:
         * The default request returns only the explicitly set attributes of the hosts. If you want to have also the inherited attributes from the parent folders you need to add the parameter effective_attributes=1.
         *
         * @param int $effective_attributes
         * @return bool|mixed
         */
        public function getAllHosts($effective_attributes = 0) {
            return $this->sendRequest('get_all_hosts', '&effective_attributes=' . $effective_attributes);
        }

        /**
         * With the discover_services action you can start a service inventory for the given host.
         *
         * $hostname:
         * { "hostname": "winxp_1" }
         *
         * $mode:
         * new      Only find new services (default)
         * remove   Remove exceeding services
         * fixall   Remove exceeding and add new services
         * refresh  Clean all autochecks and start from scratch - Tabula Rasa
         *
         * @param $hostname
         * @param string $mode
         * @return bool|mixed
         */
        public function discoverServices($hostname, $mode = 'new') {
            $post_data = array(
                'hostname' => $hostname
           );
            $post_data = json_encode($post_data);

            return $this->sendRequest('discover_services', '&mode=' . $mode, $post_data);
        }

        /**
         * The bake_agents action triggers the agent baking on the monitoring site.
         * (enterprise edition only)
         *
         * @return bool|mixed
         */
        public function bakeAgents() {
            return $this->sendRequest('bake_agents');
        }

        /**
         * With the activate_changes action you can basically do the same as you've pressed the Activate changes! button in the Web-GUI.
         * If applicable, the changed configuration will be deployed on the slave sites, followed by a monitoring core reload.
         * Unlike the GUI version, this API action can not update multiple slave sites in parallel.
         * Instead it contacts one after another, sends the new configuration and wait till the core has reloaded.
         * This could lead to (apache timeout) problems in environments with multiple slave sites.
         *
         * $sites:
         * { "sites": ["site_nr1", "site_nr2"] }
         *
         * $mode:
         * dirty = Only update sites with changes (default)
         * all = Updates all slave sites
         * specific = Only updates sites specified in the request parameter
         *
         * $$allow_foreign_changes:
         * You can also set the parameter allow_foreign_changes=1 to take over changes from foreign users.
         * If this parameter is not set and a foreign user has made changes, the request will fail.
         *
         * @param null $sites
         * @param string $mode
         * @param int $allow_foreign_changes
         * @return bool|mixed
         */
        public function activateChanges($sites = null, $mode = 'dirty', $allow_foreign_changes = 0) {
            $post_data = '{}';

            if ($sites != null) {
                $post_data = array(
                    'sites' => $sites
               );
                $post_data = json_encode($post_data);
            }

            return $this->sendRequest('activate_changes', '&mode=' . $mode . '&allow_foreign_changes=' . $allow_foreign_changes, $post_data);
        }

        /**
         * The HW/SW inventory data can now be exported using a webservice. This webservice outputs the raw structured inventory data of a host.
         *
         * Full Documentation of Werk 3585:
         * http://mathias-kettner.de/check_mk_werks.php?werk_id=3585&HTML=yes
         *
         * $hostname:
         * { "hostname": "winxp_1" }
         *
         * $output_format:
         * json, xml, python
         *
         * $paths:
         * [".hardware.memory.total_ram_usable", ".hardware.memory.total_swap"]
         *
         * @param string $hostname
         * @param string $output_format
         * @param array $paths
         * @return bool|mixed
         */
        public function hostInvApi($hostname, $output_format = 'json', $paths = array()) {
            if (is_array($hostname)) {
                if (!array_key_exists('hosts', $hostname)) {
                    $hostname = array('hosts' => $hostname);
                }
            }

            if (is_array($hostname)) {
                $hostname = json_encode($hostname);
            }

            if (count($paths) > 0 && !array_key_exists('paths', $paths)) {
                $paths = array('paths' => $paths);
            }

            if (count($paths) > 0) {
                $paths = json_encode($paths);
            }

            return $this->sendRequest('host_inv_api', (Tools::isJson($hostname) ? '&request=' . $hostname : '&host=' . $hostname) . '&output_format=' . $output_format . (!is_array($paths) ? '&request=' . $paths : ''));
        }
    }
}

?>