<?php
/**
 * Restrict form to all selected members
 *
 * Motherclass for all Restrictions
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package TorroForms/Restrictions
 * @version 1.0.0alpha1
 * @since   1.0.0
 * @license GPL 2
 *
 * Copyright 2015 awesome.ug (support@awesome.ug)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Torro_Access_Control_Selected_Members extends Torro_Access_Control {
	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Initializing.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		parent::__construct();
	}

	protected function init() {
		$this->title = __( 'Selected Members', 'torro-forms' );
		$this->name = 'selectedmembers';

		$this->option_name = __( 'Selected Members of site', 'torro-forms' );

		add_action( 'torro_formbuilder_save', array( $this, 'save' ), 10, 1 );

		$this->settings_fields = array(
			'invitations'			=> array(
				'title'					=> __( 'Invitation Mail Template', 'torro-forms' ),
				'description'			=> __( 'Setup Mail Templates for the Invitation Mail for selected Members.', 'torro-forms' ),
				'type'					=> 'title',
			),
			'invite_from_name'		=> array(
				'title'					=> __( 'From Name', 'torro-forms' ),
				'description'			=> __( 'The Mail Sender Name.', 'torro-forms' ),
				'type'					=> 'text',
				'default'				=> get_bloginfo( 'name' ),
			),
			'invite_from'			=> array(
				'title'					=> __( 'From Email', 'torro-forms' ),
				'description'			=> __( 'The Mail Sender Email.', 'torro-forms' ),
				'type'					=> 'text',
				'default'				=> get_option( 'admin_email' ),
			),
			'invite_subject'		=> array(
				'title'					=> __( 'Subject', 'torro-forms' ),
				'description'			=> __( 'The Subject of the Mail.', 'torro-forms' ),
				'type'					=> 'text',
			),
			'invite_text'			=> array(
				'title'					=> __( 'Email Text', 'torro-forms' ),
				'description'			=> __( 'The Text of the Mail.', 'torro-forms' ),
				'type'					=> 'wp_editor',
			),
			'reinvitations'			=> array(
				'title'					=> __( 'Reinvitation Mail Template', 'torro-forms' ),
				'description'			=> __( 'Setup Mail Templates for the Reinvitation Mail for selected Members.', 'torro-forms' ),
				'type'					=> 'title',
			),
			'reinvite_from_name'	=> array(
				'title'					=> __( 'From Name', 'torro-forms' ),
				'description'			=> __( 'The Mail Sender Name.', 'torro-forms' ),
				'type'					=> 'text',
				'default'				=> get_bloginfo( 'name' ),
			),
			'reinvite_from'			=> array(
				'title'					=> __( 'From Email', 'torro-forms' ),
				'description'			=> __( 'The Mail Sender Email.', 'torro-forms' ),
				'type'					=> 'text',
				'default'				=> get_option( 'admin_email' ),
			),
			'reinvite_subject'		=> array(
				'title'					=> __( 'Subject', 'torro-forms' ),
				'description'			=> __( 'The Subject of the Email.', 'torro-forms' ),
				'type'					=> 'text',
				'default'				=> get_option( 'admin_email' ),
			),
			'reinvite_text'			=> array(
				'title'					=> __( 'Email Text', 'torro-forms' ),
				'description'			=> __( 'The Text of the Mail.', 'torro-forms' ),
				'type'					=> 'wp_editor',
			)
		);
	}
	/**
	 * Saving data
	 *
	 * @param int $form_id
	 *
	 * @since 1.0.0
	 */
	public function save( $form_id ) {
		global $wpdb;

		/**
		 * Saving access-control options
		 */
		if ( array_key_exists( 'form_access_controls_selectedmembers_same_users', $_POST ) ) {
			$access_controls_same_users = $_POST['form_access_controls_selectedmembers_same_users'];
			update_post_meta( $form_id, 'form_access_controls_selectedmembers_same_users', $access_controls_same_users );
		} else {
			update_post_meta( $form_id, 'form_access_controls_selectedmembers_same_users', '' );
		}

		/**
		 * Saving access-control options
		 */
		$add_participants_option = $_POST['form_add_participants_option'];
		update_post_meta( $form_id, 'add_participants_option', $add_participants_option );

		/**
		 * Saving participants
		 */
		$form_participants = $_POST['form_participants'];

		$sql = "DELETE FROM $wpdb->torro_participants WHERE form_id = %d";
		$sql = $wpdb->prepare( $sql, $form_id );
		$wpdb->query( $sql );

		if( ! empty( $form_participants ) ) {
			$torro_participant_ids = explode( ',', $form_participants );

			if ( 0 < count( $torro_participant_ids ) ) {
				foreach ( $torro_participant_ids as $user_id ) {
					$wpdb->insert( $wpdb->torro_participants, array(
						'form_id' => $form_id,
						'user_id' => $user_id,
					) );
				}
			}
		}
	}

	/**
	 * Enqueue Scripts
	 */
	public function admin_scripts() {
		$translation = array(
			'delete'								=> __( 'Delete', 'torro-forms' ),
			'yes'									=> __( 'Yes', 'torro-forms' ),
			'no'									=> __( 'No', 'torro-forms' ),
			'just_added'							=> __( 'just added', 'torro-forms' ),
			'invitations_sent_successfully'			=> __( 'Invitations sent successfully!', 'torro-forms' ),
			'invitations_not_sent_successfully'		=> __( 'Invitations could not be sent!', 'torro-forms' ),
			'reinvitations_sent_successfully'		=> __( 'Renvitations sent successfully!', 'torro-forms' ),
			'reinvitations_not_sent_successfully'	=> __( 'Renvitations could not be sent!', 'torro-forms' ),
			'added_participants'					=> __( 'participant/s', 'torro-forms' ),
			'nonce_add_participants_allmembers'		=> torro()->ajax()->get_nonce( 'add_participants_allmembers' ),
			'nonce_invite_participants'				=> torro()->ajax()->get_nonce( 'invite_participants' ),
			'nonce_get_invite_text'				=> torro()->ajax()->get_nonce( 'get_invite_text' ),
		);

		wp_enqueue_script( 'torro-access-controls-selected-members', torro()->get_asset_url( 'access-controls-selected-members', 'js' ), array( 'torro-form-edit' ) );
		wp_localize_script( 'torro-access-controls-selected-members', 'translation_sm', $translation );
	}

	/**
	 * Registers and enqueues admin-specific styles.
	 *
	 * @since 1.0.0
	 */
	public function admin_styles() {
		wp_enqueue_style( 'torro-access-controls-selected-members', torro()->get_asset_url( 'access-controls-selected-members', 'css' ), array( 'torro-form-edit' ) );
	}

	/**
	 * Adds content to the option
	 */
	public function option_content() {
		global $wpdb, $post;

		$form_id = $post->ID;

		/**
		 * Add participants
		 */
		$html  = '<div id="acl-member-list">';

		$options = apply_filters( 'form_add_participants_options', array( 'allmembers' => __( 'all members', 'torro-forms' ) ) );

		$add_participants_option = get_post_meta( $form_id, 'add_participants_option', true );

		$html .= '<div id="torro-add-participants-options" class="form-fields">';
		$html .= '<label for"form_add_participants_option">' . esc_html__( 'Members', 'torro-forms' ) . '</label>';
		$html .= '<select id="form-add-participants-option" name="form_add_participants_option">';
		foreach ( $options as $name => $value ) {
			$selected = '';
			if ( $name === $add_participants_option ) {
				$selected = ' selected="selected"';
			}
			$html .= '<option value="' . $name . '"' . $selected . '>' . $value . '</option>';
		}
		$html .= '</select> ';
		$html .= '<input type="button" class="form-add-participants-allmembers-button button" id="form-add-participants-allmembers-button" value="' . esc_attr__( 'Add', 'torro-forms' ) . '" />';
		$html .= '</div>';

		$html .= '<div id="torro-invite-actions" class="form-fields">';
		$html .= '<label for"torro-invite-participants">' . esc_html__( 'Invitations', 'torro-forms' ) . '</label>';
		$html .= '<input type="button" id="torro-invite-participants-button" name="invite_participants" value="' . esc_html__( 'Invite', 'torro-forms' ) . '" class="button" /> ';
		$html .= '<input type="button" id="torro-reinvite-participants-button" name="reinvite_participants" value="' . esc_html__( 'Reinvite', 'torro-forms' ) . '" class="button" /> ';
		$html .= '<input type="button" id="torro-send-invitations-button" name="send_invitations" value="' . esc_html__( 'Send Invitations', 'torro-forms' ) . '" class="button-primary" />';
		$html .= '</div>';

		ob_start();
		wp_editor( '', 'torro_invite_text' );
		$editor = ob_get_clean();

		$html .= '<div id="torro-invite-email">';
		$html .= '<div class="form-fields">';
		$html .= '<div><label for="torro_invite_from_name">' . esc_attr__( 'From', 'torro-forms' ) .'</label>';
		$html .= '<input type="text" name="torro_invite_from_name" value="" /></div>';
		$html .= '<div><label for="torro_invite_from">' . esc_attr__( 'Email', 'torro-forms' ) .'</label>';
		$html .= '<input type="text" name="torro_invite_from" value="" /></div>';
		$html .= '<div><label for="torro_invite_subject">' . esc_attr__( 'Subject', 'torro-forms' ) .'</label>';
		$html .= '<input type="text" name="torro_invite_subject" value="" /></div>';
		$html .= '</div>';
		$html .= '<div id="torro-invite-text">';
		$html .= $editor;
		$html .= '</div>';
		$html .= '</div>';

		// Hooking in
		ob_start();
		do_action( 'form_add_participants_content' );
		$html .= ob_get_clean();

		/**
		 * Participiants List
		 */
		$html .= '<div id="form-participants-list">';
		$html .= $this->get_participant_list( $form_id, 0, 10 );
		$html .= '</div>';

		/**
		 * Userfilter
		 */
		$html  .= '<div id="acl-user-filter" class="actions">';
		$access_controls_same_users = get_post_meta( $form_id, 'form_access_controls_selectedmembers_same_users', true );
		$checked = 'yes' === $access_controls_same_users ? ' checked' : '';

		$html .= '<input type="checkbox" name="form_access_controls_selectedmembers_same_users" value="yes" ' . $checked . '/>';
		$html .= '<label for="form_access_controls_selectedmembers_same_users">' . esc_html__( 'Prevent multiple entries from same User', 'torro-forms' ) . '</label>';
		$html .= '</div>';

		return $html;
	}

	public function get_participant_list( $form_id, $start, $limit ){
		global $wpdb;

		$sql = $wpdb->prepare( "SELECT user_id FROM $wpdb->torro_participants WHERE form_id = %s LIMIT %d, %d", $form_id, $start, $limit );
		$user_ids = $wpdb->get_col( $sql );

		$users = array();

		if ( is_array( $user_ids ) && 0 < count( $user_ids ) ) {
			$users = get_users( array(
				                    'include'	=> $user_ids,
				                    'orderby'	=> 'ID',
			                    ) );
		}

		/**
		 * Participiants list
		 */
		$html  = '<table class="wp-list-table widefat">';
		$html .= '<thead>';
		$html .= '<tr>';
		$html .= '<th>' . esc_html__( 'ID', 'torro-forms' ) . '</th>';
		$html .= '<th>' . esc_html__( 'User nicename', 'torro-forms' ) . '</th>';
		$html .= '<th>' . esc_html__( 'Display name', 'torro-forms' ) . '</th>';
		$html .= '<th>' . esc_html__( 'Email', 'torro-forms' ) . '</th>';
		$html .= '<th>' . esc_html__( 'Status', 'torro-forms' ) . '</th>';
		$html .= '<th><a class="form-remove-all-participants">' . esc_html__( 'Delete all', 'torro-forms' ) . '</a></th>';
		$html .= '</tr>';
		$html .= '</thead>';

		$html .= '<tbody>';

		$form_participants_value = '';

		if ( is_array( $users ) && 0 < count( $users ) ) {
			// Content
			foreach ( $users as $user ) {
				if ( torro()->forms()->get( $form_id )->has_participated( $user->ID ) ) {
					$user_css = ' finished';
					$user_text = __( 'finished', 'torro-forms' );
				} else {
					$user_text = __( 'new', 'torro-forms' );
					$user_css = ' new';
				}

				$html .= '<tr class="participant participant-user-' . $user->ID . $user_css . '">';
				$html .= '<td>' . esc_html( $user->ID ) . '</td>';
				$html .= '<td>' . esc_html( $user->user_nicename ) . '</td>';
				$html .= '<td>' . esc_html( $user->display_name ) . '</td>';
				$html .= '<td>' . esc_html( $user->user_email ) . '</td>';
				$html .= '<td>' . esc_html( $user_text ) . '</td>';
				$html .= '<td><a class="button form-delete-participant" rel="' . $user->ID . '">' . esc_html__( 'Delete', 'torro-forms' ) . '</a></td>';
				$html .= '</tr>';
			}

			$form_participants_value = implode( ',', $user_ids );
		}

		$html .= '<tr class="no-users-found">';
		$html .= '<td colspan="6">' . esc_attr__( 'No Users found.', 'torro-forms' ) . '</td>';
		$html .= '</tr>';

		$html .= '</tbody>';

		$html .= '</table>';

		/**
		 * Participiants Statistics
		 */
		$user_count = count( $users );
		$html .= '<div id="form-participants-status" class="form-participants-status">';
		$html .= '<p>' . sprintf( _n( '%s participant', '%s participants', $user_count, 'torro-forms' ), number_format_i18n( $user_count ) ) . '</p>';
		$html .= '</div>';

		$html .= '<input type="hidden" id="form-participants" name="form_participants" value="' . $form_participants_value . '" />';
		$html .= '<input type="hidden" id="form-participants-count" name="form-participants-count" value="' . count( $users ) . '" />';
		$html .= '</div>';

		return $html;
	}

	/**
	 * Checks if the user can pass
	 */
	public function check() {
		$torro_form_id = torro()->forms()->get_current_form_id();

		if ( ! is_user_logged_in() ) {
			$this->add_message( 'error', __( 'You have to be logged in to participate.', 'torro-forms' ) );

			return false;
		}

		if ( ! $this->is_participant() ) {
			$this->add_message( 'error', __( 'You are not allowed to participate.', 'torro-forms' ) );

			return false;
		}

		$access_controls_same_users = get_post_meta( $torro_form_id, 'form_access_controls_selectedmembers_same_users', true );

		if ( 'yes' === $access_controls_same_users && torro()->forms( $torro_form_id )->has_participated() ) {
			$this->add_message( 'error', __( 'You have already entered your data.', 'torro-forms' ) );

			return false;
		}

		return true;
	}

	/**
	 * Checks if a user can participate
	 *
	 * @param int $form_id
	 * @param int $user_id
	 *
	 * @return boolean $can_participate
	 * @since 1.0.0
	 */
	public function is_participant( $user_id = null ) {
		global $wpdb, $current_user;

		$torro_form_id = torro()->forms()->get_current_form_id();

		// Setting up user ID
		if ( null === $user_id ) {
			get_currentuserinfo();
			$user_id = $user_id = $current_user->ID;
		}

		$sql = $wpdb->prepare( "SELECT user_id FROM $wpdb->torro_participants WHERE form_id = %d", $torro_form_id );
		$user_ids = $wpdb->get_col( $sql );

		if ( ! in_array( $user_id, $user_ids ) ) {
			return false;
		}

		return true;
	}
}

torro()->access_controls()->register( 'Torro_Access_Control_Selected_Members' );
