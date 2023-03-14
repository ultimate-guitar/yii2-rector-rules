<?php

/**
 * Keep walking
 */
class Car extends \yii\db\ActiveRecord
{
    public function set()
    {
        $this->value = 5;
        $this->someString = 'qwe';
        $this->price = 100.500;
        $this->test = [100.500];

        $this->cnt = 13;
        $this->cnt = null;
    }
}

?>
-----
<?php

/**
 * Keep walking
 * @property int $value
 * @property string $someString
 * @property float $price
 * @property float[] $test
 * @property int|null $cnt
 */
class Car extends \yii\db\ActiveRecord
{
    public function set()
    {
        $this->value = 5;
        $this->someString = 'qwe';
        $this->price = 100.500;
        $this->test = [100.500];

        $this->cnt = 13;
        $this->cnt = null;
    }
}

?>
