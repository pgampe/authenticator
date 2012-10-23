<?php

class tx_Authenticator_Fields_QrFields {
    function getField(&$PA, &$fobj) {
		if($PA['itemFormElValue']=='') {
			$options =unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['ks_apiclient']);
			$PA['itemFormElValue'] = $options['apiServer'];
		}

		$user          = $PA['row'];
		$authenticator = new tx_Authenticator_Auth_GoogleAuthenticator();
		$authenticator->setUserTable($PA['table']);

		if(trim($user['tx_authenticator_secret']) == '') {
			$authenticator->setUser($user['username'], 'TOTP');
		}

		$authUrl       = $authenticator->createURL($user['username']);

		$buffer       = $authUrl;
		$buffer       .= '<img src="' . $this->getQRCodeImage($authUrl) . '" style="float: left;"><pre>' . htmlspecialchars(print_r($authenticator->internalGetData($user['username']), true)) . '</pre>';
		$buffer       .= '<br style="clear:both">';
		#$buffer       .= '<input name="'.$PA['itemFormElName'].'" style="width: 460px;" value="'.htmlspecialchars($PA['itemFormElValue']).'" onchange="'.htmlspecialchars(implode('',$PA['fieldChangeFunc'])).'" '.$PA['onFocus'].'/>';

		return $buffer;


	}

	function getQRCodeImage($param) {
		ob_start();
		QRcode::png(
			$param,
			false,
			4,
			4
		);
		$buffer = ob_get_clean();
		header('Content-Type: text/html');
		return 'data:image/png;base64,' . base64_encode($buffer);

	}

}