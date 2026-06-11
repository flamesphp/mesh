<?php
declare(strict_types=1);


namespace Flames\Mesh\Node;

use Flames\Mesh\Attribute\YieldReady;
use Flames\Mesh\Compiler;
use Flames\Mesh\Node\Expression\AbstractExpression;

/**
 * @internal
 */
#[YieldReady]
class AssetNode extends Node implements NodeOutputInterface
{
    /**
     * @param list<string> $attributes
     */
    public function __construct(AbstractExpression $expr, array $attributes, int $lineno, ?string $tag = null)
    {
        parent::__construct(['expr' => $expr], ['attributes' => $attributes], $lineno, $tag);
    }

    public function compile(Compiler $compiler): void
    {
        $compiler->addDebugInfo($this);
        $compiler
            ->write('yield CoreExtension::renderAsset(')
            ->subcompile($this->getNode('expr'))
            ->raw(', ')
            ->repr($this->getAttribute('attributes'))
            ->raw(");\n")
        ;
    }

    public static function isAssetPath(mixed $path): bool
    {
        if (!\is_string($path)) {
            return false;
        }

        $lower = strtolower($path);

        return str_ends_with($lower, '.js')
            || str_ends_with($lower, '.css')
            || str_ends_with($lower, '.scss');
    }
}
