<?php

namespace Flames\Mesh\Node;

use Flames\Mesh\Compiler;

/**
 * @internal
 */
class BreakNode extends Node
{
    public function __construct(int $lineno, string $tag)
    {
        parent::__construct([], [], $lineno, $tag);
    }

    public function compile(Compiler $compiler): void
    {
        $compiler
            ->addDebugInfo($this)
            ->write("break;\n")
        ;
    }
}
