<?php
namespace Tx\Authenticator\Auth;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
require_once(ExtensionManagementUtility::extPath('authenticator') . 'Resources/Private/Php/otphp/lib/otphp.php');

/**
 * Class TokenAuthenticator
 *
 * @package Tx\Authenticator\Auth
 */
class TokenAuthenticator {

	/**
	 * @var string The field with the secret data
	 */
	protected $secretField = 'tx_authenticator_secret';

	/**
	 * @var string The field with the user identifier
	 */
	protected $usernameField = 'user';

	/**
	 * @param string $secretField The name of the field holding the secret data
	 */
	public function setSecretField($secretField) {
		$this->secretField = $secretField;
	}

	/**
	 * Get the user data array
	 *
	 * @param \TYPO3\CMS\Core\Authentication\AbstractUserAuthentication $user
	 * @return array tokenkey, tokentype, tokentimer, tokencounter, tokenalgorithm, user
	 */
	public function getData($user) {
		$data = unserialize(base64_decode($user->user[$this->secretField]));

		if (empty($data)) {
			$data = $this->createEmptyData();
			// Fallback if the secret is stored directly
			if (!empty($user->user[$this->secretField])) {
				$data['tokenkey'] = $user->user[$this->secretField];
			}
		}
		return $data;
	}

	/**
	 * Store the secret information
	 *
	 * @param \TYPO3\CMS\Core\Authentication\AbstractUserAuthentication $user The user identifier
	 * @param array $data The secret data array as in getData
	 */
	protected function putData($user, array $data) {
		if (empty($data)) {
			$secret = '';
			$data = NULL;
		} else {
			$secret = base64_encode(serialize($data));
		}

		$this->getDatabaseConnection()->exec_UPDATEquery(
			$user->user_table,
			$user->userid_column . ' = ' . $user->user[$user->userid_column],
			array($this->secretField => $secret)
		);
	}

	/**
	 * Set the user data
	 *
	 * @param \TYPO3\CMS\Core\Authentication\AbstractUserAuthentication $user The user identifier
	 * @param string $type Either TOTP or HOTP
	 * @param string $key The secret key
	 */
	public function setUser($user = NULL, $type = 'TOTP', $key = '') {
		$data = $this->getData($user);
		$type = strtoupper($type) === 'HOTP' ? 'HOTP' : 'TOTP';

		$data['tokentype'] = $type;
		if (!empty($key)) {
			$data['tokenkey'] = $key;
		} else {
			$data['tokenkey'] = $this->createBase32Key();
		}

		$this->putData($user, $data);
	}

	/**
	 * Creates the authenticator URL for the given user
	 *
	 * @param \TYPO3\CMS\Core\Authentication\AbstractUserAuthentication $user The user identifier
	 * @return string The full url (for QR Code images)
	 */
	public function createUrlForUser($user) {
		$data = $this->getData($user);
		$name = urlencode($user->user[$user->username_column]) . '-' . urlencode($GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename']);
		$key = $data['tokenkey'];

		// Oddity in the google authenticator... totp needs to be lowercase.
		$tokenType = strtolower($data['tokentype']);
		if ($tokenType === 'totp') {
			$url = 'otpauth://' . $tokenType . '/' . $name . '?secret=' . $key;
		} else {
			$url = 'otpauth://' . $tokenType . '/' . $name . '?secret=' . $key . '&counter=' . $data['tokencounter'];
		}
		return $url;

	}

	/**
	 * Create an empty data structure, filled with some defaults
	 *
	 * @return array tokenkey, tokentype, tokentimer, tokencounter, tokenalgorithm, user
	 */
	protected function createEmptyData() {
		$data["tokenkey"] = ""; // the token key
		$data["tokentype"] = "TOTP"; // the token type
		$data["tokentimer"] = 30; // the token timer (For totp) and not supported by ga yet
		$data["tokencounter"] = 1; // the token counter for hotp
		$data["tokenalgorithm"] = "SHA1"; // the token algorithm (not supported by ga yet)
		$data["user"] = ""; // a place for implementors to store their own data

		return $data;
	}

	/**
	 * Decodes a secret
	 *
	 * @param string $encodedSecret The serialized and base encoded secret
	 * @return string The secret
	 */
	protected function decode($encodedSecret) {
		$data = unserialize(base64_decode($encodedSecret));
		return $data['tokenkey'];
	}

	/**
	 * Verifies a token
	 *
	 * @param string $encodedSecret The serialized and base encoded secret
	 * @param integer $token
	 * @return bool
	 */
	public function verify($encodedSecret, $token) {
		$token = (integer) $token;
		$secret = $this->decode($encodedSecret);
		$totp = $this->getOneTimePasswordGenerator($secret, array());
		$success = $totp->verify_window($token, 2, 2);

		return $success;
	}

	/**
	 * Gets an instance of the one time password generator
	 *
	 * @param string $secret The secret to use
	 * @param array $options The array with options
	 * @returns \OTPHP\TOTP
	 */
	protected function getOneTimePasswordGenerator($secret, array $options) {
		return new \OTPHP\TOTP($secret, $options);
	}


	/**
	 * Creates a base 32 key (random)
	 *
	 * @return string
	 */
	function createBase32Key() {
		$alphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ234567";
		$key = "";
		for ($i = 0; $i < 16; $i++) {
			$offset = rand(0, strlen($alphabet) - 1);
			$key .= $alphabet[$offset];
		}
		return $key;
	}

	/**
	 * Returns the instance of the database connection
	 *
	 * @return \TYPO3\CMS\Core\Database\DatabaseConnection
	 */
	protected function getDatabaseConnection() {
		return $GLOBALS['TYPO3_DB'];
	}

}