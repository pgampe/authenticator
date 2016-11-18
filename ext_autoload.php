<?php

$extPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('authenticator');

return [
    'OTPHP\\TOTP' => $extPath . 'Resources/Private/Php/otphp/lib/otphp.php',
];
