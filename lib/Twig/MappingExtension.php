<?php

namespace OCA\OpenConnector\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class MappingExtension
 *
 * This class extends Twig's AbstractExtension to provide custom functions for Twig templates.
 *
 * @package OCA\OpenConnector\Twig
 */
class MappingExtension extends AbstractExtension
{


    /**
     * Get the list of custom Twig functions provided by this extension.
     *
     * @return array An array of TwigFunction objects
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction(name: 'executeMapping', callable: [MappingRuntime::class, 'executeMapping']),
        ];

    }//end getFunctions()


}//end class
