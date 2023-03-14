<?php

/**
 * Keep walking
 * @property int $value
 * @property string|bool $someString
 */
class Rocket extends \yii\db\ActiveRecord
{
    public function set()
    {
        $this->value = 5;
        $this->someString = 'qwe';
        $this->price = 100.500;
    }
}

?>
-----
<?php

/**
 * Keep walking
 * @property int $value
 * @property string|bool $someString
 * @property float $price
 */
class Rocket extends \yii\db\ActiveRecord
{
    public function set()
    {
        $this->value = 5;
        $this->someString = 'qwe';
        $this->price = 100.500;
    }
}

?>
