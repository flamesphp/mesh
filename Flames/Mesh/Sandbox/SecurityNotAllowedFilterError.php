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

namespace Flames\Mesh\Sandbox;

/**
 * @internal
 */
final class SecurityNotAllowedFilterError extends SecurityError
{
    private $filterName;

    public function __construct(string $message, string $functionName)
    {
        parent::__construct($message);
        $this->filterName = $functionName;
    }

    public function getFilterName(): string
    {
        return $this->filterName;
    }
}
