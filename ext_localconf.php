<?php
		// register system service
	t3lib_extMgm::addService($_EXTKEY,  'auth' /* sv type */,  'tx_Authenticator_Services_Auth' /* sv key */,
		array(

			'title' => 'User authentication with Google Authenticator',
			'description' => 'Authentication with username/password/onetimepassword.',

			'subtype' => 'getUserBE,authUserBE,getUserFE,authUserFE,getGroupsFE',

			'available' => FALSE,
			'priority' => 40,
			'quality' => 50,

			'os' => '',
			'exec' => '',

			'classFile' => t3lib_extMgm::extPath($_EXTKEY).'Classes/Services/Auth.php',
			'className' => 'tx_Authenticator_Services_Auth',
		)
	);

	// Add hooks to the backend login form
#$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/index.php']['loginFormHook'][$_EXTKEY] = 'EXT:' . $_EXTKEY . '/class.tx_sv_loginformhook.php:tx_sv_loginformhook->getLoginFormTag';
#$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/index.php']['loginScriptHook'][$_EXTKEY] = 'EXT:' . $_EXTKEY . '/class.tx_sv_loginformhook.php:tx_sv_loginformhook->getLoginScripts';

$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['postUserLookUp'][] = 'EXT:' . $_EXTKEY . '/Classes/Hooks/t3libUserAuth.php:tx_Authenticator_Hooks_t3libUserAuth->postUserLookUp';

