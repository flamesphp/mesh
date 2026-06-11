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

namespace Flames\Mesh\Node;

use Flames\Mesh\Attribute\YieldReady;
use Flames\Mesh\Compiler;
use Flames\Mesh\Node\Expression\AbstractExpression;
use Flames\Mesh\Node\Expression\ConstantExpression;

/**
 * @internal
 */
#[YieldReady]
class DeprecatedNode extends Node
{
    public function __construct(AbstractExpression $expr, int $lineno, ?string $tag = null)
    {
        parent::__construct(['expr' => $expr], [], $lineno, $tag);
    }

    public function compile(Compiler $compiler): void
    {
        $compiler->addDebugInfo($this);

        $expr = $this->getNode('expr');

        if ($expr instanceof ConstantExpression) {
            $compiler->write('@trigger_error(')
                ->subcompile($expr);
        } else {
            $varName = $compiler->getVarName();
            $compiler->write(sprintf('$%s = ', $varName))
                ->subcompile($expr)
                ->raw(";\n")
                ->write(sprintf('@trigger_error($%s', $varName));
        }

        $compiler
            ->raw('.')
            ->string(sprintf(' ("%s" at line %d).', $this->getTemplateName(), $this->getTemplateLine()))
            ->raw(", E_USER_DEPRECATED);\n")
        ;
    }
}
