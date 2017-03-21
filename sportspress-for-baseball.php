<?php
/*
 * Plugin Name: SportsPress for Baseball
 * Plugin URI: http://themeboy.com/
 * Description: A suite of baseball features for SportsPress.
 * Author: ThemeBoy
 * Author URI: http://themeboy.com/
 * Version: 0.9.2
 *
 * Text Domain: sportspress-for-baseball
 * Domain Path: /languages/
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'SportsPress_Baseball' ) ) :

/**
 * Main SportsPress Baseball Class
 *
 * @class SportsPress_Baseball
 * @version	0.9.2
 */
class SportsPress_Baseball {

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Define constants
		$this->define_constants();

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 30 );
		add_action( 'tgmpa_register', array( $this, 'require_core' ) );

		add_filter( 'gettext', array( $this, 'gettext' ), 20, 3 );
		add_filter( 'sportspress_event_empty_result_string', array( $this, 'event_empty_result_string' ) );
		add_filter( 'sportspress_event_performance_default_squad_number', array( $this, 'event_performance_default_squad_number' ) );
		add_filter( 'sportspress_event_performance_show_numbers', array( $this, 'event_performance_show_numbers' ), 20, 2 );

		// Include required files
		$this->includes();
	}

	/**
	 * Define constants.
	*/
	private function define_constants() {
		if ( !defined( 'SP_BASEBALL_VERSION' ) )
			define( 'SP_BASEBALL_VERSION', '0.9.2' );

		if ( !defined( 'SP_BASEBALL_URL' ) )
			define( 'SP_BASEBALL_URL', plugin_dir_url( __FILE__ ) );

		if ( !defined( 'SP_BASEBALL_DIR' ) )
			define( 'SP_BASEBALL_DIR', plugin_dir_path( __FILE__ ) );
	}

	/**
	 * Enqueue styles.
	 */
	public static function admin_enqueue_scripts() {
		wp_enqueue_style( 'sportspress-baseball-admin', SP_BASEBALL_URL . 'css/admin.css', array( 'sportspress-admin-menu-styles' ), '0.9' );
	}

	/**
	 * Include required files.
	*/
	private function includes() {
		require_once dirname( __FILE__ ) . '/includes/class-tgm-plugin-activation.php';
	}

	/**
	 * Require SportsPress core.
	*/
	public static function require_core() {
		$plugins = array(
			array(
				'name'        => 'SportsPress',
				'slug'        => 'sportspress',
				'required'    => true,
				'is_callable' => array( 'SportsPress', 'instance' ),
			),
		);

		$config = array(
			'default_path' => '',
			'menu'         => 'tgmpa-install-plugins',
			'has_notices'  => true,
			'dismissable'  => true,
			'is_automatic' => true,
			'message'      => '',
			'strings'      => array(
				'nag_type' => 'updated'
			)
		);

		tgmpa( $plugins, $config );
	}

	/** 
	 * Text filter.
	 */
	public function gettext( $translated_text, $untranslated_text, $domain ) {
		if ( $domain == 'sportspress' ) {
			switch ( $untranslated_text ) {
				case 'Events':
					$translated_text = __( 'Games', 'sportspress-for-baseball' );
					break;
				case 'Event':
					$translated_text = __( 'Game', 'sportspress-for-baseball' );
					break;
				case 'Add New Event':
					$translated_text = __( 'Add New Game', 'sportspress-for-baseball' );
					break;
				case 'Edit Event':
					$translated_text = __( 'Edit Game', 'sportspress-for-baseball' );
					break;
				case 'View Event':
					$translated_text = __( 'View Game', 'sportspress-for-baseball' );
					break;
				case 'View all events':
					$translated_text = __( 'View all games', 'sportspress-for-baseball' );
					break;
				case 'Venues':
					$translated_text = __( 'Fields', 'sportspress-for-soccer' );
					break;
				case 'Venue':
					$translated_text = __( 'Field', 'sportspress-for-soccer' );
					break;
				case 'Edit Venue':
					$translated_text = __( 'Edit Field', 'sportspress-for-soccer' );
					break;
				case 'Substitute':
				case 'Substituted':
					$translated_text = __( 'Bench', 'sportspress-for-baseball' );
					break;
				case 'Offense':
					$translated_text = __( 'Batting', 'sportspress-for-baseball' );
					break;
				case 'Defense':
					$translated_text = __( 'Pitching', 'sportspress-for-baseball' );
					break;
				case 'Display squad numbers':
					$translated_text = __( 'Display batting order', 'sportspress-for-baseball' );
					break;
			}
		}
		
		return $translated_text;
	}

	/** 
	 * Empty result string.
	 */
	public function event_empty_result_string() {
		return 'x';
	}

	/** 
	 * Default squad number.
	 */
	public function event_performance_default_squad_number() {
		return '';
	}

	/** 
	 * Hide batting order for pitchers.
	 */
	public function event_performance_show_numbers( $show_numbers, $section = -1 ) {
		return $show_numbers && 1 !== $section;
	}
}

endif;

new SportsPress_Baseball();
