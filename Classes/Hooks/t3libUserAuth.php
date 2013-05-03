<?php
namespace Tx\Authenticator\Hooks;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
				// Ignore two factor authentication, if the user has no secret yet
				if (trim($user->user['tx_authenticator_secret']) !== '') {
					// check whether secret was checked in session before
					if (!$this->isValidTwoFactorInSession($user)) {
						/** @var \Tx\Authenticator\Auth\TokenAuthenticator $authenticator */
						$authenticator = GeneralUtility::makeInstance('Tx\\Authenticator\\Auth\\TokenAuthenticator');
						//$postTokenCheck =
						//	$authenticator->authenticateUser($user->user['username'], t3lib_div::_GP('oneTimeSecret'));
						$postTokenCheck = TRUE;
						if ($postTokenCheck) {
							$this->setValidTwoFactorInSession($user);
						} else {
							$this->showForm($authenticator, GeneralUtility::_GP('oneTimeSecret'), $user);
						}
					}
				} else {
					/** @var \Tx\Authenticator\Auth\TokenAuthenticator $authenticator */
					$authenticator = GeneralUtility::makeInstance('Tx\\Authenticator\\Auth\\TokenAuthenticator');
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

	function showForm(\Tx\Authenticator\Auth\TokenAuthenticator $auth, $token, $user) {
		$error = ($token != '');

		/** @var \TYPO3\CMS\Fluid\View\StandaloneView $view */
		$view = GeneralUtility::makeInstance('TYPO3\\CMS\\Fluid\\View\\StandaloneView');
		$view->setTemplatePathAndFilename(
			ExtensionManagementUtility::extPath('authenticator') . 'Resources/Private/Templates/tokenform.html'
		);
		$view->assign('error', $error);
		$view->assign('tokenSecretUrl', $auth->createURL($user->user['username']));
		echo $view->render();
		die();
	}

}