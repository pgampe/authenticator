<?php
namespace Tx\Authenticator\Fields;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

include_once(ExtensionManagementUtility::extPath('authenticator') . 'Resources/Private/Php/phpqrcode/qrlib.php');

class QrFields {

	/**
	 * @param $PA
	 * @param $fobj
	 * @return string
	 */
	public function getField(&$PA, &$fobj) {
		return $this->createImageAndText(
			$PA['row'],
			$PA['table']
		);
	}

	/**
	 * @param $PA
	 * @param $fsobj
	 * @return string
	 */
	public function getBackendSetting(&$PA, &$fsobj) {
		return $this->createImageAndText(
			$GLOBALS['BE_USER']->user,
			$GLOBALS['BE_USER']->user_table
		);
	}

	private function createImageAndText($user, $table = '') {
		/** @var \Tx\Authenticator\Auth\TokenAuthenticator $authenticator */
		$authenticator = GeneralUtility::makeInstance('Tx\\Authenticator\\Auth\\TokenAuthenticator');
		if (!empty($table)) {
			$authenticator->setUserTable($table);
		}

		// Set random secret if empty
		if (trim($user['tx_authenticator_secret']) == '') {
			$authenticator->setUser($user['username'], 'TOTP');
		}

		$authUrl = $authenticator->createUrl($user['username']);
		$data = $authenticator->getData($user['username']);
		$label = 'Auth key: ';

		$buffer = '<a href=' . $authUrl . '>' . $label . $data['tokenkey'] . '</a>';
		$buffer .= '<br />';
		$buffer .= '<img src="' . $this->getQRCodeImage($authUrl) . '">';

		return $buffer;
	}

	/**
	 * @param string $url The url to encode
	 * @return string The image path (as data:base64)
	 */
	public function getQRCodeImage($url) {
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