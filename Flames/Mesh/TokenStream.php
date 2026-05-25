<?php

declare(strict_types=1);

// Twig fork: https://github.com/twigphp/Twig

namespace Flames\Mesh;

use Flames\Mesh\Error\SyntaxError;

/**
 * @internal
 */
final class TokenStream
{
    private array $tokens;
    private int $current = 0;
    private Source $source;

    public function __construct(array $tokens, ?Source $source = null)
    {
        $this->tokens = $tokens;
        $this->source = $source ?? new Source('', '');
    }

    public function __toString(): string
    {
        return implode("\n", $this->tokens);
    }

    public function injectTokens(array $tokens): void
    {
        $this->tokens = array_merge(\array_slice($this->tokens, 0, $this->current), $tokens, \array_slice($this->tokens, $this->current));
    }

    /**
     * Sets the pointer to the next token and returns the old one.
     */
    public function next(): Token
    {
        if (!isset($this->tokens[++$this->current])) {
            throw new SyntaxError('Unexpected end of template.', $this->tokens[$this->current - 1]->getLine(), $this->source);
        }

        return $this->tokens[$this->current - 1];
    }

    /**
     * Tests a token, sets the pointer to the next one and returns it or throws a syntax error.
     *
     * @return Token|null The next token if the condition is true, null otherwise
     */
    public function nextIf(int|string|array $primary, string|array|null $secondary = null): ?Token
    {
        return $this->tokens[$this->current]->test($primary, $secondary) ? $this->next() : null;
    }

    /**
     * Tests a token and returns it or throws a syntax error.
     */
    public function expect(int|string|array $type, string|array|null $value = null, ?string $message = null): Token
    {
        $token = $this->tokens[$this->current];
        if (!$token->test($type, $value)) {
            $line = $token->getLine();
            throw new SyntaxError(sprintf('%sUnexpected token "%s"%s ("%s" expected%s).',
                $message ? $message . '. ' : '',
                Token::typeToEnglish($token->getType()),
                $token->getValue() !== '' ? sprintf(' of value "%s"', $token->getValue()) : '',
                Token::typeToEnglish($type), $value ? sprintf(' with value "%s"', $value) : ''),
                $line,
                $this->source
            );
        }
        $this->next();

        return $token;
    }

    /**
     * Looks at the next token.
     */
    public function look(int $number = 1): Token
    {
        if (!isset($this->tokens[$this->current + $number])) {
            throw new SyntaxError('Unexpected end of template.', $this->tokens[$this->current + $number - 1]->getLine(), $this->source);
        }

        return $this->tokens[$this->current + $number];
    }

    /**
     * Tests the current token.
     */
    public function test(int|string|array $primary, string|array|null $secondary = null): bool
    {
        return $this->tokens[$this->current]->test($primary, $secondary);
    }

    /**
     * Checks if end of stream was reached.
     */
    public function isEOF(): bool
    {
        return Token::EOF_TYPE === $this->tokens[$this->current]->getType();
    }

    public function getCurrent(): Token
    {
        return $this->tokens[$this->current];
    }

    /**
     * Gets the source associated with this stream.
     *
     * @internal
     */
    public function getSourceContext(): Source
    {
        return $this->source;
    }
}
