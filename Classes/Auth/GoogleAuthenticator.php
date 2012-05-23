<?php

require_once(t3lib_extMgm::extPath('authenticator').'Library/ga4php-0.1-alpha/lib/ga4php.php');

class tx_Authenticator_Auth_GoogleAuthenticator extends GoogleAuthenticator{
	/**
	 * @var string
	 */
	protected $userTable = 'be_users';
	/**
	 * @param $table
	 */
	function setUserTable($table) {
		$this->userTable = $table;
	}

	function switchToFeUser() {
		$this->userTable = 'fe_users';
	}

	function getData($username) {
		$data = array();
		$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'tx_authenticator_secret',
			$this->userTable,
			'username = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($username)
		);
		return $rows[0]['tx_authenticator_secret'];
	}
	function putData($username, $data) {
		$rows = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
			$this->userTable,
			'username = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($username),
			array('tx_authenticator_secret' => $data)
		);
	}
	function getUsers() {
		throw new Exception('not implemented!');
	}
	function createURL($user) {
		// oddity in the google authenticator... hotp needs to be lowercase.
		$data = $this->internalGetData($user);

		$toktype = $data['tokentype'];
		$key = $this->helperhex2b32($data['tokenkey']);

		// token counter should be one more then current token value, otherwise
		// it gets confused
		$counter = $data['tokencounter'] + 1;
		$toktype = strtolower($toktype);
		if($toktype == 'hotp') {
			$url = 'otpauth://' . $toktype . '/' . $user . ' - ' . $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'] . '?secret=' . $key . '&counter=' . $counter;
		} else {
			$url = 'otpauth://' . $toktype . '/' . $user . ' - ' . $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'] . '?secret=' . $key;
		}
		return $url;

	}
}