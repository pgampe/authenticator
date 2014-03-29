<?php
namespace Tx\Authenticator\Fields;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

include_once(ExtensionManagementUtility::extPath('authenticator') . 'Resources/Private/Php/phpqrcode/qrlib.php');

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
		$authenticator = GeneralUtility::makeInstance('Tx\\Authenticator\\Auth\\TokenAuthenticator');

		// Set random secret if empty
		if (trim($user->user['tx_authenticator_secret']) == '') {
			//$authenticator->setUser($user, 'TOTP');
		}

		$authUrl = $authenticator->createUrlForUser($user);
		$data = $authenticator->getData($user);
		$label = 'Auth key: ';

		$content = '<a href=' . $authUrl . '>' . $label . $data['tokenkey'] . '</a>';
		$content .= '<br />';
		$content .= '<img src="' . $this->getQRCodeImage($authUrl) . '">';

		return $content;
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