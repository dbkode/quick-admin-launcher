<?php
/**
 * Main Wipi class file
 *
 * @package Wipi
 * @subpackage Core
 * @since 1.0.0
 */

namespace WIPI;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wipi class.
 *
 * @since 1.0.0
 */
final class Wipi {

	/**
	 * Plugin initializer
	 *
	 * @since 1.0.0
	 */
	public function init() {
		add_action( 'init', array( $this, 'setup' ) );

		// add one more test
		$a = array('1', '2');

		// test stuff.
		if($a === 2) {
			$a = false;
		}
	}

	/**
	 * Setup plugin.
	 *
	 * @since 1.0.0
	 */
	public function setup() {
		// Enqueue admin scripts.
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );

		// Register rest functions.
		add_action( 'rest_api_init', array( $this, 'register_api_routes' ) );

		// Add wipi modal to admin.
		add_action( 'admin_footer', array( $this, 'modal_html' ) );

		// Add settings page.
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Enqueue admin scripts.
	 *
	 * @since 1.0.0
	 */
	public function admin_scripts() {
		wp_enqueue_script( 'wipi-js', WIPI_PLUGIN_URL . '/dist/wipi.js', array(), WIPI_VERSION, false );

		/**
		 * Filters any additional items to be searchable.
		 *
		 * @since 1.0.0
		 *
		 * @param array $extra_items {
		 *   Array of extra items to be searchable by Wipi. Defaul empty array.
		 *
		 *     @type array $item {
		 *       Searchable item.
		 *
		 *       @type string $type Search result type.
		 *       @type string $label Search result label.
		 *       @type string $term Searchable term for this result.
		 *       @type string $link Search result link.
		 *       @type string $icon Search result icon (dashicon class name, icon path or base64 icon).
		 *     }
		 * }
		 */
		$extra_items = apply_filters( 'wipi_extra_items', array() );

		// Get hotkey from settings.
		$options = get_option( 'wipi_settings' );
		$hotkey  = array(
			'key'   => isset( $options['hotkey_key'] ) ? $options['hotkey_key'] : '',
			'alt'   => isset( $options['hotkey_alt'] ) ? $options['hotkey_alt'] : '',
			'ctrl'  => isset( $options['hotkey_ctrl'] ) ? $options['hotkey_ctrl'] : '',
			'shift' => isset( $options['hotkey_shift'] ) ? $options['hotkey_shift'] : '',
			'meta'  => isset( $options['hotkey_meta'] ) ? $options['hotkey_meta'] : '',
		);

		wp_localize_script(
			'wipi-js',
			'wipiData',
			array(
				'rest'        => esc_url_raw( rest_url( 'wipi/v1' ) ),
				'nonce'       => wp_create_nonce( 'wp_rest' ),
				'extra_items' => $extra_items,
				'hotkey'      => $hotkey,
			)
		);
	}

	/**
	 * Register API Routes for Wipi.
	 *
	 * @since 1.0.0
	 */
	public function register_api_routes() {

		// Search posts route.
		register_rest_route(
			'wipi/v1',
			'/search/(?P<term>\S+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'rest_search' ),
				'permission_callback' => function () {
					return true;
				},
			)
		);

	}

	/**
	 * Add Wipi Modal HTML to footer.
	 *
	 * @since 1.0.0
	 */
	public function modal_html() {
		include WIPI_PLUGIN_DIR . 'templates/wipi-modal.php';
	}

	/**
	 * Rest API route for searching on posts.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data GET data.
	 * @return array Posts found.
	 */
	public function rest_search( $data ) {
		$term    = $data['term'];
		$options = get_option( 'wipi_settings' );
		$results = array();

		// Search on posts.
		$post_types = isset( $options['post_types'] ) ? $options['post_types'] : false;
		if ( $post_types ) {
			$posts      = get_posts(
				array(
					's'         => $term,
					'post_type' => $post_types,
				)
			);

			// Merge all results.
			foreach ( $posts as $post ) {
				// Get post type icon.
				$ptype     = $post->post_type;
				$ptype_obj = get_post_type_object( $ptype );
				$icon      = 'dashicons-admin-post';
				if ( is_string( $ptype_obj->menu_icon ) ) {
					if ( 0 === strpos( $ptype_obj->menu_icon, 'data:image/svg+xml;base64,' ) || 0 === strpos( $ptype_obj->menu_icon, 'dashicons-' ) ) {
						$icon = $ptype_obj->menu_icon;
					} else {
						$icon = esc_url( $ptype_obj->menu_icon );
					}
				}

				$results[] = array(
					'type'  => $post->post_type,
					'icon'  => $icon,
					'label' => $post->post_title,
					'term'  => strtolower( $post->post_title ),
					'link'  => get_edit_post_link( $post->ID, '' ),
				);
			}
		}

		// Search on users.
		$users_search = isset( $options['users_search'] ) ? $options['users_search'] : false;
		if ( $users_search ) {
			$users = get_users(
				array(
					'search' => $term,
				),
			);

			foreach ( $users as $user ) {
				$results[] = array(
					'type'  => 'user',
					'icon'  => 'dashicons-admin-users',
					'label' => $user->display_name,
					'term'  => strtolower( $user->display_name ),
					'link'  => get_edit_user_link( $user->ID ),
				);
			}
		}

		/**
		 * Filters server search results.
		 *
		 * @since 1.0.0
		 *
		 * @param array $results {
		 *   Array of results.
		 *
		 *     @type array $item {
		 *       Searchable item.
		 *
		 *       @type string $type Search result type.
		 *       @type string $label Search result label.
		 *       @type string $term Searchable term for this result.
		 *       @type string $link Search result link.
		 *       @type string $icon Search result icon (dashicon class name, icon path or base64 icon).
		 *     }
		 * }
		 */
		$results = apply_filters( 'wipi_server_search_results', $results, $term );

		return $results;
	}

	/**
	 * Adds a settings page.
	 *
	 * @since 1.0.0
	 */
	public function add_settings_page() {
		add_options_page(
			__( 'Wipi Settings', 'wipi' ),
			__( 'Wipi', 'wipi' ),
			'manage_options',
			'wipi-settings',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Renders the settings page.
	 *
	 * @since 1.0.0
	 */
	public function render_settings_page() {
		?>
		<h2><?php esc_html_e( 'Wipi Settings', 'wipi' ); ?></h2>
		<form action="options.php" method="post">
				<?php
				settings_fields( 'wipi_settings' );
				do_settings_sections( 'wipi_settings' );
				?>
				<input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e( 'Save' ); ?>" />
		</form>
		<?php
	}

	/**
	 * Registers the settings.
	 *
	 * @since 1.0.0
	 */
	public function register_settings() {
		register_setting( 'wipi_settings', 'wipi_settings' );
		add_settings_section( 'wipi_settings_section', '', '__return_true', 'wipi_settings' );

		// Post types setting.
		add_settings_field(
			'wipi_setting_post_types',
			__( 'Post Types', 'wipi' ),
			array( $this, 'render_setting_post_types' ),
			'wipi_settings',
			'wipi_settings_section'
		);

		// Users search setting.
		add_settings_field(
			'wipi_setting_users_search',
			__( 'Enable Users Search', 'wipi' ),
			array( $this, 'render_setting_users_search' ),
			'wipi_settings',
			'wipi_settings_section'
		);

		// Hotkey setting.
		add_settings_field(
			'wipi_setting_hotkey',
			__( 'Hotkey', 'wipi' ),
			array( $this, 'render_setting_hotkey' ),
			'wipi_settings',
			'wipi_settings_section'
		);
	}

	/**
	 * Render setting allowed post types.
	 *
	 * @since 1.0.0
	 */
	public function render_setting_post_types() {
		$options = get_option( 'wipi_settings' );

		$value = array();
		if ( isset( $options['post_types'] ) && ! empty( $options['post_types'] ) ) {
			$value = $options['post_types'];
		}

		// Get list of post types.
		$post_types = get_post_types(
			array(
				'public' => true,
			),
			'objects'
		);
		?>
		<fieldset>
			<?php foreach ( $post_types as $post_type ) : ?>
				<label for="wipi_setting_post_type_<?php echo esc_attr( $post_type->name ); ?>">
					<input type="checkbox"
						id="wipi_setting_post_type_<?php echo esc_attr( $post_type->name ); ?>"
						name="wipi_settings[post_types][]"
						value="<?php echo esc_attr( $post_type->name ); ?>"
						<?php echo in_array( $post_type->name, $value, true ) ? 'checked' : ''; ?> />
						<?php echo esc_html( $post_type->label ); ?>
				</label>
				<br>
			<?php endforeach; ?>
		</fieldset>

		<?php
	}

	/**
	 * Render setting allow users search.
	 *
	 * @since 1.0.0
	 */
	public function render_setting_users_search() {
		$options = get_option( 'wipi_settings' );
		$value   = isset( $options['users_search'] ) ? $options['users_search'] : false;
		?>
		<fieldset>
			<label for="wipi_setting_users_search">
				<input type="checkbox"
					id="wipi_setting_users_search"
					name="wipi_settings[users_search]"
					value="1"
					<?php checked( 1, $value ); ?> />
					<?php esc_html_e( 'This will turn on users searching.', 'wipi' ); ?>
			</label>
		</fieldset>

		<?php
	}

	/**
	 * Render setting for the global hotkey.
	 *
	 * @since 1.0.0
	 */
	public function render_setting_hotkey() {
		$options        = get_option( 'wipi_settings' );
		$hotkey_display = isset( $options['hotkey_display'] ) ? $options['hotkey_display'] : '';
		$hotkey_key     = isset( $options['hotkey_key'] ) ? $options['hotkey_key'] : '';
		$hotkey_alt     = isset( $options['hotkey_alt'] ) ? $options['hotkey_alt'] : '';
		$hotkey_ctrl    = isset( $options['hotkey_ctrl'] ) ? $options['hotkey_ctrl'] : '';
		$hotkey_shift   = isset( $options['hotkey_shift'] ) ? $options['hotkey_shift'] : '';
		$hotkey_meta    = isset( $options['hotkey_meta'] ) ? $options['hotkey_meta'] : '';
		?>
		<fieldset>
			<input type="hidden" id="wipi_setting_hotkey_key" name="wipi_settings[hotkey_key]" value="<?php echo esc_html( $hotkey_key ); ?>">
			<input type="hidden" id="wipi_setting_hotkey_alt" name="wipi_settings[hotkey_alt]" value="<?php echo esc_html( $hotkey_alt ); ?>">
			<input type="hidden" id="wipi_setting_hotkey_ctrl" name="wipi_settings[hotkey_ctrl]" value="<?php echo esc_html( $hotkey_ctrl ); ?>">
			<input type="hidden" id="wipi_setting_hotkey_shift" name="wipi_settings[hotkey_shift]" value="<?php echo esc_html( $hotkey_shift ); ?>">
			<input type="hidden" id="wipi_setting_hotkey_meta" name="wipi_settings[hotkey_meta]" value="<?php echo esc_html( $hotkey_meta ); ?>">
			<label for="wipi_setting_hotkey">
				<input type="text"
					id="wipi_setting_hotkey_display"
					name="wipi_settings[hotkey_display]"
					value="<?php echo esc_html( $hotkey_display ); ?>" >
			</label>
			<br><i><?php esc_html_e( 'Click this input and press a combination of keys to open Wipi search window.', 'wipi' ); ?></i>
		</fieldset>

		<script>
			var wipi_hotkey_input = document.getElementById('wipi_setting_hotkey_display');
			wipi_hotkey_input.onkeypress = function(e) {
				e.preventDefault();
				var value = e.code.replace('Key', '');
				if ( e.altKey ) {
					value = 'ALT + ' + value;
				}
				if ( e.ctrlKey ) {
					value = 'CTRL + ' + value;
				}
				if ( e.shiftKey ) {
					value = 'SHIFT + ' + value;
				}
				if ( e.metaKey ) {
					value = 'SPECIAL + ' + value;
				}
				wipi_hotkey_input.value = value;

				// hidden inputs.
				document.getElementById('wipi_setting_hotkey_key').value = e.key;
				document.getElementById('wipi_setting_hotkey_alt').value = e.altKey ? 1 : '';
				document.getElementById('wipi_setting_hotkey_ctrl').value = e.ctrlKey ? 1 : '';
				document.getElementById('wipi_setting_hotkey_shift').value = e.shiftKey ? 1 : '';
				document.getElementById('wipi_setting_hotkey_meta').value = e.metaKey ? 1 : '';

				return false;
			}
		</script>
		<?php
	}
}
