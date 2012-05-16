<?php

require_once(t3lib_extMgm::extPath('authenticator').'Library/ga4php-0.1-alpha/lib/ga4php.php');

class tx_Authenticator_Auth_GoogleAuthenticator extends GoogleAuthenticator{
	function getData($username) {
		$data = array();
		$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'tx_authenticator_secret',
			'be_users',
			'username = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($username)
		);
		return $rows[0]['tx_authenticator_secret'];
	}
	function putData($username, $data) {
		$rows = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
			'be_users',
			'username = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($username),
			array('tx_authenticator_secret' => $data)
		);
	}
	function getUsers() {
		throw new Exception('not implemented!');
	}
}