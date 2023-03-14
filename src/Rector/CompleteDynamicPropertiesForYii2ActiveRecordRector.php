<?php

declare(strict_types=1);

namespace Muse\Rector;

use Muse\ActiveRecordAnalyzer\ActiveRecordChecker;
use Muse\ActiveRecordAnalyzer\ActiveRecordConst;
use Muse\PhpDoc\PhpDocHelper;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PropertyTagValueNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\CodeQuality\NodeAnalyzer\ClassLikeAnalyzer;
use Rector\CodeQuality\NodeAnalyzer\LocalPropertyAnalyzer;
use Rector\CodeQuality\NodeFactory\MissingPropertiesFactory;
use Rector\Core\NodeAnalyzer\PropertyPresenceChecker;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\Rector\AbstractRector;
use Rector\PostRector\ValueObject\PropertyMetadata;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Muse\Rector\Tests\CompleteDynamicPropertiesForYii2ActiveRecordRector\CompleteDynamicPropertiesForYii2ActiveRecordRectorTest
 */
final class CompleteDynamicPropertiesForYii2ActiveRecordRector extends AbstractRector
{
    private const ARRAY_MERGE_FUNCTION = 'array_merge';
    private const PARENT_NAME = 'parent';

    public function __construct(
        private MissingPropertiesFactory $missingPropertiesFactory,
        private LocalPropertyAnalyzer $localPropertyAnalyzer,
        private ClassLikeAnalyzer $classLikeAnalyzer,
        private ReflectionProvider $reflectionProvider,
        private PropertyPresenceChecker $propertyPresenceChecker,
        private ActiveRecordChecker $activeRecordChecker,
        private PhpDocHelper $phpDocHelper,
        private AstResolver $astResolver,
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add missing dynamic properties', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass extends \yii\db\ActiveRecord
{
    public function set()
    {
        $this->value = 5;
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
/**
 * @property int $value
 */
class SomeClass extends \yii\db\ActiveRecord
{
    public function set()
    {
        $this->value = 5;
    }
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
        if (!$this->activeRecordChecker->isActiveRecord($node)) {
            return null;
        }

        if ($node->isAbstract()) {
            return null;
        }

        $className = $this->getName($node);

        if ($className === null || !$this->reflectionProvider->hasClass($className)) {
            return null;
        }

        $classReflection = $this->reflectionProvider->getClass($className);
        $fetchedLocalPropertyNameToTypes = $this->localPropertyAnalyzer->resolveFetchedPropertiesToTypesFromClass($node);
        $propertiesToComplete = $this->resolvePropertiesToComplete($node, $fetchedLocalPropertyNameToTypes);
        $docTagsFromRules = $this->phpDocHelper->convertToPhpDocWithPropertyTagNode($this->extractFromRules($node, $classReflection), ActiveRecordConst::READ_WRITE_PROPERTY);
        $definedProperties = array_map(
            static fn(Node\Stmt\Property $prop) => $prop->props[0]->name->name,
            array_filter(
                $node->stmts,
                static fn(Node\Stmt $stmt) => $stmt instanceof Node\Stmt\Property && count($stmt->props) === 1 && $stmt->props[0] instanceof Node\Stmt\PropertyProperty
            )
        );
        $docTagsFromRules = $this->skipDefined($docTagsFromRules, $definedProperties);

        $docTagsFromGetters = $this->extractFromGetters($node);

        if ($propertiesToComplete === [] && $docTagsFromRules === [] && $docTagsFromGetters === []) {
            return null;
        }

        $newDocTagsFromProperties = [];

        if ($propertiesToComplete !== []) {
            $propertiesToComplete = $this->filterOutExistingProperties($node, $classReflection, $propertiesToComplete);
            $newProperties = $this->missingPropertiesFactory->create(
                $fetchedLocalPropertyNameToTypes,
                $propertiesToComplete
            );

            if ($newProperties === []) {
                return null;
            }

            $newDocTagsFromProperties = $this->phpDocHelper->convertPropertiesToDocTag($newProperties);
        }

        $combineBuiltRules = $this->phpDocHelper->mergePhpDocChildNodes($newDocTagsFromProperties, $docTagsFromRules);
        $combineBuiltRules = $this->phpDocHelper->mergePhpDocChildNodes($combineBuiltRules, $docTagsFromGetters);

        $phpDocInfo = $this->phpDocHelper->createFromNode($node);
        $docNode = $phpDocInfo->getPhpDocNode();
        $docNode->children = $this->phpDocHelper->mergePhpDocChildNodes($docNode->children, $combineBuiltRules);

        return $node;
    }

    /**
     * @param array<string, Type> $fetchedLocalPropertyNameToTypes
     * @return string[]
     */
    private function resolvePropertiesToComplete(Class_ $class, array $fetchedLocalPropertyNameToTypes): array
    {
        $propertyNames = $this->classLikeAnalyzer->resolvePropertyNames($class);

        /** @var string[] $fetchedLocalPropertyNames */
        $fetchedLocalPropertyNames = array_keys($fetchedLocalPropertyNameToTypes);

        return array_diff($fetchedLocalPropertyNames, $propertyNames);
    }

    /**
     * @param string[] $propertiesToComplete
     * @return string[]
     */
    private function filterOutExistingProperties(Class_ $class, ClassReflection $classReflection, array $propertiesToComplete): array
    {
        $missingPropertyNames = [];
        $className = $classReflection->getName();

        foreach ($propertiesToComplete as $propertyToComplete) {
            if ($classReflection->hasProperty($propertyToComplete)) {
                continue;
            }

            $propertyMetadata = new PropertyMetadata(
                $propertyToComplete,
                new ObjectType($className),
                Class_::MODIFIER_PRIVATE
            );
            $hasClassContextProperty = $this->propertyPresenceChecker->hasClassContextProperty(
                $class,
                $propertyMetadata
            );
            if ($hasClassContextProperty) {
                continue;
            }

            $missingPropertyNames[] = $propertyToComplete;
        }

        return $missingPropertyNames;
    }

    /**
     * @return array<string, string>
     */
    private function extractFromRules(Class_ $node, ClassReflection $classReflection): array
    {
        if ($node->stmts === []) {
            return [];
        }

        $plainParams = [];

        foreach ($node->stmts as $stmt) {
            if (
                !($stmt instanceof Node\Stmt\ClassMethod
                && $stmt->name->name === ActiveRecordConst::ACTIVE_RECORD_RULES
                && is_array($stmt->stmts)
                && count($stmt->stmts) === 1
                && $stmt->stmts[0] instanceof Node\Stmt\Return_)
            ) {
                continue;
            }

            /** @var Node\Expr\Array_|Node\Expr\FuncCall $expr */
            $expr = $stmt->stmts[0]->expr;

            if ($expr instanceof Node\Expr\Array_) {
                foreach ($this->extractPropertiesFromArray($expr) as $propertyName => $propertyType) {
                    $plainParams[$propertyName] = $propertyType;
                }
            } elseif ($expr instanceof Node\Expr\FuncCall && $expr->name instanceof Node\Name && (string)$expr->name === self::ARRAY_MERGE_FUNCTION) {
                foreach ($this->extractPropertiesFromArrayMerge($classReflection, $expr) as $propertyName => $propertyType) {
                    $plainParams[$propertyName] = $propertyType;
                }
            }
        }

        return $plainParams;
    }

    /**
     * @return array<PhpDocTagNode>
     */
    private function extractFromGetters(Class_ $node): array
    {
        if ($node->stmts === []) {
            return [];
        }

        $plainParams = [];

        foreach ($node->stmts as $stmt) {
            if (
                $stmt instanceof Node\Stmt\ClassMethod
                && is_array($stmt->stmts)
                && str_starts_with($stmt->name->name, ActiveRecordConst::GETTER_PREFIX)
                && count($stmt->stmts) === 1
                && $stmt->stmts[0] instanceof Node\Stmt\Return_
                && $stmt->stmts[0]->expr instanceof Node\Expr\MethodCall
                && $stmt->stmts[0]->expr->name instanceof Node\Identifier
                && ($stmt->stmts[0]->expr->name->name === ActiveRecordConst::HAS_ONE || $stmt->stmts[0]->expr->name->name === ActiveRecordConst::HAS_MANY)
            ) {
                /** @var Node\Expr\MethodCall $methodCall */
                $methodCall = $stmt->stmts[0]->expr;
                $methodArgs = $methodCall->getArgs();

                if ($methodArgs === []) {
                    continue;
                }

                $rawClass = $methodArgs[0]->value;

                if (!($rawClass instanceof Node\Expr\ClassConstFetch)) {
                    continue;
                }

                $separatedClassParts = $rawClass->class->parts ?? [];
                $class = end($separatedClassParts);
                $variable = lcfirst(substr($stmt->name->name, strlen(ActiveRecordConst::GETTER_PREFIX)));

                $plainParams[$variable] = $class . ($stmt->stmts[0]->expr->name->name === ActiveRecordConst::HAS_MANY ? '[]' : '');
            }
        }

        return $this->phpDocHelper->convertToPhpDocWithPropertyTagNode($plainParams, ActiveRecordConst::READ_PROPERTY);
    }

    private function mapValidatorToType(string $value): ?string
    {
        return ActiveRecordConst::VALIDATOR_TO_TYPE[$value] ?? null;
    }

    /**
     * @param PhpDocTagNode[] $docTags
     * @param string[] $definedProperties
     * @return PhpDocTagNode[]
     */
    private function skipDefined(array $docTags, array $definedProperties): array
    {
        $result = [];

        foreach ($docTags as $docTag) {
            /** @var PropertyTagValueNode $val */
            $val = $docTag->value;
            $property = substr($val->propertyName, 1);

            if (!in_array($property, $definedProperties, true)) {
                $result[] = $docTag;
            }
        }

        return $result;
    }

    /**
     * @return array<string, string>
     */
    private function extractPropertiesFromArray(Node\Expr\Array_ $expr): array
    {
        $plainParams = [];

        foreach ($expr->items as $arrayItem) {
            if (
                $arrayItem instanceof Node\Expr\ArrayItem
                && $arrayItem->value instanceof Node\Expr\Array_
                && is_array($arrayItem->value->items)
                && count($arrayItem->value->items) >= 2
                && $arrayItem->value->items[0]
                && $arrayItem->value->items[1]
            ) {
                $attributesNamesRaw = $arrayItem->value->items[0]->value;
                $attributesTypesRaw = $arrayItem->value->items[1]->value;
                $propertyNames = [];

                if (!$attributesTypesRaw instanceof Node\Scalar\String_) {
                    return [];
                }

                if ($attributesNamesRaw instanceof Node\Expr\Array_) {
                    foreach ($attributesNamesRaw->items as $propertyRawItem) {
                        if (($propertyRawItem->value instanceof Node\Scalar\String_) && !str_contains($propertyRawItem->value->value, '.')) {
                            $propertyNames[] = $propertyRawItem->value->value;
                        }
                    }

                    $propertyType = $this->mapValidatorToType($attributesTypesRaw->value);

                    if ($propertyType === null) {
                        continue;
                    }

                    foreach ($propertyNames as $propertyName) {
                        $plainParams[$propertyName] = $propertyType;
                    }
                } elseif ($attributesNamesRaw instanceof Node\Scalar\String_) {
                    $plainParams[$attributesNamesRaw->value] = $this->mapValidatorToType($attributesTypesRaw->value);
                }
            }
        }

        return $plainParams;
    }

    /**
     * @return array<string, string>
     */
    private function extractPropertiesFromArrayMerge(ClassReflection $classReflection, Node\Expr\FuncCall $expr): array
    {
        $plainParams = [];

        foreach ($expr->getArgs() as $arg) {
            if ($arg->value instanceof Node\Expr\Array_) {
                foreach ($this->extractPropertiesFromArray($arg->value) as $propertyName => $propertyType) {
                    $plainParams[$propertyName] = $propertyType;
                }
            } elseif (
                $arg->value instanceof Node\Expr\StaticCall
                && $arg->value->name instanceof Node\Identifier
                && $arg->value->class instanceof Node\Name
                && $arg->value->name->name === ActiveRecordConst::ACTIVE_RECORD_RULES
                && (string)$arg->value->class === self::PARENT_NAME
            ) {
                $parentReflection = $classReflection->getParentClass();

                if ($parentReflection === null) {
                    continue;
                }

                $parentClass = $this->astResolver->resolveClassFromClassReflection($parentReflection);

                if (! $parentClass instanceof Class_) {
                    continue;
                }

                foreach ($this->extractFromRules($parentClass, $parentReflection) as $propertyName => $propertyType) {
                    $plainParams[$propertyName] = $propertyType;
                }
            }
        }

        return $plainParams;
    }
}
