<?php

namespace Flames\Mesh\TokenParser;

use Flames\Mesh\Node\ContinueNode;
use Flames\Mesh\Node\Node;
use Flames\Mesh\Token;

/**
 * Continues to the next iteration of a foreach/for loop.
 *
 *   {% foreach items as item %}
 *     {% if item.hidden %}{% continue %}{% endif %}
 *     {{ item.name }}
 *   {% endforeach %}
 *
 * @internal
 */
final class ContinueTokenParser extends AbstractTokenParser
{
    public function parse(Token $token): Node
    {
        $this->parser->getStream()->expect(/* Token::BLOCK_END_TYPE */ 3);

        return new ContinueNode($token->getLine(), $this->getTag());
    }

    public function getTag(): string
    {
        return 'continue';
    }
}
