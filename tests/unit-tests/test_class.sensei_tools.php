<?php

class Sensei_Tools_Tests extends WP_UnitTestCase {
	/**
	 * Setup function.
	 */
	public function setUp() {
		parent::setUp();

		$this->factory = new Sensei_Factory();
	}

	/**
	 * Basic test for class.
	 */
	public function testClassExists() {
		$this->assertTrue( Sensei_Tools::instance() instanceof Sensei_Tools );
	}
}
