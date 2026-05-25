<?php

namespace Flames\Mesh\Extension;

use Flames\Mesh\ExpressionParser;
use Flames\Mesh\Node\Expression\Binary\StrictEqualBinary;
use Flames\Mesh\Node\Expression\Binary\StrictNotEqualBinary;
use Flames\Mesh\TemplateFunction;
use Flames\Mesh\TokenParser\BreakTokenParser;
use Flames\Mesh\TokenParser\ContinueTokenParser;
use Flames\Mesh\TokenParser\ForeachTokenParser;

/**
 * Adds common PHP syntax to Mesh templates:
 * - === and !== strict comparison operators
 * - foreach tag (PHP-like syntax for loops)
 * - break and continue tags
 * - strtotime() function
 *
 * @internal
 */
final class PhpSyntaxExtension extends AbstractExtension
{
    public function getTokenParsers(): array
    {
        return [
            new ForeachTokenParser(),
            new BreakTokenParser(),
            new ContinueTokenParser(),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TemplateFunction('strtotime', 'strtotime'),
        ];
    }

    public function getOperators(): array
    {
        return [
            [],
            [
                '===' => ['precedence' => 20, 'class' => StrictEqualBinary::class, 'associativity' => ExpressionParser::OPERATOR_LEFT],
                '!==' => ['precedence' => 20, 'class' => StrictNotEqualBinary::class, 'associativity' => ExpressionParser::OPERATOR_LEFT],
            ],
        ];
    }
}
