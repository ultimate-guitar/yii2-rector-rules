<?php

declare(strict_types=1);

namespace Muse\Rector;

use Closure;
use Muse\ActiveQueryAnalyzer\ActiveQueryChecker;
use Muse\ActiveRecordAnalyzer\ActiveRecordChecker;
use Muse\ActiveRecordAnalyzer\ActiveRecordConst;
use Muse\PhpDoc\PhpDocHelper;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Muse\Rector\Tests\CompleteMethodTypingForYii2QueryLinkedWithARRector\CompleteMethodTypingForYii2QueryLinkedWithARRectorTest
 */
final class CompleteMethodTypingForYii2QueryLinkedWithARRector extends AbstractRector
{
    private const METHODS_AR_TYPE = [
        'one' => '\%s|null one(\yii\db\Connection $db = null)',
        'all' => '\%s[] all(\yii\db\Connection $db = null)',
        'each' => '\%s[] each(int $batchSize = 100, \yii\db\Connection $db = null)',
        'batch' => '\%s[] batch(int $batchSize = 100, \yii\db\Connection $db = null)',
    ];

    private const METHODS_QUERY_TYPE = [
        'where' => '%s where(string|array|\yii\db\ExpressionInterface $condition, array $params = [])',
        'andWhere' => '%s andWhere(string|array|\yii\db\ExpressionInterface $condition, array $params = [])',
        'orWhere' => '%s orWhere(string|array|\yii\db\ExpressionInterface $condition, array $params = [])',
        'with' => '%s with(string|string[] $params)',
        'orderBy' => '%s orderBy(string|array|\yii\db\ExpressionInterface $columns)',
        'select' => '%s select(string|array|\yii\db\ExpressionInterface $columns)',
        'asArray' => '%s asArray(bool $value = true)',
        'andFilterWhere' => '%s andFilterWhere(array $condition)',
        'groupBy' => '%s groupBy(string|array|\yii\db\ExpressionInterface $columns)',
        'indexBy' => '%s indexBy(string|callable $column)',
        'andHaving' => '%s andHaving(string|array|\yii\db\ExpressionInterface $condition, array $params = [])',
    ];

    private const METHODS_CUSTOM_TYPE = [
        'column' => 'array column()',
        'count' => 'int count(string $q = \'*\', \yii\db\Connection $db = null)',
    ];

    /**
     * @var array<string, string>
     */
    private array $queryToAr = [];

    public function __construct(
        private ActiveQueryChecker $activeQueryChecker,
        private ReflectionProvider $reflectionProvider,
        private PhpDocHelper $phpDocHelper,
        private AstResolver $astResolver,
        private ActiveRecordChecker $activeRecordChecker
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add missing types for methods', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeQuery extends \yii\db\ActiveQuery
{
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
/**
 * @method Some|null one($db = null)
 * @method Some[] all($db = null)
 */
class SomeQuery extends \yii\db\ActiveQuery
{
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->activeQueryChecker->isActiveQuery($node)) {
            return null;
        }

        $className = $this->getName($node);

        if ($className === null || !$this->reflectionProvider->hasClass($className)) {
            return null;
        }

        $activeRecordName = $this->getActiveRecordName($node);

        if ($activeRecordName === null) {
            return null;
        }

        $existedMethods = array_map(static fn(ClassMethod $classMethod) => $classMethod->name->name, $node->getMethods());

        $filteredRawARMethods = $this->removeUsedMethods($existedMethods, self::METHODS_AR_TYPE);
        $withTypeARMethods = $this->applyTypeForTypeAndConvert($filteredRawARMethods, $activeRecordName);

        $usedMethodsForQuery = $this->getUsedThisMethods($node);
        $filteredRawAQMethods = $this->storeOnlyQueryMethods($usedMethodsForQuery, self::METHODS_QUERY_TYPE);
        $withTypeAQMethods = $this->applyTypeForTypeAndConvert($filteredRawAQMethods, $node->name->toString());

        $filteredRawCustomMethods = $this->storeOnlyQueryMethods($usedMethodsForQuery, self::METHODS_CUSTOM_TYPE);
        $withTypeCustomMethods = $this->applyTypeForTypeAndConvert($filteredRawCustomMethods, $node->name->toString());

        $methods = $withTypeARMethods + $withTypeAQMethods + $withTypeCustomMethods;

        $phpDocInfo = $this->phpDocHelper->createFromNode($node);
        $docNode = $phpDocInfo->getPhpDocNode();
        $docNode->children = $this->phpDocHelper->mergePhpDocChildNodes(
            $docNode->children,
            $this->phpDocHelper->convertToPhpDocWithMethodTagNode($methods, ActiveRecordConst::METHOD)
        );
        return $node;
    }

    /**
     * @param array<string> $methods
     * @return array<string, string>
     */
    private function applyTypeForTypeAndConvert(array $methods, string $type): array
    {
        $replacedTypes = array_map(static fn(string $method) => sprintf($method, $type), $methods);

        $result = [];

        foreach ($replacedTypes as $replacedType) {
            $raw = explode(' ', $replacedType, 2);
            $result[$raw[1]] = $raw[0];
        }

        return $result;
    }

    private function getActiveRecordName(Class_ $query): ?string
    {
        return $this->getLinkedActiveRecordClassName($this->getName($query));
    }

    /**
     * @param array<string> $existedMethods
     * @param array<string, string> $newMethods
     * @return array<string>
     */
    private function removeUsedMethods(array $existedMethods, array $newMethods): array
    {
        if ($existedMethods === []) {
            return array_values($newMethods);
        }

        $result = [];

        foreach ($newMethods as $name => $nameTypeAndParams) {
            if (!in_array($name, $existedMethods, true)) {
                $result[] = $nameTypeAndParams;
            }
        }

        return $result;
    }

    /**
     * @param array<string> $usedMethods
     * @param array<string, string> $newMethods
     * @return array<string>
     */
    private function storeOnlyQueryMethods(array $usedMethods, array $newMethods): array
    {
        if ($usedMethods === []) {
            return [];
        }

        $result = [];

        foreach ($newMethods as $name => $nameTypeAndParams) {
            if (in_array($name, $usedMethods, true)) {
                $result[] = $nameTypeAndParams;
            }
        }

        return $result;
    }

    /**
     * @param Class_ $node
     * @return array<string>
     */
    private function getUsedThisMethods(Class_ $node): array
    {
        $usedMethods = [];

        foreach ($node->getMethods() as $method) {
            if (count($method->getStmts()) === 1 && $method->getStmts()[0] instanceof Node\Stmt\Return_ && $method->getReturnType() === null) {
                /** @var Node\Stmt\Return_ $func */
                $func = $method->getStmts()[0];

                $methodCall = $func->expr;
                while ($methodCall instanceof Node\Expr\MethodCall && $methodCall->name instanceof Node\Identifier) {
                    $usedMethods[] = $methodCall->name->name;
                    $methodCall = $methodCall->var;
                }
            }
        }

        return array_values(array_unique($usedMethods));
    }

    private function getLinkedActiveRecordClassName(string $aq): ?string
    {
        if (array_key_exists($aq, $this->queryToAr)) {
            return $this->queryToAr[$aq];
        }

        $reader = function &($object, $property) {
            /** @phpstan-ignore-next-line */
            $value = &Closure::bind(function &() use ($property) {
                return $this->$property;
            }, $object, $object)->__invoke();
            return $value;
        };

        $reflectionProvider = $reader($this->astResolver, 'reflectionProvider');
        /** @var ClassReflection[] $classes */
        $classes = $reader($reflectionProvider, 'classes');

        foreach ($classes as $class) {
            $classAr = $this->astResolver->resolveClassFromClassReflection($class);

            if ($classAr instanceof Class_ && $this->activeRecordChecker->isClassReflectionActiveRecord($class)) {
                $localAq = $this->getActiveQueryName($classAr);

                if ($localAq !== null) {
                    $this->queryToAr[$localAq] = $class->getName();
                }
            }
        }

        return $this->queryToAr[$aq] ?? null;
    }

    private function getActiveQueryName(Class_ $ar): ?string
    {
        $find = $ar->getMethod(ActiveRecordConst::ACTIVE_RECORD_FIND);
        if (
            $find instanceof ClassMethod
            && count($find->stmts) === 1
            && $find->stmts[0] instanceof Node\Stmt\Return_
            && $find->stmts[0]->expr instanceof Node\Expr\New_
            && $find->stmts[0]->expr->class instanceof Name
        ) {
            return (string)$find->stmts[0]->expr->class;
        }

        return null;
    }
}
