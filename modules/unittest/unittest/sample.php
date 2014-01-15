<?php defined('SYSPATH') or die('No direct script access.');
class A{
	function __construct($a){
		$this->a = $a;
	}
}

class SampleTest extends UnitTestCase
{
    function __construct()
    {
        parent::__construct();
    }
    function setUp() {}
    function tearDown() {}

    function test_assertBoolean()
    {
        $this->assertTrue(true);
		$this->assertFalse(false);
    }
    function test_assertNullOrNot()
    {
        $this->assertNull(null);
		$this->assertNotNull(1);
    }
	function test_assertClassOrType(){
		$this->assertIsA(array(), "Array");
		$this->assertNotA(array(), "NULL");
	}
	function test_assertEqualOrNot(){
		$this->assertEqual(1,1);
		$this->assertNotEqual(1,2);
	}
	function test_assertMargin(){
		$this->assertWithinMargin(1,2,3);
		$this->assertOutsideMargin(1.1,-2,3);
	}
	function test_assertIdentical(){ //值和类型相同
		$this->assertIdentical(1, 1);
		$this->assertIdentical(array(1,2,3), array(1,2,3));
		$this->assertNotIdentical(array(1,2,3), array(1,2));
	}
	function test_ReferenceOrNot(){ //绑定引用
		$y = array(1,2,3);
		$x = &$y;
		$z = array(1,2);
		$w = "abc";
		$this->assertReference($x, $y); //$x与$y的引用绑定在一起
		$this->assertCopy($x, $z);  //引用不相同
	}
	function test_SameAndClone(){	
		$x = array(1,2,3); 
		$y = array(1,2,3);
		$this->assertSame($x, $y);

		$this->assertClone(new A(1), new A(1)); //同一类型的不同对象，值相同
	}
	function test_PatternOrNot(){
		$p = '/^a+b/';
		$s = 'ab';

		$this->assertPattern($p, $s);
		$this->assertNoPattern($p, "cab");
	}
	function test_ExceptionOrError(){
		$this->expectException("Kohana_Exception");
		$e = new Kohana_Exception("error");
		throw $e;
	}
}
