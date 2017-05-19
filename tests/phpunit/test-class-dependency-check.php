<?php
/**
 * Created by PhpStorm.
 * User: joche
 * Date: 19.05.2017
 * Time: 23:42
 */

namespace i_do_checkmk;

use PHPUnit\Framework\TestCase;

class Dependency_Check_Test extends TestCase {

    private $dependency_check;

    public function setUp() {
        $this->dependency_check = new Dependency_Check();
    }

    public function tearDown() {
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
        $this->assertNull(
            $this->dependency_check->check_all_dependencies()
        );
    }

}