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
   * HOTP - One time password generator.
   *
   * The HOTP class allow for the generation
   * and verification of one-time password using
   * the HOTP specified algorithm.
   *
   * This class is meant to be compatible with
   * Google Authenticator
   *
   * This class was originally ported from the rotp
   * ruby library available at https://github.com/mdp/rotp
   */
  class hotp extends OTP
  {
      /**
     *  Get the password for a specific counter value.
     *
     *  @param int $count the counter which is used to
     *  seed the hmac hash function.
     *
     *  @return int the One Time Password
     */
    public function at($count)
    {
        return $this->generateOTP($count);
    }

    /**
     * Verify if a password is valid for a specific counter value.
     *
     * @param int $otp     the one-time password
     * @param int $counter the counter value
     *
     * @return bool true if the counter is valid, false otherwise
     */
    public function verify($otp, $counter)
    {
        return $otp == $this->at($counter);
    }

    /**
     * Returns the uri for a specific secret for hotp method.
     * Can be encoded as a image for simple configuration in
     * Google Authenticator.
     *
     * @param string $name          the name of the account / profile
     * @param int    $initial_count the initial counter
     *
     * @return string the uri for the hmac secret
     */
    public function provisioning_uri($name, $initial_count)
    {
        return 'otpauth://hotp/'.urlencode($name)."?secret={$this->secret}&counter=$initial_count";
    }
  }

}
