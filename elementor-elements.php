<?php
/**
 * Plugin Name: Elementor Elements
 * Description: Additional Elements for Elementor
 * Author: Tolumbas
 * Version: 0.0.1
 *
 * Text Domain: elementor-elements
 */

/* Plugin namespace */
namespace ElementorElements {

	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly.
	}

	use Widgets;

	class Plugin {


		/**
		 * Singleton instance.
		 * 
		 * @var ElementorElements\Plugin
		 *
		 * @access private
		 * @static
		 */
		private static $_instance;


		/**
		 * Custom fonts list.
		 * 
		 * Holds custom fonts list, 'parsed' with _get_fonts().
		 * 
		 * @var array
		 *
		 * @access private
		 */
		private $_fonts;


		/**
		 * Custom widgets list.
		 * 
		 * Holds custom widgets list, 'parsed' with _get_widgets().
		 * 
		 * @var array
		 *
		 * @access private
		 */
		private $_widgets;


		/**
		 * Common class constructor.
		 */
		public function __construct() {

			$this->register_autoloader();

			\register_activation_hook( __FILE__, [$this, 'activation_hook'] );
			\register_deactivation_hook( __FILE__, [$this, 'deactivation_hook'] );

			\add_action( 'plugins_loaded', [$this, 'load_textdomain'] );
			\add_action( 'elementor/init', [$this, 'init'] );
		}


		/**
		 * Register autoloader.
		 *
		 * ElementorElements autoloader loads all the classes needed to run the plugin.
		 *
		 * @access private
		 */
		private function register_autoloader() {
			require \plugin_dir_path( __FILE__ ) . 'includes/autoloader.php';

			Autoloader::run();
		}


		/**
		 * Plugin activation hook.
		 * 
		 * @return null
		 *
		 * @access public
		 */
		public function activation_hook() {}


		/**
		 * Plugin deactivation hook.
		 * 
		 * @return null
		 *
		 * @access public
		 */
		public function deactivation_hook() {}


		/**
		 * Action callback.
		 * 
		 * Hooked to Wordpress 'plugins_loaded' action, with priority 10.
		 * 
		 * Loads plugin text domain.
		 * 
		 * @return null
		 *
		 * @access public
		 */
		public function load_textdomain() {
			\load_plugin_textdomain( 'elementor-elements', false, dirname( \plugin_basename( __FILE__ ) ) . '/languages' );
		}


		/**
		 * Action callback.
		 * 
		 * Hooked to 'elementor/init' action, with priority 10.
		 * 
		 * Registers callbacks to various Elementor hooks.
		 * 
		 * @return null
		 *
		 * @access public
		 */
		public function init () {

			/* Editor controls modifications */
			\add_action( 'elementor/controls/controls_registered',    [ $this, 'register_custom_fonts' ] );

			/* Editor widgets categories registration */
			\add_action( 'elementor/elements/categories_registered',  [ $this, 'register_categories' ] );

			/* Editor widgets registration */
			\add_action( 'elementor/widgets/widgets_registered',      [ $this, 'register_widgets' ] );

			/* Enqeue scripts */
			\add_action( 'elementor/frontend/before_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

			/* Register additional icons */
			\add_filter( 'elementor/icons_manager/additional_tabs', [ $this, 'register_additional_icons' ] );

			/* Enqeue styles */
			\add_action( 'elementor/editor/after_enqueue_styles',   [ $this, 'enqueue_styles' ] );
			\add_action( 'elementor/frontend/after_enqueue_styles', [ $this, 'enqueue_styles' ] );
		}


		/**
		 * Action callback.
		 * 
		 * Hooked to 'elementor/controls/controls_registered' action, with priority 10.
		 * 
		 * Registers custom fonts stored in 'assets/fonts'.
		 *
		 * @param Elementor\Controls_Manager $manager The controls manager.
		 * 
		 * @access public
		 * 
		 * @return null
		 */
		public function register_custom_fonts( $manager ) {

			// First we get the fonts setting of the font control
			$fonts = $manager->get_control( 'font' )->get_settings( 'options' );

			// Gather fonts from 'assets/fonts' directory
			$custom_fonts = $this->_get_fonts();

			// Setup custom fonts list
			$custom_fonts = array_combine( array_keys( $custom_fonts ), array_fill( 0, count( $custom_fonts ), 'system' ) );

			// Then we append the custom font family in the list of the fonts we retrieved in the previous step
			$new_fonts = array_merge(
				$custom_fonts,
				$fonts
			);

			// Then we set a new list of fonts as the fonts setting of the font control
			$manager->get_control( 'font' )->set_settings( 'options', $new_fonts );
		}


		/**
		 * Filter callback
		 * 
		 * Hooked to 'elementor/icons_manager/additional_tabs' action, with priority 10.
		 * 
		 * @param  array  $tabs List of settings for additional Icons Manager tabs
		 * @return array $tabs  Filtered tabs settings list
		 */
		public function register_additional_icons( $tabs=[] ) {

			$tabs['fontello'] = [
				'name' => 'fontello',

				/* Icons Manager Label */
				'label' => __( 'Fontello Icons', 'elementor-elements' ),
				
				/* Icons list */
				'url' => Utils::get_url( 'assets/icons/fontello/icons.css' ),

				/* Font Face & basic styles */
				'enqueue' => [ Utils::get_url( 'assets/icons/fontello/font.css' ) ],

				/* Icon prefix */
				'prefix' => 'fontello-icon-',

				/* Icon-font class prefix */
				'displayPrefix' => 'fontello',

				/**
				 * Selector structure
				 * <element class="{Class prefix} {Icon prefix}{Icon name}">
				 */

				/* Icons Manager Icon */
				'labelIcon' => 'eicon-favorite',

				/* Version */
				'ver' => null,

				/* List of Icon names */
				'fetchJson' => Utils::get_url( 'assets/icons/fontello/icons.json' ),

				'native' => false,
			];

			return $tabs;
		}


		/**
		 * Generates custom fonts list stored in 'assets/fonts'.
		 * 
		 * @access private
		 * 
		 * @return array custom fonts list.
		 */
		protected function _get_fonts() {

			if( null !== $this->_fonts ) {
				return $this->_fonts;
			}

			$this->_fonts = [];

			$fonts_dirs = Utils::get_file( 'assets/fonts/' );
			$fonts_dirs = \apply_filters( 'elementor-elements/fonts_dirs', $fonts_dirs );

			if ( ! is_array( $fonts_dirs ) ) {
				$fonts_dirs = [ $fonts_dirs ];
			}

			$fonts_dirs = array_filter( $fonts_dirs, function( $value ) {
				return is_string( $value ) && is_dir( $value );
			} );

			$fonts_dirs = array_map( 'untrailingslashit', $fonts_dirs );

			if( empty( $fonts_dirs ) ){
				return $this->_fonts;
			}

			try {

				$dirs = [];

				foreach ( $fonts_dirs as $fonts_dir ) {
					$dirs = array_merge( glob( $fonts_dir .'/*', GLOB_ONLYDIR|GLOB_NOESCAPE ), $dirs );
				}


				if( empty( $dirs ) ) {
					return $this->_fonts;
				}

				foreach ( $dirs as $dir ) {

					if ( file_exists( $dir . '/stylesheet.css' ) ) {

						$blog_url = \untrailingslashit( get_bloginfo( 'url' ) );
						$url = \wp_normalize_path( str_ireplace( \untrailingslashit( ABSPATH ), $blog_url, $dir ) );

						if( 0 !== strpos( $url, $blog_url ) ){
							continue;
						}

						$this->_fonts[basename( $dir )] = $url . '/stylesheet.css';
					}
				}
			} catch( Exception $e ) {

				error_log( $e->getMessage() );
			}

			$this->_fonts = \apply_filters( 'elementor-elements/fonts_list', $this->_fonts );

			return $this->_fonts;
		}


		/**
		 * Action callback.
		 * 
		 * Hooked to 'elementor/elements/categories_registered' action, with priority 10.
		 * 
		 * Registers custom categories of widgets.
		 *
		 * @param Elementor\Elements_Manager $manager Elements manager instance.
		 * 
		 * @access public
		 * 
		 * @return null
		 */
		public function register_categories( $manager ) {

			$manager->add_category( 'elementor-elements', [
				'title' => __( 'Elementor Elements', 'elementor-elements' ),
				'icon'  => 'fa fa-plug',
			] );
		}


		/**
		 * Action callback.
		 * 
		 * Hooked to 'elementor/frontend/before_enqueue_scripts' action, with priority 10.
		 * 
		 * Registers custom scripts and styles needed.
		 *
		 * @param Elementor\Widgets_Manager $manager The widgets manager.
		 * 
		 * @access public
		 * 
		 * @return null
		 */
		public function register_widgets( $manager ) {

			$widgets = $this->_get_widgets();

			try {

				foreach ( $widgets as $widget ) {

					$widget_class = __NAMESPACE__ . '\\widgets\\' . $widget;

					$manager->register_widget_type( new $widget_class() );
				}
			} catch ( Exception $e ) {

				error_log( $e->getMessage() );
			}
		}


		/**
		 * Generates custom widgets list stored in 'includes/widgets'.
		 * 
		 * @access private
		 * 
		 * @return array custom widgets list.
		 */
		protected function _get_widgets() {

			if( null !== $this->_widgets && is_array( $this->_widgets ) ) {
				return $this->_widgets;
			}

			$default_widgets_list = [
				'Demo_Widget',
			];

			$this->_widgets = \apply_filters( 'elementor-elements/widgets_list', $default_widgets_list );

			return $this->_widgets;
		}


		/**
		 * Action callback.
		 * 
		 * Hooked to 'elementor/frontend/before_enqueue_scripts' action, with priority 10.
		 * 
		 * Registers and queues custom scripts needed.
		 * 
		 * @access public
		 * 
		 * @return null
		 */
		public function enqueue_scripts() {

			/* Frontend script */
			\wp_enqueue_script(
				'elementor-elements-frontend',
				Utils::get_url( 'assets/js/frontend.js' ),
				[ 'jquery', 'elementor-frontend' ],
				null,
				false
			);
		}


		/**
		 * Action callback.
		 * 
		 * Hooked to 'elementor/editor/after_enqueue_styles' action, with priority 10.
		 * Hooked to 'elementor/frontend/after_enqueue_styles' action, with priority 10.
		 * 
		 * Registers and queues custom scripts needed.
		 * 
		 * @access public
		 * 
		 * @return null
		 */
		public function enqueue_styles() {

			$this->_enqueue_fonts();
		}

		/**
		 * Enqueue find and enqueue custom fonts
		 * 
		 * @return null
		 */
		private function _enqueue_fonts(){

			$fonts = $this->_get_fonts();

			if( empty( $fonts ) ) {
				return;
			}

			foreach( $fonts as $name => $url ) {

				\wp_enqueue_style(
					strtolower( $name ) . '-font',
					$url,
					[],
					null
				);
			}
		}


		/**
		 * Retrives or creates singleton instance.
		 *
		 * @access public
		 * @static
		 * 
		 * @return ElementorElements\Plugin
		 */
		public static function instance() {

			if( null === self::$_instance ){
				self::$_instance = new self();
			}

			return self::$_instance;
		}
	}
}

/* Global namespace */
namespace {

	/* Allows override */
	if ( ! function_exists( 'elementor_elements' ) ) {

		/**
		 *	Utility function to accses 'ElementorElements\Plugin' instance
		 * 
		 * @return ElementorElements\Plugin instance
		 */
		function elementor_elements() {

			return ElementorElements\Plugin::instance();
		}
	}

	/* Initialize singleton */
	elementor_elements();
}
