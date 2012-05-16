<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$tempColumns = array (
	'tx_authenticator_secret' => array (		
		'exclude' => 0,		
		'label' => 'LLL:EXT:authenticator/locallang_db.xml:be_users.tx_authenticator_secret',		
		'config' => array (
			'type' => 'none',
		)
	),
);


t3lib_div::loadTCA('be_users');
t3lib_extMgm::addTCAcolumns('be_users',$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes('be_users','tx_authenticator_secret;;;;1-1-1');

$tempColumns = array (
	'tx_authenticator_secret' => array (		
		'exclude' => 0,		
		'label' => 'LLL:EXT:authenticator/locallang_db.xml:fe_users.tx_authenticator_secret',		
		'config' => array (
			'type' => 'none',
		)
	),
);


t3lib_div::loadTCA('fe_users');
t3lib_extMgm::addTCAcolumns('fe_users',$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes('fe_users','tx_authenticator_secret;;;;1-1-1');
?>