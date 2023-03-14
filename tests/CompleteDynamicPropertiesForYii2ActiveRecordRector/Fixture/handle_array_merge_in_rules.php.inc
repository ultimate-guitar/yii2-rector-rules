<?php

namespace Parents {
    /**
     * @property string $wheel
     * @property string $tail
     * @property int $wing
     */
    class ParentUnicycle extends \yii\db\ActiveRecord
    {
        /**
         * @inheritdoc
         */
        public function rules()
        {
            return [
                [['wheel', 'tail'], 'string'],
                [['wheel', 'tail'], 'required'],
                [['wing'], 'integer'],
            ];
        }
    }
}

namespace Children {
    class Unicycle extends \Parents\ParentUnicycle
    {
        /**
         * @inheritdoc
         */
        public function rules()
        {
            return \array_merge(
                parent::rules(),
                [
                    ['chair', 'string'],
                    [['door'], 'integer'],
                ]
            );
        }
    }
}

?>
-----
<?php

namespace Parents {
    /**
     * @property string $wheel
     * @property string $tail
     * @property int $wing
     */
    class ParentUnicycle extends \yii\db\ActiveRecord
    {
        /**
         * @inheritdoc
         */
        public function rules()
        {
            return [
                [['wheel', 'tail'], 'string'],
                [['wheel', 'tail'], 'required'],
                [['wing'], 'integer'],
            ];
        }
    }
}

namespace Children {
    /**
     * @property string $wheel
     * @property string $tail
     * @property int $wing
     * @property string $chair
     * @property int $door
     */
    class Unicycle extends \Parents\ParentUnicycle
    {
        /**
         * @inheritdoc
         */
        public function rules()
        {
            return \array_merge(
                parent::rules(),
                [
                    ['chair', 'string'],
                    [['door'], 'integer'],
                ]
            );
        }
    }
}

?>
