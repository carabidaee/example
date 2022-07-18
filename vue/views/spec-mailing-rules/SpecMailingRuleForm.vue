<template>
    <div>
        <Loader :loading="loading"/>
        <Errors :errors="errors" additional-wrapper-class="b-row"/>
        <Warning v-if="is_writes_contacts" additional_class="mt10 ml10 mr10">
            Это правило пока недоступно для сохранения, т.к. по нему записываются контакты
        </Warning>
        <RuleNameAndSegmentation/>
        <div class="row min_h100" :class="{add_pb_save_bar: !is_writes_contacts}">
            <div class="col-3 p25 bord-r border_gray">
                <DeliveryExceptions/>
            </div>
            <div class="col-6 bord-r border_gray">
                <NewsRules/>
            </div>
            <div class="col-3">
                <AdditionalInformationOnOrder/>
            </div>
        </div>
        <LabSaveBar v-if="!is_writes_contacts" @save-all="saveRule"/>
    </div>
</template>

<script>
    import {mapState, mapActions, mapGetters, mapMutations} from "vuex";

    import SpecMailingRuleStore from '../../store/modules/spec_mailing_rules/spec_mailing_rule';
    import RuleNameAndSegmentation from "../../blocks/spec-mailing-rules/form/RuleNameAndSegmentation";
    import DeliveryExceptions from "../../blocks/spec-mailing-rules/form/DeliveryExceptions";
    import LabSaveBar from "../../components/lab-save-bar/LabSaveBar";
    import Loader from "../../blocks/spec-mailing-rules/form/Loader";
    import NewsRules from "../../blocks/spec-mailing-rules/form/NewsRules";
    import AdditionalInformationOnOrder from "../../blocks/spec-mailing-rules/form/AdditionalInformationOnOrder";
    import Errors from "../../blocks/spec-mailing-rules/form/Errors";
    import Warning from "../../blocks/spec-mailing-rules/form/Warning";

    export default {
        name: "SpecMailingRuleForm",
        components: {
            Warning,
            Errors,
            AdditionalInformationOnOrder,
            NewsRules,
            Loader,
            LabSaveBar,
            DeliveryExceptions,
            RuleNameAndSegmentation,
        },
        props: ['id_rule'],
        beforeCreate() {
            if (!_.has(this.$store._modules.root._children, 'spec_mailing_rule')) {
                this.$store.registerModule('spec_mailing_rule', SpecMailingRuleStore);
            }
        },
        beforeRouteEnter(to, from, next) {
            next(async (vm) => {
                if (to.name === 'SpecMailingRuleUpdate') {
                    let response = await vm.download(vm.id_rule);
                    if (_.isNull(response.data.rule)) {
                        vm.addError('Такого правила не существует!');
                        next({name: 'SpecMailingRules'});
                    }
                }
                next();
            });
        },
        computed: {
            ...mapState('spec_mailing_rule', {
                is_writes_contacts: 'is_writes_contacts',
                loading: 'loading',
                errors: 'errors'
            }),
            ...mapGetters('spec_mailing_rule', {
                rule: 'GET_RULE'
            }),
            name: function() {
                return _.get(this.rule, 'name_rule', '');
            },
        },
        methods: {
            ...mapActions("messages", {
                addError: 'addError',
                success: 'addSuccess'
            }),
            ...mapActions('spec_mailing_rule', {
                download: 'DOWNLOAD',
                save: 'SAVE'
            }),
            saveRule() {
                let result = true;
                if (_.get(this.rule, 'use_for_segmentation', 0) === 1) {
                    result = confirm('Это правило используется для сегментации. После сохранения некоторое время  ' +
                        'редактирование этого правила будет недоступно из-за загрузки контаков по его данным в ' +
                        'специальную таблицу. Вы точно хотите сохранить?');
                }
                if (result) {
                    this.save(this.$route.path).then(response => {
                        if (!response.data.errors) {
                            let message = (this.$route.name === 'SpecMailingRuleUpdate')
                                ? `Данные правила "${this.name}" успешно изменены`
                                : 'Новое правило успешно создано';
                            this.success(message);
                        } else {
                            this.addError('Не удалось сохранить правило');
                        }
                    });
                }
            }
        },
    }
</script>

<style>
    input[type="number"]::-webkit-outer-spin-button,
    input[type="number"]::-webkit-inner-spin-button {
        -webkit-appearance: none;
    }

    input[type='number'],
    input[type="number"]:hover,
    input[type="number"]:focus {
        appearance: none;
        -moz-appearance: textfield;
    }

    li {
        list-style-type: none;
    }
    ul {
        margin-left: 0;
        padding-left: 0;
    }
</style>