<?php
/*
Plugin Name:  WP Events & Hooks Listeners
Plugin URI:   https://developer.wordpress.org/plugins/wp-events-hooks-listeners
Description:  Perform actions based on Wordpress events. Post to external webhooks, perform any actions, send email, send notification to slack, facebook, etc.
Version:      1.0
Author:       Shaharia Azam <mail@shahariadigital.com>.
Author URI:   http://www.shaharia.com?utm_source=wp-events-hooks-listeners
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  wp-events-hooks-listeners
*/

use ShahariaAzam\WPEventsHooksListeners\Bootstrap;

if( !defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

require __DIR__ . DIRECTORY_SEPARATOR . "vendor/autoload.php";

define( "WP_ACTIONS_ON_EVENTS_PLUGIN_ROOT_DIR", __DIR__ );
define( "WP_ACTIONS_ON_EVENTS_PLUGIN_VERSION", 1.0 );
define( "WP_ACTIONS_ON_EVENTS_PLUGIN_FILE", basename(__FILE__) );
define( "WP_ACTIONS_ON_EVENTS_PLUGIN_FILE_FULL_PATH", __FILE__ );
define( "WP_ACTIONS_ON_EVENTS_PLUGIN_SLUG", basename(__DIR__) );
define( "WP_ACTIONS_ON_EVENTS_PLUGIN_OPTIONS_KEY", "wp_actions_on_events_options" );
define( "WP_ACTIONS_ON_EVENTS_PLUGIN_OPTIONS_GROUP", "wp_actions_on_events_options" );

$bootstrap = new Bootstrap();
$bootstrap->load();