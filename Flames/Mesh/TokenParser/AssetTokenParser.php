<?php
declare(strict_types=1);


namespace Flames\Mesh\TokenParser;

use Flames\Mesh\Error\SyntaxError;
use Flames\Mesh\Node\AssetNode;
use Flames\Mesh\Node\Expression\ConstantExpression;
use Flames\Mesh\Node\Node;
use Flames\Mesh\Token;

/**
 * Renders a static JS or CSS asset tag.
 *
 *   {% asset '/assets/test.js' %}
 *   {% asset '/assets/test.js' async defer %}
 *   {% asset '/assets/test.css' %}
 *   {% asset '/assets/test.scss' %}
 *   {% asset '/assets/test.less' %}
 *
 * @internal
 */
final class AssetTokenParser extends AbstractTokenParser
{
    public function parse(Token $token): Node
    {
        $expr = $this->parser->getExpressionParser()->parseExpression();

        if ($expr instanceof ConstantExpression && !AssetNode::isAssetPath($expr->getAttribute('value'))) {
            throw new SyntaxError('The "asset" tag expects a path ending in ".js", ".css", ".scss" or ".less".', $token->getLine(), $this->parser->getStream()->getSourceContext());
        }

        $attributes = $this->parseAttributes();
        $this->parser->getStream()->expect(/* Token::BLOCK_END_TYPE */ 3);

        return new AssetNode($expr, $attributes, $token->getLine(), $this->getTag());
    }

    /**
     * @return list<string>
     */
    protected function parseAttributes(): array
    {
        $stream = $this->parser->getStream();
        $attributes = [];

        while ($stream->test(/* Token::NAME_TYPE */ 5)) {
            $attributes[] = $stream->next()->getValue();
        }

        return $attributes;
    }

    public function getTag(): string
    {
        return 'asset';
    }
}
