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
   * TOTP - One time password generator.
   *
   * The TOTP class allow for the generation
   * and verification of one-time password using
   * the TOTP specified algorithm.
   *
   * This class is meant to be compatible with
   * Google Authenticator
   *
   * This class was originally ported from the rotp
   * ruby library available at https://github.com/mdp/rotp
   */
  class totp extends OTP
  {
      /**
     * The interval in seconds for a one-time password timeframe
     * Defaults to 30.
     *
     * @var int
     */
    public $interval;

      public function __construct($s, $opt = [])
      {
          $this->interval = isset($opt['interval']) ? $opt['interval'] : 30;
          parent::__construct($s, $opt);
      }

    /**
     *  Get the password for a specific timestamp value.
     *
     *  @param int $timestamp the timestamp which is timecoded and
     *  used to seed the hmac hash function.
     *
     *  @return int the One Time Password
     */
    public function at($timestamp)
    {
        return $this->generateOTP($this->timecode($timestamp));
    }

    /**
     *  Get the password for the current timestamp value.
     *
     *  @return int the current One Time Password
     */
    public function now()
    {
        return $this->generateOTP($this->timecode(time()));
    }

    /**
     * Verify if a password is valid for a specific counter value.
     *
     * @param int $otp       the one-time password
     * @param int $timestamp the timestamp for the a given time, defaults to current time.
     *
     * @return bool true if the counter is valid, false otherwise
     */
    public function verify($otp, $timestamp = null)
    {
        if ($timestamp === null) {
            $timestamp = time();
        }

        return $otp == $this->at($timestamp);
    }

      public function verify_window($otp, $backwards = 0, $forwards = 0)
      {
          $now = time();
          $start = $now - ($backwards * $this->interval);
          $end = $now + ($forwards * $this->interval);
          for ($t = $start, $i = 0; $i < ($backwards + $forwards + 1); $i++, $t += $this->interval) {
              if ($this->verify($otp, $t)) {
                  return true;
              }
          }

          return false;
      }

    /**
     * Returns the uri for a specific secret for totp method.
     * Can be encoded as a image for simple configuration in
     * Google Authenticator.
     *
     * @param string $name the name of the account / profile
     *
     * @return string the uri for the hmac secret
     */
    public function provisioning_uri($name)
    {
        return 'otpauth://totp/'.urlencode($name)."?secret={$this->secret}";
    }

    /**
     * Transform a timestamp in a counter based on specified internal.
     *
     * @param int $timestamp
     *
     * @return int the timecode
     */
    protected function timecode($timestamp)
    {
        return (int) ((((int) $timestamp * 1000) / ($this->interval * 1000)));
    }
  }

}
