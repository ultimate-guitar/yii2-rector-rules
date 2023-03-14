<?php

use Muse\Rector\Tests\CompleteDynamicPropertiesForYii2ActiveRecordRector\Source\LinkEntity;

class Scooter extends \yii\db\ActiveRecord
{
    public function getNiceCar()
    {
        return $this->hasOne(LinkEntity::class, ['user_id' => 'car_id']);
    }
}

?>
-----
<?php

use Muse\Rector\Tests\CompleteDynamicPropertiesForYii2ActiveRecordRector\Source\LinkEntity;

/**
 * @property-read LinkEntity $niceCar
 */
class Scooter extends \yii\db\ActiveRecord
{
    public function getNiceCar()
    {
        return $this->hasOne(LinkEntity::class, ['user_id' => 'car_id']);
    }
}

?>
