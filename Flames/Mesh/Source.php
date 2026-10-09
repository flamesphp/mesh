<?php

declare(strict_types=1);

// Twig fork: https://github.com/twigphp/Twig

namespace Flames\Mesh;

use Flames\Mesh\Source\PostProcess;

/**
 * @internal
 */
final readonly class Source
{
    private string $code;

    /**
     * @param string $code The template source code
     * @param string $name The template logical name
     * @param string $path The filesystem path of the template if any
     */
    public function __construct(string $code, private string $name, private string $path = '')
    {
        $this->code = PostProcess::parse($code);
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPath(): string
    {
        return $this->path;
    }
}
