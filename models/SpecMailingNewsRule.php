<?php


namespace backend\models\spec_mailing_rule;


use yii\db\ActiveRecord;

/**
 * This is the model class for table "KL_SpecMailingNewsRule".
 *
 * @property int $id
 * @property int $id_rule
 * @property int $id_news
 * @property int $is_exception
 * @property int $min_amount
 * @property int $max_amount
 * @property int $is_have_duplicates
 * @property float $buy_coeff
 * @property int $buy_term
 * @property float $basket_coeff
 * @property int $basket_term
 * @property float $differed_coeff
 * @property int $differed_term
 * @property float $viewed_coeff
 * @property int $viewed_term
 */
class SpecMailingNewsRule extends ActiveRecord
{
    const SCENARIO_UNSAVED_AND_UNBOUND = 'unsaved_and_unbound';

    /**
     * @return string
     */
    public static function tableName(): string
    {
        return 'KL_SpecMailingNewsRule';
    }

    /**
     * @return array
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'id_rule' => 'ID правила',
            'id_news' => 'ID новости',
            'is_exception' => 'Признак исключения',
            'min_amount' => 'Минимальное количество',
            'max_amount' => 'Максимальное количество',
            'is_have_duplicates' => 'Признак использования одинаковых товаров',
            'buy_coeff' => 'Коэффициент для купленных товаров',
            'buy_term' => 'Количество для купленных товаров',
            'basket_coeff' => 'Коэффициент для товаров в корзине',
            'basket_term' => 'Количество товаров в корзине',
            'differed_coeff' => 'Коэффициент для отложенных товаров',
            'differed_term' => 'Количество для отложенных товаров',
            'viewed_coeff' => 'Коэффициент для просмотренных товаров',
            'viewed_term' => 'Количетсво просмотренных товаров',
        ];
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            ['id_rule', 'required', 'except' => self::SCENARIO_UNSAVED_AND_UNBOUND],
            [['id_news', 'min_amount', 'buy_coeff', 'buy_term', 'basket_coeff', 'basket_term',
                'differed_coeff', 'differed_term', 'viewed_coeff', 'viewed_term'], 'required'],
            [['id', 'id_rule', 'id_news', 'min_amount', 'max_amount', 'is_have_duplicates', 'buy_term', 'basket_term',
                'differed_term', 'viewed_term'], 'integer'],
            ['is_exception', 'in', 'range' => [0, 1]],
            [['buy_coeff', 'basket_coeff', 'differed_coeff', 'viewed_coeff'], 'double', 'max' => 1],
        ];
    }
}