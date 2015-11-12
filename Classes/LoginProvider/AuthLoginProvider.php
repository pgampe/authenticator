<?php
namespace Tx\Authenticator\LoginProvider;

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

use TYPO3\CMS\Backend\Controller\LoginController;
use TYPO3\CMS\Backend\LoginProvider\LoginProviderInterface;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Class AuthLoginProvider
 *
 * @package Tx\Authenticator\LoginProvider
 */
class AuthLoginProvider implements LoginProviderInterface {

	/**
	 * @param StandaloneView $view
	 * @param PageRenderer $pageRenderer
	 * @param LoginController $loginController
	 */
	public function render(StandaloneView $view, PageRenderer $pageRenderer, LoginController $loginController) {
		$view->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName('EXT:authenticator/Resources/Private/Templates/LoginAuthenticator.html'));
		//$view->assign('presetOpenId', GeneralUtility::_GP('openid_url'));
	}

}
