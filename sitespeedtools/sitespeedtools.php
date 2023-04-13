<?php
/**
 * Plugin Name: Site Speed Tools
 * Plugin URI: https://www.sitespeedtools.com
 * Description: Analyse and fix the most critical issues slowing down your WordPress site with Site Speed Tools.
 * Version: 1.0.0
 * Author: Leon Stafford
 * Author URI: https://ljs.dev
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * @package SiteSpeedTools
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once plugin_dir_path( __FILE__ ) . 'settings-page.php';
require_once plugin_dir_path( __FILE__ ) . 'speed-test-page.php';

add_action( 'admin_menu', 'sst_add_admin_menu' );
add_action( 'admin_init', 'sst_settings_init' );
add_action( 'admin_post_sst_submit', 'sst_submit' );

function sst_add_admin_menu() {
    add_menu_page(
        'Site Speed Tools',
        'Site Speed Tools',
        'manage_options',
        'site_speed_tools_speed_test',
        'sst_speed_test_page',
        'dashicons-performance'
    );
    add_submenu_page(
        'site_speed_tools_speed_test',
        'Speed Test',
        'Speed Test',
        'manage_options',
        'site_speed_tools_speed_test',
        'sst_speed_test_page'
    );
    add_submenu_page(
        'site_speed_tools_speed_test',
        'Settings',
        'Settings',
        'manage_options',
        'site_speed_tools_settings',
        'sst_options_page'
    );
}

function sst_submit() {
    $options = get_option('sst_settings');

     if (empty($options['sst_api_key'])) {
        set_transient('sst_api_error_message', 'API key is not set.', 60);
        wp_redirect(admin_url('admin.php?page=site_speed_tools_speed_test'));
        exit;
    }

    $api_key = $options['sst_api_key'];
    $site_info = sst_get_site_info();

    define( 'SST_DEVELOPMENT_MODE', true );

    if ( defined( 'SST_DEVELOPMENT_MODE' ) && SST_DEVELOPMENT_MODE ) {
        $api_endpoint = 'https://apidev.sitespeedtools.com/api/v1/wordpress';
    } else {
        $api_endpoint = 'https://api.sitespeedtools.com/api/v1/wordpress';
    }

    $response = wp_remote_request( $api_endpoint, array(
        'method' => 'POST',
        'timeout' => 45,
        'redirection' => 5,
        'httpversion' => '1.0',
        'blocking' => true,
        'headers' => array(),
        'body' => array( 'api_key' => $api_key, 'site_info' => $site_info ),
        'cookies' => array()
        )
    );

    if ( is_wp_error( $response ) ) {
        $error_message = $response->get_error_message();
        error_log($error_message);
        set_transient( 'sst_api_error_message', $error_message, 60 );
        wp_redirect( admin_url( 'admin.php?page=site_speed_tools_speed_test' ) );
        exit;
    } else {
        error_log( 'Site Speed Tools API response: ' . $response['body'] );
        wp_redirect( admin_url( 'admin.php?page=site_speed_tools_speed_test' ) );
        exit;
    }
}

function sst_get_site_info(
    $site_url = '',
    $site_title = '',
    $site_description = '',
    $site_language = '',
    $plugins = array(),
    $themes = array(),
    $permalinks_structure = '',
    $users = array(),
    $posts = array(),
    $pages = array(),
    $comments = array()
) {
    $site_url = get_site_url();
    $site_title = get_bloginfo( 'name' );
    $site_description = get_bloginfo( 'description' );
    $site_language = get_bloginfo( 'language' );
    $plugins = get_plugins();
    $themes = wp_get_themes();
    $permalinks_structure = get_option( 'permalink_structure' );
    $users = get_users();
    $posts = get_posts();
    $pages = get_pages();
    $comments = get_comments();
    return json_encode( array(
        'site_url' => $site_url,
        'site_title' => $site_title,
        'site_description' => $site_description,
        'site_language' => $site_language,
        'plugins' => $plugins,
        'themes' => $themes,
        'permalinks_structure' => $permalinks_structure,
        'users' => $users,
        'posts' => $posts,
        'pages' => $pages,
        'comments' => $comments
    ) );
}