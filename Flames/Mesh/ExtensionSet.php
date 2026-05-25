<?php

declare(strict_types=1);

// Twig fork: https://github.com/twigphp/Twig

namespace Flames\Mesh;

use Flames\Mesh\Error\RuntimeError;
use Flames\Mesh\Extension\ExtensionInterface;
use Flames\Mesh\Extension\GlobalsInterface;
use Flames\Mesh\Extension\StagingExtension;
use Flames\Mesh\Node\Expression\Binary\AbstractBinary;
use Flames\Mesh\Node\Expression\Unary\AbstractUnary;
use Flames\Mesh\NodeVisitor\NodeVisitorInterface;
use Flames\Mesh\TokenParser\TokenParserInterface;

/**
 * @internal
 */
final class ExtensionSet
{
    private array $extensions = [];
    private bool $initialized = false;
    private bool $runtimeInitialized = false;
    private StagingExtension $staging;
    /** @var array<string, TokenParserInterface> */
    private array $parsers = [];
    private array $visitors = [];
    /** @var array<string, TemplateFilter> */
    private array $filters = [];
    /** @var array<string, TemplateFilter> */
    private array $wildcardFilters = [];
    /** @var array<string, TemplateTest> */
    private array $tests = [];
    /** @var array<string, TemplateTest> */
    private array $wildcardTests = [];
    /** @var array<string, TemplateFunction> */
    private array $functions = [];
    /** @var array<string, TemplateFunction> */
    private array $wildcardFunctions = [];
    /** @var array<string, array{precedence: int, class: class-string<AbstractUnary>}> */
    private array $unaryOperators = [];
    /** @var array<string, array{precedence: int, class: class-string<AbstractBinary>, associativity: ExpressionParser::OPERATOR_*}> */
    private array $binaryOperators = [];
    /** @var array<string, mixed>|null */
    private ?array $globals = null;
    private array $functionCallbacks = [];
    private array $filterCallbacks = [];
    private array $parserCallbacks = [];
    private int $lastModified = 0;

    public function __construct()
    {
        $this->staging = new StagingExtension();
    }

    public function initRuntime(): void
    {
        $this->runtimeInitialized = true;
    }

    public function hasExtension(string $class): bool
    {
        return isset($this->extensions[ltrim($class, '\\')]);
    }

    public function getExtension(string $class): ExtensionInterface
    {
        $class = ltrim($class, '\\');

        if (!isset($this->extensions[$class])) {
            throw new RuntimeError(sprintf('The "%s" extension is not enabled.', $class));
        }

        return $this->extensions[$class];
    }

    /**
     * @param ExtensionInterface[] $extensions
     */
    public function setExtensions(array $extensions): void
    {
        foreach ($extensions as $extension) {
            $this->addExtension($extension);
        }
    }

    /**
     * @return ExtensionInterface[]
     */
    public function getExtensions(): array
    {
        return $this->extensions;
    }

    public function getSignature(): string
    {
        return json_encode(array_keys($this->extensions));
    }

    public function isInitialized(): bool
    {
        return $this->initialized || $this->runtimeInitialized;
    }

    public function getLastModified(): int
    {
        if (0 !== $this->lastModified) {
            return $this->lastModified;
        }

        foreach ($this->extensions as $extension) {
            $r = new \ReflectionObject($extension);
            if (is_file($r->getFileName()) && ($extensionTime = filemtime($r->getFileName())) > $this->lastModified) {
                $this->lastModified = $extensionTime;
            }
        }

        return $this->lastModified;
    }

    public function addExtension(ExtensionInterface $extension): void
    {
        $class = \get_class($extension);

        if ($this->initialized) {
            throw new \LogicException(sprintf('Unable to register extension "%s" as extensions have already been initialized.', $class));
        }

        if (isset($this->extensions[$class])) {
            throw new \LogicException(sprintf('Unable to register extension "%s" as it is already registered.', $class));
        }

        $this->extensions[$class] = $extension;
    }

    public function addFunction(TemplateFunction $function): void
    {
        if ($this->initialized) {
            throw new \LogicException(sprintf('Unable to add function "%s" as extensions have already been initialized.', $function->getName()));
        }

        $this->staging->addFunction($function);
    }

    /**
     * @return TemplateFunction[]
     */
    public function getFunctions(): array
    {
        if (!$this->initialized) {
            $this->initExtensions();
        }

        return $this->functions;
    }

    public function getFunction(string $name): ?TemplateFunction
    {
        if (!$this->initialized) {
            $this->initExtensions();
        }

        if (isset($this->functions[$name])) {
            return $this->functions[$name];
        }

        foreach ($this->wildcardFunctions as $pattern => $function) {
            $compiledPattern = str_replace('\\*', '(.*?)', preg_quote($pattern, '#'));
            if (preg_match('#^' . $compiledPattern . '$#', $name, $matches)) {
                array_shift($matches);
                $function->setArguments($matches);

                return $function;
            }
        }

        foreach ($this->functionCallbacks as $callback) {
            if (false !== $function = $callback($name)) {
                return $function;
            }
        }

        return null;
    }

    public function registerUndefinedFunctionCallback(callable $callable): void
    {
        $this->functionCallbacks[] = $callable;
    }

    public function addFilter(TemplateFilter $filter): void
    {
        if ($this->initialized) {
            throw new \LogicException(sprintf('Unable to add filter "%s" as extensions have already been initialized.', $filter->getName()));
        }

        $this->staging->addFilter($filter);
    }

    /**
     * @return TemplateFilter[]
     */
    public function getFilters(): array
    {
        if (!$this->initialized) {
            $this->initExtensions();
        }

        return $this->filters;
    }

    public function getFilter(string $name): ?TemplateFilter
    {
        if (!$this->initialized) {
            $this->initExtensions();
        }

        if (isset($this->filters[$name])) {
            return $this->filters[$name];
        }

        foreach ($this->wildcardFilters as $pattern => $filter) {
            $compiledPattern = str_replace('\\*', '(.*?)', preg_quote($pattern, '#'));
            if (preg_match('#^' . $compiledPattern . '$#', $name, $matches)) {
                array_shift($matches);
                $filter->setArguments($matches);

                return $filter;
            }
        }

        foreach ($this->filterCallbacks as $callback) {
            if (false !== $filter = $callback($name)) {
                return $filter;
            }
        }

        return null;
    }

    public function registerUndefinedFilterCallback(callable $callable): void
    {
        $this->filterCallbacks[] = $callable;
    }

    public function addNodeVisitor(NodeVisitorInterface $visitor): void
    {
        if ($this->initialized) {
            throw new \LogicException('Unable to add a node visitor as extensions have already been initialized.');
        }

        $this->staging->addNodeVisitor($visitor);
    }

    /**
     * @return NodeVisitorInterface[]
     */
    public function getNodeVisitors(): array
    {
        if (!$this->initialized) {
            $this->initExtensions();
        }

        return $this->visitors;
    }

    public function addTokenParser(TokenParserInterface $parser): void
    {
        if ($this->initialized) {
            throw new \LogicException('Unable to add a token parser as extensions have already been initialized.');
        }

        $this->staging->addTokenParser($parser);
    }

    /**
     * @return TokenParserInterface[]
     */
    public function getTokenParsers(): array
    {
        if (!$this->initialized) {
            $this->initExtensions();
        }

        return $this->parsers;
    }

    public function getTokenParser(string $name): ?TokenParserInterface
    {
        if (!$this->initialized) {
            $this->initExtensions();
        }

        if (isset($this->parsers[$name])) {
            return $this->parsers[$name];
        }

        foreach ($this->parserCallbacks as $callback) {
            if (false !== $parser = $callback($name)) {
                return $parser;
            }
        }

        return null;
    }

    public function registerUndefinedTokenParserCallback(callable $callable): void
    {
        $this->parserCallbacks[] = $callable;
    }

    /**
     * @return array<string, mixed>
     */
    public function getGlobals(): array
    {
        if (null !== $this->globals) {
            return $this->globals;
        }

        $globals = [];
        foreach ($this->extensions as $extension) {
            if (!$extension instanceof GlobalsInterface) {
                continue;
            }

            $extGlobals = $extension->getGlobals();
            if (!\is_array($extGlobals)) {
                throw new \UnexpectedValueException(sprintf('"%s::getGlobals()" must return an array of globals.', \get_class($extension)));
            }

            $globals = array_merge($globals, $extGlobals);
        }

        if ($this->initialized) {
            $this->globals = $globals;
        }

        return $globals;
    }

    public function addTest(TemplateTest $test): void
    {
        if ($this->initialized) {
            throw new \LogicException(sprintf('Unable to add test "%s" as extensions have already been initialized.', $test->getName()));
        }

        $this->staging->addTest($test);
    }

    /**
     * @return TemplateTest[]
     */
    public function getTests(): array
    {
        if (!$this->initialized) {
            $this->initExtensions();
        }

        return $this->tests;
    }

    public function getTest(string $name): ?TemplateTest
    {
        if (!$this->initialized) {
            $this->initExtensions();
        }

        if (isset($this->tests[$name])) {
            return $this->tests[$name];
        }

        foreach ($this->wildcardTests as $pattern => $test) {
            $compiledPattern = str_replace('\\*', '(.*?)', preg_quote($pattern, '#'));
            if (preg_match('#^' . $compiledPattern . '$#', $name, $matches)) {
                array_shift($matches);
                $test->setArguments($matches);

                return $test;
            }
        }

        return null;
    }

    /**
     * @return array<string, array{precedence: int, class: class-string<AbstractUnary>}>
     */
    public function getUnaryOperators(): array
    {
        if (!$this->initialized) {
            $this->initExtensions();
        }

        return $this->unaryOperators;
    }

    /**
     * @return array<string, array{precedence: int, class: class-string<AbstractBinary>, associativity: ExpressionParser::OPERATOR_*}>
     */
    public function getBinaryOperators(): array
    {
        if (!$this->initialized) {
            $this->initExtensions();
        }

        return $this->binaryOperators;
    }

    private function initExtensions(): void
    {
        $this->parsers = [];
        $this->filters = [];
        $this->wildcardFilters = [];
        $this->functions = [];
        $this->wildcardFunctions = [];
        $this->tests = [];
        $this->wildcardTests = [];
        $this->visitors = [];
        $this->unaryOperators = [];
        $this->binaryOperators = [];

        foreach ($this->extensions as $extension) {
            $this->initExtension($extension);
        }
        $this->initExtension($this->staging);
        $this->initialized = true;
    }

    private function initExtension(ExtensionInterface $extension): void
    {
        // filters
        foreach ($extension->getFilters() as $filter) {
            $filterName = $filter->getName();
            if (str_contains($filterName, '*')) {
                $this->wildcardFilters[$filterName] = $filter;
            } else {
                $this->filters[$filterName] = $filter;
            }
        }

        // functions
        foreach ($extension->getFunctions() as $function) {
            $functionName = $function->getName();
            if (str_contains($functionName, '*')) {
                $this->wildcardFunctions[$functionName] = $function;
            } else {
                $this->functions[$functionName] = $function;
            }
        }

        // tests
        foreach ($extension->getTests() as $test) {
            $testName = $test->getName();
            if (str_contains($testName, '*')) {
                $this->wildcardTests[$testName] = $test;
            } else {
                $this->tests[$testName] = $test;
            }
        }

        // token parsers
        foreach ($extension->getTokenParsers() as $parser) {
            if (!$parser instanceof TokenParserInterface) {
                throw new \LogicException('getTokenParsers() must return an array of \Mesh\TokenParser\TokenParserInterface.');
            }

            $this->parsers[$parser->getTag()] = $parser;
        }

        // node visitors
        foreach ($extension->getNodeVisitors() as $visitor) {
            $this->visitors[] = $visitor;
        }

        // operators
        if ($operators = $extension->getOperators()) {
            if (!\is_array($operators)) {
                throw new \InvalidArgumentException(sprintf('"%s::getOperators()" must return an array with operators, got "%s".', \get_class($extension), \is_object($operators) ? \get_class($operators) : \gettype($operators) . (\is_resource($operators) ? '' : '#' . $operators)));
            }

            if (2 !== \count($operators)) {
                throw new \InvalidArgumentException(sprintf('"%s::getOperators()" must return an array of 2 elements, got %d.', \get_class($extension), \count($operators)));
            }

            $this->unaryOperators = array_merge($this->unaryOperators, $operators[0]);
            $this->binaryOperators = array_merge($this->binaryOperators, $operators[1]);
        }
    }
}
