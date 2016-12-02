<?php
/**
 * RSS Poster Functions.
 *
 * @package RSS Poster
 * @version 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Attempt to get an RSS feed.
 *
 * @author Greg
 * @param array $args Feed settings.
 * @return string Feed data.
 * @since 1.0.0
 */
function rssposter_get_feed( $args = array() ) {

	// This is all for nothing if this class isn't available.
	if ( ! class_exists( 'DOMDocument' ) ) {
		return esc_html__( 'My apologies, but I\'m unable to load the PHP DOMDocument class. Please contact your systems administrator.', 'rssposter' );
	}

	// Check for plugin settings.
	$options = get_option( 'rssposter_settings' );

	// If we have settings, use `em.
	if ( is_array( $options ) ) {
		$rss_feed_name = ( $options['rssposter_feed_name'] ) ? $options['rssposter_feed_name'] : '';
		$rss_feed_schedule = ( $options['rssposter_feed_schedule'] ) ? $options['rssposter_feed_schedule'] * HOUR_IN_SECONDS : 4;
		$rss_feed_url = ( $options['rssposter_feed_url'] ) ? $options['rssposter_feed_url'] : '';
	}

	// Setup defaults.
	$defaults = array(
		'name'     => $rss_feed_name,
		'schedule' => $rss_feed_schedule,
		'url'      => $rss_feed_url,
	);

	// Parse args.
	$args = wp_parse_args( $args, $defaults );

	// There's no URL, bail.
	if ( empty( $args['url'] ) ) {
		return esc_html__( 'Hey there good looking. I need a RSS feed URL before I can move forward.', 'rssposter' );
	}

	// Set transient key.
	$transient_key = 'rssposter_' . esc_html( strtolower( str_replace( ' ', '_', $args['name'] ) ) );

	// Attempt to grab transient.
	$data = get_transient( $transient_key );

	// No transient? No problem, let's query the feed.
	if ( false === $data ) {

		// Instantiate the DOMDocument class.
		$dom = new DOMDocument();

		// Query the RSS feed.
		$dom->load( esc_url( $args['url'] ) );

		// Attempt to save the RSS feed as raw XML.
		$data = $dom->saveXML();

		// If this fails, alert the user and bail.
		if ( empty( $data ) ) {
			return esc_html__( 'Bummer. I\'m unable to save the RSS feed. Please try again later.', 'rssposter' );
		}

		// Save the data to the database.
		set_transient( $transient_key, $data, absint( $args['rss_feed_schedule'] ) * HOUR_IN_SECONDS );
	}

	// Return feed data.
	return $data;
}

/**
 * Parse an RSS feed and turn items into posts.
 *
 * @author Greg
 * @param array $args Default arguments.
 * @since 1.0.0
 */
function rssposter_rss_to_post( $args = array() ) {

	// Check for plugin settings.
	$options = get_option( 'rssposter_settings' );

	// If we have settings, use `em.
	if ( is_array( $options ) ) {
		$rss_feed_author = ( $options['rssposter_feed_author'] ) ? $options['rssposter_feed_author'] : 1;
		$rss_feed_category = ( $options['rssposter_feed_category'] ) ? $options['rssposter_feed_category'] : 1;
		$rss_feed_limit = ( $options['rssposter_feed_items_count'] ) ? $options['rssposter_feed_items_count'] : 1;
		$rss_feed_post_status = ( $options['rssposter_feed_post_status'] ) ? $options['rssposter_feed_post_status'] : 'draft';
	}

	// Sest new post defaults.
	// https://developer.wordpress.org/reference/functions/wp_insert_post/#parameters.
	$defaults = array(
		'comment_status'        => '',
		'context'               => '',
		'guid'                  => '',
		'import_id'             => 0,
		'limit'                 => absint( $rss_feed_limit ),
		'menu_order'            => 0,
		'ping_status'           => '',
		'pinged'                => '',
		'post_author'           => absint( $rss_feed_author ),
		'post_category'         => array( absint( $rss_feed_category ) ),
		'post_content_filtered' => '',
		'post_content'          => '',
		'post_excerpt'          => '',
		'post_parent'           => 0,
		'post_password'         => '',
		'post_status'           => esc_html( $rss_feed_post_status ),
		'post_title'            => '',
		'post_type'             => 'post',
		'to_ping'               => '',
	);

	// Parse args.
	$args = wp_parse_args( $args, $defaults );

	// Fetch the RSS feed.
	$data = rssposter_get_feed();

	// No data? Bail!
	if ( empty( $data ) ) {
		return esc_html__( 'Ugh. There doesn\'t seem to be any data in the RSS feed.', 'rssposter' );
	}

	// Instantiate the DOMDocument class.
	$dom = new DOMDocument();

	// Load the RSS feed.
	$dom->loadXML( $data );

	// Validate the data.
	if ( ! is_object( $dom ) ) {
		return esc_html__( 'Yikes. For some reason, the data I was expecting should be in the form of an object.', 'rssposter' );
	}

	$feed = array();

	// Loop through each RSS feed item and fetch data.
	foreach ( $dom->getElementsByTagName( 'item' ) as $node ) {

		// Build an array of item content.
		$feed_item = array(
			'feed_author' => $node->getElementsByTagName( 'creator' )->item( 0 )->nodeValue,
			'feed_date'   => $node->getElementsByTagName( 'pubDate' )->item( 0 )->nodeValue,
			'feed_desc'   => $node->getElementsByTagName( 'description' )->item( 0 )->nodeValue,
			'feed_link'   => $node->getElementsByTagName( 'link' )->item( 0 )->nodeValue,
			'feed_title'  => $node->getElementsByTagName( 'title' )->item( 0 )->nodeValue,
		);

		// Build an array of feed items and fill it with feed data.
		array_push( $feed, $feed_item );
	}

	// Loop through feed array and convert it to post data.
	for ( $i = 0; $i < absint( $args['limit'] ); $i++ ) {

		// For each feed item, build an array of post data.
		$rss_to_post = array(
			'post_author'   => absint( $args['post_author'] ),
			'post_category' => $args['post_category'],
			'post_content'  => $feed[ $i ]['feed_desc'],
			'post_status'   => esc_html( $args['post_status'] ),
			'post_title'    => wp_strip_all_tags( $feed[ $i ]['feed_title'] ),
		);

		// Look for duplicates.
		$duplicates = new WP_Query( array(
			'post_status'            => 'any',
			'title'                  => $rss_to_post['post_title'],
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		) );

		// No duplicate was found...
		if ( false === $duplicates->have_posts() ) {
			wp_insert_post( $rss_to_post ); // Finally, create a new post!
		}
	}
}

/**
 * Create custom wp cron intervals.
 *
 * @author Greg
 * @param array $schedules Current WordPress Cron scheduled intervals.
 * @return array           Updated intervals.
 * @since 1.0.0
 */
function rssposter_filter_cron_schedules( $schedules ) {

	$schedules['four_hours'] = array(
		'display'  => esc_html__( 'Every Four Hours', 'rssposter' ),
		'interval' => 4 * HOUR_IN_SECONDS,
	);

	$schedules['eight_hours'] = array(
		'display'  => esc_html__( 'Every Eight Hours', 'rssposter' ),
		'interval' => 8 * HOUR_IN_SECONDS,
	);

	$schedules['sixteen_hours'] = array(
		'display'  => esc_html__( 'Every Sixteen Hours', 'rssposter' ),
		'interval' => 16 * HOUR_IN_SECONDS,
	);

	$schedules['twenty_hours'] = array(
		'display'  => esc_html__( 'Every Twenty Hours', 'rssposter' ),
		'interval' => 20 * HOUR_IN_SECONDS,
	);

	return $schedules;
}

/**
 * Register fetch intervals with WP Cron.
 *
 * @author Greg
 * @since 1.0.0
 */
function rssposter_set_fetch_interval() {

	// Get the interval schedule.
	$schedule = rssposter_get_post_recurrence_option();

	// No schedule? Bail!
	if ( ! $schedule ) {
		return __( 'Sorry, I cannot locate a schedule! Try saving the settings again.', 'rssposter' );
	}

	// Add the new schedule.
	if ( ! wp_next_scheduled( 'rssposter_schedule' ) ) {
		wp_schedule_event( time(), $schedule, 'rssposter_schedule' );
	}
}

/**
 * Get the post recurrence option.
 *
 * @author Greg
 * @return string The interval at which new posts are made.
 * @since 1.0.0
 */
function rssposter_get_post_recurrence_option() {

	// Check for plugin settings.
	$options = get_option( 'rssposter_settings' );

	// If we have settings, use `em.
	if ( empty( $options ) ) {
		return __( 'Sorry, I can\'t seem to find a schedule.', 'rssposter' );
	}

	// Set a schedule.
	$schedule = ( $options['rssposter_feed_schedule'] ) ? $options['rssposter_feed_schedule'] : 4;

	// Determine our post schedule.
	switch ( $schedule ) {
		case 1 :
			$schedule = 'hourly';
			break;
		case 8 :
			$schedule = 'eight_hours';
			break;
		case 12 :
			$schedule = 'twicedaily';
			break;
		case 16 :
			$schedule = 'sixteen_hours';
			break;
		case 20 :
			$schedule = 'twenty_hours';
			break;
		case 24 :
			$schedule = 'daily';
			break;
		default :
			$schedule = 'four_hours';
			break;
	}

	return $schedule;
}

/**
 * Perform actions upon plugin activation.
 *
 * @author Greg
 * @since 1.0.0
 */
function rssposter_activate() {
	add_action( 'rssposter_schedule', 'rssposter_rss_to_post' );
}

/**
 * Perform actions upon plugin deactivation.
 *
 * @author Greg
 * @since 1.0.0
 */
function rssposter_deactivate() {
	wp_clear_scheduled_hook( 'rssposter_schedule' );
	delete_option( 'rssposter_settings' );
}
