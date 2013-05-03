<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$tempColumns = array (
	'tx_authenticator_secret' => array (		
		'exclude' => 0,		
		'label' => 'LLL:EXT:authenticator/locallang_db.xml:be_users.tx_authenticator_secret',		
		'config' => array (
			'type' => 'user',
			'userFunc' => 'EXT:authenticator/Classes/Fields/QrFields.php:Tx\Authenticator\Fields\QrFields->getField'
		)
	),
);


\TYPO3\CMS\Core\Utility\GeneralUtility::loadTCA('be_users');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('be_users',$tempColumns,1);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('be_users','tx_authenticator_secret;;;;1-1-1');

$tempColumns = array (
	'tx_authenticator_secret' => array (		
		'exclude' => 0,		
		'label' => 'LLL:EXT:authenticator/locallang_db.xml:fe_users.tx_authenticator_secret',		
		'config' => array (
			'type' => 'user',
			'userFunc' => 'EXT:authenticator/Classes/Fields/QrFields.php:Tx\Authenticator\Fields\QrFields->getField'
		)
	),
);


\TYPO3\CMS\Core\Utility\GeneralUtility::loadTCA('fe_users');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users',$tempColumns,1);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users','tx_authenticator_secret;;;;1-1-1');

?>