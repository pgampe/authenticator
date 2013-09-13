<?php
namespace Tx\Authenticator\Hooks;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Authentication\AbstractUserAuthentication;

require_once(ExtensionManagementUtility::extPath('authenticator') . 'Resources/Private/Php/phpqrcode/qrlib.php');

/**
 * Class UserAuthHook
 *
 * @package Tx\Authenticator\Hooks
 */
class UserAuthHook {

	/**
	 * Fills in the user data and checks the secret on the way
	 *
	 * @param $params
	 * @param $caller
	 */
	function postUserLookUp(&$params, &$caller) {
		if (TYPO3_MODE == 'BE') {
			$user = $GLOBALS['BE_USER'];
		} elseif (TYPO3_MODE == 'FE') {
			$user = $GLOBALS['FE_USER'];
		} else {
			// Unsupported mode, return early
			return;
		}
		if ($user instanceof AbstractUserAuthentication) {
			if ($user->user['uid']) {
				// Ignore two factor, if two factor authentication is disabled for this user
				if ($user->user['tx_authenticator_enabled'] & 1) {
					// Ignore two factor authentication, if the user has no secret yet
					if (trim($user->user['tx_authenticator_secret']) !== '') {
						// Check whether secret was checked in session before
						if (!$this->isValidTwoFactorInSession($user)) {
							/** @var \Tx\Authenticator\Auth\TokenAuthenticator $authenticator */
							$authenticator = GeneralUtility::makeInstance('Tx\\Authenticator\\Auth\\TokenAuthenticator');
							$postTokenCheck = $authenticator->verify($user->user['username'], GeneralUtility::_GP('oneTimeSecret'));
							if ($postTokenCheck) {
								$this->setValidTwoFactorInSession($user);
							} else {
								$this->showForm(GeneralUtility::_GP('oneTimeSecret'));
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
	}

	/**
	 * @param \TYPO3\CMS\Core\Authentication\AbstractUserAuthentication $user
	 * @return boolean TRUE if the user is already authenticated
	 */
	function isValidTwoFactorInSession($user) {
		return $user->getSessionData('authenticatorIsValidTwoFactor') === TRUE;
	}

	/**
	 * @param \TYPO3\CMS\Core\Authentication\AbstractUserAuthentication $user
	 */
	function setValidTwoFactorInSession($user) {
		$user->setAndSaveSessionData('authenticatorIsValidTwoFactor', TRUE);
	}

	/**
	 * Render the form and exit execution
	 *
	 * @param string $token Provided (wrong) token
	 */
	function showForm($token) {
		$error = ($token != '');

		/** @var \TYPO3\CMS\Fluid\View\StandaloneView $view */
		$view = GeneralUtility::makeInstance('TYPO3\\CMS\\Fluid\\View\\StandaloneView');
		$view->setTemplatePathAndFilename(
			ExtensionManagementUtility::extPath('authenticator') . 'Resources/Private/Templates/tokenform.html'
		);
		$view->assign('error', $error);
		echo $view->render();
		die();
	}

}