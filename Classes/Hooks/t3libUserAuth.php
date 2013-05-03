<?php
namespace Tx\Authenthicator\Hooks;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

require_once(ExtensionManagementUtility::extPath('authenticator') . 'Classes/Auth/GoogleAuthenticator.php');
require_once(ExtensionManagementUtility::extPath('authenticator') . 'Resources/Private/Php/phpqrcode/qrlib.php');

class t3libUserAuth {
	function postUserLookUp(&$params, &$caller) {
		if (TYPO3_MODE == 'BE') {
			$user = $GLOBALS['BE_USER'];
		} elseif (TYPO3_MODE == 'FE') {
			$user = $GLOBALS['FE_USER'];
		}
		if ($user) {
			if ($user->user['uid']) {
				//ignore two factor, if no secret
				if (trim($user->user['tx_authenticator_secret']) !== '') {
					// check whether secret was checked in session before
					if (!$this->isValidTwoFactorInSession($user)) {
						$authenticator = new tx_Authenticator_Auth_GoogleAuthenticator();
						// @todo set user table
						$postTokenCheck =
							$authenticator->authenticateUser($user->user['username'], t3lib_div::_GP('oneTimeSecret'));
						if ($postTokenCheck) {
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
		return TRUE;
		return $user->getSessionData('authenticatorIsValidTwoFactor') === TRUE;
	}

	function setValidTwoFactorInSession($user) {
		$user->setAndSaveSessionData('authenticatorIsValidTwoFactor', TRUE);
	}

	function showForm(tx_Authenticator_Auth_GoogleAuthenticator $auth, $postTokenCheck, $token, $user) {
		$error = ($token != '');

		$view = t3lib_div::makeInstance('Tx_Fluid_View_StandaloneView');
		$view->setTemplatePathAndFilename(
			t3lib_extMgm::extPath('authenticator') . 'Resources/Private/Templates/tokenform.html'
		);
		$view->assign('error', $error);
		$view->assign('tokenSecretUrl', $auth->createURL($user->user['username']));
		echo $view->render();
		die();
	}

}