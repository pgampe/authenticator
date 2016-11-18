<?php
namespace Tx\Authenticator\Fields;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class QrFields
 *
 * Provides rendering for the backend user settings module
 *
 * @package Tx\Authenticator\Fields
 */
class QrFields
{
    /**
     * Hook function for the user settings module
     *
     * @param array $PA
     * @param \TYPO3\CMS\Setup\Controller\SetupModuleController $fsobj
     * @return string
     */
    public function getBackendSetting(&$PA, &$fsobj)
    {
        /** @var \TYPO3\CMS\Core\Page\PageRenderer $pageRenderer */
        $pageRenderer = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Page\PageRenderer::class);
        $pageRenderer->addJsFile(
            ExtensionManagementUtility::extRelPath('authenticator') . '/Resources/Public/JavaScript/qrcode.js'
        );
        return $this->createImageAndText($GLOBALS['BE_USER']);
    }

    /**
     * Creates the QR Code image and the corresponding text for the user settings module
     *
     * @param \TYPO3\CMS\Core\Authentication\AbstractUserAuthentication $user
     * @return string The HTML for the user settings module
     */
    protected function createImageAndText($user)
    {
        /** @var \Tx\Authenticator\Auth\TokenAuthenticator $authenticator */
        $authenticator = GeneralUtility::makeInstance('Tx\\Authenticator\\Auth\\TokenAuthenticator', $user);

        // Set random secret if empty
        if (trim($user->user['tx_authenticator_secret']) == '') {
            $authenticator->createToken('TOTP');
        }

        $label = $user->user[$user->username_column] . '-' . $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'];
        $extConfig = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['authenticator']);
        $createQr = $extConfig['showQrCodeInBackendUserSettings'];
        $authUrl = $authenticator->createUrlForUser($label);
        $data = $authenticator->getData();

        /** @var \TYPO3\CMS\Fluid\View\StandaloneView $view */
        $view = GeneralUtility::makeInstance('TYPO3\\CMS\\Fluid\\View\\StandaloneView');
        $view->setTemplatePathAndFilename(
            ExtensionManagementUtility::extPath('authenticator') . 'Resources/Private/Backend/BackendUserSettings.html'
        );
        $view->assign('createQr', $createQr);
        $view->assign('authUrl', $authUrl);
        $view->assign('tokenKey', $data['tokenkey']);
        return $view->render();
    }
}
