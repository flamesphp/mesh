<?php
declare(strict_types=1);


// Twig fork: https://github.com/twigphp/Twig

namespace Flames\Mesh;

/**
 * @internal
 */
class Markup implements \Countable, \JsonSerializable, \Stringable
{
    private $content;

    public function __construct($content, private $charset)
    {
        $this->content = (string) $content;
    }

    public function __toString(): string
    {
        return $this->content;
    }

    /**
     * @return int
     */
    #[\ReturnTypeWillChange]
    public function count()
    {
        return mb_strlen($this->content, $this->charset);
    }

    /**
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->content;
    }
}
