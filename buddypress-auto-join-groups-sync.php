<?php
/**
 * Plugin Name:  BuddyPress Auto Join Groups Sync
 * Description:  This plugin basically setup a cron job for sync users groups using BuddyPress Auto Join Groups plugin.
 * Author:       BuddyDev
 * Version:      1.0.0
 * License:      GPLv2 or later (license.txt)
 */

// Do not allow direct access over web.
defined( 'ABSPATH' ) || exit;

/**
 *  Syncs users groups
 */
class ByddyPress_Auto_Join_Groups_Sync {

	/**
	 * Singleton.
	 *
	 * @var ByddyPress_Auto_Join_Groups_Sync
	 */
	private static $instance = null;

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->setup();
	}

	/**
	 * Boots the class.
	 *
	 * @return ByddyPress_Auto_Join_Groups_Sync
	 */
	public static function boot() {

		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Sets up hooks.
	 */
	private function setup() {
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

		// hook runs daily.
		add_action( 'bpajg_sync_users_groups', array( $this, 'sync' ) );
	}

	/**
	 * Schedule cron job.
	 */
	public function activate() {

		if ( ! function_exists( 'buddypress' ) ) {
			return;
		}

		if ( ! wp_next_scheduled( 'bpajg_sync_users_groups' ) ) {
			wp_schedule_event( time(), 'daily', 'bpajg_sync_users_groups' );
		}
	}

	/**
	 * Disable cron job.
	 */
	public function deactivate() {
		wp_unschedule_event( wp_next_scheduled( 'bpajg_sync_users_groups' ), 'bpajg_sync_users_groups' );
	}

	/**
	 * Deletes user periodically.
	 */
	public function sync() {

		if ( ! function_exists( 'buddypress' ) || ! function_exists( 'bp_auto_join_groups' ) ) {
			return;
		}

		$user_ids = get_users( array( 'fields' => 'ID' ) );

		if ( ! $user_ids ) {
			return;
		}

		$auto_joiner = \BuddyPress_Auto_Join_Groups\Core\Groups_Auto_Joiner::boot();

		foreach ( $user_ids as $user_id ) {
			$auto_joiner->sync_user_groups( $user_id );
		}
	}
}

// init.
ByddyPress_Auto_Join_Groups_Sync::boot();
