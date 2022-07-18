<?php


namespace backend\models\spec_mailing_rule;


use backend\components\LabActiveDataProvider;

class SpecMailingRuleSearch extends SpecMailingRule
{
    /**
     * @return array
     */
    public function fields(): array
    {
        return [
            'name_rule' => 'name_rule',
        ];
    }

    /**
     * Поиск правил рассылок по переданным параметрам
     * @param mixed $params
     * @return LabActiveDataProvider
     */
    public function search($params): LabActiveDataProvider
    {
        $query = SpecMailingRule::find();
        $this->load($params, '');
        if ($this->validate()) {
            $query->andFilterWhere(['like', 'name_rule', $this->name_rule]);
        }
        return (new LabActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 30,
                'defaultPageSize' => 30,
                'pageSizeLimit' => [1, 100],
                'forcePageParam' => false,
                'pageSizeParam' => false,
            ],
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC
                ]
            ],
        ]));
    }
}