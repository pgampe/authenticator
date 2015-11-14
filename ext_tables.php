<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
// Get EM settings
$settings = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$_EXTKEY]);
if (!is_array($settings)) {
    $settings = array();
}

// Extend user settings
$GLOBALS['TYPO3_USER_SETTINGS']['columns']['tx_authenticator_secret'] = array(
	'label' => 'LLL:EXT:authenticator/Resources/Private/Language/locallang_db.xlf:be_users.tx_authenticator_secret_user',
	'type' => 'user',
	'userFunc' => 'EXT:authenticator/Classes/Fields/QrFields.php:Tx\Authenticator\Fields\QrFields->getBackendSetting',
);

$GLOBALS['TYPO3_USER_SETTINGS']['columns']['tx_authenticator_enabled'] = array(
	'label' => 'LLL:EXT:authenticator/Resources/Private/Language/locallang_db.xlf:be_users.tx_authenticator_enabled_user',
	'type' => 'check',
	'table' => 'be_users',
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToUserSettings(',--div--;LLL:EXT:authenticator/Resources/Private/Language/locallang_db.xlf:be_users.tx_authenticator_secret_user_title,tx_authenticator_enabled,tx_authenticator_secret');
