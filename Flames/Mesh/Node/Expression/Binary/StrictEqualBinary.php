<?php

namespace Flames\Mesh\Node\Expression\Binary;

use Flames\Mesh\Compiler;

/**
 * @internal
 */
class StrictEqualBinary extends AbstractBinary
{
    public function operator(Compiler $compiler): Compiler
    {
        return $compiler->raw('===');
    }
}
