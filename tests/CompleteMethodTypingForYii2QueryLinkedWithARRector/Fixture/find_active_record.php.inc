<?php

namespace Far\Far\Away\Kingdom {
    use Abs\Tract\HorseHelper;

    class Horse extends \yii\db\ActiveRecord
    {
        public static function find()
        {
            return new HorseHelper(get_called_class());
        }
    }
}

namespace Abs\Tract {
    class HorseHelper extends \yii\db\ActiveQuery
    {
        public function active()
        {
            return $this->andWhere(['>=', '1', '2'])->orderBy('abc')->orWhere('xyz');
        }
    }
}
?>
-----
<?php

namespace Far\Far\Away\Kingdom {
    use Abs\Tract\HorseHelper;

    class Horse extends \yii\db\ActiveRecord
    {
        public static function find()
        {
            return new HorseHelper(get_called_class());
        }
    }
}

namespace Abs\Tract {
    /**
     * @method \Far\Far\Away\Kingdom\Horse|null one(\yii\db\Connection $db = null)
     * @method \Far\Far\Away\Kingdom\Horse[] all(\yii\db\Connection $db = null)
     * @method \Far\Far\Away\Kingdom\Horse[] each(int $batchSize = 100, \yii\db\Connection $db = null)
     * @method \Far\Far\Away\Kingdom\Horse[] batch(int $batchSize = 100, \yii\db\Connection $db = null)
     * @method HorseHelper andWhere(string|array|\yii\db\ExpressionInterface $condition, array $params = [])
     * @method HorseHelper orWhere(string|array|\yii\db\ExpressionInterface $condition, array $params = [])
     * @method HorseHelper orderBy(string|array|\yii\db\ExpressionInterface $columns)
     */
    class HorseHelper extends \yii\db\ActiveQuery
    {
        public function active()
        {
            return $this->andWhere(['>=', '1', '2'])->orderBy('abc')->orWhere('xyz');
        }
    }
}
?>
