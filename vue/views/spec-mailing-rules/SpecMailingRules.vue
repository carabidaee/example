<template>
    <div class="row align-items-stretch both min_h100">
        <div class="col-12 col-sm-4 col-md-3 both background_grayish">
            <div class="row">
                <div class="b-form-field b-field-default">
                    <a class="btn btn-medium btn-red" href="/labadmin/yii/spec-mailing-rules/create">Создать правило</a>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-8 col-md-9 both background_white">
            <SpecMailingRulesList/>
        </div>
    </div>
</template>

<script>
    import { mapState, mapActions, mapGetters, mapMutations } from "vuex";

    import specMailingRulesStore from '../../store/modules/spec_mailing_rules/spec_mailing_rules';

    import SpecMailingRulesList from "../../blocks/spec-mailing-rules/list/SpecMailingRulesList";
    export default {
        name: 'SpecMailingRules',
        components: {
            SpecMailingRulesList,
        },
        beforeCreate: function () {
            if (!_.has(this.$store._modules.root._children, 'spec_mailing_rules')) {
                this.$store.registerModule('spec_mailing_rules', specMailingRulesStore);
            }
        },
        beforeRouteEnter(to, from, next) {
            next(vm => {
                vm.routeUpdateData(to, next);
            });
        },
        beforeRouteUpdate(to, from, next) {
            this.routeUpdateData(to, next);
        },
        methods: {
            ...mapActions({
                error: 'messages/addError',
                load: 'spec_mailing_rules/LOAD'
            }),
            routeUpdateData: _.debounce(function (route, next) {
                return this.load({route: route}).then(() => {
                    next();
                }).catch(error => {
                    if (!axios.isCancel(error)) {
                        this.error(error.response.data);
                    }
                    next(false);
                });
            }, 300)
        }
    }
</script>