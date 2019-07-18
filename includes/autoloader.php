<?php
/**
 * Utility class holds helper methods
 */
namespace ElementorElements {

	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly.
	}

	class Autoloader {


		/**
		 * Classes Mapping
		 *
		 * List of classes mapped via class records of type 'ClassName' => 'path/to/class-file.php'
		 * 
		 * @var array
		 *
		 * @access private
		 * @static
		 */
		private static $classes_map;


		/**
		 * Initializes and retrives $classes_map
		 * 
		 * @access public
		 * @static
		 *
		 * @return array
		 */
		public static function get_classes_map() {

			self::$classes_map = [
				'Utils' => 'includes/utils.php',
			];

			return self::$classes_map;
		}


		/**
		 * Run autoloader.
		 *
		 * Register a function as `__autoload()` implementation.
		 *
		 * @access public
		 * @static
		 */
		public static function run() {
			spl_autoload_register( [ __CLASS__, 'autoload' ] );
		}

		/**
		 * Autoload.
		 *
		 * For a given class, check if it exist and load it.
		 *
		 * @access private
		 * @static
		 *
		 * @param string $class Class name.
		 */
		private static function autoload( $class ) {

			if ( 0 !== strpos( $class, __NAMESPACE__ . '\\' ) ) {
				return;
			}

			$relative_class_name = preg_replace( '/^' . __NAMESPACE__ . '\\\/', '', $class );

			$class_name = __NAMESPACE__ . '\\' . $relative_class_name;

			if ( ! class_exists( $class_name ) ) {

				self::load_class( $relative_class_name );
			}
		}

		/**
		 * Load class.
		 *
		 * For a given class name, require the class file.
		 *
		 * @access private
		 * @static
		 *
		 * @param string $relative_class_name Class name.
		 */
		private static function load_class( $relative_class_name ) {
			$classes_map = self::get_classes_map();

			if ( isset( $classes_map[ $relative_class_name ] ) ) {
				$filename = plugin_dir_path( dirname( __FILE__ ) ) . '/' . $classes_map[ $relative_class_name ];
			} else {
				$filename = strtolower(
					preg_replace(
						[ '/([a-z])([A-Z])/', '/_/', '/\\\/' ],
						[ '$1-$2', '-', DIRECTORY_SEPARATOR ],
						$relative_class_name
					)
				);

				$filename = plugin_dir_path( __FILE__ ) . $filename . '.php';
			}

			if ( is_readable( $filename ) ) {
				require $filename;
			}
		}
	}
}
