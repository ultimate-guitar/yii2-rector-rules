<?php

declare(strict_types=1);

namespace Muse\ActiveRecordAnalyzer;

use PhpParser\Node\Stmt\Class_;
use PHPStan\PhpDocParser\Ast\PhpDoc\MixinTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\Reflection\ClassReflection;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Core\NodeAnalyzer\ClassAnalyzer;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\Reflection\ReflectionResolver;

final class ActiveRecordChecker
{
    public function __construct(
        private ClassAnalyzer $classAnalyzer,
        private ReflectionResolver $reflectionResolver,
        private PhpDocInfoFactory $phpDocInfoFactory,
        private AstResolver $astResolver
    ) {
    }

    public function isActiveRecord(Class_ $class): bool
    {
        if ($this->classAnalyzer->isAnonymousClass($class)) {
            return false;
        }

        $classReflection = $this->reflectionResolver->resolveClassReflection($class);

        if (!$classReflection instanceof ClassReflection) {
            return false;
        }

        $parentClassReflections = $classReflection->getParents();

        if ($parentClassReflections === []) {
            $phpDocInfo = $this->phpDocInfoFactory->createFromNode($class);

            if ($phpDocInfo !== null) {
                $docNode = $phpDocInfo->getPhpDocNode();
                $children = $docNode->children;

                foreach ($children as $childDocNode) {
                    if (
                        $childDocNode instanceof PhpDocTagNode
                        && $childDocNode->name === ActiveRecordConst::ALTERNATIVE_OPTION_TO_LINK_AR
                        && $childDocNode->value instanceof MixinTagValueNode
                        && $childDocNode->value->type instanceof IdentifierTypeNode
                        && $childDocNode->value->type->name === '\\' . ActiveRecordConst::YII2_ACTIVE_RECORD_PATH
                    ) {
                        return true;
                    }
                }
            }

            return false;
        }

        if (!$this->isContainActiveRecord($parentClassReflections)) {
            return false;
        }

        return true;
    }

    public function isClassReflectionActiveRecord(ClassReflection $classReflection): bool
    {
        $class = $this->astResolver->resolveClassFromClassReflection($classReflection);

        if (!$class instanceof Class_) {
            return false;
        }

        if ($this->classAnalyzer->isAnonymousClass($class)) {
            return false;
        }

        $parentClassReflections = $classReflection->getParents();

        if ($parentClassReflections === []) {
            $phpDocInfo = $this->phpDocInfoFactory->createFromNode($class);

            if ($phpDocInfo !== null) {
                $docNode = $phpDocInfo->getPhpDocNode();
                $children = $docNode->children;

                foreach ($children as $childDocNode) {
                    if (
                        $childDocNode instanceof PhpDocTagNode
                        && $childDocNode->name === ActiveRecordConst::ALTERNATIVE_OPTION_TO_LINK_AR
                        && $childDocNode->value instanceof MixinTagValueNode
                        && $childDocNode->value->type instanceof IdentifierTypeNode
                        && $childDocNode->value->type->name === '\\' . ActiveRecordConst::YII2_ACTIVE_RECORD_PATH
                    ) {
                        return true;
                    }
                }
            }

            return false;
        }

        if (!$this->isContainActiveRecord($parentClassReflections)) {
            return false;
        }

        return true;
    }

    /**
     * @param ClassReflection[] $parentClassReflections
     */
    private function isContainActiveRecord(array $parentClassReflections): bool
    {
        foreach ($parentClassReflections as $classReflection) {
            if ($classReflection->getName() === ActiveRecordConst::YII2_ACTIVE_RECORD_PATH) {
                return true;
            }
        }

        return false;
    }
}
