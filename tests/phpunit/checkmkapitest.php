<?php
/**
 * Created by PhpStorm.
 * User: joche
 * Date: 09.05.2017
 * Time: 21:00
 */

namespace i_do_checkmk;

use PHPUnit\Framework\TestCase;

class CheckMKAPITest extends TestCase {

    private $api;

    public function setUp() {
        $this->api = new Check_MK_API( '134.255.253.124', 'wdktest', 'automation', 'JFYWBAEWKJIVSILRXETE' );
    }

    public function tearDown() {
        $this->a = null;
    }

    public function test_construct()
    {
       $this->assertInstanceOf(
           Check_MK_API::class,
           new Check_MK_API( '134.255.253.124', 'wdktest', 'automation', 'JFYWBAEWKJIVSILRXETE' )
       );
    }

    public function test_get_all_hosts() {
        $this->assertEquals(
          0,
          $this->api->get_all_host(0)->result_code
        );
    }

    public function test_host_inv_api() {
        $this->assertEquals(
            0,
            $this->api->host_inv_api('root3.webdesign-kronberg.de')->result_code
        );
    }

    public function test_bake_agents() {
        $this->assertEquals(
            false,
            $this->api->bake_agents()
        );
    }

    public function test_discover_services() {
        $this->assertEquals(
            0,
            $this->api->discover_services( 'root3.webdesign-kronberg.de' )->result_code
        );
    }

    public function test_get_host() {
        $this->assertEquals(
            0,
            $this->api->get_host( 'root3.webdesign-kronberg.de' )->result_code
        );
    }

//    public function test_add_host() {
//        $this->assertEquals(
//            0,
//            $this->api->add_host( 'testserver', 'test' )->result_code
//        );
//    }

    public function test_activate_changes() {
        $this->assertEquals(
            false,
            $this->api->activate_changes('root3.webdesign-kronberg.de')
        );
    }

}