<?php


namespace backend\controllers;

use backend\models\spec_mailing_rule\SpecMailingRule;
use backend\models\spec_mailing_rule\SpecMailingRuleSearch;
use yii\filters\VerbFilter;

class SpecMailingRulesController extends AbstractBaseController
{
    /**
     * @param $action
     * @return bool
     * @throws \yii\web\BadRequestHttpException
     */
    public function beforeAction($action)
    {
        $this->view->params['selectmenuurl'] = 'yii/spec-mailing-rules/';
        return parent::beforeAction($action);
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'delete' => ['post'],
            ],
        ];
        return $behaviors;
    }

    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $search = new SpecMailingRuleSearch();
            $provider = $search->search(\Yii::$app->request->get());
            $data = [
                'rules' => $provider->getArrayModels(['id', 'name_rule']),
                'pagination' => $provider->getArrayPagination(),
                'search' => $search->name_rule,
            ];
            return $this->ajax($data);
        }
        return $this->renderContent('');
    }

    public function actionCreate()
    {
        if (\Yii::$app->request->isAjax && \Yii::$app->request->isPost) {
            $mailing_rule = new SpecMailingRule();
            $mailing_rule->load(\Yii::$app->request->post(), 'rule');
            if (!$mailing_rule->save()) {
                return $this->ajax([
                    'errors' => $mailing_rule->getFirstErrors()
                ]);
            }
        }
        return $this->renderContent('');
    }

    public function actionUpdate($id_rule)
    {
        if (\Yii::$app->request->isAjax) {
            $mailing_rule = SpecMailingRule::findOne(['id' => $id_rule]);

            if (\Yii::$app->request->isGet) {
                if (!$mailing_rule) {
                    return $this->ajax(['rule' => null]);
                }
                return $this->ajax([
                    'rule' => $mailing_rule->toArray(),
                    'delivery_exceptions' => $mailing_rule->getArrayOfDeliveryExceptions(),
                    'news_rules' => $mailing_rule->getArrayOfNewsRules(),
                    'is_writes_contacts' => $mailing_rule->isWritesContacts()
                ]);
            }

            if (\Yii::$app->request->isPost) {
                $data = \Yii::$app->request->post();
                $mailing_rule->load($data, 'rule');
                if (!$mailing_rule->save()) {
                    return $this->ajax([
                        'errors' => $mailing_rule->getFirstErrors()
                    ]);
                }
            }
        }
        return $this->renderContent('');
    }

    public function actionDelete()
    {
        $id_rule = \Yii::$app->request->post('id_rule');
        if (!$id_rule) {
            return $this->ajax(['error' => 'Необходимо передать id_rule']);
        }

        $mailing_rule = SpecMailingRule::findOne(['id' => $id_rule]);
        try {
            $mailing_rule->delete();
        } catch (\Throwable $e) {
            return $this->ajax(['error' => $e->getMessage()]);
        }

        return $this->ajax(['success' => true]);
    }

    public function actionIsWritesContacts($id_rule)
    {
        $is_writes_contacts = false;
        $mailing_rule = SpecMailingRule::findOne(['id' => $id_rule]);
        if ($mailing_rule && $mailing_rule->isWritesContacts()) {
            $is_writes_contacts = true;
        }

        return $this->ajax(['is_writes_contacts' => $is_writes_contacts]);
    }
}