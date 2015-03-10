<?php
/* Icinga Web 2 | (c) 2013-2015 Icinga Development Team | http://www.gnu.org/licenses/gpl-2.0.txt */

namespace Tests\Icinga\Regression;

use Icinga\Test\BaseTestCase;

/**
 * Class Bug4102
 *
 * Bogus regression test
 *
 * @see https://dev.icinga.org/issues/4102
 */
class Bug4102Test extends BaseTestCase
{
    /**
     * Test class name to match definition
     */
    public function testClassName()
    {
        $class = get_class($this);
        $this->assertContains('Bug4102Test', $class);
    }

    /**
     * Test namespace to match definition
     */
    public function testNamespace()
    {
        $namespace = __NAMESPACE__;
        $this->assertEquals('Tests\Icinga\Regression', $namespace);
    }

    /**
     * Test phpunit inheritance
     */
    public function testInheritance()
    {
        $this->assertInstanceOf('\PHPUnit_Framework_TestCase', $this);
    }
}
