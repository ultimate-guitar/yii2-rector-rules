# 2 Rules Overview

## CompleteDynamicPropertiesForYii2ActiveRecordRector

Add missing dynamic properties

- class: [`Muse\Rector\CompleteDynamicPropertiesForYii2ActiveRecordRector`](src/Rector/CompleteDynamicPropertiesForYii2ActiveRecordRector.php)

```diff
+/**
+ * @property int $value
+ */
 class SomeClass extends \yii\db\ActiveRecord
 {
     public function set()
     {
         $this->value = 5;
     }
 }
```

<br>

## CompleteMethodTypingForYii2QueryLinkedWithARRector

Add missing types for methods

- class: [`Muse\Rector\CompleteMethodTypingForYii2QueryLinkedWithARRector`](src/Rector/CompleteMethodTypingForYii2QueryLinkedWithARRector.php)

```diff
+/**
+ * @method Some|null one($db = null)
+ * @method Some[] all($db = null)
+ */
 class SomeQuery extends \yii\db\ActiveQuery
 {
 }
```

<br>
