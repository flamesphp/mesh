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
final class SecurityNotAllowedPropertyError extends SecurityError
{
    public function __construct(string $message, private readonly string $className, private readonly string $propertyName)
    {
        parent::__construct($message);
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getPropertyName()
    {
        return $this->propertyName;
    }
}
