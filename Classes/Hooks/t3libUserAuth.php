<?php

require_once(t3lib_extMgm::extPath('authenticator') . 'Classes/Auth/GoogleAuthenticator.php');
require_once(t3lib_extMgm::extPath('authenticator') . 'Library/phpqrcode/qrlib.php');

class tx_Authenticator_Hooks_t3libUserAuth {
	function postUserLookUp(&$params, &$caller) {
		if($GLOBALS['BE_USER']->user['uid']) {
				//ignore two factor, if no secret
			if(trim($GLOBALS['BE_USER']->user['tx_authenticator_secret']) !== '') {
				// check wether secret was checked in session before
				if(!$this->isValidTwoFactorInSession()) {
					$authenticator  = new tx_Authenticator_Auth_GoogleAuthenticator();
					$postTokenCheck = $authenticator->authenticateUser($GLOBALS['BE_USER']->user['username'], t3lib_div::_GP('oneTimeSecret'));
					if($postTokenCheck) {
						$this->setValidTwoFactorInSession();
					} else {
						$this->showForm($authenticator, $postTokenCheck, t3lib_div::_GP('oneTimeSecret'));
					}
				}
			} else {
				$authenticator = new tx_Authenticator_Auth_GoogleAuthenticator();
				$authenticator->setUser($GLOBALS['BE_USER']->user['username'], 'TOTP');
			}
		}
	}
	function isValidTwoFactorInSession() {
		return $GLOBALS['BE_USER']->getSessionData('authenticatorIsValidTwoFactor') === TRUE;
	}
	function setValidTwoFactorInSession() {
		$GLOBALS['BE_USER']->setAndSaveSessionData('authenticatorIsValidTwoFactor', TRUE);
	}
	function showForm(tx_Authenticator_Auth_GoogleAuthenticator $auth, $postTokenCheck, $token) {
		if($token != '') {
			echo 'error';
		}

		echo '<form><input type="text" name="oneTimeSecret"><input type="submit"></form>';
		echo $auth->createURL($GLOBALS['BE_USER']->user['username']);
		print_r($auth->errorList);

			ob_start();
			QRcode::png(
				$auth->createURL($GLOBALS['BE_USER']->user['username']),
				false,
				10,
				10
			);
			$buffer = ob_get_clean();
		header('Content-Type: text/html');
		echo '<img src="data:image/png;base64,' . base64_encode($buffer) . '">';
		echo 'more info: http://support.google.com/accounts/bin/answer.py?hl=de&answer=1066447';
		die('show form');
	}
}