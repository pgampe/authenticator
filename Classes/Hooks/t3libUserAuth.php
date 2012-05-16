<?php

require_once(t3lib_extMgm::extPath('authenticator') . 'Classes/Auth/GoogleAuthenticator.php');
require_once(t3lib_extMgm::extPath('authenticator') . 'Library/phpqrcode/qrlib.php');

class tx_Authenticator_Hooks_t3libUserAuth {
	function postUserLookUp(&$params, &$caller) {
		if(TYPO3_MODE == 'BE') {
			$user = $GLOBALS['BE_USER'];
		} elseif(TYPO3_MODE == 'FE') {
			$user = $GLOBALS['FE_USER'];
		}
		if($user) {
			if($user->user['uid']) {
					//ignore two factor, if no secret
				if(trim($user->user['tx_authenticator_secret']) !== '') {
					// check wether secret was checked in session before
					if(!$this->isValidTwoFactorInSession($user)) {
						$authenticator  = new tx_Authenticator_Auth_GoogleAuthenticator();
						$postTokenCheck = $authenticator->authenticateUser($user->user['username'], t3lib_div::_GP('oneTimeSecret'));
						if($postTokenCheck) {
							$this->setValidTwoFactorInSession($user);
						} else {
							$this->showForm($authenticator, $postTokenCheck, t3lib_div::_GP('oneTimeSecret'), $user);
						}
					}
				} else {
					$authenticator = new tx_Authenticator_Auth_GoogleAuthenticator();
					$authenticator->setUser($user->user['username'], 'TOTP');
				}
			}
		}
	}
	function isValidTwoFactorInSession($user) {
		return $user->getSessionData('authenticatorIsValidTwoFactor') === TRUE;
	}
	function setValidTwoFactorInSession($user) {
		$user->setAndSaveSessionData('authenticatorIsValidTwoFactor', TRUE);
	}
	function showForm(tx_Authenticator_Auth_GoogleAuthenticator $auth, $postTokenCheck, $token, $user) {
		$error = ($token != '');

		$view = t3lib_div::makeInstance('Tx_Fluid_View_StandaloneView');
		$view->setTemplatePathAndFilename(t3lib_extMgm::extPath('authenticator') . 'Rescources/Private/Templates/tokenform.html');
		$view->assign('error',            $error);
		$view->assign('tokenSecretUrl',   $auth->createURL($user->user['username']));
		$view->assign('tokenImagebase64', $this->getQRCodeImage($auth->createURL($user->user['username'])));
		echo $view->render();
		die();
	}
	function getQRCodeImage($param) {
		ob_start();
		QRcode::png(
			$param,
			false,
			10,
			10
		);
		$buffer = ob_get_clean();
		header('Content-Type: text/html');
		return 'data:image/png;base64,' . base64_encode($buffer);

	}
}