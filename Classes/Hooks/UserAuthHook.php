<?php
namespace Tx\Authenticator\Hooks;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class UserAuthHook
 *
 * @package Tx\Authenticator\Hooks
 */
class UserAuthHook {

	/** @var null|\TYPO3\CMS\Core\Authentication\AbstractUserAuthentication */
	protected $user = NULL;

	/**
	 * Check if authentication is needed and validate the secret
	 *
	 * @param $params
	 * @param $caller
	 */
	public function postUserLookUp(&$params, &$caller) {
		$this->injectUser();
		if ($this->user === NULL) {
			// Unsupported mode, return early
			return;
		}
		if ($this->canAuthenticate() && $this->needsAuthentication()) {
			/** @var \Tx\Authenticator\Auth\TokenAuthenticator $authenticator */
			$authenticator = GeneralUtility::makeInstance('Tx\\Authenticator\\Auth\\TokenAuthenticator');
			$postTokenCheck = $authenticator->verify($this->user->user['tx_authenticator_secret'], (integer) GeneralUtility::_GP('oneTimeSecret'));
			if ($postTokenCheck) {
				$this->setValidTwoFactorInSession();
			} else {
				$this->showForm(GeneralUtility::_GP('oneTimeSecret'));
			}
		}
	}

	/**
	 * Inject the user object depending on the current context
	 *
	 * @param \TYPO3\CMS\Core\Authentication\AbstractUserAuthentication $user
	 * @return void
	 */
	protected function injectUser($user = NULL) {
		if ($this->user !== NULL) {
			// user is already injected, return early
			return;
		}
		if ($user !== NULL) {
			$this->user = $user;
		} elseif (TYPO3_MODE == 'BE') {
			$this->user = $GLOBALS['BE_USER'];
		} elseif (TYPO3_MODE == 'FE') {
			$this->user = $GLOBALS['FE_USER'];
		}
		if (!$this->user instanceof \TYPO3\CMS\Core\Authentication\AbstractUserAuthentication) {
			// Invalid object or unsupported mode
			$this->user = NULL;
		}
	}

	/**
	 * Check for a valid user, enabled two factor authentication and if a secret is set
	 *
	 * @return boolean TRUE if the user can be authenticated
	 */
	protected function canAuthenticate() {
		if ($this->user->user['uid'] > 0
			&& $this->user->user['tx_authenticator_enabled'] & 1
			&& $this->user->user['tx_authenticator_secret'] !== ''
		) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 * Check whether the user is already authenticated
	 *
	 * @return boolean FALSE if the user is already authenticated
	 */
	protected function needsAuthentication() {
		return $this->user->getSessionData('authenticatorIsValidTwoFactor') !== TRUE;
	}

	/**
	 * Mark the current session as checked
	 *
	 * @return void
	 */
	protected function setValidTwoFactorInSession() {
		$this->user->setAndSaveSessionData('authenticatorIsValidTwoFactor', TRUE);
	}

	/**
	 * Render the form and exit execution
	 *
	 * @param string $token Provided (wrong) token
	 */
	protected function showForm($token) {
		$error = ($token != '');

		// Translation service is initialized too late in bootstrap
		$GLOBALS['LANG'] = GeneralUtility::makeInstance('TYPO3\CMS\Lang\LanguageService');
		if (isset($GLOBALS['BE_USER'])) {
			$GLOBALS['LANG']->init($GLOBALS['BE_USER']->uc['lang']);
		} else {
			// Empty language means: fall back to default (english)
			$GLOBALS['LANG']->init('');
		}

		/** @var \TYPO3\CMS\Fluid\View\StandaloneView $view */
		$view = GeneralUtility::makeInstance('TYPO3\\CMS\\Fluid\\View\\StandaloneView');
		$view->setTemplatePathAndFilename(
			ExtensionManagementUtility::extPath('authenticator') . 'Resources/Private/Templates/tokenform.html'
		);
		$view->assign('error', $error);
		$view->assign('token', $token);
		echo $view->render();

		// Remove translation service in frontend
		if (!isset($GLOBALS['BE_USER'])) {
			unset($GLOBALS['LANG']);
		}
		die();
	}

}