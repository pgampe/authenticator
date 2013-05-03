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

		$user = $PA['row'];
		/** @var \Tx\Authenticator\Auth\TokenAuthenticator $authenticator */
		$authenticator = GeneralUtility::makeInstance('Tx\\Authenticator\\Auth\\TokenAuthenticator');
		$authenticator->setUserTable($PA['table']);

		if (trim($user['tx_authenticator_secret']) == '') {
			$authenticator->setUser($user['username'], 'TOTP');
		}

		$authUrl = $authenticator->createUrl($user['username']);

		$buffer = $authUrl;
		$buffer .= '<img src="' . $this->getQRCodeImage($authUrl) . '" style="float: left;"><pre>' . htmlspecialchars(
			print_r($authenticator->getData($user['username']), TRUE)
		) . '</pre>';
		$buffer .= '<br style="clear:both">';

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