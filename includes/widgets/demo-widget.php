<?php

/* Sample Widget */

namespace ElementorElements\Widgets {

	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly.
	}

	use Elementor\Widget_Common;
	use Elementor\Controls_Manager;
	use Elementor\Controls_Stack;
	use ElementorElements\Utils;

	class Demo_Widget extends Widget_Common {

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

		public function render() {

			$settings = $this->get_settings_for_display();

			$container_key = 'container';

			$this->add_render_attribute( $container_key, [
				'class' => [ 'demo-class' ],
			] );

			$args = [
				'container_atts' => $this->get_render_attribute_string( $container_key ),
			];

			Utils::render_widget_template( 'template', $this, $args );
		}

		public function _content_template() {
			Utils::render_widget_template( 'content-template', $this, [] );
		}
	}
}
