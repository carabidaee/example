<template>
    <div v-if="!emptyRules">
        <pagination v-bind="pagination" class="both"></pagination>

        <div class="w100" :class="{update_list: loading}" ref="listContainer">
            <span v-if="loading" class="update_list_icon icon-loader icon-size--128"></span>
            <table class="b-table font_size_s"  :class="{update_list_content: loading}">
                <tbody>
                <SpecMailingRulesItem v-for="(rule, index) in rules"
                                     :index="(pagination.current - 1) * pagination.page_size + index + 1"
                                     :id_rule="rule.id"
                                     :key="rule.id"
                ></SpecMailingRulesItem>
                </tbody>
            </table>
        </div>
    </div>
    <div v-else class="p25 font_size_m color_black tac">
        <div v-if="loading" class="tac relative">
            <span class="icon-loader icon-size--128"></span>
        </div>
        <div v-else>Правила не найдены</div>
    </div>
</template>

<script>
    import { mapState, mapActions, mapGetters, mapMutations } from "vuex";

    import Pagination from '../../../components/Pagination';
    import SpecMailingRulesItem from "./SpecMailingRulesItem";

    export default {
        name: "SpecMailingRulesItems",
        components: {
            SpecMailingRulesItem,
            Pagination
        },
        computed: {
            ...mapState('spec_mailing_rules', {
                rules: state => state.list,
                loading: state => state.loading,
                pagination: state => state.pagination
            }),
            emptyRules() {
                return _.isEmpty(this.rules);
            }
        }
    }
</script>

<style scoped>
    .pagination{
        padding-left: 25px;
    }
    .update_list {
        position: relative;
        width: 100%;
    }
    .update_list_icon {
        content: "";
        position: absolute;
        top: 0;
        left: 50%;
        transform: translateX(-50%);
    }
    .update_list_content{
        opacity: 0.5;
    }
</style>