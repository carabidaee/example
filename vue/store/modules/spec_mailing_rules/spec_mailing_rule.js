import Vue from 'vue';

const default_news_rule = {
    id_news: '',
    is_exception: 0,
    min_amount: '',
    max_amount: '',
    is_have_duplicates: 0,

    buy_coeff: 1,
    buy_term: 365,

    basket_coeff: 1,
    basket_term: 30,

    differed_coeff: 0.7,
    differed_term: 90,

    viewed_coeff: 0.5,
    viewed_term: 3
};

export default {
    namespaced: true,
    state: {
        errors: {},
        rule: {},

        delivery_exceptions: [
            {
                delivery_kind_type: 3,
                delivery_kind: null
            },
            {
                delivery_kind_type: 6,
                delivery_kind: 47
            }

        ],
        is_delivery_exceptions_changed: false,

        news_rules: [
            Object.assign({id: 1}, default_news_rule)
        ],
        is_news_rules_changed: false,

        is_writes_contacts: false,

        loading: false,
    },
    getters: {
        GET_RULE: (state) => {
            return state.rule;
        },
        GET_DELIVERY_EXCEPTIONS: (state) => {
            return state.delivery_exceptions;
        },
        GET_NEWS_RULE_BY_ID: (state) => (id) => {
            let news_rule = _.find(state.news_rules, {id: id});
            return !_.isUndefined(news_rule) ? news_rule : null;
        }
    },
    actions: {
        DOWNLOAD({dispatch, state, commit}, id_rule) {
            commit('SET_LOADING', true);
            let url = `/labadmin/yii/spec-mailing-rules/update?id_rule=${id_rule}`;
            return this.$axios.get(url).then(response => {
                if (!_.isNull(response.data.rule)) {
                    commit('SET_RULE', response.data.rule);
                }
                if (!_.isEmpty(response.data.delivery_exceptions)) {
                    commit('SET_DELIVERY_EXCEPTIONS', response.data.delivery_exceptions);
                }
                if (!_.isEmpty(response.data.news_rules)) {
                    commit('SET_NEWS_RULES', response.data.news_rules);
                }
                if (response.data.is_writes_contacts) {
                    setTimeout(() => dispatch('CHECK_IS_WRITES_CONTACTS'), 60000)
                    commit('SET_IS_WRITES_CONTACTS', response.data.is_writes_contacts);
                }
                commit('SET_LOADING', false);
                return response;
            });
        },
        CHECK_IS_WRITES_CONTACTS({dispatch, state, commit}) {
            if (!state.rule.id) {
                return;
            }
            let url_checking_contact_records = `/labadmin/yii/spec-mailing-rules/is-writes-contacts`;
            this.$axios.get(url_checking_contact_records, {
                params: {
                    id_rule: state.rule.id
                }
            }).then(function (response) {
                if (response.data.is_writes_contacts) {
                    setTimeout(() => dispatch('CHECK_IS_WRITES_CONTACTS'), 60000)
                }
                commit('SET_IS_WRITES_CONTACTS', response.data.is_writes_contacts);
            });
        },
        SAVE({state, commit}, url) {
            commit('SET_LOADING', true);
            return this.$axios.post(url, {
                rule: state.rule,
                delivery_exceptions: state.delivery_exceptions,
                is_delivery_exceptions_changed: state.is_delivery_exceptions_changed,
                news_rules: _.forEach(JSON.parse(JSON.stringify(state.news_rules)), elem => delete elem.id),
                is_news_rules_changed: state.is_news_rules_changed
            }).then(response => {
                if (response.data.errors) {
                    commit('SET_ERRORS', response.data.errors);
                }
                commit('SET_LOADING', false);
                return response;
            })
        },
        CHANGE_RULE({state, commit, dispatch}, prop_val) {
            dispatch('CHECK_ERROR', prop_val);
            commit('SET_RULE', Object.assign({}, state.rule, prop_val));
        },
        CHANGE_DELIVERY_EXCEPTIONS({state, commit}, delivery_exceptions) {
            commit('SET_IS_DELIVERY_EXCEPTIONS_CHANGED', true);
            commit('SET_DELIVERY_EXCEPTIONS', delivery_exceptions);
        },
        ADD_NEWS_RULE({state, commit}) {
            if (!state.is_news_rules_changed)
                commit('SET_IS_NEWS_RULES_CHANGED', true);
            commit('ADD_NEWS_RULE');
        },
        DELETE_NEWS_RULE({state, commit}, id) {
            if (!state.is_news_rules_changed)
                commit('SET_IS_NEWS_RULES_CHANGED', true);
            commit('DELETE_NEWS_RULE', id);
        },
        CHANGE_NEWS_RULE_PROPERTY({state, commit, dispatch}, {id, prop, val}) {
            dispatch('CHECK_ERROR', {[prop]: val});
            if (!state.is_news_rules_changed)
                commit('SET_IS_NEWS_RULES_CHANGED', true);
            commit('SET_NEWS_RULE_PROPERTY', {id, prop, val});
        },
        CHECK_ERROR({state, commit}, prop_val) {
            let prop = _.keys(prop_val)[0];
            let val = prop_val[prop];
            if (!_.isEmpty(_.toString(val)) && _.has(state.errors, prop)) {
                commit('DELETE_ERROR', prop);
            }
        }
    },
    mutations: {
        SET_RULE(state, rule) {
            Vue.set(state, 'rule', rule);
        },
        SET_DELIVERY_EXCEPTIONS(state, data) {
            Vue.set(state, 'delivery_exceptions', data);
        },
        SET_NEWS_RULES(state, data) {
            Vue.set(state, 'news_rules', data);
        },
        SET_IS_DELIVERY_EXCEPTIONS_CHANGED(state, data) {
            Vue.set(state, 'is_delivery_exceptions_changed', data);
        },
        SET_IS_WRITES_CONTACTS(state, val) {
            Vue.set(state, 'is_writes_contacts', val);
        },
        SET_LOADING(state, value) {
            Vue.set(state, 'loading', value);
        },
        ADD_NEWS_RULE(state) {
            let last_id = state.news_rules.at(-1).id;
            let news_rule = Object.assign({id: last_id + 1}, default_news_rule);
            state.news_rules.push(news_rule);

        },
        DELETE_NEWS_RULE(state, id) {
            let index = _.findIndex(state.news_rules, {id: id});
            state.news_rules.splice(index, 1);
        },
        SET_NEWS_RULE_PROPERTY(state, {id, prop, val}) {
            let news_rule = _.find(state.news_rules, {id: id});
            Vue.set(news_rule, prop, val);
        },
        SET_IS_NEWS_RULES_CHANGED(state, data) {
            Vue.set(state, 'is_news_rules_changed', data);
        },
        SET_ERRORS(state, data) {
            Vue.set(state, 'errors', data);
        },
        DELETE_ERROR(state, prop) {
            Vue.delete(state.errors, prop);
        }
    }
};