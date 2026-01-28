<?php
/**
 * Uninstall script for Dynamic Classes for Elementor
 * 
 * This file is executed when the plugin is uninstalled via WordPress admin.
 * It cleans up all plugin data from the database.
 */

// Exit if accessed directly or if not uninstalling
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete all cached CSS transients
global $wpdb;

// Delete transients
$wpdb->query(
    "DELETE FROM {$wpdb->options} 
    WHERE option_name LIKE '_transient_dce_dynamic_css_%' 
    OR option_name LIKE '_transient_timeout_dce_dynamic_css_%'"
);

// Note: We don't delete the Kit settings themselves as they're part of Elementor's data
// Users may want to keep their class configurations even after uninstalling

// Clear any cached data
wp_cache_flush();
