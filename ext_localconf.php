<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

// Register hook for user auth, use post user lookup as next possible hook AFTER user authentication
$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['postUserLookUp'][] = 'Tx\Authenticator\Hooks\UserAuthHook->postUserLookUp';
