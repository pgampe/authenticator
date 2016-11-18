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

$tempColumnsFrontend = [];
$tempColumnsFrontend['tx_authenticator_secret'] = [
    'exclude' => 0,
    'label' => 'LLL:EXT:authenticator/Resources/Private/Language/locallang_db.xlf:fe_users.tx_authenticator_secret',
];
$tempColumnsFrontend['tx_authenticator_secret']['config'] = [
    'type' => 'passthrough',
];
$tempColumnsFrontend['tx_authenticator_enabled'] = [
    'label' => 'LLL:EXT:authenticator/Resources/Private/Language/locallang_db.xlf:fe_users.tx_authenticator_eaddTCAcolumnsnabled',
    'config' => [
        'type' => 'check',
        'items' => [
            [
                'LLL:EXT:authenticator/Resources/Private/Language/locallang_db.xlf:fe_users.tx_authenticator_enabled_item',
                0,
            ],
        ],
        'default' => 0,
    ],
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users', $tempColumnsFrontend);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'tx_authenticator_enabled');
