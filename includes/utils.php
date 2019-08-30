<?php
/**
 * Utility class holds helper methods
 */
namespace ElementorElements {

	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly.
	}

	class Utils {

		/**
		 *	Retrives absolute path relative relative to plugin base dir.
		 * 
		 * @param  string $file - file/directory name, relative to plugin base dir. Default is empty string
		 * 
		 * @return string absolute path to file/directory
		 *
		 * @access public
		 * @static
		 */
		public static function get_file( $file = '' ) {
			return plugin_dir_path( dirname( __FILE__ ) ) . $file;
		}


		/**
		 * Retrives URL for given file/directory name.
		 * 
		 * @param  string $file - file/directory name, relative to plugin base URL. Default is empty string
		 * 
		 * @return string URL of file/directory
		 *
		 * @access public
		 * @static
		 */
		public static function get_url( $file = '' ) {
			return plugin_dir_url( dirname( __FILE__ ) ) . $file;
		}


		/**
		 * Searches $template file in theme and plugin templates
		 * and includes it using $widget context.
		 * Extracts $template_args into that context.
		 * 
		 * @param  string                $template      Template file
		 * @param  Elementor\Widget_Base $widget        Widget instance
		 * @param  array                 $template_args Array of values
		 * 
		 * @return boolean               true if $template inclusion succeeded
		 *
		 * @access public
		 * @static
		 */
		public static function render_widget_template( $template='', $widget=null, $template_args=[] ) {

			if( ! is_string( $template )
				|| empty( $template )
				|| ! $widget
				|| ! is_subclass_of( $widget, 'Elementor\\Widget_Base' )
				|| ! is_array( $template_args ) ){

				return false;
			}

			$template_located = false;

			$widget_name = $widget->get_name();

			$template_names = [
				\trailingslashit( implode( DIRECTORY_SEPARATOR, [ 'elementor-elements', 'templates', 'widgets', $widget_name ] ) ) . $template . '.php',
				\trailingslashit( implode( DIRECTORY_SEPARATOR, [ 'elementor-elements', 'widgets', $widget_name ] ) ) . $template . '.php',
				\trailingslashit( implode( DIRECTORY_SEPARATOR, [ 'templates', 'elementor-elements', 'widgets', $widget_name ] ) ) . $template . '.php',
			];

			if( $located = \locate_template( $template_names ) ) {

				$template_located = $located;
			} elseif( $located = self::locate_widget_template( $template, $widget ) ) {

				$template_located = $located;
			} else {

				error_log( sprintf( __( "Unable to locate template '%s' for wiget '%s'", 'elementor-elements' ), $template, $widget_name ) );
				return false;
			}

			$include_args = \apply_filters( 'elementor-elements/include_widget_template_args', [
				'template_located' => $template_located,
				'template_args'    => $template_args,
				'widget'           => $widget,
			] );

			wp_parse_args( $include_args, [
				'template_located' => '',
				'template_args'    => [],
				'widget'           => null,
			] );

			return self::include_widget_template( $include_args['template_located'], $include_args['widget'], $include_args['template_args'] );
		}


		/**
		 * Searches $template file in plugin templates directory
		 * 
		 * @param  string                $template      Template file name
		 * @param  Elementor\Widget_Base $widget        Widget instance
		 * 
		 * @return mixed               absolute path to $template file or false
		 *
		 * @access public
		 * @static
		 */
		public static function locate_widget_template( $template='', $widget=null ) {

			if ( ! is_string( $template )
				|| empty( $template )
				|| ! $widget
				|| ! is_subclass_of( $widget, 'Elementor\\Widget_Base' ) ) {

				return false;
			}

			$tempates_dir = self::get_file( 'templates' );
			$widget_name = $widget->get_name();
			$template = implode( DIRECTORY_SEPARATOR, [ $tempates_dir, $widget_name, $template ] ) . '.php';

			if( is_readable( $template ) ) {

				return $template;
			}

			return false;
		}

		/**
		 * Includes $template file using $widget context
		 * and extracts $template_args into that context
		 * 
		 * @param  string                $template      Template file
		 * @param  Elementor\Widget_Base $widget        Widget instance
		 * @param  array                 $template_args Array of values
		 * 
		 * @return boolean               true if $template inclusion succeeded
		 *
		 * @access public
		 * @static
		 */
		public static function include_widget_template( $template='', $widget=null, $template_args=[] ) {

			if ( ! is_string( $template )
				|| ! is_readable( $template )
				|| ! $widget
				|| ! is_subclass_of( $widget, 'Elementor\\Widget_Base' ) ) {

				return false;
			}

			$closure = TemplateClosure::instance()->closure( $template, $template_args );

			$closure = $closure->bindTo( $widget, $widget );

			$closure( $template );

			return true;
		}
	}

	class TemplateClosure {

		/**
		 * Closure Holder
		 * @var $instance
		 */
		private static $instance = null;

		/**
		 * Generates PHP Closure instance
		 * @param  string $template_file [description]
		 * @param  array  $template_args [description]
		 * @return Closure               [description]
		 */
		public final function closure( $template_file, $template_args ) {
			return function ( $template_file ) use ( $template_args ) {

				extract( $template_args );

				ob_start();
				include $template_file;
				ob_end_flush();
			};
		}

		/**
		 * Singleton getter
		 * @return TemplateClosure class instance
		 */
		public static function instance() {

			if( null === self::$instance ){
				self::$instance = new self();
			}

			return self::$instance;
		}
	}
}
