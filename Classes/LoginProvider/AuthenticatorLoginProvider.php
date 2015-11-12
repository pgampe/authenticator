class AuthenticatorLoginProvider extends UsernamePasswordLoginProvider {

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