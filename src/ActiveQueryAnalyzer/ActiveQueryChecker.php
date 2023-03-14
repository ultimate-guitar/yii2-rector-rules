<?php

declare(strict_types=1);

namespace Muse\ActiveQueryAnalyzer;

use Muse\ActiveRecordAnalyzer\ActiveRecordConst;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Reflection\ClassReflection;
use Rector\Core\NodeAnalyzer\ClassAnalyzer;
use Rector\Core\Reflection\ReflectionResolver;

class ActiveQueryChecker
{
    public function __construct(
        private ClassAnalyzer $classAnalyzer,
        private ReflectionResolver $reflectionResolver
    ) {
    }

    public function isActiveQuery(Class_ $class): bool
    {
        if ($this->classAnalyzer->isAnonymousClass($class)) {
            return false;
        }

        $classReflection = $this->reflectionResolver->resolveClassReflection($class);

        if (!$classReflection instanceof ClassReflection) {
            return false;
        }

        $parentClassReflections = $classReflection->getParents();

        if (!$this->isContainActiveQuery($parentClassReflections)) {
            return false;
        }

        return true;
    }

    /**
     * @param ClassReflection[] $parentClassReflections
     */
    private function isContainActiveQuery(array $parentClassReflections): bool
    {
        foreach ($parentClassReflections as $classReflection) {
            if ($classReflection->getName() === ActiveRecordConst::YII2_ACTIVE_QUERY_PATH) {
                return true;
            }
        }

        return false;
    }
}
