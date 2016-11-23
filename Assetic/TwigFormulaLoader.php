<?php

/*
 * This file is part of the Liip/ThemeBundle
 *
 * (c) Liip AG
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Liip\ThemeBundle\Assetic;

use Assetic\Factory\Resource\ResourceInterface;
use Psr\Log\LoggerInterface;
use Liip\ThemeBundle\ActiveTheme;
use Assetic\Extension\Twig\TwigFormulaLoader as BaseTwigFormulaLoader;

/**
 * Extends the base twig formula loader but iterates over all the
 * active themes. This ensures that formulae present in one
 * theme but not another are correctly registered.
 *
 * Note that errors will be logged each time a template is not found.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class TwigFormulaLoader extends BaseTwigFormulaLoader
{
    private $activeTheme;
    private $twig;
    private $logger;

    public function __construct(
        \Twig_Environment $twig,
        LoggerInterface $logger = null,
        ActiveTheme $activeTheme = null
    ) {
        parent::__construct($twig, $logger);
        $this->activeTheme = $activeTheme;
        $this->twig = $twig;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ResourceInterface $resource)
    {
        $formulae = array();
        $failureTemplates = array();
        $successTemplates = array();

        $previousTheme = $this->activeTheme->getName();

        foreach ($this->activeTheme->getThemes() as $theme) {
            $this->activeTheme->setName($theme);

            try {
                // determine if the template has any errors
                if (class_exists('Twig_Source')) {
                    $content = new \Twig_Source($resource->getContent(), (string) $resource->getContent());
                } else {
                    $content = $resource->getContent();
                }
                $this->twig->tokenize($content);

                // delegate the formula loading to the parent
                $formulae += parent::load($resource);
                $successTemplates[(string) $resource] = true;
            } catch (\Exception $e) {
                $failureTemplates[(string) $resource] = $e->getMessage();
            }
        }

        $this->activeTheme->setName($previousTheme);

        if ($this->logger) {
            foreach ($failureTemplates as $failureTemplate => $exceptionMessage) {
                if (isset($successTemplates[$failureTemplate])) {
                    continue;
                }

                $this->logger->error(sprintf(
                    'The template "%s" contains an error: "%s"',
                    $resource, $exceptionMessage
                ));
            }
        }

        return $formulae;
    }
}
