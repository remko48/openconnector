<?php

namespace OCA\OpenConnector\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class AuthenticationExtension
 *
 * This class extends Twig's AbstractExtension to provide custom functions for Twig templates.
 *
 * @package OCA\OpenConnector\Twig
 */
class AuthenticationExtension extends AbstractExtension
{
    /**
     * Get the list of custom Twig functions provided by this extension.
     *
     * @return array An array of TwigFunction objects
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction(name: 'oauthToken', callable: [AuthenticationRuntime::class, 'oauthToken']),
            new TwigFunction(name: 'decosToken', callable: [AuthenticationRuntime::class, 'decosToken']),
            new TwigFunction(name: 'jwtToken', callable: [AuthenticationRuntime::class, 'jwtToken']),
        ];
    }//end getFunctions()
}//end class
