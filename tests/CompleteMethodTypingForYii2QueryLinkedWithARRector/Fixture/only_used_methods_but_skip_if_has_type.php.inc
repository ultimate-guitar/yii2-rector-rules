<?php

namespace Ar {

    use Ar\Queries\MotoQuery;

    class Moto extends \yii\db\ActiveRecord
    {
        public static function find()
        {
            return new MotoQuery(get_called_class());
        }
    }
}

namespace Ar\Queries {
    class MotoQuery extends \yii\db\ActiveQuery
    {
        public function active(): self
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

    use Ar\Queries\MotoQuery;

    class Moto extends \yii\db\ActiveRecord
    {
        public static function find()
        {
            return new MotoQuery(get_called_class());
        }
    }
}

namespace Ar\Queries {
    /**
     * @method \Ar\Moto|null one(\yii\db\Connection $db = null)
     * @method \Ar\Moto[] all(\yii\db\Connection $db = null)
     * @method \Ar\Moto[] each(int $batchSize = 100, \yii\db\Connection $db = null)
     * @method \Ar\Moto[] batch(int $batchSize = 100, \yii\db\Connection $db = null)
     * @method int count(string $q = '*', \yii\db\Connection $db = null)
     */
    class MotoQuery extends \yii\db\ActiveQuery
    {
        public function active(): self
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
