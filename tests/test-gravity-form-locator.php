<?php
/**
 * Class Gravity_Form_LocatorTest
 *
 * @package Gravity_Form_Locator
 */

/**
 * Responsible for testing Gravity_Form_Locator.
 */
class Gravity_Form_LocatorTest extends WP_UnitTestCase {

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
     * @dataProvider data_get_shortcode_id
     */
	public function test_get_shortcode_id( $shortcode, $expected ) {
		$result = $this->gravity_form_locator->get_shortcode_id( $shortcode );
		$this->assertEquals( $expected, $result );
	}

	public function data_get_shortcode_id() {
		return array(
			'standard' => [ '[gravityform id="1" title="false" description="false"]', 1 ],
			'minimal' => [ '[gravityform id="1"]', 1 ],
			'missing ID' => [ '[gravityform title="false" description="false"]', false ],
		);
	}
}
