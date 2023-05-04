<?php
/**
 * Main Wpal class file
 *
 * @package Wpal
 * @subpackage Core
 * @since 1.0.0
 */

namespace WPAL;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wpal class.
 *
 * @since 1.0.0
 */
final class Wpal {

	/**
	 * Plugin initializer
	 *
	 * @since 1.0.0
	 */
	public function init() {
		add_action( 'init', array( $this, 'setup' ) );
	}

	/**
	 * Setup plugin.
	 *
	 * @since 1.0.0
	 */
	public function setup() {
		// Enqueue admin scripts.
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		add_filter( 'script_loader_tag', array( $this, 'defer_parsing_of_js' ), 10 );

		// Register rest functions.
		add_action( 'rest_api_init', array( $this, 'register_api_routes' ) );

		// Add wpal modal to admin.
		add_action( 'admin_footer', array( $this, 'modal_html' ) );

		// Add settings page.
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );

		// Add wpal link to admin bar
		add_action( 'admin_bar_menu', array( $this, 'add_admin_menu_item' ), 999 );

		// Add a settings link to the plugins page.
		$plugin_dir_name = basename( WPAL_PLUGIN_DIR );
		add_filter( 'plugin_action_links_' . $plugin_dir_name . '/wpal.php', array( $this, 'add_settings_link' ) );
	}

	/**
	 * Enqueue admin scripts.
	 *
	 * @since 1.0.0
	 */
	public function admin_scripts() {
		wp_enqueue_script( 'wpal-js', WPAL_PLUGIN_URL . '/dist/wpal.js', array(), WPAL_VERSION, false );

		/**
		 * Filters any additional items to be searchable.
		 *
		 * @since 1.0.0
		 *
		 * @param array $extra_items {
		 *   Array of extra items to be searchable by Wpal. Defaul empty array.
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
		$extra_items = apply_filters( 'wpal_extra_items', array() );

		// Get hotkey from settings.
		$options = get_option( 'wpal_settings' );
		$hotkey  = array(
			'key'   => isset( $options['hotkey_key'] ) ? $options['hotkey_key'] : 'k',
			'alt'   => isset( $options['hotkey_alt'] ) ? $options['hotkey_alt'] : '',
			'ctrl'  => isset( $options['hotkey_ctrl'] ) ? $options['hotkey_ctrl'] : '1',
			'shift' => isset( $options['hotkey_shift'] ) ? $options['hotkey_shift'] : '',
			'meta'  => isset( $options['hotkey_meta'] ) ? $options['hotkey_meta'] : '',
		);

		wp_localize_script(
			'wpal-js',
			'wpalData',
			array(
				'rest'        => esc_url_raw( rest_url( 'wpal/v1' ) ),
				'nonce'       => wp_create_nonce( 'wp_rest' ),
				'extra_items' => $extra_items,
				'hotkey'      => $hotkey,
			)
		);
	}

	public function defer_parsing_of_js( $url ) {
		if ( strpos( $url, 'wpal.js' ) ) {
			return str_replace( ' src', ' defer src', $url );
		}
		return $url;
	}

	/**
	 * Register API Routes for Wpal.
	 *
	 * @since 1.0.0
	 */
	public function register_api_routes() {

		// Search posts route.
		register_rest_route(
			'wpal/v1',
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
	 * Add Wpal Modal HTML to footer.
	 *
	 * @since 1.0.0
	 */
	public function modal_html() {
		include WPAL_PLUGIN_DIR . 'templates/wpal-modal.php';
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
		$options = get_option( 'wpal_settings' );
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
		$results = apply_filters( 'wpal_server_search_results', $results, $term );

		return $results;
	}

	/**
	 * Adds a settings page.
	 *
	 * @since 1.0.0
	 */
	public function add_settings_page() {
		add_options_page(
			__( 'WP Admin Launcher', 'wpal' ),
			__( 'WP Admin Launcher', 'wpal' ),
			'manage_options',
			'wpal-settings',
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
		<h2 class="wpal-settings-title">
			<?php esc_html_e( 'WP Admin Launcher Settings', 'wpal' ); ?>
		</h2>
		<form action="options.php" method="post">
				<?php
				settings_fields( 'wpal_settings' );
				do_settings_sections( 'wpal_settings' );
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
		register_setting( 'wpal_settings', 'wpal_settings' );
		add_settings_section( 'wpal_settings_section', '', '__return_true', 'wpal_settings' );

		// Post types setting.
		add_settings_field(
			'wpal_setting_post_types',
			__( 'Post Types', 'wpal' ),
			array( $this, 'render_setting_post_types' ),
			'wpal_settings',
			'wpal_settings_section'
		);

		// Users search setting.
		add_settings_field(
			'wpal_setting_users_search',
			__( 'Enable Users Search', 'wpal' ),
			array( $this, 'render_setting_users_search' ),
			'wpal_settings',
			'wpal_settings_section'
		);

		// Hotkey setting.
		add_settings_field(
			'wpal_setting_hotkey',
			__( 'Hotkey', 'wpal' ),
			array( $this, 'render_setting_hotkey' ),
			'wpal_settings',
			'wpal_settings_section'
		);
	}

	/**
	 * Render setting allowed post types.
	 *
	 * @since 1.0.0
	 */
	public function render_setting_post_types() {
		$options = get_option( 'wpal_settings' );

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
				<label for="wpal_setting_post_type_<?php echo esc_attr( $post_type->name ); ?>">
					<input type="checkbox"
						id="wpal_setting_post_type_<?php echo esc_attr( $post_type->name ); ?>"
						name="wpal_settings[post_types][]"
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
		$options = get_option( 'wpal_settings' );
		$value   = isset( $options['users_search'] ) ? $options['users_search'] : false;
		?>
		<fieldset>
			<label for="wpal_setting_users_search">
				<input type="checkbox"
					id="wpal_setting_users_search"
					name="wpal_settings[users_search]"
					value="1"
					<?php checked( 1, $value ); ?> />
					<?php esc_html_e( 'This will turn on users searching.', 'wpal' ); ?>
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
		$options        = get_option( 'wpal_settings' );
		$hotkey_display = isset( $options['hotkey_display'] ) ? $options['hotkey_display'] : '';
		$hotkey_key     = isset( $options['hotkey_key'] ) ? $options['hotkey_key'] : '';
		$hotkey_alt     = isset( $options['hotkey_alt'] ) ? $options['hotkey_alt'] : '';
		$hotkey_ctrl    = isset( $options['hotkey_ctrl'] ) ? $options['hotkey_ctrl'] : '';
		$hotkey_shift   = isset( $options['hotkey_shift'] ) ? $options['hotkey_shift'] : '';
		$hotkey_meta    = isset( $options['hotkey_meta'] ) ? $options['hotkey_meta'] : '';
		?>
		<fieldset>
			<input type="hidden" id="wpal_setting_hotkey_key" name="wpal_settings[hotkey_key]" value="<?php echo esc_html( $hotkey_key ); ?>">
			<input type="hidden" id="wpal_setting_hotkey_alt" name="wpal_settings[hotkey_alt]" value="<?php echo esc_html( $hotkey_alt ); ?>">
			<input type="hidden" id="wpal_setting_hotkey_ctrl" name="wpal_settings[hotkey_ctrl]" value="<?php echo esc_html( $hotkey_ctrl ); ?>">
			<input type="hidden" id="wpal_setting_hotkey_shift" name="wpal_settings[hotkey_shift]" value="<?php echo esc_html( $hotkey_shift ); ?>">
			<input type="hidden" id="wpal_setting_hotkey_meta" name="wpal_settings[hotkey_meta]" value="<?php echo esc_html( $hotkey_meta ); ?>">
			<label for="wpal_setting_hotkey">
				<input type="text"
					id="wpal_setting_hotkey_display"
					name="wpal_settings[hotkey_display]"
					value="<?php echo esc_html( $hotkey_display ); ?>" >
			</label>
			<br><i><?php esc_html_e( 'Click this input and press a combination of keys to open Wpal search window.', 'wpal' ); ?></i>
		</fieldset>

		<script>
			var wpal_hotkey_input = document.getElementById('wpal_setting_hotkey_display');
			wpal_hotkey_input.onkeydown = function(e) {
				e.preventDefault();
				console.log(e);
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
				wpal_hotkey_input.value = value;

				// hidden inputs.
				document.getElementById('wpal_setting_hotkey_key').value = e.key;
				document.getElementById('wpal_setting_hotkey_alt').value = e.altKey ? 1 : '';
				document.getElementById('wpal_setting_hotkey_ctrl').value = e.ctrlKey ? 1 : '';
				document.getElementById('wpal_setting_hotkey_shift').value = e.shiftKey ? 1 : '';
				document.getElementById('wpal_setting_hotkey_meta').value = e.metaKey ? 1 : '';

				return false;
			}
		</script>
		<?php
	}

	/**
	 * Add admin bar menu item.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Admin_Bar $wp_admin_bar WP_Admin_Bar instance.
	 */
	public function add_admin_menu_item( $wp_admin_bar ) {
		$args = array(
				'id' => 'wpal-admin-bar',
				'title' => 'Wpal',
				'href' => '#',
				'meta' => array(
					'class' => 'wpal-admin-bar',
					'title' => 'Wpal Quick Launcher'
				)
		);
		$wp_admin_bar->add_node( $args );
	}

	/**
	 * Add settings link to plugin page.
	 *
	 * @since 1.0.
	 *
	 * @param array $links Array of links.
	 * @return array Array of links.
	 */
	public function add_settings_link( $links ) {
		$settings_link = '<a href="options-general.php?page=wpal-settings">' . __( 'Settings' ) . '</a>';
		array_push( $links, $settings_link );
		return $links;
	}
}