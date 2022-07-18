<template>
    <div class="b-form-field col-12 mb25">
        <div class="b-form-field-input-container font_size_m color_black link b-with-icon-left b-with-icon-right">
            <input v-model="rule_search"
                   placeholder="Поиск правила по названию"
                   class="b-form-field-input font_size_m b-content-with-icon">
            <span class="b-icon-left icon-container icon-search"><span class="icon icon-gray"></span></span>

            <lab-icon v-show="rule_search.length > 0" @click.native="clearSearch"
                      type="delete" size="middle" class="b-icon-right"
            ></lab-icon>
        </div>
    </div>
</template>

<script>
    import { mapState, mapActions, mapGetters, mapMutations } from "vuex";
    export default {
        name: "SpecMailingRulesSearch",
        computed: {
            ...mapState('spec_mailing_rules', {
                search: state => state.search
            }),
            rule_search: {
                get: function () {
                    return this.search;
                },
                set: _.debounce(function(val) {
                    this.changeSearch(val);
                }, 1000)
            },
        },
        methods: {
            ...mapActions('spec_mailing_rules', {
                CHANGE_SEARCH: 'CHANGE_SEARCH'
            }),
            changeSearch: function(val) {
                this.CHANGE_SEARCH(val).then(() => {
                    this.$router.push({query: {name_rule: val}});
                })
            },
            clearSearch: function() {
                this.changeSearch('')
            }
        }
    }
</script>

<style scoped>

</style>