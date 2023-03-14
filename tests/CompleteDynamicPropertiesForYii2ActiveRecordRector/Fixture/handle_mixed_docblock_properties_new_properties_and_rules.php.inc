<?php

/**
 * @property int $id
 * @property string $wheel
 */
class Submarine extends \yii\db\ActiveRecord
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

    public function set()
    {
        $this->value = 5;
    }
}

?>
-----
<?php

/**
 * @property int $id
 * @property string $wheel
 * @property int $value
 * @property string $tail
 * @property int $wing
 */
class Submarine extends \yii\db\ActiveRecord
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

    public function set()
    {
        $this->value = 5;
    }
}

?>
