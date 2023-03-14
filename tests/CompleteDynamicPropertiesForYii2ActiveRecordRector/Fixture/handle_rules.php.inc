<?php

class Plane extends \yii\db\ActiveRecord
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

?>
-----
<?php

/**
 * @property string $wheel
 * @property string $tail
 * @property int $wing
 */
class Plane extends \yii\db\ActiveRecord
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

?>
