<?php

namespace backend\models\spec_mailing_rule;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "KL_SpecMailingRule_new".
 *
 * @property int $id
 * @property string $name_rule
 * @property int $use_for_segmentation
 * @property int $no_orders_in_process
 * @property int $did_not_order
 * @property int $did_not_come
 * @property null|SpecMailingDeliveryException[] $deliveryExceptions
 * @property null|SpecMailingNewsRule[] $newsRules
 * @property null|SpecMailingDeliveryException[] $unsaved_delivery_exceptions
 * @property bool $is_delivery_exceptions_changed
 * @property null|SpecMailingNewsRule[] $unsaved_news_rules
 * @property bool $is_news_rules_changed
 */
class SpecMailingRule extends ActiveRecord
{
    private $unsaved_delivery_exceptions;
    private $is_delivery_exceptions_changed = false;
    private $unsaved_news_rules;
    private $is_news_rules_changed = false;

    /**
     * @return string
     */
    public static function tableName(): string
    {
        return 'KL_SpecMailingRule_new';
    }

    /**
     * @return ActiveQuery
     */
    public function getDeliveryExceptions(): ActiveQuery
    {
        return $this->hasMany(SpecMailingDeliveryException::class, ['id_rule' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getNewsRules(): ActiveQuery
    {
        return $this->hasMany(SpecMailingNewsRule::class, ['id_rule' => 'id']);
    }

    /**
     * @return array
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID правила',
            'name_rule' => 'Название правила',
            'use_for_segmentation' => 'Признак сегментации',
            'no_orders_in_process' => 'Признак отсутствия заказов в обработке',
            'did_not_order' => 'Кол-во дней, за которые не делались заказы',
            'did_not_come' => 'Кол-во дней, в течение которых клиент на заходил на сайт и в приложение'
        ];
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            [['name_rule'], 'required'],
            [['id', 'did_not_order', 'did_not_come'], 'integer'],
            ['name_rule', 'string'],
            [['use_for_segmentation', 'no_orders_in_process'], 'in', 'range' => [0, 1]],
        ];
    }

    /**
     * @param null $attributeNames
     * @param bool $clearErrors
     * @return bool
     */
    public function validate($attributeNames = null, $clearErrors = true): bool
    {
        if (parent::validate($attributeNames, $clearErrors)) {
            if (!$this->validateDeliveryExceptions()) {
                return false;
            }
            if (!$this->validateNewsRules()) {
                return false;
            }
            return true;
        }

        return false;
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert): bool
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if (!$insert && $this->isWritesContacts()) {
            $this->addError('id', 'Это правило нельзя сохранять, пока по его данным записываются 
                контакты');
            return false;
        }
        return true;
    }

    /**
     * @param bool $runValidation
     * @param null $attributeNames
     * @return bool
     */
    public function save($runValidation = true, $attributeNames = null): bool
    {
        if (parent::save($runValidation, $attributeNames)) {
            $this->saveDeliveryExceptions();
            $this->saveNewsRules();

            $this->writeOrDeleteContacts();
            return true;
        }

        return false;
    }

    /**
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function delete(): bool
    {
        if (parent::delete()) {
            $this->deleteDeliveryExceptions();
            $this->deleteNewsRules();
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param array $data
     * @param null $formName
     * @return bool
     */
    public function load($data, $formName = null): bool
    {
        if (!parent::load($data, $formName)) {
            return false;
        }

        if ($data['is_delivery_exceptions_changed']) {
            $this->loadDeliveryExceptions($data['delivery_exceptions']);
        }

        if ($data['is_news_rules_changed']) {
            $this->loadNewsRules($data['news_rules']);
        }
        return true;
    }

    /**
     * Загружаем данные исключений доставок
     * @param mixed $data
     */
    private function loadDeliveryExceptions($data): void
    {
        $this->is_delivery_exceptions_changed = true;

        if ($data && is_array($data)) {
            foreach ($data as $item) {
                $delivery_exception = new SpecMailingDeliveryException();
                $delivery_exception->load($item, '');
                $this->unsaved_delivery_exceptions[] = $delivery_exception;
            }
        }
    }

    /**
     * Валидация данных исключений доставок
     * @return bool
     */
    private function validateDeliveryExceptions(): bool
    {
        if ($this->unsaved_delivery_exceptions) {
            foreach ($this->unsaved_delivery_exceptions as $delivery_exception) {
                if (!$delivery_exception->validate(['delivery_kind_type', 'delivery_kind'])) {
                    foreach ($delivery_exception->getFirstErrors() as $name_attribute => $error) {
                        $this->addError($name_attribute, $error);
                    }
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Сохраняем исключения доставок
     * @return bool
     */
    private function saveDeliveryExceptions(): bool
    {
        if (!$this->is_delivery_exceptions_changed) {
            return true;
        }
        // Удаляем старые исключения доставок
        if ($this->deliveryExceptions) {
            $this->unlinkAll('deliveryExceptions', true);
        }
        // Записываем новые исключения доставок
        if ($this->unsaved_delivery_exceptions) {
            foreach ($this->unsaved_delivery_exceptions as $delivery_exception) {
                $this->link('deliveryExceptions', $delivery_exception);
            }
        }
        return true;
    }

    /**
     * Удаляем исключения доставок
     */
    private function deleteDeliveryExceptions(): void
    {
        if ($this->deliveryExceptions) {
            $this->unlinkAll('deliveryExceptions', true);
        }
    }

    /**
     * Загружаем данные правил новостей
     * @param mixed $data
     */
    private function loadNewsRules($data): void
    {
        $this->is_news_rules_changed = true;

        if ($data && is_array($data)) {
            foreach ($data as $item) {
                $news_rule = new SpecMailingNewsRule();
                $news_rule->scenario = SpecMailingNewsRule::SCENARIO_UNSAVED_AND_UNBOUND;
                $news_rule->load($item, '');
                $this->unsaved_news_rules[] = $news_rule;
            }
        }
    }

    /**
     * Валидация данных правил новостей
     * @return bool
     */
    private function validateNewsRules(): bool
    {
        if ($this->unsaved_news_rules) {
            foreach ($this->unsaved_news_rules as $news_rule) {
                if (!$news_rule->validate()) {
                    foreach ($news_rule->getFirstErrors() as $name_attribute => $error) {
                        $this->addError($name_attribute, $error);
                    }
                    return false;
                }
            }
        }

        if (!$this->unsaved_news_rules && !$this->newsRules) {
            $this->addError('id_news', 'Необходимо заполнить «ID новости».');
            return false;
        }

        return true;
    }

    /**
     * Сохраняем правила новостей
     * @return bool
     */
    private function saveNewsRules(): bool
    {
        if (!$this->is_news_rules_changed) {
            return true;
        }
        // Удаляем старые правила новостей
        if ($this->newsRules) {
            $this->unlinkAll('newsRules', true);
        }
        // Записываем новые правила новостей
        if ($this->unsaved_news_rules) {
            foreach ($this->unsaved_news_rules as $new_rule) {
                $this->link('newsRules', $new_rule);
            }
        }
        return true;
    }

    /**
     * Удаляем правила новостей
     */
    private function deleteNewsRules(): void
    {
        if ($this->newsRules) {
            $this->unlinkAll('newsRules', true);
        }
    }

    /**
     * Получить массив с данными доставок-исключений
     * @return array
     */
    public function getArrayOfDeliveryExceptions(): array
    {
        $response = [];
        if (!$this->deliveryExceptions) {
            return $response;
        }

        foreach ($this->deliveryExceptions as $deliveryException) {
            $response[] = $deliveryException->toArray(['delivery_kind_type', 'delivery_kind']);
        }
        return $response;
    }

    /**
     * Получить массив с данными правил новостей
     * @return array
     */
    public function getArrayOfNewsRules(): array
    {
        $response = [];
        if (!$this->newsRules) {
            return $response;
        }

        foreach ($this->newsRules as $news_rule) {
            $response[] = $news_rule->toArray();
        }
        return $response;
    }

    /**
     * Метод проверяет, не записываются ли сейчас контакты для правила
     * @return bool
     */
    public function isWritesContacts(): bool
    {
        if ((bool)$this->use_for_segmentation) {
            $key = "writes_contacts_for_rule_{$this->id}";
            if (\cFactory::getMemcache()->getValue($key) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Запускаем скрипт для получения контактов, с последующей записью их в бд, или удалем все записанные контакты
     */
    private function writeOrDeleteContacts(): void
    {
        if ((bool)$this->use_for_segmentation) {
            $exe = PHPCLI . BASE_DIR . "cli.php spec-mailing-rules/write-contacts-for-rule {$this->id} > /dev/null &";
            exec($exe);
        } else {
            ContactsFromRulesUsingSegmentation::deleteAllContactsFromRules($this->id);
        }
    }
}