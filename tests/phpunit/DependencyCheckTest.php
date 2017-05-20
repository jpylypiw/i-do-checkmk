<?php
/**
 * Check class for Class "DependencyCheck"
 */

namespace I_Do_Checkmk;

use PHPUnit\Framework\TestCase;

function function_exists($function) {
    if (DependencyCheckTest::$exists == true)
        return true;
    return false;
}

class DependencyCheckTest extends TestCase {

    private $dependency_check;
    public static $exists;

    public function setUp() {
        self::$exists = true;
        $this->dependency_check = new DependencyCheck();
    }

    public function tearDown() {
        self::$exists = null;
        $this->dependency_check = null;
    }

    public function test_construct()
    {
        $this->assertInstanceOf(
            DependencyCheck::class,
            new DependencyCheck()
        );
    }

    public function test_check_all_dependencies()
    {
        $this->assertTrue(
            $this->dependency_check->checkAllDependencies()
        );
    }

    public function test_check_curl()
    {
        self::$exists = false;

        $this->expectException(
            \Exception::class
        );

        $this->dependency_check->checkCurl();
    }

    public function test_check_json()
    {
        self::$exists = false;

        $this->expectException(
            \Exception::class
        );

        $this->dependency_check->checkJson();
    }
}