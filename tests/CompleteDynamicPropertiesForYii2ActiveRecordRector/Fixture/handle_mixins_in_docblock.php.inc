<?php

/**
 * @mixin \yii\db\ActiveRecord
 */
class Bicycle
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
 * @mixin \yii\db\ActiveRecord
 * @property int $value
 * @property string $someString
 * @property float $price
 * @property float[] $test
 * @property int|null $cnt
 */
class Bicycle
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
