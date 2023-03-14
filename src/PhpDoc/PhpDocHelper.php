<?php

declare(strict_types=1);

namespace Muse\PhpDoc;

use Muse\ActiveRecordAnalyzer\ActiveRecordConst;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocChildNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PropertyTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;

class PhpDocHelper
{
    public function __construct(
        private PhpDocInfoFactory $phpDocInfoFactory
    ) {
    }

    /**
     * @param array<string, string> $paramsNameToType
     * @return array<PhpDocTagNode>
     */
    public function convertToPhpDocWithPropertyTagNode(array $paramsNameToType, string $specificName): array
    {
        $result = [];

        if ($paramsNameToType === []) {
            return $result;
        }

        foreach ($paramsNameToType as $name => $type) {
            if ($type !== null) {
                $result[] = new PhpDocTagNode($specificName, new PropertyTagValueNode(new IdentifierTypeNode($type), '$' . $name, ''));
            }
        }

        return $result;
    }

    /**
     * @param array<string, string> $paramsNameToType
     * @return PhpDocTagNode[]
     */
    public function convertToPhpDocWithMethodTagNode(array $paramsNameToType, string $specificName): array
    {
        $result = [];

        if ($paramsNameToType === []) {
            return $result;
        }

        foreach ($paramsNameToType as $name => $type) {
            //todo handle as MethodTag
            $result[] = new PhpDocTagNode($specificName, new VarTagValueNode(new IdentifierTypeNode($type), $name, ''));
        }

        return $result;
    }

    public function createFromNode(Class_ $node): PhpDocInfo
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);

        if ($phpDocInfo === null) {
            $phpDocInfo = $this->phpDocInfoFactory->createEmpty($node);
        }

        return $phpDocInfo;
    }

    /**
     * @param Property[] $properties
     * @return PhpDocTagNode[]
     */
    public function convertPropertiesToDocTag(array $properties): array
    {
        $result = [];

        foreach ($properties as $property) {
            if (count($property->props) !== 1) {
                continue;
            }

            $docBlock = $property->getAttribute('php_doc_info');

            if (!($docBlock instanceof PhpDocInfo) && count($docBlock->getPhpDocNode()->children) !== 1) {
                continue;
            }

            $tagNode = $docBlock->getPhpDocNode()->children[0] ?? null;

            if (!($tagNode instanceof PhpDocTagNode) || !($tagNode->value instanceof VarTagValueNode || $tagNode->value instanceof PropertyTagValueNode)) {
                continue;
            }

            $tagNode->name = ActiveRecordConst::READ_WRITE_PROPERTY;

            if ($tagNode->value instanceof VarTagValueNode) {
                $tagNode->value = new PropertyTagValueNode($tagNode->value->type, '$' . $property->props[0]->name->name, '');
            } else {
                continue;
            }

            $result[] = $tagNode;
        }

        return $result;
    }

    /**
     * Don't overwrite existed property
     *
     * @param array<PhpDocChildNode> $originalNodes
     * @param array<PhpDocChildNode|PhpDocTagNode> $newDocTagNodes
     * @return array<PhpDocChildNode>
     */
    public function mergePhpDocChildNodes(array $originalNodes, array $newDocTagNodes): array
    {
        if ($originalNodes === []) {
            return $newDocTagNodes;
        }

        $originalNames = [];
        foreach ($originalNodes as $node) {
            if ($node instanceof PhpDocTagNode && $node->value instanceof PropertyTagValueNode) {
                $originalNames[] = $node->value->propertyName;
            }
        }

        foreach ($newDocTagNodes as $docTag) {
            if ($docTag instanceof PhpDocTagNode && $docTag->value instanceof PropertyTagValueNode && !in_array($docTag->value->propertyName, $originalNames, true)) {
                $originalNodes[] = $docTag;
            }
        }

        return $originalNodes;
    }
}
