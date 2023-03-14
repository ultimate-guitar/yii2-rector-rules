<?php

use Muse\Rector\Tests\CompleteDynamicPropertiesForYii2ActiveRecordRector\Source\LinkEntity;

class Jet extends \yii\db\ActiveRecord
{
    public function getNiceCars()
    {
        return $this->hasMany(LinkEntity::class, ['user_id' => 'car_id']);
    }
}

?>
-----
<?php

use Muse\Rector\Tests\CompleteDynamicPropertiesForYii2ActiveRecordRector\Source\LinkEntity;

/**
 * @property-read LinkEntity[] $niceCars
 */
class Jet extends \yii\db\ActiveRecord
{
    public function getNiceCars()
    {
        return $this->hasMany(LinkEntity::class, ['user_id' => 'car_id']);
    }
}

?>
