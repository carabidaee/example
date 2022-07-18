<?php


namespace backend\models\spec_mailing_rule;

use Exception;

/**
 * Class SQLRequestForSpecMailingRule
 * @package backend\models\spec_mailing_rule
 * @property SpecMailingRule $mailing_rule
 */
class SQLRequestForSpecMailingRule
{
    private $mailing_rule;

    /**
     * SQLRequestForSpecMailingRule constructor.
     * @param int $id
     * @throws \Exception
     */
    public function __construct(int $id)
    {
        $this->mailing_rule = SpecMailingRule::findOne(['id' => $id]);
        if (!$this->mailing_rule) {
            throw new \Exception("Правила с таким id не существует");
        }
    }

    /**
     * Метод возвращает SQL-запрос для получения контактов по данным конкретного правила
     * @return string
     * @throws Exception
     */
    public function get(): string
    {
        try {
            $sql = "
                SELECT
                    con.ID_Contact
                FROM tblContact con WITH(NOLOCK)
                    JOIN tblCode cod WITH(NOLOCK)
                        ON con.ID_Contact = cod.ID_Contact
                    {$this->getSQLRequestToJoinNewsRules()}
                    CROSS APPLY (
                        SELECT
                            ID_Contact
                        FROM UserSession
                        WHERE ID_Contact = con.ID_Contact
                        GROUP BY ID_Contact
                        HAVING 
                            DATEDIFF(
                                DAY, 
                                IIF(MAX(CreateDate) > MAX(updatedate), MAX(CreateDate), MAX(updatedate)), 
                                GETDATE()
                            ) > {$this->mailing_rule->did_not_come}
                    ) us
                WHERE 
                    {$this->getConditionsForNewsRules()}
            ";
        } catch (\Exception $e) {
            $id_rule = $this->mailing_rule->id;
            $error_text = $e->getMessage();
            throw new Exception("Ошибка формирования запроса по данным правила с ID = $id_rule: $error_text");
        }

        return $sql;
    }

    /**
     * Метод возвращает SQL-подзапрос для правил новостей
     * @return string
     * @throws \Exception
     */
    private function getSQLRequestToJoinNewsRules(): string
    {
        $sql_query = "";
        if ($news_rules = $this->mailing_rule->newsRules) {
            foreach ($news_rules as $index => $news_rule) {
                $sql_query .= "
                    LEFT JOIN ({$this->getSQLToGetClientsWithBooksInPurchases($news_rule)}) AS buy{$index}
                        ON buy{$index}.id_contact = con.ID_Contact
                    LEFT JOIN ({$this->getSQLToGetClientsWithBooksInBasket($news_rule)}) AS basket{$index}
                        ON basket{$index}.ID_Main = cod.ID_Main
                    LEFT JOIN ({$this->getSQLToGetClientsWithBooksInDelayed($news_rule)}) AS differed{$index}
                        ON differed{$index}.ID_Contact = con.ID_Contact
                    LEFT JOIN ({$this->getSQLToGetClientsWithBooksInViewed($news_rule)}) AS viewed{$index}
                        ON viewed{$index}.id_main = cod.ID_Main
                ";
            }
        } else {
            throw new \Exception("Отсутствуют правила новостей!");
        }
        return $sql_query;
    }

    /**
     * Метод возвращает SQL запрос для получения клиентов, купивших книги, по id и сроку из правила новости.
     * @param SpecMailingNewsRule $news_rule
     * @return string
     */
    private function getSQLToGetClientsWithBooksInPurchases(SpecMailingNewsRule $news_rule): string
    {
        $operator = !((bool)$news_rule->is_have_duplicates) ? 'DISTINCT' : '';
        $join_for_delivery_exceptions = "";
        $conditions = "";
        $having = "";

        if ($delivery_exceptions = $this->mailing_rule->deliveryExceptions) {
            $join_for_delivery_exceptions = "JOIN tblDelivery td WITH(NOLOCK) ON td.id_orderim = bab.id_orderim
                JOIN KL_TypeDeliveryKind ktdk WITH(NOLOCK) ON ktdk.DeliveryKind = td.deliveryKind";

            $conditions_for_delivery_exceptions = "WHERE";
            foreach ($delivery_exceptions as $index => $delivery_exception) {
                if ($delivery_exception->delivery_kind) {
                    $conditions_for_delivery_exceptions .= ($index > 0 ? " AND " : " ")
                        . "td.DeliveryKind != {$delivery_exception->delivery_kind}";
                } else {
                    $conditions_for_delivery_exceptions .= ($index > 0 ? " AND " : " ")
                        . "ktdk.DeliveryKindType != {$delivery_exception->delivery_kind_type}";
                }
            }

            $conditions .= $conditions_for_delivery_exceptions;
        }

        if ($this->mailing_rule->did_not_order > 0) {
            $having .= "HAVING DATEDIFF(day, (
                SELECT MAX(dateCreated)
                FROM tblOrder AS o2 WITH(NOLOCK)
                WHERE o2.ID_Contact = bab.id_contact
            ), GETDATE()) > {$this->mailing_rule->did_not_order}";
        }
        if ((bool)$this->mailing_rule->no_orders_in_process) {
            $having .= ($having ? " AND" : "HAVING") . " NOT EXISTS(
                SELECT TOP 1
                    ID_Order
                FROM tblOrderHistory AS oh WITH(NOLOCK)
                WHERE
                    oh.ID_Contact = bab.id_contact 
                    AND ID_Status < 7
            )";
        }

        return "
            SELECT
                bab.id_contact, 
                COUNT({$operator} bab.id_books) as number
            FROM tblBooksAlreadyBuy bab WITH(NOLOCK)
                JOIN tblListBooks lb WITH(NOLOCK)
                    ON bab.id_books = lb.ID_Books
                        AND lb.listId = {$news_rule->id_news}
                JOIN tblOrder o WITH(NOLOCK)
                    ON o.ID_OrderIM = bab.id_orderim
                        AND DATEDIFF(DAY, o.dateCreated, GETDATE()) <= {$news_rule->buy_term}
                {$join_for_delivery_exceptions}
            {$conditions}
            GROUP BY bab.id_contact 
            {$having}";
    }

    /**
     * Метод возвращает SQL запрос для получения клиентов, добабивших книги в корзину, по id и сроку из правила новости.
     * @param SpecMailingNewsRule $news_rule
     * @return string
     */
    private function getSQLToGetClientsWithBooksInBasket(SpecMailingNewsRule $news_rule): string
    {
        $operator = !((bool)$news_rule->is_have_duplicates) ? 'DISTINCT' : '';

        return "
            SELECT 
                b.ID_Main, 
                COUNT({$operator} b.ID_Books) as number
            FROM tblBasket b WITH(NOLOCK)
                JOIN tblListBooks lb WITH(NOLOCK)
                    ON b.ID_Books = lb.ID_Books
                        AND lb.listId = {$news_rule->id_news}
                        AND DATEDIFF(DAY, b.CreateDate, GETDATE()) <= {$news_rule->basket_term}
            GROUP BY b.ID_Main";
    }

    /**
     * Метод возвращает SQL запрос для получения клиентов, добабивших книги в отложенные, по id и сроку из правила
     * новости.
     * @param SpecMailingNewsRule $news_rule
     * @return string
     */
    private function getSQLToGetClientsWithBooksInDelayed(SpecMailingNewsRule $news_rule): string
    {
        $operator = !((bool)$news_rule->is_have_duplicates) ? 'DISTINCT' : '';

        return "
            SELECT
                po.ID_Contact,
                COUNT({$operator} po.ID_Books) as number
            FROM tbl_putOrder po WITH(NOLOCK)
                JOIN tblListBooks lb WITH (NOLOCK)
                    ON po.ID_Books = lb.ID_Books
                        AND lb.listId = {$news_rule->id_news}
                        AND DATEDIFF(DAY, po.datecreated, GETDATE()) <= {$news_rule->differed_term}
            GROUP BY po.ID_Contact";
    }

    /**
     * Метод возвращает SQL запрос для получения клиентов, просмотревших книги, по id и сроку из правила новости.
     * @param SpecMailingNewsRule $news_rule
     * @return string
     */
    private function getSQLToGetClientsWithBooksInViewed(SpecMailingNewsRule $news_rule): string
    {
        $operator = !((bool)$news_rule->is_have_duplicates) ? 'DISTINCT' : '';

        return "
            SELECT
                uv.id_main,
                COUNT({$operator} uv.id_books) as number
            FROM UserVisit uv WITH(NOLOCK)
                JOIN tblListBooks lb WITH(NOLOCK)
                    ON uv.id_books = lb.ID_Books
                        AND lb.listId = {$news_rule->id_news}
                        AND DATEDIFF(DAY, uv.date, GETDATE()) <= {$news_rule->viewed_term}
            GROUP BY uv.id_main";
    }

    /**
     * Метод возвращает условия для новостей
     * @return string
     * @throws \Exception
     */
    private function getConditionsForNewsRules(): string
    {
        $include = [];
        $exclude = [];
        if ($news_rules = $this->mailing_rule->newsRules) {
            foreach ($news_rules as $index => $news_rule) {
                $condition = '';

                $total = "(
                    ISNULL(buy{$index}.number, 0) * {$news_rule->buy_coeff} +
                    ISNULL(basket{$index}.number, 0) * {$news_rule->basket_coeff} +
                    ISNULL(differed{$index}.number, 0) * {$news_rule->differed_coeff} +
                    ISNULL(viewed{$index}.number, 0) * {$news_rule->viewed_coeff}
                )";

                if ($news_rule->max_amount) {
                    $condition .= $total
                        . ((bool)$news_rule->is_exception ? " NOT BETWEEN " : " BETWEEN ")
                        . "{$news_rule->min_amount} AND {$news_rule->max_amount}";
                } else {
                    $condition .= $total
                        . ((bool)$news_rule->is_exception ? " <= " : " >= ")
                        . $news_rule->min_amount;
                }

                if ((bool)$news_rule->is_exception) {
                    $exclude[] = $condition;
                } else {
                    $include[] = $condition;
                }
            }
        } else {
            throw new \Exception("Отсутствуют правила новостей!");
        }

        $condition = '';
        if (!empty($include)) {
            $condition .= '(' . implode(' OR ', $include) . ')';
        }

        if (!empty($exclude)) {
            $condition .= (empty($include) ? '' : ' AND ') . '(' . implode(' AND ', $exclude) . ')';
        }

        return $condition;
    }
}