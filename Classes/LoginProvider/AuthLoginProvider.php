<?php
namespace Tx\Authenticator\LoginProvider;

use TYPO3\CMS\Backend\Controller\LoginController;
use TYPO3\CMS\Backend\LoginProvider\UsernamePasswordLoginProvider;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
/**
 * Class AuthLoginProvider
 */
 
class AuthLoginProvider extends \TYPO3\CMS\Backend\LoginProvider\UsernamePasswordLoginProvider {
	/**
	 * Renders the login fields
	 *
	 * @param StandaloneView $view
	 * @param PageRenderer $pageRenderer
	 * @param LoginController $loginController
	 */
	public function render(StandaloneView $view, PageRenderer $pageRenderer, LoginController $loginController) {
		parent::render($view, $pageRenderer, $loginController);
		$view->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName('EXT:authenticator/Resources/Private/Templates/LoginAuthenticator.html'));
	}
}
