<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
// Get EM settings
$settings = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['authenticator']);
if (!is_array($settings)) {
    $settings = array();
}

/*****
 * Backend user records
 */
$tempColumnsBackend = array();
$tempColumnsBackend['tx_authenticator_secret'] = array(
	'exclude' => 0,
	'label' => 'LLL:EXT:authenticator/locallang_db.xlf:be_users.tx_authenticator_secret',
);
if (isset($settings['showQrCodeInBackendUserRecords']) && !empty($settings['showQrCodeInBackendUserRecords'])) {
	$tempColumnsBackend['tx_authenticator_secret']['config'] = array(
		'type' => 'user',
		'userFunc' => 'EXT:authenticator/Classes/Fields/QrFields.php:Tx\Authenticator\Fields\QrFields->getField'
	);
} else {
	$tempColumnsBackend['tx_authenticator_secret']['config'] = array(
		'type' => 'passthrough',
	);
}
$tempColumnsBackend['tx_authenticator_enabled'] = array(
	'label' => 'LLL:EXT:authenticator/locallang_db.xlf:be_users.tx_authenticator_enabled',
	'config' => array(
		'type' => 'check',
		'items' => array(
			array('LLL:EXT:authenticator/locallang_db.xlf:be_users.tx_authenticator_enabled_item', 0)
		),
		'default' => 0
	)
);

\TYPO3\CMS\Core\Utility\GeneralUtility::loadTCA('be_users');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('be_users', $tempColumnsBackend, TRUE);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('be_users', 'tx_authenticator_secret;;;;1-1-1,tx_authenticator_enabled');

/*****
 * Frontend user records
 */
$tempColumnsFrontend = array();
$tempColumnsFrontend['tx_authenticator_secret'] = array(
	'exclude' => 0,
	'label' => 'LLL:EXT:authenticator/locallang_db.xlf:fe_users.tx_authenticator_secret',

);
if (isset($settings['showQrCodeInFrontendUserRecords']) && !empty($settings['showQrCodeInFrontendUserRecords'])) {
	$tempColumnsFrontend['tx_authenticator_secret']['config'] = array(
		'type' => 'user',
		'userFunc' => 'EXT:authenticator/Classes/Fields/QrFields.php:Tx\Authenticator\Fields\QrFields->getField'
	);
} else {
	$tempColumnsFrontend['tx_authenticator_secret']['config'] = array(
		'type' => 'passthrough',
	);
}
$tempColumnsFrontend['tx_authenticator_enabled'] = array(
	'label' => 'LLL:EXT:authenticator/locallang_db.xlf:fe_users.tx_authenticator_enabled',
	'config' => array(
		'type' => 'check',
		'items' => array(
			array('LLL:EXT:authenticator/locallang_db.xlf:fe_users.tx_authenticator_enabled_item', 0)
		),
		'default' => 0
	)
);

\TYPO3\CMS\Core\Utility\GeneralUtility::loadTCA('fe_users');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users', $tempColumnsFrontend, 1);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'tx_authenticator_secret;;;;1-1-1,tx_authenticator_enabled');


// extend user settings
$GLOBALS['TYPO3_USER_SETTINGS']['columns']['tx_authenticator_secret'] = array(
	'label' => 'LLL:EXT:authenticator/locallang_db.xlf:be_users.tx_authenticator_secret_user',
	'type' => 'user',
	'userFunc' => 'EXT:authenticator/Classes/Fields/QrFields.php:Tx\Authenticator\Fields\QrFields->getBackendSetting',
);
$GLOBALS['TYPO3_USER_SETTINGS']['columns']['tx_authenticator_enabled'] = array(
	'label' => 'LLL:EXT:authenticator/locallang_db.xlf:be_users.tx_authenticator_enabled_user',
	'type' => 'check',
	'table' => 'be_users',
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToUserSettings(',--div--;LLL:EXT:authenticator/locallang_db.xlf:be_users.tx_authenticator_secret_user_title,tx_authenticator_enabled,tx_authenticator_secret');
?>