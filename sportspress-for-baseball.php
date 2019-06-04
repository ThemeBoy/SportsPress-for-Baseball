<?php
/*
 * Plugin Name: SportsPress for Baseball
 * Plugin URI: http://themeboy.com/
 * Description: A suite of baseball features for SportsPress.
 * Author: ThemeBoy
 * Author URI: http://themeboy.com/
 * Version: 1.0.2
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
 * @version	1.0.2
 */
class SportsPress_Baseball {

	/** @var array Performance variables to use partial innings. */
	public $partial_inning_keys;

	/**
	 * Constructor.
	 */
	public function __construct() {
		register_activation_hook( __FILE__, array( $this, 'install' ) );

		// Define constants
		$this->define_constants();

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 30 );
		add_action( 'tgmpa_register', array( $this, 'require_core' ) );

		add_filter( 'gettext', array( $this, 'gettext' ), 20, 3 );
		add_filter( 'sportspress_event_empty_result_string', array( $this, 'event_empty_result_string' ) );
		add_filter( 'sportspress_event_performance_default_squad_number', array( $this, 'event_performance_default_squad_number' ) );
		add_filter( 'sportspress_event_performance_show_numbers', array( $this, 'event_performance_show_numbers' ), 20, 2 );

		// Define default sport
		add_filter( 'sportspress_default_sport', array( $this, 'default_sport' ) );

		// Partial innings
		add_action( 'sportspress_meta_box_performance_details', array( $this, 'partial_innings_meta_box_setting' ) );
		add_action( 'sportspress_process_sp_performance_meta', array( $this, 'save_partial_innings_meta_box_setting' ) );
		add_action( 'sportspress_before_event_performance', array( $this, 'get_partial_inning_keys' ) );
		add_filter( 'sportspress_event_performance_add_value', array( $this, 'convert_partial_innings_notation_to_real_value' ), 10, 2 );
		add_filter( 'sportspress_event_performance_table_total_value', array( $this, 'prepare_event_performance_table_total_for_display' ), 10, 3 );
		add_action( 'sportspress_before_player_statistics_loop', array( $this, 'get_partial_inning_keys' ) );
		add_filter( 'sportspress_player_performance_add_value', array( $this, 'convert_partial_innings_notation_to_real_value' ), 10, 2 );
		add_filter( 'sportspress_player_performance_table_placeholder', array( $this, 'convert_real_value_to_partial_innings_notation' ), 10, 3 );
		add_filter( 'sportspress_player_performance_table_placeholders', array( $this, 'convert_real_values_to_partial_innings_notation' ) );

		// Include required files
		$this->includes();
	}

	/**
	 * Install.
	*/
	public static function install() {
		$post = get_page_by_path( 'pitcher_ip', OBJECT, 'sp_performance' );
		if ( ! $post ) return;

		update_post_meta( $post->ID, 'sp_partial_innings', 1 );
	}

	/**
	 * Define constants.
	*/
	private function define_constants() {
		if ( !defined( 'SP_BASEBALL_VERSION' ) )
			define( 'SP_BASEBALL_VERSION', '1.0.2' );

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
				'version'     => '2.6.17',
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

	/**
	 * Define default sport.
	*/
	public function default_sport() {
		return 'baseball';
	}

	/**
	 * Add partial innings to details meta box.
	*/
	public function partial_innings_meta_box_setting( $post ) {
		$partial_innings = get_post_meta( $post->ID, 'sp_partial_innings', true );
		?>
		<p>
			<strong><?php _e( 'Partial Innings', 'sportspress' ); ?></strong>
			<i class="dashicons dashicons-editor-help sp-desc-tip" title="<?php _e( 'Convert decimals (.1 and .2) to partial innings (1/3 and 2/3)', 'sportspress' ); ?>"></i>
		</p>

		<input name="sp_partial_innings" id="sp_partial_innings" value="0" type="hidden">

		<label>
			<input name="sp_partial_innings" id="sp_partial_innings" value="1" type="checkbox" <?php checked( $partial_innings ); ?>>
			<?php _e( 'Decimal notation for baseball', 'sportspress' ); ?>
		</label>
		<?php
	}

	/**
	 * Save partial innings meta box setting.
	*/
	public function save_partial_innings_meta_box_setting( $post_id ) {
		update_post_meta( $post_id, 'sp_partial_innings', sp_array_value( $_POST, 'sp_partial_innings', 0 ) );
	}

	/**
	 * Get performance keys to use partial innings.
	*/
	public function get_partial_inning_keys( $columns = null ) {
		if ( $columns == null ) {
			// Get performance columns
			$args = array(
				'post_type' => 'sp_performance',
				'numberposts' => 100,
				'posts_per_page' => 100,
				'orderby' => 'menu_order',
				'order' => 'ASC',
			);

			$columns = get_posts( $args );
		}

		// Initialize partial inning keys reference array.
		$this->partial_inning_keys = array();

		foreach ( $columns as $column ) {
			$partial_innings = get_post_meta( $column->ID, 'sp_partial_innings', true );
			if ( $partial_innings ) {
				$this->partial_inning_keys[] = $column->post_name;
			}
		}
	}

	/**
	 * Convert partial innings notation to real value.
	*/
	public function convert_partial_innings_notation_to_real_value( $value, $key ) {
		if ( ! is_array( $this->partial_inning_keys ) ) {
			$this->get_partial_inning_keys();
		}

		if ( in_array( $key, $this->partial_inning_keys ) ) {
			list( $whole, $decimal ) = sscanf( $value, '%d.%d' );
			if ( in_array( $decimal, array( 1, 2 ) ) ) {
				$value = (int) $whole + (int) $decimal / 3;
			}
		}

		return $value;
	}

	/**
	 * Convert real value to partial innings notation.
	*/
	public function convert_real_value_to_partial_innings_notation( $value, $key ) {
		if ( ! is_array( $this->partial_inning_keys ) ) {
			$this->get_partial_inning_keys();
		}

		if ( in_array( $key, $this->partial_inning_keys ) ) {
			$value = floatval( $value );
			$value = floor( $value ) + round( ( $value - floor( $value ) ) * 3 ) / 10;
		}

		return $value;
	}

	/**
	 * Convert real values to partial innings notation.
	*/
	public function convert_real_values_to_partial_innings_notation( $values = array() ) {
		foreach ( $values as $key => $value ) {
			$values[ $key ] = $this->convert_real_value_to_partial_innings_notation( $value, $key );
		}

		return $values;
	}

	/**
	 * Convert event performance table total value for display using partial innings notation.
	*/
	public function prepare_event_performance_table_total_for_display( $value, $data, $key ) {
		return $this->convert_real_value_to_partial_innings_notation( $value, $key );
	}
}

endif;

new SportsPress_Baseball();
