<?php

/*
 * This file is part of the Liip/ThemeBundle
 *
 * (c) Liip AG
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Liip\ThemeBundle;

use Liip\ThemeBundle\Helper\DeviceDetectionInterface;

/**
 * Contains the currently active theme and allows to change it.
 *
 * This is a service so we can inject it as reference to different parts of the application.
 *
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
class ActiveTheme
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $themes;

    /**
     * @var DeviceDetectionInterface
     */
    private $deviceDetection;

    /**
     * @param string                          $name
     * @param array                           $themes
     * @param Helper\DeviceDetectionInterface $deviceDetection
     */
    public function __construct($name, array $themes = array(), DeviceDetectionInterface $deviceDetection = null)
    {
        $this->setThemes($themes);

        if ($name) {
            $this->setName($name);
        }
        $this->deviceDetection = $deviceDetection;
    }

    /**
     * @return DeviceDetectionInterface
     */
    public function getDeviceDetection()
    {
        return $this->deviceDetection;
    }

    public function getThemes()
    {
        return (array) $this->themes;
    }

    public function setThemes(array $themes)
    {
        $this->themes = $themes;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        if (!in_array($name, $this->themes)) {
            throw new \InvalidArgumentException(sprintf(
                'The active theme "%s" must be in the themes list (%s)',
                $name, implode(',', $this->themes)
            ));
        }

        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getDeviceType()
    {
        if (!$this->deviceDetection) {
            return '';
        }

        return $this->deviceDetection->getType();
    }
}
