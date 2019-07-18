<?php

/* Sample Widget */

namespace ElementorElements\Widgets {

	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly.
	}

	use Elementor\Widget_Common;
	use ElementorElements\Utils;

	class Demo_Widget extends Widget_Common {

		public function __construct( $data = [], $args = null ) {
			parent::__construct( $data, $args );

			$this->add_actions();
		}

		public function get_name() {
			return 'demo-widget';
		}

		public function get_title() {
			return __( 'Demo Widget', 'elementor-elements' );
		}

		public function get_icon() {
			return 'eicon-section';
		}

		public function show_in_panel() {
			return true;
		}

		public function get_categories() {
			return [ 'elementor-elements' ];
		}

		public function register_controls( $element ) {

			$element->add_control(
				'settings_json',
				[
					'type' => \Elementor\Controls_Manager::HIDDEN,
					'value' => $this->get_settings_json(),
				]
			);
		}

		protected function render() {

			$settings = $this->get_settings_for_display();

			$container_key = 'container';

			$this->add_render_attribute( $container_key, [
				'class' => [ 'demo-class' ],
				'data-settings' => $settings['settings_json'],
			] );

			$args = [
				'container_atts' => $this->get_render_attribute_string( $container_key ),
			];

			Utils::render_widget_template( 'template', $this, $args );
		}

		/*protected function _content_template() {
			Utils::render_widget_template( 'content-template', $this, [] );
		}*/

		protected function get_settings_json( $settings = null ){

			$data = [
				'demo' => 'settings',
			];

			$settings_json = wp_json_encode( $data );

			return $settings_json;
		}

		private function add_actions() {
			add_action( 'elementor/element/demo-widget/_section_style/before_section_end',   [ $this, 'register_controls' ] );
		}
	}
}
