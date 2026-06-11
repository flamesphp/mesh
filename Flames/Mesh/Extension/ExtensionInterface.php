<?php
declare(strict_types=1);


/*
 * This file is part of Template.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flames\Mesh\Extension;

use Flames\Mesh\ExpressionParser;
use Flames\Mesh\Node\Expression\Binary\AbstractBinary;
use Flames\Mesh\Node\Expression\Unary\AbstractUnary;
use Flames\Mesh\NodeVisitor\NodeVisitorInterface;
use Flames\Mesh\TemplateFilter;
use Flames\Mesh\TemplateFunction;
use Flames\Mesh\TemplateTest;
use Flames\Mesh\TokenParser\TokenParserInterface;

/**
 * @internal
 */
interface ExtensionInterface
{
    /**
     * Returns the token parser instances to add to the existing list.
     *
     * @return TokenParserInterface[]
     */
    public function getTokenParsers();

    /**
     * Returns the node visitor instances to add to the existing list.
     *
     * @return NodeVisitorInterface[]
     */
    public function getNodeVisitors();

    /**
     * Returns a list of filters to add to the existing list.
     *
     * @return TemplateFilter[]
     */
    public function getFilters();

    /**
     * Returns a list of tests to add to the existing list.
     *
     * @return TemplateTest[]
     */
    public function getTests();

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return TemplateFunction[]
     */
    public function getFunctions();

    /**
     * Returns a list of operators to add to the existing list.
     *
     * @return array<array> First array of unary operators, second array of binary operators
     *
     * @psalm-return array{
     *     array<string, array{precedence: int, class: class-string<AbstractUnary>}>,
     *     array<string, array{precedence: int, class: class-string<AbstractBinary>, associativity: ExpressionParser::OPERATOR_*}>
     * }
     */
    public function getOperators();
}
