<?php

namespace Ar {

    use Ar\Queries\BicycleQuery;

    class Bicycle extends \yii\db\ActiveRecord
    {
        public static function find()
        {
            return new BicycleQuery(get_called_class());
        }
    }
}

namespace Ar\Queries {
    class BicycleQuery extends \yii\db\ActiveQuery
    {
        public function all($db = null)
        {
        }
    }
}
?>
-----
<?php

namespace Ar {

    use Ar\Queries\BicycleQuery;

    class Bicycle extends \yii\db\ActiveRecord
    {
        public static function find()
        {
            return new BicycleQuery(get_called_class());
        }
    }
}

namespace Ar\Queries {
    /**
     * @method \Ar\Bicycle|null one(\yii\db\Connection $db = null)
     * @method \Ar\Bicycle[] each(int $batchSize = 100, \yii\db\Connection $db = null)
     * @method \Ar\Bicycle[] batch(int $batchSize = 100, \yii\db\Connection $db = null)
     */
    class BicycleQuery extends \yii\db\ActiveQuery
    {
        public function all($db = null)
        {
        }
    }
}
?>
