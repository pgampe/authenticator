<?php
/**
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

$tempColumnsBackend = array();
$tempColumnsBackend['tx_authenticator_secret'] = array(
    'exclude' => 0,
    'label' => 'LLL:EXT:authenticator/Resources/Private/Language/locallang_db.xlf:be_users.tx_authenticator_secret',
);
$tempColumnsBackend['tx_authenticator_secret']['config'] = array(
    'type' => 'passthrough',
);
$tempColumnsBackend['tx_authenticator_enabled'] = array(
    'label' => 'LLL:EXT:authenticator/Resources/Private/Language/locallang_db.xlf:be_users.tx_authenticator_enabled',
    'config' => array(
        'type' => 'check',
        'items' => array(
            array(
                'LLL:EXT:authenticator/Resources/Private/Language/locallang_db.xlf:be_users.tx_authenticator_enabled_item',
                0,
            ),
        ),
        'default' => 0,
    ),
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('be_users', $tempColumnsBackend);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('be_users', 'tx_authenticator_enabled');
