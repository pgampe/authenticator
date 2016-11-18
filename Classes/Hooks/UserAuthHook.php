<?php
namespace Tx\Authenticator\Hooks;

use Tx\Authenticator\Auth\TokenAuthenticator;
use TYPO3\CMS\Backend\Template\DocumentTemplate;
use TYPO3\CMS\Core\Authentication\AbstractUserAuthentication;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Lang\LanguageService;

/**
 * Straddels into the normal backend user authentication process to display the 2-factor form.
 */
class UserAuthHook
{
    /** @var null|AbstractUserAuthentication */
    protected $user = null;

    /**
     * Check if authentication is needed and validate the secret
     *
     * @param $params
     * @param $caller
     */
    public function postUserLookUp(&$params, &$caller)
    {
        $this->injectUser();
        if ($this->user === null) {
            // Unsupported mode, return early
            return;
        }
        if ($this->canAuthenticate() && $this->needsAuthentication()) {
            /** @var \Tx\Authenticator\Auth\TokenAuthenticator $authenticator */
            $authenticator = GeneralUtility::makeInstance(TokenAuthenticator::class, $this->user);
            $postTokenCheck = $authenticator->verify(
                $this->user->user['tx_authenticator_secret'],
                (integer)GeneralUtility::_GP('oneTimeSecret')
            );
            if ($postTokenCheck) {
                $this->setValidTwoFactorInSession();
            } else {
                $this->showForm(GeneralUtility::_GP('oneTimeSecret'));
            }
        }
    }

    /**
     * Inject the user object depending on the current context
     *
     * @param AbstractUserAuthentication $user
     * @return void
     */
    protected function injectUser($user = null)
    {
        if ($this->user !== null) {
            // user is already injected, return early
            return;
        }
        if ($user !== null) {
            $this->user = $user;
        } elseif (TYPO3_MODE == 'BE') {
            $this->user = $GLOBALS['BE_USER'];
        } elseif (TYPO3_MODE == 'FE') {
            $this->user = $GLOBALS['FE_USER'];
        }
        if (!$this->user instanceof AbstractUserAuthentication) {
            // Invalid object or unsupported mode
            $this->user = null;
        }
    }

    /**
     * Check for a valid user, enabled two factor authentication and if a secret is set
     *
     * @return boolean TRUE if the user can be authenticated
     */
    protected function canAuthenticate()
    {
        if ($this->user->user['uid'] > 0
            && $this->user->user['tx_authenticator_enabled'] & 1
            && $this->user->user['tx_authenticator_secret'] !== ''
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check whether the user is already authenticated
     *
     * @return boolean FALSE if the user is already authenticated
     */
    protected function needsAuthentication()
    {
        return $this->user->getSessionData('authenticatorIsValidTwoFactor') !== true;
    }

    /**
     * Mark the current session as checked
     *
     * @return void
     */
    protected function setValidTwoFactorInSession()
    {
        $this->user->setAndSaveSessionData('authenticatorIsValidTwoFactor', true);
    }

    /**
     * Render the form and exit execution
     *
     * @param string $token Provided (wrong) token
     */
    protected function showForm($token)
    {
        $error = ($token != '');

        // Translation service is initialized too late in bootstrap
        $GLOBALS['LANG'] = GeneralUtility::makeInstance(LanguageService::class);
        if (isset($GLOBALS['BE_USER'])) {
            $GLOBALS['LANG']->init($GLOBALS['BE_USER']->uc['lang']);
        } else {
            // Empty language means: fall back to default (english)
            $GLOBALS['LANG']->init('');
        }

        $GLOBALS['TBE_TEMPLATE'] = GeneralUtility::makeInstance(DocumentTemplate::class);
        $backendExtConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['backend']);
        if (!empty($backendExtConf['loginHighlightColor'])) {
            $GLOBALS['TBE_TEMPLATE']->inDocStylesArray[] = '
				.btn-login.tx_authenticator_login_button { background-color: ' . $backendExtConf['loginHighlightColor'] . '; }
				.panel-login .panel-body.tx_authenticator_login_wrap { border-color: ' . $backendExtConf['loginHighlightColor'] . '; }
			';
        }


        /** @var \TYPO3\CMS\Fluid\View\StandaloneView $view */
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setLayoutRootPaths(['EXT:authenticator/Resources/Private/Layouts']);
        $view->setTemplateRootPaths(['EXT:authenticator/Resources/Private/Templates']);
        $view->setTemplate('LoginToken');
        $view->assign('error', $error);
        $view->assign('token', $token);

        $content = $GLOBALS['TBE_TEMPLATE']->startPage('TYPO3 CMS Login: ' . $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename']);
        $content .= $view->render();
        $content .= $GLOBALS['TBE_TEMPLATE']->endPage();

        // Remove translation service in frontend
        if (!isset($GLOBALS['BE_USER'])) {
            unset($GLOBALS['LANG']);
        }
        ob_clean();
        die($content);
    }
}
