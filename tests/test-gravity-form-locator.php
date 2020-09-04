<?php
/**
 * Class CoreTest
 *
 * @package Gravity_Form_Locator
 */
use PHPUnit\Framework\TestCase;

/**
 * Responsible for testing Core.
 */
class CoreTest extends TestCase {

	/**
	 * The instantiated class.
	 *
	 * @var $core
	 */
	protected $core;

	public function setUp() {
		$this->core = new Core();
    }

	/**
     * @dataProvider data_get_shortcode_ids
     */
	public function test_get_shortcode_ids( $shortcodes, $expected ) {
		$result = $this->core->get_shortcode_ids( $shortcodes );
		$this->assertEquals( $expected, $result );
	}

	public function data_get_shortcode_ids() {
		return array(
			'standard' => array(
				array(
					'[gravityform id="10" title="false" description="false"]'
				),
				array(10)
			),
			'minimal' => array(
				array(
					'[gravityform id="1"]'
				),
				array(1)
			),
			'huge ID' => array(
				array(
					'[gravityform id="1123456789123456789"]'
				),
				array(1123456789123456789)
			),
			'multiple' => array(
				array(
					'[gravityform id="1" title="false" description="false"]',
					'[gravityform id="2" title="false" description="false"]'
				),
				array(1, 2)
			),
			'missing ID' => array(
				array(
					'[gravityform title="false" description="false"]'
				),
				false
			),
		);
	}
}
