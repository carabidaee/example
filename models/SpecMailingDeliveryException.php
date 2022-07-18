<?php


namespace backend\models\spec_mailing_rule;


use yii\db\ActiveRecord;

/**
 * This is the model class for table "KL_SpecMailingDeliveryException".
 *
 * @property int $id
 * @property int $id_rule
 * @property int $delivery_kind_type
 * @property null|int $delivery_kind
 */
class SpecMailingDeliveryException extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName(): string
    {
        return 'KL_SpecMailingDeliveryException';
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            [['id_rule', 'delivery_kind_type'], 'required'],
            [['id', 'id_rule', 'delivery_kind_type', 'delivery_kind'], 'integer'],
        ];
    }
}