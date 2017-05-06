<?php
/**
 * Welcome to i-do-checkmk.
 *
 * This class contains the interface to Check_MK.
 *
 * You can find the full Documentation of Check_MK JSON Web API here:
 * https://mathias-kettner.de/checkmk_wato_webapi.html
 *
 * Web-API requests are only available for automation users.
 * Please keep in mind that the configured user permissions within WATO, e.g. write permission to a folder, also apply to automation users.
 * You might give the automation user the role Administrator if you want full control.
 */

namespace i_do_checkmk;

if ( ! class_exists( 'Check_MK_API' )) {

    /**
     * Class Check_MK_API
     * @package i_do_checkmk
     */
    class Check_MK_API {

        /**
         * Defines the hostname of Check_MK Server.
         *
         * @var string
         */
        private $_HOSTNAME;

        /**
         * Contains the Check_MK Server Site ID.
         * This is important for distributed Monitoring.
         *
         * @var string
         */
        private $_INSTANCE;

        /**
         * The username of the API User.
         * Please don't use omd user.
         *
         * @var string
         */
        private $_USERNAME;

        /**
         * The automatition secret of the api user.
         * Please do not use normal password.
         *
         * @var string
         */
        private $_PASSWORD;

        /**
         * Set the API URL of the Check_MK Server.
         * Please do not add http://. This is added automatically.
         *
         * Example: myfancyhostname.com
         *
         * @var string
         */
        private $_API_URL;

        /**
         * Check_MK_API constructor.
         * @param $hostname
         * @param $instance
         * @param $username
         * @param $password
         * @param bool $ssl
         */
        public function __construct($hostname, $instance, $username, $password, $ssl = false)
        {
            $this->_HOSTNAME = $hostname;
            $this->_INSTANCE = $instance;
            $this->_USERNAME = $username;
            $this->_PASSWORD = $password;

            $this->_API_URL = $hostname . '/' . $instance . '/check_mk/webapi.py';
            if ($ssl == true) {
                $this->_API_URL = 'https://' . $this->_API_URL;
            } else {
                $this->_API_URL = 'http://' . $this->_API_URL;
            }
        }

        /**
         * Send API Request to Check_MK Server with CURL.
         *
         * @param $action
         * @param string $attributes
         * @param string $post_data
         * @return bool|mixed
         */
        private function send_request($action, $attributes = '', $post_data = '') {
            $response = '';
            $ch = null;

            try {
                $ch = curl_init();

                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_URL, $this->_API_URL . '?action=' . $action . '&_username=' . $this->_USERNAME . '&_secret=' . $this->_PASSWORD . $attributes);
                curl_setopt($ch, CURLOPT_POSTFIELDS, 'request=' . $post_data);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                $response = curl_exec($ch);
            }
            catch (\Exception $exception) {
                error_log($exception->getMessage());
            }
            finally {
                curl_close($ch);
            }

            return $this->validate_response($response);
        }

        /**
         * Checks, if the server answer is valid and contains a valid json response.
         * If not we will return false, so the main class can handle this.
         *
         * @param $response
         * @return bool|mixed
         */
        private function validate_response($response) {
            if ( ! json_decode($response)) {
                error_log('Can not validate Check_MK Server Response. Server Response as Text: ' . $response);
                return false;
            }

            $response = json_decode($response);

            if ($response->result_code == 1) {
                error_log('Got Error from Check_MK. Error Message: ' . $response->result);
                return false;
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
        public function add_host($hostname, $folder, $attributes = '', $nodes = array(), $create_folders = 1) {
            $post_data = array(
                'hostname' => $hostname,
                'folder' => $folder,
                'attributes' => $attributes,
                'nodes' => $nodes
            );
            $post_data = json_encode($post_data);

            return $this->send_request('add_host', '&create_folders=' . $create_folders, $post_data);
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
        public function edit_host($hostname, $attributes = '', $unset_attributes = array(), $nodes = array()) {
            $post_data = array(
                'hostname' => $hostname,
                'attributes' => $attributes,
                'unset_attributes' => $unset_attributes,
                'nodes' => $nodes
            );
            $post_data = json_encode($post_data);

            return $this->send_request('edit_host', $post_data);
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
        public function delete_host($hostname) {
            $post_data = array(
                'hostname' => $hostname
            );
            $post_data = json_encode($post_data);

            return $this->send_request('delete_host', $post_data);
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
        public function get_host($hostname, $effective_attributes = 0) {
            $post_data = array(
                'hostname' => $hostname
            );
            $post_data = json_encode($post_data);

            return $this->send_request('get_host', '&effective_attributes=' . $effective_attributes, $post_data);
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
        public function get_all_host($effective_attributes = 0) {
            return $this->send_request('get_all_hosts', '&effective_attributes=' . $effective_attributes);
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
        public function discover_services($hostname, $mode = 'new') {
            $post_data = array(
                'hostname' => $hostname
            );
            $post_data = json_encode($post_data);

            return $this->send_request('discover_services', '&mode=' . $mode, $post_data);
        }

        /**
         * The bake_agents action triggers the agent baking on the monitoring site.
         * (enterprise edition only)
         *
         * @return bool|mixed
         */
        public function bake_agents() {
            return $this->send_request('bake_agents');
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
        public function activate_changes($sites = null, $mode = 'dirty', $allow_foreign_changes = 0) {
            $post_data = '';

            if ($sites != null) {
                $post_data = array(
                    'sites' => $sites
                );
                $post_data = json_encode($post_data);
            }

            return $this->send_request('activate_changes', '&mode=' . $mode . '&allow_foreign_changes=' . $allow_foreign_changes, $post_data);
        }

    }

}

?>