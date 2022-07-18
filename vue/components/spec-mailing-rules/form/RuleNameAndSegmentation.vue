<template>
    <div class="row b-row align-items-stretch">
        <div class="col-6">
            <LabField v-model="name"
                      label="НАЗВАНИЕ ПРАВИЛА"
                      labelClass="font_size_xs font_caps font_bold"
                      containerClass="b-field-default">
                <span slot="hint">
                    {{name.length}} {{ name.length | wordEnding('символ', 'символа', 'символов')}}
                </span>
            </LabField>
        </div>
        <div class="col-6">
            <lab-field containerClass="b-field-default">
                <span slot="input">
                    <label class="b-form-checkbox font_size_m">
                        <input v-model="use_for_segmentation"
                               value="1" type="checkbox"
                               :true-value="1" :false-value="0"
                               class="b-form-checkbox-button-container regular_font_size_m color_black hidden">
                        <span class="b-form-checkbox-icon"></span>
                        <span class="b-form-checkbox-label font_size_s pointer no-select">
                            Использовать для сегментации
                        </span>
                    </label>
                </span>
            </lab-field>
        </div>
    </div>
</template>

<script>
    import {mapState, mapGetters, mapActions} from "vuex";
    export default {
        name: "RuleNameAndSegmentation",
        computed: {
            ...mapGetters('spec_mailing_rule', {
                rule: 'GET_RULE'
            }),
            name: {
                get() {
                    return _.get(this.rule, 'name_rule', '');
                },
                set(val) {
                    this.changeRule({'name_rule': val})
                }
            },
            use_for_segmentation: {
                get() {
                    return _.get(this.rule, 'use_for_segmentation', 0);
                },
                set(val) {
                    this.changeRule({'use_for_segmentation': val})
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