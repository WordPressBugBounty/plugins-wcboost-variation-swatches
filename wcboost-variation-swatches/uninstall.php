<?php
/**
 * Uninstall plugin
 */

// If uninstall not called from WordPress then exit
defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

global $wpdb;

// Restore swatches attributes to standard select type.
$table = $wpdb->prefix . 'woocommerce_attribute_taxonomies';
$query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table ) );

if ( $wpdb->get_var( $query ) == $table ) {
	$update = "UPDATE `$table` SET `attribute_type` = 'select' WHERE `attribute_type` != 'text'";
	$wpdb->query( $update );
}

// Remove options.
delete_option( 'wcboost_variation_swatches_ignore_restore' );
delete_site_option( 'wcboost_variation_swatches_ignore_restore' );
