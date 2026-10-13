<?php
/**
 * Uninstall plugin
 *
 * @package WCBoost\VariationSwatches
 */

// If uninstall not called from WordPress then exit.
defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

global $wpdb;

// The free and Pro versions use the same attribute types. Do not restore them if the other version is active.
// The plugin being uninstalled is not loaded, so this constant can only come from the other version.
$other_version_active = defined( 'WCBOOST_VARIATION_SWATCHES_VERSION' );

if ( ! $other_version_active ) {
	$active_plugins = (array) get_option( 'active_plugins', [] );

	if ( is_multisite() ) {
		$active_plugins = array_merge( $active_plugins, array_keys( (array) get_site_option( 'active_sitewide_plugins', [] ) ) );
	}

	foreach ( $active_plugins as $active_plugin ) {
		if ( in_array( basename( $active_plugin ), [ 'wcboost-variation-swatches.php', 'wcboost-variation-swatches-pro.php' ], true ) ) {
			$other_version_active = true;
			break;
		}
	}
}

// Restore swatches attributes to standard select type.
$table = $wpdb->prefix . 'woocommerce_attribute_taxonomies';

if ( ! $other_version_active &&$wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table ) ) ) === $table ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$update = "UPDATE `$table` SET `attribute_type` = 'select' WHERE `attribute_type` IN ('color', 'image', 'label', 'button')";
	$wpdb->query( $update ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

	// Clear the attribute caches so the change takes effect immediately.
	delete_transient( 'wc_attribute_taxonomies' );

	if ( class_exists( 'WC_Cache_Helper' ) ) {
		WC_Cache_Helper::invalidate_cache_group( 'woocommerce-attributes' );
	}
}

// Remove options.
delete_option( 'wcboost_variation_swatches_ignore_restore' );
delete_site_option( 'wcboost_variation_swatches_ignore_restore' );
