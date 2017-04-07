<?php

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

namespace OTPHP {
/**
 * One Time Password Generator.
 *
 * The OTP class allow the generation of one-time
 * password that is described in rfc 4xxx.
 *
 * This is class is meant to be compatible with
 * Google Authenticator.
 *
 * This class was originally ported from the rotp
 * ruby library available at https://github.com/mdp/rotp
 */
class otp
{
    /**
     * The base32 encoded secret key.
     *
     * @var string
     */
    public $secret;

    /**
     * The algorithm used for the hmac hash function.
     *
     * @var string
     */
    public $digest;

    /**
     * The number of digits in the one-time password.
     *
     * @var int
     */
    public $digits;

    /**
     * Constructor for the OTP class.
     *
     * @param string $secret the secret key
     * @param array  $opt    options array can contain the
     *                       following keys :
     * @param int digits : the number of digits in the one time password
     *                       Currently Google Authenticator only support 6. Defaults to 6.
     * @param string digest : the algorithm used for the hmac hash function
     *                       Google Authenticator only support sha1. Defaults to sha1
     *
     * @return new OTP class.
     */
    public function __construct($secret, $opt = [])
    {
        $this->digits = isset($opt['digits']) ? $opt['digits'] : 6;
        $this->digest = isset($opt['digest']) ? $opt['digest'] : 'sha1';
        $this->secret = $secret;
    }

    /**
     * Generate a one-time password.
     *
     * @param int $input : number used to seed the hmac hash function.
     *                   This number is usually a counter (HOTP) or calculated based on the current
     *                   timestamp (see TOTP class).
     *
     * @return int the one-time password
     */
    public function generateOTP($input)
    {
        $hash = hash_hmac($this->digest, $this->intToBytestring($input), $this->byteSecret());
        foreach (str_split($hash, 2) as $hex) { // stupid PHP has bin2hex but no hex2bin WTF
        $hmac[] = hexdec($hex);
        }
        $offset = $hmac[19] & 0xf;
        $code = ($hmac[$offset + 0] & 0x7F) << 24 |
        ($hmac[$offset + 1] & 0xFF) << 16 |
        ($hmac[$offset + 2] & 0xFF) << 8 |
        ($hmac[$offset + 3] & 0xFF);

        return $code % pow(10, $this->digits);
    }

    /**
     * Returns the binary value of the base32 encoded secret.
     *
     * @return binary secret key
     */
    public function byteSecret()
    {
        return \Base32::decode($this->secret);
    }

    /**
     * Turns an integer in a OATH bytestring.
     *
     * @param int $int
     *
     * @return string bytestring
     */
    public function intToBytestring($int)
    {
        $result = [];
        while ($int != 0) {
            $result[] = chr($int & 0xFF);
            $int >>= 8;
        }

        return str_pad(implode(array_reverse($result)), 8, "\000", STR_PAD_LEFT);
    }
}
}
