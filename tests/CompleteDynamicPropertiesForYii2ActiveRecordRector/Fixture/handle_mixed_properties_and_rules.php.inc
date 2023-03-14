<?php

class Helicopter extends \yii\db\ActiveRecord
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
 * @property int $value
 * @property string $wheel
 * @property string $tail
 * @property int $wing
 */
class Helicopter extends \yii\db\ActiveRecord
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
