<?php

/*
 * This file is part of the Liip/ThemeBundle
 *
 * (c) Liip AG
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Liip\ThemeBundle\Helper;

class DeviceDetection implements DeviceDetectionInterface
{
    protected $userAgent;
    protected $devices = array(
        'tablet' => array(
            'androidtablet' => 'android(?!.*(?:mobile|opera mobi|opera mini))',
            'blackberrytablet' => 'rim tablet os',
            'ipad' => '(ipad)',
        ),
        'plain' => array(
            'kindle' => '(kindle)',
            'IE6' => 'MSIE 6.0',
        ),
        'phone' => array(
            'android' => 'android.*mobile|android.*opera mobi|android.*opera mini',
            'blackberry' => 'blackberry',
            'iphone' => '(iphone|ipod)',
            'palm' => '(avantgo|blazer|elaine|hiptop|palm|plucker|xiino|webOS)',
            'windows' => 'windows ce; (iemobile|ppc|smartphone)',
            'windowsphone' => 'windows phone',
            'generic' => '(mobile|mmp|midp|o2|pda|pocket|psp|symbian|smartphone|treo|up.browser|up.link|vodafone|wap|opera mini|opera mobi|opera mini)',
        ),
        'desktop' => array(
            'osx' => 'Mac OS X',
            'linux' => 'Linux',
            'windows' => 'Windows',
            'generic' => '',
        ),
    );

    protected $type = null;
    protected $device = null;

    public function __construct($userAgent = null)
    {
        $this->setUserAgent($userAgent);
    }

    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;
    }

    public function setDevices($devices)
    {
        $this->devices = $devices;
    }

    protected function init()
    {
        if (null === $this->type || null === $this->device) {
            list($device, $type) = $this->determineDevice($this->userAgent);
            $this->device = $device;
            $this->type = $type;
        }
    }

    /**
     * Overloads isAndroid() | isAndroidtablet() | isIphone() | isIpad() | isBlackberry() | isBlackberrytablet() | isPalm() | isWindowsphone() | isWindows() | isGeneric() through isDevice().
     *
     * @param string $name
     * @param array  $arguments
     *
     * @return bool
     */
    public function __call($name, $arguments)
    {
        $isDevice = substr($name, 2);
        if ($name !== 'is'.ucfirst($isDevice)) {
            trigger_error("Method $name not defined", E_USER_WARNING);
        }

        $device = $this->device;
        if (null === $device) {
            if (empty($arguments['userAgent'])) {
                $this->init();
                $device = $this->device;
            } else {
                list($device, $type) = $this->determineDevice($arguments['userAgent']);
            }
        }

        return strtolower($isDevice) === $device;
    }

    /**
     * Returns true if any type of mobile device detected, including special ones.
     *
     * @param string $userAgent optional to override the default user agent
     *
     * @return bool
     */
    public function isPhone($userAgent = null)
    {
        if (null === $userAgent) {
            $this->init();
            $type = $this->type;
        } else {
            list($device, $type) = $this->determineDevice($userAgent);
        }

        return $type === 'phone';
    }

    /**
     * Returns true if any type of tablet device detected, including special ones.
     *
     * @param string $userAgent optional to override the default user agent
     *
     * @return bool
     */
    public function isTablet($userAgent = null)
    {
        if (null === $userAgent) {
            $this->init();
            $type = $this->type;
        } else {
            list($device, $type) = $this->determineDevice($userAgent);
        }

        return $type === 'tablet';
    }

    /**
     * Returns true if any type of desktop device detected, including special ones.
     *
     * @param string $userAgent optional to override the default user agent
     *
     * @return bool
     */
    public function isDesktop($userAgent = null)
    {
        if (null === $userAgent) {
            $this->init();
            $type = $this->type;
        } else {
            list($device, $type) = $this->determineDevice($userAgent);
        }

        return $type === 'desktop';
    }

    public function determineDevice($userAgent)
    {
        foreach ($this->devices as $type => $devices) {
            foreach ($devices as $device => $regexp) {
                if ((bool) preg_match('/'.$regexp.'/i', $userAgent)) {
                    return array($device, $type);
                }
            }
        }

        return array(null, null);
    }

    public function getType()
    {
        $this->init();

        return $this->type;
    }

    /**
     * Force device type.
     *
     * @param string $type
     */
    public function setType($type)
    {
        if (in_array($type, array_keys($this->devices))) {
            $this->type = $type;
        }
    }

    public function getDevice()
    {
        $this->init();

        return $this->device;
    }
}
