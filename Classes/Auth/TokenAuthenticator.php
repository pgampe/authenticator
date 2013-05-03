<?php
namespace Tx\Authenticator\Auth;

class TokenAuthenticator {

	/**
	 * @var \TYPO3\CMS\Core\Database\DatabaseConnection
	 */
	protected $database = NULL;

	/**
	 * @var string
	 */
	protected $userTable = 'be_users';

	/**
	 * @var string The field with the secret data
	 */
	protected $secretField = 'tx_authenticator_secret';

	/**
	 * @var string The field with the username identifier
	 */
	protected $usernameField = 'username';

	/**
	 * Initializes the database settings
	 */
	public function __construct() {
		if (TYPO3_MODE ===  'FE') {
			$this->userTable = 'fe_users';
		}
		$this->database = $GLOBALS['TYPO3_DB'];
	}

	/**
	 * Set a custom user table
	 *
	 * @param $userTable
	 */
	public function setUserTable($userTable) {
		$this->userTable = $userTable;
	}

	/**
	 * @param string $secretField The name of the field holding the secret data
	 */
	public function setSecretField($secretField) {
		$this->secretField = $secretField;
	}

	/**
	 * @param string $usernameField The name of the user identifier field
	 */
	public function setUsernameField($usernameField) {
		$this->usernameField = $usernameField;
	}

	/**
	 * Get the user data array
	 *
	 * @param $username
	 * @return array tokenkey, tokentype, tokentimer, tokencounter, tokenalgorithm, user
	 */
	public function getData($username) {
		$row = $this->database->exec_SELECTgetSingleRow(
			$this->secretField,
			$this->userTable,
			'username = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($username)
		);

		$secret = $row[$this->secretField];
		$data = unserialize(base64_decode($secret));

		if(empty($data)) {
			$data = $this->createEmptyData();
			// Fallback if the secret is stored directly
			if (!empty($secret)) {
				$data['tokenkey'] = $secret;
			}
		}
		return $data;
	}

	/**
	 * Store the secret information
	 *
	 * @param string $username The user identifier
	 * @param array $data The secret data array as in getData
	 */
	protected function putData($username, array $data) {
		if (empty($data)) {
			$secret = '';
		} else {
			$secret = base64_encode(serialize($data));
		}

		$this->database->exec_UPDATEquery(
			$this->userTable,
			$this->usernameField . ' = ' . $this->database->fullQuoteStr($username, $this->userTable),
			array($this->secretField => $secret)
		);

	}

	/**
	 * Set set the user data
	 *
	 * @param string $username The user identifier
	 * @param string $type Either TOTP or HOTP
	 * @param string $key The secret key
	 */
	public function setUser($username, $type = 'TOTP', $key = '') {
		$data = $this->getData($username);
		$type = strtoupper($type) === 'HOTP' ? 'HOTP' : 'TOTP';
		$data['tokentype'] = $type;
		$data['tokenkey'] = $key;
		$this->putData($username, $data);
	}

	function getUsers() {
		throw new Exception('not implemented!');
	}

	function createURL($username) {
		$data = $this->getData($username);

		// Oddity in the google authenticator... totp needs to be lowercase.
		$tokenType = strtolower($data['tokentype']);
		$key = $this->helperhex2b32($data['tokenkey']);

		// Token counter should be one more then current token value, otherwise
		// it gets confused
		$counter = $data['tokencounter'] + 1;

		if($tokenType == 'hotp') {
			$url = 'otpauth://' . $tokenType . '/' . urlencode($username) . ' - ' . urlencode($GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename']) . '?secret=' . $key . '&counter=' . $counter;
		} else {
			$url = 'otpauth://' . $tokenType . '/' . urlencode($username) . ' - ' . urlencode($GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename']) . '?secret=' . $key;
		}
		return $url;

	}

	/**
	 * Create an empty data structure, filled with some defaults
	 *
	 * @return array tokenkey, tokentype, tokentimer, tokencounter, tokenalgorithm, user
	 */
	function createEmptyData() {
		$data["tokenkey"] = ""; // the token key
		$data["tokentype"] = "TOTP"; // the token type
		$data["tokentimer"] = 30; // the token timer (For totp) and not supported by ga yet
		$data["tokencounter"] = 1; // the token counter for hotp
		$data["tokenalgorithm"] = "SHA1"; // the token algorithm (not supported by ga yet)
		$data["user"] = ""; // a place for implementors to store their own data

		return $data;
	}
}