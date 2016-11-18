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
    /**
     * @var AbstractUserAuthentication
     */
    protected $user = null;

    /**
     * Check if authentication is needed and validate the secret
     *
     * @param array $params
     * @param AbstractUserAuthentication $user
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function postUserLookUp(array $params, AbstractUserAuthentication $user)
    {
        $this->user = $user;

        if ($this->canAuthenticate() && $this->needsAuthentication()) {
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
     * Check for a valid user, enabled two factor authentication and if a secret is set
     *
     * @return boolean TRUE if the user exists and can be authenticated
     */
    protected function canAuthenticate()
    {
        return $this->user instanceof AbstractUserAuthentication
               && $this->user->user['uid'] > 0
               && $this->user->user['tx_authenticator_enabled'] & 1 === 1
               && $this->user->user['tx_authenticator_secret'] !== '';
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
        $this->initializeLanguageService();

        $documentTemplate = $this->getDocumentTemplate();

        $backendExtConf = unserialize($this->getExtConf('backend'));

        $highlightColor = $backendExtConf['loginHighlightColor'];
        if (!empty($highlightColor)) {
            $css = '.btn-login.tx_authenticator_login_button, ';
            $css .= '.btn-login.tx_authenticator_login_button:hover, ';
            $css .= '.btn-login.tx_authenticator_login_button:active, ';
            $css .= '.btn-login.tx_authenticator_login_button:active:hover, ';
            $css .= '.btn-login.tx_authenticator_login_button:focus { background-color: ' . $highlightColor . '; }';
            $css .= ' .panel-login .panel-body.tx_authenticator_login_wrap { border-color: ' . $highlightColor . '; }';
            $documentTemplate->inDocStylesArray[] = $css;
        }

        $content = $documentTemplate->startPage('TYPO3 CMS Login: ' . $this->getSiteName());
        $content .= $this->renderLoginForm($token);
        $content .= $documentTemplate->endPage();

        $this->printContentAndDie($content);
    }

    /**
     * @param string $content
     *
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    protected function printContentAndDie($content)
    {
        // throw away any previous rendered/outputted content
        ob_clean();
        // output "our" content
        echo $content;
        // quit immediately to prevent any further rendering
        die();
    }

    /**
     * @return DocumentTemplate
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function getDocumentTemplate()
    {
        if (!isset($GLOBALS['TBE_TEMPLATE']) || !($GLOBALS['TBE_TEMPLATE'] instanceof DocumentTemplate)) {
            $GLOBALS['TBE_TEMPLATE'] = GeneralUtility::makeInstance(DocumentTemplate::class);
        }
        return $GLOBALS['TBE_TEMPLATE'];
    }

    /**
     * @param string $token
     * @return string
     */
    protected function renderLoginForm($token)
    {
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setLayoutRootPaths(['EXT:authenticator/Resources/Private/Layouts']);
        $view->setTemplateRootPaths(['EXT:authenticator/Resources/Private/Templates']);
        $view->setTemplate('LoginToken');
        $view->assign('token', $token);
        return $view->render();
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function initializeUserAuthentication()
    {
        if (TYPO3_MODE === 'BE' && isset($GLOBALS['BE_USER'])) {
            $this->user = $GLOBALS['BE_USER'];
        } elseif (TYPO3_MODE === 'FE' && isset($GLOBALS['FE_USER'])) {
            $this->user = $GLOBALS['FE_USER'];
        } else {
            $this->user = null;
        }
    }

    /**
     * @return mixed
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function getSiteName()
    {
        return $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'];
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function initializeLanguageService()
    {
        // Translation service is initialized too late in bootstrap
        $GLOBALS['LANG'] = GeneralUtility::makeInstance(LanguageService::class);
        $GLOBALS['LANG']->init((TYPO3_MODE === 'BE' && isset($this->user->uc['lang'])) ? $this->user->uc['lang'] : '');
    }

    /**
     * @param string $extKey
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function getExtConf($extKey)
    {
        return $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$extKey];
    }
}
