<?php

namespace Ar {

    use Ar\Queries\BoatQuery;

    class Boat extends \yii\db\ActiveRecord
    {
        public static function find()
        {
            return new BoatQuery(get_called_class());
        }
    }
}

namespace Ar\Queries {
    class BoatQuery extends \yii\db\ActiveQuery
    {
        public function active()
        {
            return $this->andWhere(['>=', '1', '2'])->orderBy('abc')->orWhere('xyz');
        }

        public function new()
        {
            return $this->count();
        }
    }
}
?>
-----
<?php

namespace Ar {

    use Ar\Queries\BoatQuery;

    class Boat extends \yii\db\ActiveRecord
    {
        public static function find()
        {
            return new BoatQuery(get_called_class());
        }
    }
}

namespace Ar\Queries {
    /**
     * @method \Ar\Boat|null one(\yii\db\Connection $db = null)
     * @method \Ar\Boat[] all(\yii\db\Connection $db = null)
     * @method \Ar\Boat[] each(int $batchSize = 100, \yii\db\Connection $db = null)
     * @method \Ar\Boat[] batch(int $batchSize = 100, \yii\db\Connection $db = null)
     * @method BoatQuery andWhere(string|array|\yii\db\ExpressionInterface $condition, array $params = [])
     * @method BoatQuery orWhere(string|array|\yii\db\ExpressionInterface $condition, array $params = [])
     * @method BoatQuery orderBy(string|array|\yii\db\ExpressionInterface $columns)
     * @method int count(string $q = '*', \yii\db\Connection $db = null)
     */
    class BoatQuery extends \yii\db\ActiveQuery
    {
        public function active()
        {
            return $this->andWhere(['>=', '1', '2'])->orderBy('abc')->orWhere('xyz');
        }

        public function new()
        {
            return $this->count();
        }
    }
}
?>
