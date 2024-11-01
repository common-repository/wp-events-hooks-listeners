<?php


namespace ShahariaAzam\WPEventsHooksListeners;


class Bootstrap {
	public function __construct() {

	}

	public function load() {
		if ( is_admin()/* && current_user_can('administrator')*/ ) {
			//Whitelist WP options keys
			add_action( 'admin_init', array( Functions::class, 'registerWhitelistedOptions' ) );

			add_filter( 'plugin_row_meta', array( Functions::class, 'custom_plugin_row_meta' ), 10, 2 );

			//Plugin hooks
			register_activation_hook( WP_ACTIONS_ON_EVENTS_PLUGIN_ROOT_DIR . DIRECTORY_SEPARATOR . WP_ACTIONS_ON_EVENTS_PLUGIN_FILE, array(
				Functions::class,
				'onActivatingPlugin'
			) );

			register_deactivation_hook( WP_ACTIONS_ON_EVENTS_PLUGIN_ROOT_DIR . DIRECTORY_SEPARATOR . WP_ACTIONS_ON_EVENTS_PLUGIN_FILE, array(
				Functions::class,
				'onDeactivatingPlugin'
			) );
			register_uninstall_hook( WP_ACTIONS_ON_EVENTS_PLUGIN_ROOT_DIR . DIRECTORY_SEPARATOR . WP_ACTIONS_ON_EVENTS_PLUGIN_FILE, array(
				Functions::class,
				'onDeletingPlugin'
			) );
			// End of plugin hooks

			// Build Plugin Admin Menu
			add_action( 'admin_menu', array( Functions::class, 'adminMenuInit' ) );

			//Admin load custom scripts
			if ( isset( $_GET['page'] ) && $_GET['page'] === WP_ACTIONS_ON_EVENTS_PLUGIN_SLUG ) {
				add_action( 'admin_enqueue_scripts', array( Functions::class, 'loadPluginAdminPageStaticAssets' ) );
			}

			add_action( 'wp_ajax_waoe_save_actions_webhooks', array( Functions::class, "saveActionsWebhooksAjax" ) );
			add_action( 'wp_ajax_waoe_get_actions_webhooks', array( Functions::class, "getActionsWebhooksAjax" ) );
		}

		self::registerUsersHooks();
	}

	public static function registerUsersHooks() {
		$options = Functions::getOptions( "actions" );
		if(!empty($options)){
			foreach ( $options as $key => $value ) {
				if ( $key === "someone_publish_post" ) {
					add_action( 'publish_post', array( Bootstrap::class, 'postToWebhookAfterPostPublished' ), 10, 2 );
				}
			}
		}
	}

	public static function postToWebhookAfterPostPublished( $ID, $post ) {
		$options = Functions::getOptions( "actions" );
		if ( ! empty( $options['someone_publish_post']['webhook_url'] ) ) {
			$data = array(
				'author'         => $post->post_author,
				'author_name'    => get_the_author_meta( 'display_name', $post->post_author ),
				'post_title'     => $post->post_title,
				'permalink'      => get_permalink( $ID ),
				'post_edit_link' => get_edit_post_link( $ID, '' )
			);

            $response = wp_remote_post( $options['someone_publish_post']['webhook_url'], [
                'method'      => 'POST',
                'timeout'     => 45,
                'redirection' => 2,
                'httpversion' => '1.0',
                'blocking'    => true,
                'headers'     => array('Content-Type' => 'application/json'),
                'body'        => json_encode($data),
                'cookies'     => array()
            ] );
		}
	}
}