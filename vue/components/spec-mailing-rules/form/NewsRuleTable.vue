<template>
    <div class="b-form-field">
        <table class="b-table font_size_xs">
            <thead>
            <tr class="font_caps font_size_xs font_bold bold b-row">
                <td class="pl35 pr35">ДЕЙСТВИЕ</td>
                <td class="pl35 pr35">КОЭФФИЦИЕНТ</td>
                <td class="pl35 pr35">СРОКИ</td>
            </tr>
            </thead>
            <tbody class="vab">
            <tr v-for="row in rows" :key="row.index">
                <td class="tac">{{ row.title }}</td>
                <td class="tac">
                    <LabField v-model="row.coeff"
                              type="number"
                              @input="changeProperty({
                                    type_action: row.type_action,
                                    type_field: 'coeff',
                                    value: $event
                              })"
                              inputClass="font_size_xs tac"
                              containerClass="width45"
                              :label="false"
                    />
                </td>
                <td class="tac">
                    <LabField v-model="row.term"
                              type="number"
                              @input="changeProperty({
                                    type_action: row.type_action,
                                    type_field: 'term',
                                    value: $event
                              })"
                              inputClass="font_size_xs tac"
                              containerClass="width45"
                              :label="false"
                    />
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
    import {mapActions, mapGetters} from "vuex";

    export default {
        name: "NewsRuleTable",
        props: ['id'],
        data() {
            return {
                viewLabLabel: false
            }
        },
        computed: {
            rows() {
                return [
                    {
                        title: 'ПОКУПКА',
                        type_action: 'buy',
                        coeff: this.getPropertyValue('buy_coeff'),
                        term:  this.getPropertyValue('buy_term'),
                    },
                    {
                        title: 'КОРЗИНА',
                        type_action: 'basket',
                        coeff: this.getPropertyValue('basket_coeff'),
                        term:  this.getPropertyValue('basket_term'),
                    },
                    {
                        title: 'ОТЛОЖЕННЫЕ',
                        type_action: 'differed',
                        coeff: this.getPropertyValue('differed_coeff'),
                        term:  this.getPropertyValue('differed_term'),
                    },
                    {
                        title: 'ПРОСМОТРЕННЫЕ',
                        type_action: 'viewed',
                        coeff: this.getPropertyValue('viewed_coeff'),
                        term:  this.getPropertyValue('viewed_term'),
                    }
                ];
            },
            ...mapGetters('spec_mailing_rule', {
                getNewsRuleById: 'GET_NEWS_RULE_BY_ID'
            }),
            news_rule() {
                return this.getNewsRuleById(this.id);
            },
        },
        methods: {
            ...mapActions('spec_mailing_rule', {
                changeNewsRuleProperty: 'CHANGE_NEWS_RULE_PROPERTY'
            }),
            getPropertyValue(property_name) {
                return _.get(this.news_rule, property_name, '');
            },
            changeProperty({type_action, type_field, value}) {
                let prop = type_action + '_' + type_field;
                this.changeNewsRuleProperty({id: this.id, prop: prop, val: value})
            }
        }
    }
</script>

<style scoped>

</style>