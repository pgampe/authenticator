<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$tempColumns = array(
	'tx_authenticator_secret' => array(
		'exclude' => 0,
		'label' => 'LLL:EXT:authenticator/locallang_db.xml:be_users.tx_authenticator_secret',
		'config' => array(
			'type' => 'user',
			'userFunc' => 'EXT:authenticator/Classes/Fields/QrFields.php:Tx\Authenticator\Fields\QrFields->getField'
		)
	),
	'tx_authenticator_enabled' => array(
		'label' => 'LLL:EXT:authenticator/locallang_db.xml:be_users.tx_authenticator_enabled',
		'config' => array(
			'type' => 'check',
			'items' => array(
				array('LLL:EXT:authenticator/locallang_db.xml:be_users.tx_authenticator_enabled_item', 0)
			),
			'default' => 0
		)
	),
);

\TYPO3\CMS\Core\Utility\GeneralUtility::loadTCA('be_users');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('be_users', $tempColumns, 1);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('be_users', 'tx_authenticator_secret;;;;1-1-1,tx_authenticator_enabled');

$tempColumns = array(
	'tx_authenticator_secret' => array(
		'exclude' => 0,
		'label' => 'LLL:EXT:authenticator/locallang_db.xml:fe_users.tx_authenticator_secret',
		'config' => array(
			'type' => 'user',
			'userFunc' => 'EXT:authenticator/Classes/Fields/QrFields.php:Tx\Authenticator\Fields\QrFields->getField'
		)
	),
	'tx_authenticator_enabled' => array(
		'label' => 'LLL:EXT:authenticator/locallang_db.xml:fe_users.tx_authenticator_enabled',
		'config' => array(
			'type' => 'check',
			'items' => array(
				array('LLL:EXT:authenticator/locallang_db.xml:fe_users.tx_authenticator_enabled_item', 0)
			),
			'default' => 0
		)
	),
);

\TYPO3\CMS\Core\Utility\GeneralUtility::loadTCA('fe_users');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users', $tempColumns, 1);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'tx_authenticator_secret;;;;1-1-1,tx_authenticator_enabled');


// extend user settings
$GLOBALS['TYPO3_USER_SETTINGS']['columns']['tx_authenticator_secret'] = array(
	'label' => 'LLL:EXT:authenticator/locallang_db.xml:be_users.tx_authenticator_secret_user',
	'type' => 'user',
	'userFunc' => 'EXT:authenticator/Classes/Fields/QrFields.php:Tx\Authenticator\Fields\QrFields->getBackendSetting',
);
$GLOBALS['TYPO3_USER_SETTINGS']['columns']['tx_authenticator_enabled'] = array(
	'label' => 'LLL:EXT:authenticator/locallang_db.xml:be_users.tx_authenticator_enabled_user',
	'type' => 'check',
	'table' => 'be_users',
);
$GLOBALS['TYPO3_USER_SETTINGS']['showitem'] .= ',
	--div--;LLL:EXT:authenticator/locallang_db.xml:be_users.tx_authenticator_secret_user_title,tx_authenticator_enabled,tx_authenticator_secret';

?>