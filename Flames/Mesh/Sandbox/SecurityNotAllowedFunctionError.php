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
final class SecurityNotAllowedFunctionError extends SecurityError
{
    private $functionName;

    public function __construct(string $message, string $functionName)
    {
        parent::__construct($message);
        $this->functionName = $functionName;
    }

    public function getFunctionName(): string
    {
        return $this->functionName;
    }
}
