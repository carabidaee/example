<?php

namespace backend\models\spec_mailing_rule;

use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "KL_ContactsFromSpecMailingRules".
 *
 * @property int $id
 * @property int $id_rule
 * @property int $id_contact
 */
class ContactsFromRulesUsingSegmentation extends \yii\db\ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName(): string
    {
        return 'KL_ContactsFromSpecMailingRules';
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            [['id_rule', 'id_contact'], 'required'],
            [['id_rule', 'id_contact'], 'integer'],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels(): array
    {
        return [
            'id_rule' => 'Id Rule',
            'id_contact' => 'Id Contact',
        ];
    }

    /**
     * Метод для записи контактов из правил, которые используются для сегментации
     * @throws \Exception
     */
    public static function checkRulesAndWriteContacts(): void
    {
        $ids_rules_using_segmentation = ArrayHelper::getColumn(
            SpecMailingRule::find()
                ->select('id')
                ->where(['use_for_segmentation' => 1])
                ->asArray()
                ->all(),
            "id"
        );
        $saved_ids_rules = ArrayHelper::getColumn(
            self::find()
                ->select('id_rule')
                ->distinct()
                ->asArray()
                ->all(),
            'id_rule');
        $ids_rules_to_delete = array_diff($saved_ids_rules, $ids_rules_using_segmentation);
        self::deleteAllContactsFromRules($ids_rules_to_delete);

        foreach ($ids_rules_using_segmentation as $id_rule) {
            self::writeContactsForRule($id_rule);
        }
    }

    /**
     * Метод для записи контактов для конкретного правила
     * @param int $id_rule
     * @return bool
     * @throws \Exception
     */
    public static function writeContactsForRule(int $id_rule): bool
    {
        try {
            $sql_request = (new SQLRequestForSpecMailingRule($id_rule))->get();
            $connection = \Yii::$app->db;
            $command = $connection->createCommand($sql_request);
            $result = $command->queryAll();
        } catch (\Exception $e) {
            throw $e;
        }
        $ids_contacts = ArrayHelper::getColumn($result, "ID_Contact");

        $saved_contacts = self::find()
            ->select(['id_contact'])
            ->where(['id_rule' => $id_rule])
            ->asArray()
            ->all();
        if (!$saved_contacts) {
            self::saveContactsFromRule($ids_contacts, $id_rule);
            return true;
        }
        $saved_ids_contacts = ArrayHelper::getColumn($saved_contacts, 'id_contact');

        $ids_contacts_to_add = array_diff($ids_contacts, $saved_ids_contacts);
        self::saveContactsFromRule($ids_contacts_to_add, $id_rule);

        $ids_contacts_to_delete = array_diff($saved_ids_contacts, $ids_contacts);
        self::deleteContactsFromRule($ids_contacts_to_delete, $id_rule);

        return true;
    }

    /**
     * Метод для сохранения контактов для конкретного правила
     * @param array $ids_contacts
     * @param int $id_rule
     */
    private static function saveContactsFromRule(array $ids_contacts, int $id_rule): void
    {
        foreach ($ids_contacts as $id_contact) {
            $contact = new self();
            $contact->setAttributes([
                'id_rule' => $id_rule,
                'id_contact' => $id_contact
            ]);
            $contact->save(false);
        }
    }

    /**
     * Метод для удаления контактов из конкретного правила
     * @param array $ids_contacts
     * @param int $id_rule
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    private static function deleteContactsFromRule(array $ids_contacts, int $id_rule): void
    {
        foreach ($ids_contacts as $id_contact) {
            $contact = self::findOne([
                'id_rule' => $id_rule,
                'id_contact' => $id_contact
            ]);
            if ($contact) {
                $contact->delete();
            }
        }
    }

    /**
     * Метод для для удаления всех контактов из конкретного правила или правил
     * @param array|int $ids_rules
     */
    public static function deleteAllContactsFromRules($ids_rules): void
    {
        foreach ((array)$ids_rules as $id_rule) {
            self::deleteAll(['id_rule' => $id_rule]);
        }
    }
}
