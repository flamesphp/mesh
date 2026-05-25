<?php

declare(strict_types=1);

namespace Flames\Mesh\Node\Expression\Binary;

use Flames\Mesh\Compiler;
use Flames\Mesh\Node\Expression\AbstractExpression;
use Flames\Mesh\Node\Node;

/**
 * @internal
 */
abstract class AbstractBinary extends AbstractExpression
{
    public function __construct(Node $left, Node $right, int $lineno)
    {
        parent::__construct(['left' => $left, 'right' => $right], [], $lineno);
    }

    public function compile(Compiler $compiler): void
    {
        $compiler
            ->raw('(')
            ->subcompile($this->getNode('left'))
            ->raw(' ')
        ;
        $this->operator($compiler);
        $compiler
            ->raw(' ')
            ->subcompile($this->getNode('right'))
            ->raw(')')
        ;
    }

    abstract public function operator(Compiler $compiler): Compiler;
}
