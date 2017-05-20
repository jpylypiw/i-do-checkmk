<?php
/**
 * Test class for class "CheckMkApi".
 */

namespace I_Do_Checkmk;

use PHPUnit\Framework\TestCase;

class CheckMkApiTest extends TestCase {

    private $api;

    public function setUp() {
        $this->api = new CheckMkApi( '134.255.253.124', 'wdktest', 'automation', 'JFYWBAEWKJIVSILRXETE' );
    }

    public function tearDown() {
        $this->api = null;
    }

    // *********************************
    // __construct
    // *********************************

    public function test_construct()
    {
       $this->assertInstanceOf(
           CheckMkApi::class,
           new CheckMkApi( '134.255.253.124', 'wdktest', 'automation', 'JFYWBAEWKJIVSILRXETE' )
       );
    }

    public function test_construct_error()
    {
        $this->expectException(
            \Exception::class
        );
        new CheckMkApi( '134.255.253.124', 'error', 'error', '12345' );
    }

    // *********************************
    // add_host
    // *********************************

    public function test_add_host() {
        $this->assertEquals(
            0,
            $this->api->addHost( 'testserver', 'test' )->result_code
        );
    }

    public function test_add_host_cluster() {
        $this->assertEquals(
            0,
            $this->api->addHost( 'testserver2', 'test', array('alias' => 'test'), array('root3.webdesign-kronberg.de') )->result_code
        );
    }

    // *********************************
    // edit_host
    // *********************************

    public function test_edit_host() {
        $this->assertEquals(
            0,
            $this->api->editHost( 'testserver' )->result_code
        );
    }

    public function test_edit_host_unset() {
        $this->assertEquals(
            0,
            $this->api->editHost( 'testserver', 'alias' )->result_code
        );
    }

    public function test_edit_host_attribute() {
        $this->assertEquals(
            0,
            $this->api->editHost( 'testserver', array(), array('ipaddress' => '127.0.0.1') )->result_code
        );
    }

    public function test_edit_host_nodes() {
        $this->assertEquals(
            0,
            $this->api->editHost( 'testserver', array(), null, array('root3.webdesign-kronberg.de') )->result_code
        );
    }

    // *********************************
    // delete_host
    // *********************************

    public function test_delete_host_1() {
        $this->assertEquals(
            0,
            $this->api->deleteHost( 'testserver' )->result_code
        );
    }

    public function test_delete_host_2() {
        $this->assertEquals(
            0,
            $this->api->deleteHost( 'testserver2' )->result_code
        );
    }

    // *********************************
    // get_host
    // *********************************

    public function test_get_host() {
        $this->assertEquals(
            0,
            $this->api->getHost( 'root3.webdesign-kronberg.de' )->result_code
        );
    }

    public function test_get_host_effective_attributes() {
        $this->assertEquals(
            0,
            $this->api->getHost( 'root3.webdesign-kronberg.de', 1 )->result_code
        );
    }

    // *********************************
    // get_all_host
    // *********************************

    public function test_get_all_hosts() {
        $this->assertEquals(
            0,
            $this->api->getAllHosts(0)->result_code
        );
    }

    public function test_get_all_hosts_effective_attributes() {
        $this->assertEquals(
            0,
            $this->api->getAllHosts(1)->result_code
        );
    }

    // *********************************
    // discover_services
    // *********************************

    public function test_discover_services() {
        $this->assertEquals(
            0,
            $this->api->discoverServices( 'root3.webdesign-kronberg.de' )->result_code
        );
    }

    public function test_discover_services_mode() {
        $this->assertEquals(
            0,
            $this->api->discoverServices( 'root3.webdesign-kronberg.de', 'refresh' )->result_code
        );
    }

    // *********************************
    // bake_agents
    // *********************************

    public function test_bake_agents() {
        // Expect Exception for check_mk raw installation

        $this->expectException(
            \Exception::class
        );
        $this->api->bakeAgents();
    }

    // *********************************
    // activate_changes
    // *********************************

    public function test_activate_changes() {
        // Generate Change
        $this->api->discoverServices( 'root3.webdesign-kronberg.de' )->result_code;

        $this->assertEquals(
            0,
            $this->api->activateChanges()->result_code
        );
    }

    public function test_activate_changes_special_site() {
        // Generate Change
        $this->api->discoverServices( 'root3.webdesign-kronberg.de' )->result_code;

        $this->assertEquals(
            0,
            $this->api->activateChanges( array('wdktest') )->result_code
        );
    }

    public function test_activate_changes_all() {
        // Generate Change
        $this->api->discoverServices( 'root3.webdesign-kronberg.de' )->result_code;

        $this->assertEquals(
            0,
            $this->api->activateChanges( array('wdktest'), 'all', 1 )->result_code
        );
    }

    // *********************************
    // host_inv_api
    // *********************************

    public function test_host_inv_api() {
        $this->assertEquals(
            0,
            $this->api->hostInvApi('root3.webdesign-kronberg.de')->result_code
        );
    }

    public function test_host_inv_api_multiple_hosts() {
        $this->assertEquals(
            0,
            $this->api->hostInvApi( array('root3.webdesign-kronberg.de', 'xenserver1.webdesign-kronberg.de') )->result_code
        );
    }

    public function test_host_inv_api_xml() {
        $this->assertNotEquals(
            false,
            simplexml_load_string (
                $this->api->hostInvApi( 'root3.webdesign-kronberg.de', 'xml' ),
                'SimpleXmlElement',
                LIBXML_NOERROR+LIBXML_ERR_FATAL+LIBXML_ERR_NONE)

        );
    }

    public function test_host_inv_api_paths() {
        $this->assertEquals(
            0,
            $this->api->hostInvApi( 'root3.webdesign-kronberg.de', 'json', array('.hardware.memory.total_ram_usable') )->result_code
        );
    }

}