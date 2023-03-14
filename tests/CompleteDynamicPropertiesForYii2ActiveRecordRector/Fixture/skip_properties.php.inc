<?php

class Baloon extends \yii\db\ActiveRecord
{
    public string $password = '';

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['password'], 'string', 'max' => 100],
        ];
    }
}

?>
-----
<?php

class Baloon extends \yii\db\ActiveRecord
{
    public string $password = '';

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['password'], 'string', 'max' => 100],
        ];
    }
}

?>