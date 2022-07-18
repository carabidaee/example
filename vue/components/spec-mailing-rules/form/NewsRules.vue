<template>
    <div>
        <div class="row b-row p25 justify-content-end">
            <LabBtn :theme="theme_button" @click="addNewsRule">Добавить новость</LabBtn>
        </div>
        <ul class="news-rules-list">
            <NewsRule v-for="(news_rule, index) in news_rules"
                      :id="news_rule.id"
                      :index="index"
                      :ability_to_delete="news_rules.length > 1"
                      :key="news_rule.id"
            />
        </ul>
    </div>
</template>

<script>
    import {mapState, mapGetters, mapActions} from "vuex";
    import NewsRule from "./NewsRule";
    export default {
        name: "NewsRules",
        components: {NewsRule},
        data() {
            return {
                theme_button: 'white'
            }
        },
        computed: {
            ...mapState('spec_mailing_rule', {
                news_rules: state => _.get(state, 'news_rules', [{}]),
            }),
        },
        methods: {
            ...mapActions('spec_mailing_rule', {
                addNewsRule: 'ADD_NEWS_RULE'
            })
        }
    }
</script>

<style scoped>

</style>