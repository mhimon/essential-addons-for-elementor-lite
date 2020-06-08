<?php

namespace Essential_Addons_Elementor\Traits;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Trait Login_Registration is responsible for login or registering user using custom login | register widget.
 * @package Essential_Addons_Elementor\Traits
 */
trait Login_Registration {

	public function login_or_register_user() {
		// login or register form?
		if ( isset( $_POST['eael-login-submit'] ) ) {
			$this->log_user_in();
		} elseif ( isset( $_POST['eael-register-submit'] ) ) {
			$this->register_user();
		}
	}

	/**
	 * It logs the user in when the login form is submitted normally without AJAX.
	 */
	protected function log_user_in() {
		// before even thinking about login, check security and exit early if something is not right.
		if ( empty( $_POST['eael-login-nonce'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( $_POST['eael-login-nonce'], 'eael-login-action' ) ) {
			return;
		}


		if ( ! session_id() && ! headers_sent() ) {
			session_start();
		}
		do_action( 'eael/login-register/before-login');

		$user_login = ! empty( $_POST['eael-user-login'] ) ? sanitize_text_field( $_POST['eael-user-login'] ) : '';
		if ( false !== strpos( '@', $user_login ) ) {
			$user_login = sanitize_email( $user_login );
		}

		$password   = ! empty( $_POST['eael-user-password'] ) ? sanitize_text_field( $_POST['eael-user-password'] ) : '';
		$rememberme = ! empty( $_POST['eael-rememberme'] ) ? sanitize_text_field( $_POST['eael-rememberme'] ) : '';

		$credentials = [
			'user_login'    => $user_login,
			'user_password' => $password,
			'remember'      => ( 'forever' === $rememberme ),
		];

		$user_data = wp_signon( $credentials );

		if ( is_wp_error( $user_data ) ) {

			if ( isset( $user_data->errors['invalid_email'][0] ) ) {

				$_SESSION['eael_login_error'] = 'invalid_email';

			} elseif ( isset( $user_data->errors['invalid_username'][0] ) ) {

				$_SESSION['eael_login_error'] = 'invalid_username';

			} elseif ( isset( $user_data->errors['incorrect_password'][0] ) ) {

				$_SESSION['eael_login_error'] = 'incorrect_password';
			}
		} else {

			wp_set_current_user( $user_data->ID, $user_login );
			do_action( 'wp_login', $user_data->user_login, $user_data );
			do_action( 'eael/login-register/after-login', $user_data->user_login, $user_data );

			if ( !empty(  $_POST['redirect_to'] ) ) {
				wp_safe_redirect( $_POST['redirect_to'] );
				exit();
			}
		}
	}

	/**
	 * It register the user in when the registration form is submitted normally without AJAX.
	 */
	protected function register_user() {
		// perform registration....
	}
}