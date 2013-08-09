<?php
namespace Tx\Authenticator\Hooks;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Philipp Gampe <philipp.gampe@typo3.org>
 *  All rights reserved
 *
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * This class provides a hook to the login form to add extra javascript code
 * and supply a proper form tag.
 *
 * @author Philipp Gampe <philipp.gampe@typo3.org>
 */
class LoginFormHook {

	/**
	 * Adds RSA-specific JavaScript and returns a form tag
	 *
	 * @param array $params
	 * @param \TYPO3\CMS\Backend\Controller\LoginController $pObj
	 * @return string string Form tag
	 * @throws \TYPO3\CMS\Core\Error\Exception
	 */
	public function getLoginFormTag(array $params, \TYPO3\CMS\Backend\Controller\LoginController &$pObj) {
		$form = '';
		if ($pObj->loginSecurityLevel == 'rsa') {
			// Todo
		}
		return $form;
	}

	/**
	 * Provides form code for the superchallenged authentication.
	 *
	 * @param array $params Parameters to the script
	 * @param \TYPO3\CMS\Backend\Controller\LoginController $pObj Calling object
	 * @return string The code for the login form
	 */
	public function getLoginScripts(array $params, \TYPO3\CMS\Backend\Controller\LoginController &$pObj) {
		$content = '';
		if ($pObj->loginSecurityLevel == 'rsa') {
			// Todo
		}
		return $content;
	}

}

?>