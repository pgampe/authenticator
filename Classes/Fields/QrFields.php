<?php
namespace Tx\Authenticator\Fields;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class QrFields
 *
 * Provides rendering for the backend user settings module
 *
 * @package Tx\Authenticator\Fields
 */
class QrFields {

	/**
	 * Hook function for the user settings module
	 *
	 * @param $PA
	 * @param $fsobj
	 * @return string
	 */
	public function getBackendSetting(&$PA, &$fsobj) {
		return $this->createImageAndText($GLOBALS['BE_USER']);
	}

	/**
	 * Creates the QR Code image and the corresponding text for the user settings module
	 *
	 * @param \TYPO3\CMS\Core\Authentication\AbstractUserAuthentication $user
	 * @return string The HTML for the user settings module
	 */
	protected function createImageAndText($user) {
		/** @var \Tx\Authenticator\Auth\TokenAuthenticator $authenticator */
		$authenticator = GeneralUtility::makeInstance('Tx\\Authenticator\\Auth\\TokenAuthenticator', $user);

		// Set random secret if empty
		if (trim($user->user['tx_authenticator_secret']) == '') {
			$authenticator->createToken('TOTP');
		}

		$label = $user->user[$user->username_column] . '-' . $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'];
		$authUrl = $authenticator->createUrlForUser($label);
		$data = $authenticator->getData();

		$image = $this->getQRCodeImage($authUrl);
		/** @var \TYPO3\CMS\Fluid\View\StandaloneView $view */
		$view = GeneralUtility::makeInstance('TYPO3\\CMS\\Fluid\\View\\StandaloneView');
		$view->setTemplatePathAndFilename(
			ExtensionManagementUtility::extPath('authenticator') . 'Resources/Private/Templates/BackendUserSettings.html'
		);
		$view->assign('authUrl', $authUrl);
		$view->assign('tokenKey', $data['tokenkey']);
		$view->assign('QrCode', $image);
		return $view->render();
	}

	/**
	 * Creates the actual QR code image and returns it as data URL
	 *
	 * @param string $url The url to encode
	 * @return string The image path (as data:base64)
	 */
	protected function getQRCodeImage($url) {
		ob_start();
		\QRcode::png(
			$url,
			FALSE,
			4,
			4
		);
		$buffer = ob_get_clean();
		header('Content-Type: text/html');
		return 'data:image/png;base64,' . base64_encode($buffer);

	}

}