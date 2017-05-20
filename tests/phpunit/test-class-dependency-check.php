<?php
/**
 * Check class for Class "Dependency_Check"
 */

namespace i_do_checkmk;

use PHPUnit\Framework\TestCase;

function function_exists($function) {
    if (Dependency_Check_Test::$exists == true)
        return true;
    return false;
}

class Dependency_Check_Test extends TestCase {

    private $dependency_check;
    public static $exists;

    public function setUp() {
        self::$exists = true;
        $this->dependency_check = new Dependency_Check();
    }

    public function tearDown() {
        self::$exists = null;
        $this->dependency_check = null;
    }

    public function test_construct()
    {
        $this->assertInstanceOf(
            Dependency_Check::class,
            new Dependency_Check()
        );
    }

    public function test_check_all_dependencies()
    {
        $this->assertTrue(
            $this->dependency_check->check_all_dependencies()
        );
    }

    public function test_check_curl()
    {
        self::$exists = false;

        $this->expectException(
            \Exception::class
        );

        $this->dependency_check->check_curl();
    }

    public function test_check_json()
    {
        self::$exists = false;

        $this->expectException(
            \Exception::class
        );

        $this->dependency_check->check_json();
    }

}