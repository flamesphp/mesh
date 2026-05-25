<?php

namespace Flames\Mesh\TokenParser;

use Flames\Mesh\Node\BreakNode;
use Flames\Mesh\Node\Node;
use Flames\Mesh\Token;

/**
 * Breaks out of a foreach/for loop.
 *
 *   {% foreach items as item %}
 *     {% if item.disabled %}{% break %}{% endif %}
 *     {{ item.name }}
 *   {% endforeach %}
 *
 * @internal
 */
final class BreakTokenParser extends AbstractTokenParser
{
    public function parse(Token $token): Node
    {
        $this->parser->getStream()->expect(/* Token::BLOCK_END_TYPE */ 3);

        return new BreakNode($token->getLine(), $this->getTag());
    }

    public function getTag(): string
    {
        return 'break';
    }
}
