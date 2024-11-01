<?php


namespace ShahariaAzam\WPEventsHooksListeners;

class Functions {
	public static function custom_plugin_row_meta( $links, $file ) {
		if ( strpos( $file, WP_ACTIONS_ON_EVENTS_PLUGIN_FILE ) !== false ) {
			$new_links = array(
				'configuration' => '<a href="/wp-admin/admin.php?page=wp-events-hooks-listeners" target="_blank">Add Actions</a>'
			);

			$links = array_merge( $links, $new_links );
		}

		return $links;
	}

	public static function onActivatingPlugin() {
		add_option(WP_ACTIONS_ON_EVENTS_PLUGIN_OPTIONS_KEY, serialize([]));
	}

	public static function onDeactivatingPlugin() {
		update_option(WP_ACTIONS_ON_EVENTS_PLUGIN_OPTIONS_KEY, serialize([]));
	}

	public static function onDeletingPlugin() {
		//on delete plugin, cleanup options data
		delete_option(WP_ACTIONS_ON_EVENTS_PLUGIN_OPTIONS_KEY);
	}

	public static function registerWhitelistedOptions() {
		register_setting( WP_ACTIONS_ON_EVENTS_PLUGIN_OPTIONS_KEY, WP_ACTIONS_ON_EVENTS_PLUGIN_OPTIONS_GROUP );
	}

	public static function adminMenuInit() {
		//Custom CSS to fix admin menu
		add_action( 'admin_head', array( Functions::class, 'overrideAdminMenuCSS' ) );

		$menuItems = [
			[
				'page_title'        => "Manage Actions",
				'menu_title'        => "WP Events Hooks Listeners",
				'capabilities'      => 'manage_options',
				'menu_slug'         => "wp-events-hooks-listeners",
				'callback_function' => array( Functions::class, 'adminPageDisplay' ),
				'menu_icon'         => "dashicons-randomize",
			]
		];
		foreach ( $menuItems as $item ) {
			add_menu_page( $item['page_title'], $item['menu_title'], $item['capabilities'], $item['menu_slug'], $item['callback_function'], $item['menu_icon'] );
		}
	}

	public static function overrideAdminMenuCSS() {

	}

	public static function adminPageDisplay() {
		echo "<div class='wrap'><div class='container-fluid'><div class='wp-mail-gateway-plugin-adminpage' id='wpMailGatewayPluginAdminPage'>";
		$htmlParse = new HTMLParser(file_get_contents(WP_ACTIONS_ON_EVENTS_PLUGIN_ROOT_DIR . DIRECTORY_SEPARATOR . "src/admin_template.html"), []);
		echo $htmlParse->output();
		echo "</div></div></div>";
	}

	public static function loadPluginAdminPageStaticAssets() {
		wp_register_style( 'bootstrap', plugins_url( 'assets/css/bootstrap.css', WP_ACTIONS_ON_EVENTS_PLUGIN_FILE_FULL_PATH ), false, WP_ACTIONS_ON_EVENTS_PLUGIN_VERSION );
		wp_enqueue_style( 'bootstrap' );

		wp_register_style( 'sweetalert2.min', plugins_url( 'assets/css/sweetalert2.min.css', WP_ACTIONS_ON_EVENTS_PLUGIN_FILE_FULL_PATH ), array( "bootstrap" ), WP_ACTIONS_ON_EVENTS_PLUGIN_VERSION );
		wp_enqueue_style( 'sweetalert2.min' );

		wp_register_style( 'waoe.main', plugins_url( 'assets/css/main.css', WP_ACTIONS_ON_EVENTS_PLUGIN_FILE_FULL_PATH ), array( "bootstrap" ), WP_ACTIONS_ON_EVENTS_PLUGIN_VERSION );
		wp_enqueue_style( 'waoe.main' );

		wp_register_script( 'popperjs', plugins_url( 'assets/js/popper.min.js', WP_ACTIONS_ON_EVENTS_PLUGIN_FILE_FULL_PATH ), array( 'jquery' ), WP_ACTIONS_ON_EVENTS_PLUGIN_VERSION, true );
		wp_enqueue_script( "popperjs" );

		wp_register_script( 'bootstrap.min', plugins_url( 'assets/js/bootstrap.min.js', WP_ACTIONS_ON_EVENTS_PLUGIN_FILE_FULL_PATH ), array( 'jquery' ), WP_ACTIONS_ON_EVENTS_PLUGIN_VERSION, true );
		wp_enqueue_script( 'bootstrap.min' );

		wp_register_script( 'sweetalert2.min', plugins_url( 'assets/js/sweetalert2.min.js', WP_ACTIONS_ON_EVENTS_PLUGIN_FILE_FULL_PATH ), array( 'bootstrap.min' ), WP_ACTIONS_ON_EVENTS_PLUGIN_VERSION, true );
		wp_enqueue_script( 'sweetalert2.min' );

		wp_register_script( 'waoe.main', plugins_url( 'assets/js/main.js', WP_ACTIONS_ON_EVENTS_PLUGIN_FILE_FULL_PATH ), array(
			'bootstrap.min'
		), WP_ACTIONS_ON_EVENTS_PLUGIN_VERSION, true );
		wp_enqueue_script( 'waoe.main' );
	}

	public static function saveActionsWebhooksAjax(){
		$postData = $_POST;

		$action = [
			'event' => null,
			'action' => null,
			'webhook_url' => null
		];

		$postDataWebhook_url = filter_var($postData['webhook_url'], FILTER_SANITIZE_URL);
		if(filter_var($postDataWebhook_url, FILTER_VALIDATE_URL)){
		    $action['webhook_url'] = $postDataWebhook_url;
        }else{
		    wp_send_json_success(['success' => false, 'message' => 'Webhook URL is not valid']);
		    exit();
        }

		$postDataAction = filter_var($postData['action'], FILTER_SANITIZE_STRING);
		if(!empty($postDataAction)){
		    $action['action'] = esc_html($postDataAction);
        }else{
            wp_send_json_success(['success' => false, 'message' => 'Invalid action']);
            exit();
        }

		$postDataEvent = filter_var($postData['event'], FILTER_SANITIZE_STRING);
		if(!empty($postDataEvent)){
		    $action['event'] = esc_html($postDataEvent);
        }else{
            wp_send_json_success(['success' => false, 'message' => 'Invalid event']);
            exit();
        }

		self::updateOptions([
			'actions' => [
				$action['event'] => $action
			]
		]);
		wp_send_json_success(self::getOptions());
		exit();
	}

	public static function getActionsWebhooksAjax(){
		$postData = $_POST;

		$options = self::getOptions("actions");

        $postDataEvent = filter_var($postData['event'], FILTER_SANITIZE_STRING);

		if(array_key_exists($postDataEvent, $options) && !empty($options[$postDataEvent])){
			wp_send_json_success($options[$postDataEvent]);
			exit();
		}

		wp_send_json_success([]);
		exit();
	}

	/**
	 * @param array $options
	 *
	 * @return array|mixed
	 */
	public static function updateOptions( array $options = [] ) {
		$existingOptions = self::getOptions();
		foreach ($options as $item => $value){
			$existingOptions[$item] = $value;
		}

		update_option(WP_ACTIONS_ON_EVENTS_PLUGIN_OPTIONS_KEY, serialize($existingOptions));
		return $existingOptions;
	}

	public static function getOptions( $key = null ) {
		$options = get_option( WP_ACTIONS_ON_EVENTS_PLUGIN_OPTIONS_KEY );
		if(!empty($options)){

			$data = unserialize($options);

			if(!empty($key)){
				if(array_key_exists($key, $data)){
					return $data[$key];
				}else{
					return null;
				}
			}

			return unserialize($options);
		}

		return [];
	}
}