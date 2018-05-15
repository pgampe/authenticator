<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Authenticator',
    'description' => 'Implements a two factor authentication for TYPO3. Currently a for backend only.',
    'category' => 'services',
    'author' => 'Philipp Gampe, Oliver Eglseder',
    'author_email' => 'philipp.gampe@typo3.org, php@vxvr.de',
    'author_company' => '',
    'state' => 'stable',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 1,
    'version' => '0.2.0',
    'constraints' => [
        'depends' => [
            'typo3' => '8.4.0-9.9.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
