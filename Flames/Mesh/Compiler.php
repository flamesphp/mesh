<?php

declare(strict_types=1);

// Twig fork: https://github.com/twigphp/Twig

namespace Flames\Mesh;

use Flames\Mesh\Node\Node;

/**
 * @internal
 */
class Compiler
{
    private ?int $lastLine = null;
    private string $source = '';
    private int $indentation = 0;
    private string $indentStr = '';
    private Environment $env;
    private array $debugInfo = [];
    private int $sourceOffset = 0;
    private int $sourceLine = 1;
    private int $varNameSalt = 0;

    public function __construct(Environment $env)
    {
        $this->env = $env;
    }

    public function getEnvironment(): Environment
    {
        return $this->env;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * @return $this
     */
    public function reset(int $indentation = 0): static
    {
        $this->lastLine = null;
        $this->source = '';
        $this->debugInfo = [];
        $this->sourceOffset = 0;
        $this->sourceLine = 1;
        $this->indentation = $indentation;
        $this->indentStr = $indentation > 0 ? str_repeat('    ', $indentation) : '';
        $this->varNameSalt = 0;

        return $this;
    }

    /**
     * @return $this
     */
    public function compile(Node $node, int $indentation = 0): static
    {
        $this->reset($indentation);
        $node->compile($this);

        return $this;
    }

    /**
     * @return $this
     */
    public function subcompile(Node $node, bool $raw = true): static
    {
        if (!$raw) {
            $this->source .= $this->indentStr;
        }

        $node->compile($this);

        return $this;
    }

    /**
     * Adds a raw string to the compiled code.
     *
     * @return $this
     */
    public function raw(string $string): static
    {
        $this->source .= $string;

        return $this;
    }

    /**
     * Writes a string to the compiled code by adding indentation.
     *
     * @return $this
     */
    public function write(string ...$strings): static
    {
        foreach ($strings as $string) {
            $this->source .= $this->indentStr . $string;
        }

        return $this;
    }

    /**
     * Adds a quoted string to the compiled code.
     *
     * @return $this
     */
    public function string(string $value): static
    {
        $this->source .= '"' . addcslashes($value, "\0\t\"\$\\") . '"';

        return $this;
    }

    /**
     * Returns a PHP representation of a given value.
     *
     * @return $this
     */
    public function repr(mixed $value): static
    {
        if (\is_int($value) || \is_float($value)) {
            if (false !== $locale = setlocale(\LC_NUMERIC, '0')) {
                setlocale(\LC_NUMERIC, 'C');
            }

            $this->source .= var_export($value, true);

            if (false !== $locale) {
                setlocale(\LC_NUMERIC, $locale);
            }
        } elseif (null === $value) {
            $this->source .= 'null';
        } elseif (\is_bool($value)) {
            $this->source .= $value ? 'true' : 'false';
        } elseif (\is_array($value)) {
            $this->source .= 'array(';
            $first = true;
            foreach ($value as $key => $v) {
                if (!$first) {
                    $this->source .= ', ';
                }
                $first = false;
                $this->repr($key);
                $this->source .= ' => ';
                $this->repr($v);
            }
            $this->source .= ')';
        } else {
            $this->string((string) $value);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function addDebugInfo(Node $node): static
    {
        if ($node->getTemplateLine() !== $this->lastLine) {
            $this->source .= $this->indentStr . '// line ' . $node->getTemplateLine() . "\n";

            $this->sourceLine += substr_count($this->source, "\n", $this->sourceOffset);
            $this->sourceOffset = \strlen($this->source);
            $this->debugInfo[$this->sourceLine] = $node->getTemplateLine();

            $this->lastLine = $node->getTemplateLine();
        }

        return $this;
    }

    public function getDebugInfo(): array
    {
        ksort($this->debugInfo);

        return $this->debugInfo;
    }

    /**
     * @return $this
     */
    public function indent(int $step = 1): static
    {
        $this->indentation += $step;
        $this->indentStr = str_repeat('    ', $this->indentation);

        return $this;
    }

    /**
     * @return $this
     *
     * @throws \LogicException When trying to outdent too much so the indentation would become negative
     */
    public function outdent(int $step = 1): static
    {
        if ($this->indentation < $step) {
            throw new \LogicException('Unable to call outdent() as the indentation would become negative.');
        }

        $this->indentation -= $step;
        $this->indentStr = $this->indentation > 0 ? str_repeat('    ', $this->indentation) : '';

        return $this;
    }

    public function getVarName(): string
    {
        return '__internal_compile_' . $this->varNameSalt++;
    }
}
