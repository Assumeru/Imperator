<?php
/**
 * Plugin Name: Imperator
 * Version: 1.0
 * Author: Evil Eye
 * Text Domain: EE_imperator
 * Domain Path: /i18n
 */
//Edit this if you're using a different install path
require_once ABSPATH.'/imperator/app/imperator.class.php';

final class EE_Imperator {
	private static $initialised = false;
	private $inputs = array(
		'max_chat_message_age' => array(
			'title' => 'Max. chat message age',
			'description' => 'The time (in seconds) until a chat message is deleted.',
			'type' => 'number',
			'min' => 0
		),
		'min_chat_messages_preserved' => array(
			'title' => 'Min. chat messages preserved',
			'description' => 'The minimum number of chat messages to preserve.',
			'type' => 'number',
			'min' => 0
		),
		'max_finished_game_age' => array(
			'title' => 'Max. finished game age',
			'description' => 'The time (in seconds) until a finished game is deleted.',
			'type' => 'number',
			'min' => 0
		),
		'inactive_game_time' => array(
			'title' => 'Max. inactive game time',
			'description' => 'The time (in seconds) until an inactive game is deleted.',
			'type' => 'number',
			'min' => 0
		),
		'longpolling_timeout' => array(
			'title' => 'Long polling timeout',
			'description' => 'The time (in seconds) between each update check.',
			'type' => 'number',
			'min' => 0
		),
		'longpolling_tries' => array(
			'title' => 'Max. long polling tries',
			'description' => 'The maximum number of long polling tries before timing out. Set to 0 for infinite tries.',
			'type' => 'number',
			'min' => 0
		),
		'websocket_address' => array(
			'title' => 'WebSocket address',
			'description' => 'The address websockets connect to.',
			'type' => 'text'
		),
		'websocket_port' => array(
			'title' => 'WebSocket port',
			'description' => 'The port websockets connect to.',
			'type' => 'text'
		),
		'websocket_path' => array(
			'title' => 'WebSocket path',
			'description' => 'The path websockets connect to.',
			'type' => 'text'
		),
		'log_path' => array(
			'title' => 'Log path',
			'description' => 'The path error.log and output.log are written to.',
			'type' => 'text'
		),
		'img_url' => array(
			'title' => 'Image url',
			'description' => 'The url to get images from. Needs to contain %1$s.',
			'type' => 'text'
		),
		'js_url' => array(
			'title' => 'Javascript url',
			'description' => 'The url to get javascript from. Needs to contain %1$s.',
			'type' => 'text'
		),
		'css_url' => array(
			'title' => 'CSS url',
			'description' => 'The url to get stylesheets from. Needs to contain %1$s.',
			'type' => 'text'
		)
	);

	public static function init() {
		if(!self::$initialised) {
			self::$initialised = true;
			new self();
		}
	}

	private function __construct() {
		$this->setUpHooks();
	}

	private function setUpHooks() {
		register_activation_hook(__FILE__, array($this, 'install'));
		register_deactivation_hook(__FILE__, array($this, 'deactivate'));
		add_action('EE_imperator_run_cron', array($this, 'runCron'));
		add_action('admin_init', array($this, 'initAdmin'));
		add_action('admin_menu', array($this, 'showAdminMenu'));
	}

	public function install() {
		wp_schedule_event(time(), 'daily', 'EE_imperator_run_cron');
		$db = \imperator\Imperator::getDatabaseManager();
		$db->dropTables();
		$db->createTables();
		update_option('EE_imperator_settings', \imperator\outside\wordpress\WordPressSettings::$defaultSettings);
	}

	public function runCron() {
		$cron = new \imperator\Cron();
		$numChats = $cron->cleanChat();
		$numGames = $cron->cleanGames();
		\imperator\Imperator::getLogger()->log(\imperator\Logger::LEVEL_DEBUG, 'EE_imperator cleanup task completed: '.$numChats.' chat messages and '.$numGames.' games deleted.');
	}

	public function deactivate() {
		wp_clear_scheduled_hook('EE_imperator_run_cron');
	}

	public function initAdmin() {
        register_setting('EE_imperator', 'EE_imperator_settings');
	}

	public function showAdminMenu() {
		add_options_page('Imperator', 'Imperator', 'manage_options', 'EE_imperator', array($this, 'showSettings'));
	}

	public function showSettings() {
		$settings = get_option('EE_imperator_settings');
?>
<div class="wrap">
	<h2>Imperator</h2>
	<form method="post" action="options.php">
		<table class="form-table">
<?php
		settings_fields('EE_imperator');
		foreach($this->inputs as $id => $input) {
			$this->renderInput($settings, $id, $input);
		}
?>
		</table>
		<?php submit_button(); ?>
	</form>
</div>
<?php
	}

	private function renderInput($settings, $id, $setting) {
?>
			<tr>
				<th><?php echo $setting['title']; ?></th>
				<td><input name="EE_imperator_settings[<?php echo $id; ?>]" type="<?php echo $setting['type']; ?>" <?php if(isset($setting['min'])) { echo 'min="', $setting['min'],'"'; } ?> value="<?php echo $settings[$id]; ?>" />
				<p class="description"><?php echo $setting['description']; ?></p></td>
			</tr>
<?php
	}
}

EE_Imperator::init();