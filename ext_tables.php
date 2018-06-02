<?php

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

// Extend user settings
$GLOBALS['TYPO3_USER_SETTINGS']['columns']['tx_authenticator_secret'] = [
    'label'    => 'LLL:EXT:authenticator/Resources/Private/Language/locallang_db.xlf:be_users.tx_authenticator_secret_user',
    'type'     => 'user',
    'userFunc' => 'EXT:authenticator/Classes/Fields/QrFields.php:Tx\Authenticator\Fields\QrFields->getBackendSetting',
];

$GLOBALS['TYPO3_USER_SETTINGS']['columns']['tx_authenticator_enabled'] = [
    'label' => 'LLL:EXT:authenticator/Resources/Private/Language/locallang_db.xlf:be_users.tx_authenticator_enabled_user',
    'type'  => 'check',
    'table' => 'be_users',
];
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToUserSettings(
    ',--div--;LLL:EXT:authenticator/Resources/Private/Language/locallang_db.xlf:be_users.tx_authenticator_secret_user_title,tx_authenticator_enabled,tx_authenticator_secret'
);
