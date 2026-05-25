<?php

namespace Flames\Mesh\Node\Expression\Binary;

use Flames\Mesh\Compiler;

/**
 * @internal
 */
class StrictNotEqualBinary extends AbstractBinary
{
    public function operator(Compiler $compiler): Compiler
    {
        return $compiler->raw('!==');
    }
}
