<?php
declare(strict_types=1);


/*
 * This file is part of Template.
 *
 * (c) Fabien Potencier
 * (c) Armin Ronacher
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flames\Mesh\Node\Expression;

use Flames\Mesh\Compiler;

/**
 * @internal
 */
class AssignNameExpression extends NameExpression
{
    public function compile(Compiler $compiler): void
    {
        $compiler
            ->raw('$context[')
            ->string($this->getAttribute('name'))
            ->raw(']')
        ;
    }
}
