<?php
declare(strict_types=1);


/*
 * This file is part of Template.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Flames\Mesh\Environment;
use Flames\Mesh\Extension\EscaperExtension;

/**
 * @internal
 * @deprecated since Template 3.9
 */
function twig_raw_filter($string)
{
    trigger_deprecation('twig/twig', '3.9', 'Using the internal "%s" function is deprecated.', __FUNCTION__);

    return $string;
}

/**
 * @internal
 * @deprecated since Template 3.9
 */
function twig_escape_filter(Environment $env, $string, $strategy = 'html', $charset = null, $autoescape = false)
{
    trigger_deprecation('twig/twig', '3.9', 'Using the internal "%s" function is deprecated.', __FUNCTION__);

    return EscaperExtension::escape($env, $string, $strategy, $charset, $autoescape);
}
