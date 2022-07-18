<?php


namespace scripts\controllers;


use backend\models\spec_mailing_rule\ContactsFromRulesUsingSegmentation;

class SpecMailingRulesController extends ConsoleController
{
    /**
     * Запись контактов для правил, использующихся для сегментации
     */
    public function actionWriteContacts(): void
    {
        try {
            ContactsFromRulesUsingSegmentation::checkRulesAndWriteContacts();
        } catch (\Exception $e) {
            \cTimelog::log('Error: ' . $e->getMessage());
        }
    }

    /**
     * Запись контактов для конкретного правила
     * @param int $id_rule
     */
    public function actionWriteContactsForRule(int $id_rule): void
    {
        try {
            \cFactory::getMemcache()->setValue("writes_contacts_for_rule_{$id_rule}", true, 3600);
            ContactsFromRulesUsingSegmentation::writeContactsForRule($id_rule);
            \cFactory::getMemcache()->delete("writes_contacts_for_rule_{$id_rule}");
        } catch (\Exception $e) {
            \cTimelog::log('Error: ' . $e->getMessage());
        }
    }
}