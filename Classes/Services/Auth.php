<?php
/**
 * Created by JetBrains PhpStorm.
 * User: kay
 * Date: 15.05.12
 * Time: 21:59
 * To change this template use File | Settings | File Templates.
 */

class tx_Authenticator_Services_Auth extends tx_sv_authbase {

	function authUser($user) {
		/**
		 * check if user has a secret, if not return true
		 *
		 * else ask for onetime password!
		 */

		/**
		 * if all
		 *
		 *   check for computer cookie
		 *
		 *   check if cookie is known to database
		 *
		 *   check if cookie is still valid -> against db
		 *
		 *   renew cookie
		 *
		 * else
		 *
		 *   show form element for secret
		 *
		 */

		//t3lib_utility_Http::redirect

		#print_r($GLOBALS['BE_USER']);

		#die();

		return true;
	}
}