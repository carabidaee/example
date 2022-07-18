<template>
    <tr class="b-table-row b-table-row-pad b-table-row-hover">
        <td v-if="index" v-text="index" class="font_size_s color_gray" style="padding: 10px 10px;"></td>
        <td>{{rule.id}}</td>
        <td>
            <router-link :to="{name: 'SpecMailingRuleUpdate', params: {id_rule: id_rule}}"
                         :class="{ disabled_link:   loadingDelete }"
                         class="text-decoration-none color_black"
            >
                {{rule.name_rule}}
            </router-link>
        </td>
        <td>
            <LabIcon @click.native="deleteRule" title="Удалить" type="delete" size="small" :loading="loadingDelete"/>
        </td>
    </tr>
</template>

<script>
    /**
     * @param {{id:integer}} rule
     * @param {{name_rule:string}} rule
     */
    import {mapGetters, mapActions} from "vuex";

    export default {
        name: "SpecMailingRulesItem",
        props: {
            id_rule: {
                required: true
            },
            index: {
                type: Number,
                default: null
            },
        },
        data() {
            return {
                loadingDelete: false,
            }
        },
        computed: {
            ...mapGetters('spec_mailing_rules', {
                getRuleByID: 'GET_RULE_BY_ID'
            }),
            rule() {
                return this.getRuleByID(this.id_rule);
            }
        },
        methods: {
            ...mapActions({
                addError: 'messages/addError',
                load: 'spec_mailing_rules/LOAD',
                deleteRuleById: 'spec_mailing_rules/DELETE_RULE_BY_ID'
            }),
            deleteRule() {
                if (confirm('Точно удалить?')) {
                    this.loadingDelete = true;
                    this.$axios.post(`${this.$route.path}/delete`, {id_rule: this.id_rule})
                        .then(response => {
                            if (response.data.error) {
                                this.deletionError(response.data.error);
                            } else {
                                this.load({route: this.$route, use_loading: false});
                            }
                        })
                        .catch(error => {
                            this.deletionError(error.response.data);
                        });
                }
            },
            deletionError(text) {
                this.loadingDelete = false;
                this.addError(text);
            }
        }
    }
</script>

<style>
    .disabled_link {
        pointer-events: none;
    }
</style>