<template>
    <li class="news-rule-item">
        <div class="row p10">
            <div class="col-1">
                <div class="row justify-content-center height100 align-content-center">
                    <p class="font_bold font_size_xs">{{ index + 1 }}</p>
                </div>
            </div>
            <div class="col-xl-2 col-sm-4 p10">
                <LabField label="ID НОВОСТИ" labelClass="font_size_xxs font_caps font_bold">
                    <div slot="input" class="col-12 b-with-icon-right">
                        <input v-model.number="id_news" type="number"
                               class="b-form-field-input font_size_xs b-content-with-icon"/>
                        <a v-if="id_news.toString().length > 0" :href="getNewsEditLink()" target="_blank">
                            <lab-icon type="link" class="b-icon-right"></lab-icon>
                        </a>
                    </div>
                </LabField>
            </div>
            <div class="col-xl-2 col-sm-7 p10 tac">
                <LabField :label="false">
                    <span slot="input">
                        <label class="b-form-checkbox">
                            <input v-model="is_exception"
                                   value="1" type="checkbox"
                                   :true-value="1" :false-value="0"
                                   class="b-form-checkbox-button-container regular_font_size_m color_black hidden">
                            <span class="b-form-checkbox-icon"></span>
                            <span class="b-form-checkbox-label font_size_xxs font_bold pointer no-select">
                                ИСКЛЮЧЕНИЕ
                            </span>
                        </label>
                    </span>
                </LabField>
            </div>
            <div class="col-xl-2 col-sm-3 p10">
                <LabField v-model.number="min_amount"
                          type="number"
                          label="КОЛ-ВО (MIN)"
                          labelClass="font_size_xxs font_caps font_bold"
                          inputClass="font_size_xs lh25"
                />
            </div>
            <div class="col-xl-2 col-sm-3 p10">
                <LabField v-model.number="max_amount"
                          type="number"
                          label="КОЛ-ВО (MAX)"
                          labelClass="font_size_xxs font_caps font_bold"
                          inputClass="font_size_xs lh25"
                />
            </div>
            <div class="col-xl-2 col-sm-5 p10 tac">
                <LabField :label="false">
                    <span slot="input">
                        <label class="b-form-checkbox">
                            <input v-model="is_have_duplicates"
                                   value="1" type="checkbox"
                                   :true-value="1" :false-value="0"
                                   class="b-form-checkbox-button-container regular_font_size_m color_black hidden">
                            <span class="b-form-checkbox-icon"></span>
                            <span class="b-form-checkbox-label font_size_xxs font_bold pointer no-select">
                                УЧИТЫВАТЬ ОДИНАКОВЫЕ
                            </span>
                        </label>
                    </span>
                </LabField>
            </div>
            <div class="col-1">
                <div class="row justify-content-center height100 align-content-center">
                    <LabIcon v-show="ability_to_delete" @click.native="deleteNewsRule(id)" title="Удалить" type="delete" size="small"/>
                </div>
            </div>
        </div>
        <div class="row b-row justify-content-center pt50 pb50">
            <NewsRuleTable :id="id"/>
        </div>
    </li>
</template>

<script>
    import {mapState, mapGetters, mapActions} from "vuex";
    import NewsRuleTable from "./NewsRuleTable";
    export default {
        name: "NewsRule",
        components: {NewsRuleTable},
        props: ['id', 'index', 'ability_to_delete'],
        computed: {
            ...mapGetters('spec_mailing_rule', {
                getNewsRuleById: 'GET_NEWS_RULE_BY_ID'
            }),
            news_rule() {
                return this.getNewsRuleById(this.id);
            },
            id_news: {
                get() {
                    return _.get(this.news_rule, 'id_news', '');
                },
                set(value) {
                    this.changeNewsRuleProperty({id: this.id, prop: 'id_news', val: value})
                }
            },
            is_exception: {
                get() {
                    return _.get(this.news_rule, 'is_exception', '');
                },
                set(value) {
                    this.changeNewsRuleProperty({id: this.id, prop: 'is_exception', val: value})
                }
            },
            min_amount: {
                get() {
                    return _.get(this.news_rule, 'min_amount', '');
                },
                set(value) {
                    this.changeNewsRuleProperty({id: this.id, prop: 'min_amount', val: value})
                }
            },
            max_amount: {
                get() {
                    return _.get(this.news_rule, 'max_amount', '');
                },
                set(value) {
                    this.changeNewsRuleProperty({id: this.id, prop: 'max_amount', val: value})
                }
            },
            is_have_duplicates: {
                get() {
                    return _.get(this.news_rule, 'is_have_duplicates', '');
                },
                set(value) {
                    this.changeNewsRuleProperty({id: this.id, prop: 'is_have_duplicates', val: value})
                }
            }
        },
        methods: {
            ...mapActions('spec_mailing_rule', {
                changeNewsRuleProperty: 'CHANGE_NEWS_RULE_PROPERTY',
                deleteNewsRule: 'DELETE_NEWS_RULE'
            }),
            getNewsEditLink() {
                return "/labadmin/oop.php?cl=list&me=edit&id=" + this.id_news;
            },
        },
    }
</script>

<style>
    .lh25 {
        line-height: 25px;
    }
</style>