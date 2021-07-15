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

		wp_localize_script(
			'wipi-js',
			'wipiData',
			array(
				'rest'        => esc_url_raw( rest_url( 'wipi/v1' ) ),
				'nonce'       => wp_create_nonce( 'wp_rest' ),
				'extra_items' => $extra_items,
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
		$term = $data['term'];

		// Search on posts.
		$posts = get_posts(
			array(
				's'         => $term,
				'post_type' => 'any',
			)
		);

		// Search on users.
		$users = get_users(
			array(
				'search' => $term,
			),
		);

		// Merge all results.
		$results = array();
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
		foreach ( $users as $user ) {
			$results[] = array(
				'type'  => 'user',
				'icon'  => 'dashicons-admin-users',
				'label' => $user->display_name,
				'term'  => strtolower( $user->display_name ),
				'link'  => get_edit_user_link( $user->ID ),
			);
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

		add_settings_field(
			'wipi_setting_post_types',
			__( 'Post Types', 'wipi' ),
			array( $this, 'render_setting_post_types' ),
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
}
