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

namespace Flames\Mesh\Node;

use Flames\Mesh\Attribute\YieldReady;
use Flames\Mesh\Compiler;

/**
 * @internal
 */
#[YieldReady]
class BlockNode extends Node
{
    public function __construct(string $name, Node $body, int $lineno, ?string $tag = null)
    {
        parent::__construct(['body' => $body], ['name' => $name], $lineno, $tag);
    }

    public function compile(Compiler $compiler): void
    {
        $compiler
            ->addDebugInfo($this)
            ->write(sprintf("public function block_%s(\$context, array \$blocks = [])\n", $this->getAttribute('name')), "{\n")
            ->indent()
            ->write("\$macros = \$this->macros;\n")
        ;

        $compiler
            ->subcompile($this->getNode('body'))
            ->write("return; yield '';\n") // needed when body doesn't yield anything
            ->outdent()
            ->write("}\n\n")
        ;
    }
}
