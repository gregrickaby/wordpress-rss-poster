<?php
/**
 * RSS Poster Options Page.
 *
 * @package RSS Poster
 * @version 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Deal with the options page.
 *
 * @author Greg
 * @since 1.0.0
 */
class RssPosterOptionsPage {

	/**
	 * Constructor.
	 */
	function __construct() {
		add_action( 'admin_menu', array( $this, 'rssposter_add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'rssposter_settings_init' ) );
	}

	/**
	 * Register options page with WordPress.
	 */
	public function rssposter_add_admin_menu() {
		add_options_page(
			'RSS Poster',     // Page title.
			'RSS Poster',     // Menu title.
			'manage_options', // Minimum user level in order to display.
			'rssposter',      // Menu slug.
			array( $this, 'rssposter_options_page' ) // Page callback method.
		);
	}

	/**
	 * Register settings and options fields.
	 */
	public function rssposter_settings_init() {

		// Register a setting.
		register_setting( 'rssposter_options_page', 'rssposter_settings' );

		/**
		 * Add new settings section.
		 */
		add_settings_section(
			'rssposter_options_page_section',                      // Section ID.
			'',                                                    // Section title.
			array( $this, 'rssposter_settings_section_callback' ), // Section callback.
			'rssposter_options_page'                               // The menu page in which to display this section.
		);

		/**
		 * RSS feed name.
		 */
		add_settings_field(
			'rssposter_text_field_feed_name',             // Setting ID.
			esc_html__( 'RSS Feed Name', 'rssposser' ),   // Setting title.
			array( $this, 'rssposter_feed_name_render' ), // Setting render callback.
			'rssposter_options_page',                     // The menu page in which to display this setting.
			'rssposter_options_page_section'              // The section in which to display this setting.
		);

		/**
		 * RSS feed URL.
		 */
		add_settings_field(
			'rssposter_feed_url',
			esc_html__( 'RSS Feed URL', 'rssposser' ),
			array( $this, 'rssposter_feed_url_render' ),
			'rssposter_options_page',
			'rssposter_options_page_section'
		);

		/**
		 * RSS feed author.
		 */
		add_settings_field(
			'rssposer_feed_author',
			esc_html__( 'Select An Author', 'rssposser' ),
			array( $this, 'rssposter_feed_author_render' ),
			'rssposter_options_page',
			'rssposter_options_page_section'
		);

		/**
		 * RSS feed category.
		 */
		add_settings_field(
			'rssposter_feed_category',
			esc_html__( 'Select A Category', 'rssposser' ),
			array( $this, 'rssposter_feed_category_render' ),
			'rssposter_options_page',
			'rssposter_options_page_section'
		);

		/**
		 * Post status.
		 */
		add_settings_field(
			'rssposter_feed_post_status',
			esc_html__( 'Select A Post Status', 'rssposser' ),
			array( $this, 'rssposter_feed_post_status_render' ),
			'rssposter_options_page',
			'rssposter_options_page_section'
		);

		/**
		 * Items count.
		 */
		add_settings_field(
			'rssposter_feed_items_count',
			esc_html__( 'How Many Items', 'rssposser' ),
			array( $this, 'rssposter_feed_items_count_render' ),
			'rssposter_options_page',
			'rssposter_options_page_section'
		);

		/**
		 * RSS feed schedule.
		 */
		add_settings_field(
			'rssposter_feed_schedule',
			esc_html__( 'How Often', 'rssposser' ),
			array( $this, 'rssposter_feed_schedule_render' ),
			'rssposter_options_page',
			'rssposter_options_page_section'
		);
	}

	/**
	 * RSS feed URL render.
	 */
	public function rssposter_feed_url_render() {
		$options = get_option( 'rssposter_settings' );

		// Set a default.
		if ( empty( $options['rssposter_feed_url'] ) ) {
			$options['rssposter_feed_url'] = '';
		}

		?>
		<input type="url" class="regular-text code" name="rssposter_settings[rssposter_feed_url]" value="<?php echo esc_url( $options['rssposter_feed_url'] ); ?>">
		<p class="description"><?php esc_html_e( 'Enter a valid XML RSS feed URL, like:', 'rssposter' ); ?> https://wordpress.com/feed</p>
		<?php
	}

	/**
	 * RSS feed name render.
	 */
	public function rssposter_feed_name_render() {
		$options = get_option( 'rssposter_settings' );

		// Set a default.
		if ( empty( $options['rssposter_feed_name'] ) ) {
			$options['rssposter_feed_name'] = '';
		}

		?>
		<input type="text" class="regular-text" name="rssposter_settings[rssposter_feed_name]" value="<?php echo esc_html( $options['rssposter_feed_name'] ); ?>">
		<p class="description"><?php esc_html_e( 'Example: Top Stories or Entertainment News', 'rssposter' ); ?></p>
		<?php
	}

	/**
	 * RSS feed author render.
	 */
	public function rssposter_feed_author_render() {
		$options = get_option( 'rssposter_settings' );

		// Set a default.
		if ( empty( $options['rssposter_feed_author'] ) ) {
			$options['rssposter_feed_author'] = 1;
		}

		// Create a dropdown of all users.
		wp_dropdown_users( array(
			'include_selected' => true,
			'name'             => 'rssposter_settings[rssposter_feed_author]',
			'selected'         => esc_attr( $options['rssposter_feed_author'] ),
			'show'             => 'display_name_with_login',
		) ); ?>

		<p class="description"><?php esc_html_e( 'This will override the RSS feed author.', 'rssposter' ); ?></p>
		<?php
	}

	/**
	 * RSS feed category render.
	 */
	public function rssposter_feed_category_render() {
		$options = get_option( 'rssposter_settings' );

		// Set a default.
		if ( empty( $options['rssposter_feed_category'] ) ) {
			$options['rssposter_feed_category'] = 1;
		}

		// Create a dropdown of categories.
		wp_dropdown_categories( array(
			'name'       => 'rssposter_settings[rssposter_feed_category]',
			'selected'   => esc_attr( $options['rssposter_feed_category'] ),
			'hide_empty' => false,
			'orderby'    => 'name',
		) ); ?>

		<p class="description"><?php esc_html_e( 'Which category do you want feed items to display in?', 'rssposter' ); ?></p>
		<?php
	}

	/**
	 * Post status render.
	 */
	public function rssposter_feed_post_status_render() {
		$options = get_option( 'rssposter_settings' );

		// Set a default.
		if ( empty( $options['rssposter_feed_post_status'] ) ) {
			$options['rssposter_feed_post_status'] = 'draft';
		}

		?>
		<select name="rssposter_settings[rssposter_feed_post_status]">
			<option value="draft" <?php selected( $options['rssposter_feed_post_status'], 'draft' ); ?>>Draft</option>
			<option value="future" <?php selected( $options['rssposter_feed_post_status'], 'future' ); ?>>Future</option>
			<option value="pending" <?php selected( $options['rssposter_feed_post_status'], 'pending' ); ?>>Pending</option>
			<option value="private" <?php selected( $options['rssposter_feed_post_status'], 'private' ); ?>>Private</option>
			<option value="publish" <?php selected( $options['rssposter_feed_post_status'], 'publish' ); ?>>Publish</option>
			<option value="trash" <?php selected( $options['rssposter_feed_post_status'], 'trash' ); ?>>Trash</option>
		</select>
		<p class="description"><?php esc_html_e( 'Which post status should new items inherit?', 'rssposter' ); ?></p>
		<?php
	}

	/**
	 * RSS Feed items count render.
	 */
	public function rssposter_feed_items_count_render() {
		$options = get_option( 'rssposter_settings' );

		// Set a default.
		if ( empty( $options['rssposter_feed_items_count'] ) ) {
			$options['rssposter_feed_items_count'] = 1;
		}

		?>
		<select name="rssposter_settings[rssposter_feed_items_count]">
			<option value="1" <?php selected( $options['rssposter_feed_items_count'], 1 ); ?>>1</option>
			<option value="2" <?php selected( $options['rssposter_feed_items_count'], 2 ); ?>>2</option>
			<option value="3" <?php selected( $options['rssposter_feed_items_count'], 3 ); ?>>3</option>
			<option value="4" <?php selected( $options['rssposter_feed_items_count'], 4 ); ?>>4</option>
			<option value="5" <?php selected( $options['rssposter_feed_items_count'], 5 ); ?>>5</option>
		</select>
		<?php esc_html_e( 'item(s)', 'rssposter' ); ?>
		<p class="description"><?php esc_html_e( 'How many items should RSS Poster post?', 'rssposter' ); ?></p>
		<?php
	}

	/**
	 * Feed cache timeout render.
	 */
	public function rssposter_feed_schedule_render() {
		$options = get_option( 'rssposter_settings' );

		// Set a default.
		if ( empty( $options['rssposter_feed_schedule'] ) ) {
			$options['rssposter_feed_schedule'] = 1;
		}

		?>
		<select name="rssposter_settings[rssposter_feed_schedule]">
			<option value="1" <?php selected( $options['rssposter_feed_schedule'], 1 ); ?>>1</option>
			<option value="4" <?php selected( $options['rssposter_feed_schedule'], 4 ); ?>>4</option>
			<option value"8" <?php selected( $options['rssposter_feed_schedule'], 8 ); ?>>8</option>
			<option value="12" <?php selected( $options['rssposter_feed_schedule'], 12 ); ?>>12</option>
			<option value="16" <?php selected( $options['rssposter_feed_schedule'], 16 ); ?>>16</option>
			<option value="20" <?php selected( $options['rssposter_feed_schedule'], 20 ); ?>>20</option>
			<option value="24" <?php selected( $options['rssposter_feed_schedule'], 24 ); ?>>24</option>
		</select>
		<?php esc_html_e( 'hour(s)', 'rssposter' ); ?>
		<p class="description"><?php esc_html_e( 'How often should RSS Poster fetch and post new items?', 'rssposter' ); ?></p>
		<?php
	}

	/**
	 * Section description render.
	 */
	public function rssposter_settings_section_callback() {
		echo esc_html__( 'Please enter RSS Feed details and settings below.', 'rssposser' );
	}

	/**
	 * Create the options page markup.
	 */
	public function rssposter_options_page() {
		?>
		<form action='options.php' method='post'>
		<h1><?php esc_html_e( 'RSS Poster', 'rssposter' ); ?></h1>
		<?php
			settings_fields( 'rssposter_options_page' );
			do_settings_sections( 'rssposter_options_page' );
			rssposter_set_fetch_interval();
			submit_button();
		?>
		</form>
		<?php
	}
}

/**
 * Kick off the options page.
 */
if ( class_exists( 'RssPosterOptionsPage' ) ) {
	new RssPosterOptionsPage();
}
