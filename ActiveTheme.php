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

/**
 * Contains the currently active theme and allows to change it.
 * 
 * This is a service so we can inject it as reference to different parts of the application.
 * 
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
class ActiveTheme
{
    private $name;
    private $themes;
    
    /**
     * @param string $name
     * @param array $allowedThemes
     */
    public function __construct($name, array $allowedThemes)
    {
        $this->themes = $allowedThemes;
        $this->setName($name);
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function setName($name)
    {
        if (! in_array($name, $this->themes)) {
            throw new \InvalidArgumentException(sprintf('The active theme must be in the themes list.'));
        }
        
        $this->name = $name;
    }
}