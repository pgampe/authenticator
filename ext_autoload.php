<?php

$extPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('authenticator');

return array(
	'OTPHP\\TOTP' => $extPath . 'Resources/Private/Php/otphp/lib/otphp.php',
	'QRcode' => $extPath . 'Resources/Private/Php/phpqrcode/qrlib.php',
);
