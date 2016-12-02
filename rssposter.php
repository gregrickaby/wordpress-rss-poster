<?php
/**
 * RSS Poster Plugin.
 *
 * @package RSS Poster
 * @version 1.0.0
 */

/**
 * Plugin Name: RSS Poster
 * Plugin URI: https://github.com/gregrickaby/wordpress-rss-poster
 * Description: Create posts in WordPress from an RSS feed.
 * Author: Greg Rickaby
 * Version: 1.0.0
 * Author URI: https://gregrickaby.com
 * License: GPL3+
 * Text Domain: rssposter
 * Domain Path: /languages/
 */

require_once( 'class-options.php' );
require_once( 'functions.php' );

// Register activation hooks.
register_activation_hook( __FILE__, 'rssposter_activate' );
register_deactivation_hook( __FILE__, 'rssposter_deactivate' );

// Plugin hooks.
add_action( 'wp', 'rssposter_set_fetch_interval' );

// Plugin filters.
add_filter( 'cron_schedules', 'rssposter_filter_cron_schedules' );
