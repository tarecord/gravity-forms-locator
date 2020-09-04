<?php
/**
 * Class Gravity_Form_LocatorTest
 *
 * @package Gravity_Form_Locator
 */
use PHPUnit\Framework\TestCase;

/**
 * Responsible for testing Gravity_Form_Locator.
 */
class Gravity_Form_LocatorTest extends TestCase {

	/**
	 * The instantiated class.
	 *
	 * @var $gravity_form_locator
	 */
	protected $gravity_form_locator;

	public function setUp() {
		$this->gravity_form_locator = new Gravity_Form_Locator();
    }

	/**
     * @dataProvider data_get_shortcode_ids
     */
	public function test_get_shortcode_ids( $shortcodes, $expected ) {
		$result = $this->gravity_form_locator->get_shortcode_ids( $shortcodes );
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
