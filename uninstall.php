<?php

/**
 * Opticommerce RX Uninstall
 *
 * Uninstalling delete its table and options.
 *
 * @version 1.0
 */
defined('WP_UNINSTALL_PLUGIN') || exit;

global $wpdb;

// Delete options.
$wpdb->query("DELETE FROM $wpdb->options WHERE option_name = 'rx_db_version';");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}user_prescriptions");
// Clear any cached data that has been removed.
wp_cache_flush();
