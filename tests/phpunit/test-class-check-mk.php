<?php
/**
 * Test class for class "Check_MK_API".
 */

namespace i_do_checkmk;

use PHPUnit\Framework\TestCase;

class Check_MK_API_Test extends TestCase {

    private $api;

    public function setUp() {
        $this->api = new Check_MK_API( '134.255.253.124', 'wdktest', 'automation', 'JFYWBAEWKJIVSILRXETE' );
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
           Check_MK_API::class,
           new Check_MK_API( '134.255.253.124', 'wdktest', 'automation', 'JFYWBAEWKJIVSILRXETE' )
       );
    }

    public function test_construct_error()
    {
        $this->expectException(
            \Exception::class
        );
        new Check_MK_API( '134.255.253.124', 'error', 'error', '12345' );
    }

    // *********************************
    // add_host
    // *********************************

    public function test_add_host() {
        $this->assertEquals(
            0,
            $this->api->add_host( 'testserver', 'test' )->result_code
        );
    }

    public function test_add_host_cluster() {
        $this->assertEquals(
            0,
            $this->api->add_host( 'testserver2', 'test', array('alias' => 'test'), array('root3.webdesign-kronberg.de') )->result_code
        );
    }

    // *********************************
    // edit_host
    // *********************************

    public function test_edit_host() {
        $this->assertEquals(
            0,
            $this->api->edit_host( 'testserver' )->result_code
        );
    }

    public function test_edit_host_unset() {
        $this->assertEquals(
            0,
            $this->api->edit_host( 'testserver', 'alias' )->result_code
        );
    }

    public function test_edit_host_attribute() {
        $this->assertEquals(
            0,
            $this->api->edit_host( 'testserver', array(), array('ipaddress' => '127.0.0.1') )->result_code
        );
    }

    public function test_edit_host_nodes() {
        $this->assertEquals(
            0,
            $this->api->edit_host( 'testserver', array(), null, array('root3.webdesign-kronberg.de') )->result_code
        );
    }

    // *********************************
    // delete_host
    // *********************************

    public function test_delete_host_1() {
        $this->assertEquals(
            0,
            $this->api->delete_host( 'testserver' )->result_code
        );
    }

    public function test_delete_host_2() {
        $this->assertEquals(
            0,
            $this->api->delete_host( 'testserver2' )->result_code
        );
    }

    // *********************************
    // get_host
    // *********************************

    public function test_get_host() {
        $this->assertEquals(
            0,
            $this->api->get_host( 'root3.webdesign-kronberg.de' )->result_code
        );
    }

    public function test_get_host_effective_attributes() {
        $this->assertEquals(
            0,
            $this->api->get_host( 'root3.webdesign-kronberg.de', 1 )->result_code
        );
    }

    // *********************************
    // get_all_host
    // *********************************

    public function test_get_all_hosts() {
        $this->assertEquals(
            0,
            $this->api->get_all_host(0)->result_code
        );
    }

    public function test_get_all_hosts_effective_attributes() {
        $this->assertEquals(
            0,
            $this->api->get_all_host(1)->result_code
        );
    }

    // *********************************
    // discover_services
    // *********************************

    public function test_discover_services() {
        $this->assertEquals(
            0,
            $this->api->discover_services( 'root3.webdesign-kronberg.de' )->result_code
        );
    }

    public function test_discover_services_mode() {
        $this->assertEquals(
            0,
            $this->api->discover_services( 'root3.webdesign-kronberg.de', 'refresh' )->result_code
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
        $this->api->bake_agents();
    }

    // *********************************
    // activate_changes
    // *********************************

    public function test_activate_changes() {
        // Generate Change
        $this->api->discover_services( 'root3.webdesign-kronberg.de' )->result_code;

        $this->assertEquals(
            0,
            $this->api->activate_changes()->result_code
        );
    }

    public function test_activate_changes_special_site() {
        // Generate Change
        $this->api->discover_services( 'root3.webdesign-kronberg.de' )->result_code;

        $this->assertEquals(
            0,
            $this->api->activate_changes( array('wdktest') )->result_code
        );
    }

    public function test_activate_changes_all() {
        // Generate Change
        $this->api->discover_services( 'root3.webdesign-kronberg.de' )->result_code;

        $this->assertEquals(
            0,
            $this->api->activate_changes( array('wdktest'), 'all', 1 )->result_code
        );
    }

    // *********************************
    // host_inv_api
    // *********************************

    public function test_host_inv_api() {
        $this->assertEquals(
            0,
            $this->api->host_inv_api('root3.webdesign-kronberg.de')->result_code
        );
    }

    public function test_host_inv_api_multiple_hosts() {
        $this->assertEquals(
            0,
            $this->api->host_inv_api( array('root3.webdesign-kronberg.de', 'xenserver1.webdesign-kronberg.de') )->result_code
        );
    }

    public function test_host_inv_api_xml() {
        $this->assertNotEquals(
            false,
            simplexml_load_string (
                $this->api->host_inv_api( 'root3.webdesign-kronberg.de', 'xml' ),
                'SimpleXmlElement',
                LIBXML_NOERROR+LIBXML_ERR_FATAL+LIBXML_ERR_NONE)

        );
    }

    public function test_host_inv_api_paths() {
        $this->assertEquals(
            0,
            $this->api->host_inv_api( 'root3.webdesign-kronberg.de', 'json', array('.hardware.memory.total_ram_usable') )->result_code
        );
    }

}