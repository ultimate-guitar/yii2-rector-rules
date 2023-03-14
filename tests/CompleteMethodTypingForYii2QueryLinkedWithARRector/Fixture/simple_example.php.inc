<?php

namespace Ar {

    use Ar\Queries\JumperQuery;

    class Jumper extends \yii\db\ActiveRecord
    {
        public static function find()
        {
            return new JumperQuery(get_called_class());
        }
    }
}

namespace Ar\Queries {
    class JumperQuery extends \yii\db\ActiveQuery
    {
    }
}
?>
-----
<?php

namespace Ar {

    use Ar\Queries\JumperQuery;

    class Jumper extends \yii\db\ActiveRecord
    {
        public static function find()
        {
            return new JumperQuery(get_called_class());
        }
    }
}

namespace Ar\Queries {
    /**
     * @method \Ar\Jumper|null one(\yii\db\Connection $db = null)
     * @method \Ar\Jumper[] all(\yii\db\Connection $db = null)
     * @method \Ar\Jumper[] each(int $batchSize = 100, \yii\db\Connection $db = null)
     * @method \Ar\Jumper[] batch(int $batchSize = 100, \yii\db\Connection $db = null)
     */
    class JumperQuery extends \yii\db\ActiveQuery
    {
    }
}
?>
