<?php
namespace Tx\Authenticator\Auth;

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class TokenAuthenticator
 *
 * @package Tx\Authenticator\Auth
 */
class TokenAuthenticator implements SingletonInterface {

	/**
	 * @var string The field with the secret data
	 */
	protected $secretField = 'tx_authenticator_secret';

	/**
	 * @var \TYPO3\CMS\Core\Authentication\AbstractUserAuthentication
	 */
	protected $user = NULL;

	/**
	 * User data array from the user object, effectively the database row of the user
	 *
	 * @var array $this->user->user
	 */
	protected $userData = array();

	/**
	 * @param \TYPO3\CMS\Core\Authentication\AbstractUserAuthentication $user
	 */
	public function __construct($user) {
		$this->setUser($user);
	}

	/**
	 * @param string $secretField The name of the field holding the secret data
	 */
	public function setSecretField($secretField) {
		$this->secretField = $secretField;
	}

	/**
	 * Set the current user context
	 *
	 * @param \TYPO3\CMS\Core\Authentication\AbstractUserAuthentication $user
	 * @throws \UnexpectedValueException
	 */
	public function setUser(\TYPO3\CMS\Core\Authentication\AbstractUserAuthentication $user) {
		$this->user = $user;
		if (is_array($user->user)) {
			$this->userData = $user->user;
		} else {
			throw new \UnexpectedValueException('The user object has not been initialized - the user data is missing.', 1396181716);
		}
	}

	/**
	 * Set the user data
	 *
	 * @param string $type Either TOTP or HOTP
	 * @param string $key The secret key
	 */
	public function createToken($type = 'TOTP', $key = '') {
		$data = $this->getData();
		$type = strtoupper($type) === 'HOTP' ? 'HOTP' : 'TOTP';

		$data['tokentype'] = $type;
		if (!empty($key)) {
			$data['tokenkey'] = $key;
		} else {
			$data['tokenkey'] = $this->createBase32Key();
		}

		$this->putData($data);
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
	 * Get the user data array
	 *
	 * @return array tokenkey, tokentype, tokentimer, tokencounter, tokenalgorithm, user
	 */
	public function getData() {
		$data = unserialize(base64_decode($this->userData[$this->secretField]));

		if (empty($data)) {
			$data = $this->createEmptyData();
			// Fallback if the secret is stored directly
			if (!empty($this->userData[$this->secretField])) {
				$data['tokenkey'] = $this->userData[$this->secretField];
			}
		}
		return $data;
	}

	/**
	 * Store the secret information
	 *
	 * @param array $data The secret data array as in getData
	 */
	protected function putData(array $data) {
		if (empty($data)) {
			$secret = '';
			$data = NULL;
		} else {
			$secret = base64_encode(serialize($data));
		}

		$this->getDatabaseConnection()->exec_UPDATEquery(
			$this->user->user_table,
			$this->user->userid_column . ' = ' . $this->userData[$this->user->userid_column],
			array($this->secretField => $secret)
		);
	}

	/**
	 * Creates the authenticator URL for the given user
	 *
	 * @param string $name The name of the token, will be urlencoded automatically
	 * @return string The full url (for QR Code images)
	 */
	public function createUrlForUser($name) {
		$data = $this->getData();
		$key = $data['tokenkey'];
		$name = urlencode($name);

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
	 * Gets an instance of the one time password generator
	 *
	 * @param string $secret The secret to use
	 * @param array $options The array with options
	 * @returns \OTPHP\TOTP
	 */
	protected function getOneTimePasswordGenerator($secret, array $options) {
		return GeneralUtility::makeInstance('OTPHP\\TOTP',$secret, $options);
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