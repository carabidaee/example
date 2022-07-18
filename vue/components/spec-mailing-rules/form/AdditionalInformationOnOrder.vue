<template>
    <div>
        <div class="row b-row p25">
            <LabField :label="false">
                <span slot="input">
                    <label class="b-form-checkbox font_size_m">
                        <input v-model="no_orders_in_process"
                               value="1" type="checkbox"
                               :true-value="1" :false-value="0"
                               class="b-form-checkbox-button-container regular_font_size_m color_black hidden">
                        <span class="b-form-checkbox-icon"></span>
                        <span class="b-form-checkbox-label font_size_s pointer no-select">
                            Нет заказов в обработке
                        </span>
                    </label>
                </span>
            </LabField>
        </div>
        <div class="row p25 font_size_s align-items-center">
            Не делал заказ
            <LabField v-model="did_not_order"
                      type="number"
                      inputClass="font_size_s tac pb0 font_bold"
                      containerClass="width45 plr5"
                      :label="false"
            />
            дней
        </div>
    </div>
</template>

<script>
    import {mapActions, mapGetters} from "vuex";

    export default {
        name: "AdditionalInformationOnOrder",
        computed: {
            ...mapGetters('spec_mailing_rule', {
                rule: 'GET_RULE'
            }),
            no_orders_in_process: {
                get() {
                    return _.get(this.rule, 'no_orders_in_process', 0);
                },
                set(val) {
                    this.changeRule({'no_orders_in_process': val})
                }
            },
            did_not_order: {
                get() {
                    return _.get(this.rule, 'did_not_order', '');
                },
                set(val) {
                    this.changeRule({'did_not_order': val})
                }
            }
        },
        methods: {
            ...mapActions('spec_mailing_rule', {
                changeRule: 'CHANGE_RULE'
            })
        }
    }
</script>

<style scoped>

</style>