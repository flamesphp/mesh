<?php

namespace Flames\Mesh\TokenParser;

use Flames\Mesh\Node\Expression\AssignNameExpression;
use Flames\Mesh\Node\ForNode;
use Flames\Mesh\Node\Node;
use Flames\Mesh\Token;

/**
 * Loops over each item of a sequence using PHP-like foreach syntax.
 *
 *   <ul>
 *    {% foreach users as user %}
 *      <li>{{ user.username|e }}</li>
 *    {% endforeach %}
 *   </ul>
 *
 *   With key:
 *   {% foreach users as key => user %}
 *     {{ key }}: {{ user.username|e }}
 *   {% endforeach %}
 *
 * @internal
 */
final class ForeachTokenParser extends AbstractTokenParser
{
    public function parse(Token $token): Node
    {
        $lineno = $token->getLine();
        $stream = $this->parser->getStream();

        $seq = $this->parser->getExpressionParser()->parseExpression();

        $stream->expect(/* Token::OPERATOR_TYPE */ 8, 'as');

        $firstTarget = $this->parser->getExpressionParser()->parseAssignmentExpression()->getNode('0');

        if ($stream->nextIf(/* Token::ARROW_TYPE */ 12)) {
            $keyTarget = new AssignNameExpression($firstTarget->getAttribute('name'), $firstTarget->getTemplateLine());
            $valueTarget = $this->parser->getExpressionParser()->parseAssignmentExpression()->getNode('0');
        } else {
            $keyTarget = new AssignNameExpression('_key', $lineno);
            $valueTarget = $firstTarget;
        }

        $valueTarget = new AssignNameExpression($valueTarget->getAttribute('name'), $valueTarget->getTemplateLine());

        $stream->expect(/* Token::BLOCK_END_TYPE */ 3);
        $body = $this->parser->subparse([$this, 'decideForeachFork']);
        if ('else' == $stream->next()->getValue()) {
            $stream->expect(/* Token::BLOCK_END_TYPE */ 3);
            $else = $this->parser->subparse([$this, 'decideForeachEnd'], true);
        } else {
            $else = null;
        }
        $stream->expect(/* Token::BLOCK_END_TYPE */ 3);

        return new ForNode($keyTarget, $valueTarget, $seq, null, $body, $else, $lineno, $this->getTag());
    }

    public function decideForeachFork(Token $token): bool
    {
        return $token->test(['else', 'endforeach']);
    }

    public function decideForeachEnd(Token $token): bool
    {
        return $token->test('endforeach');
    }

    public function getTag(): string
    {
        return 'foreach';
    }
}
