import Vue from 'vue';

export default {
    namespaced: true,
    state: {
        list: {},
        pagination :{},
        search: '',
        loading: false,
        loadSource: {},
    },
    getters: {
        GET_RULE_BY_ID: (state) => (id) => {
            return state.list.find(rule => rule.id === id)
        }
    },
    actions: {
        LOAD({state, commit}, {route, use_loading = true}) {
            let source = axios.CancelToken.source();
            if (!_.isEmpty(state.loadSource)) {
                state.loadSource.cancel();
            }
            if (use_loading) {
                commit('CHANGE_LOADING', true);
            }
            commit('CHANGE_LOAD_SOURCE', source);
            return this.$axios.get(route.fullPath, {
                cancelToken: source.token
            }).then(response => {
                commit('SET_LIST', response.data.rules);
                commit('SET_PAGINATION', response.data.pagination);
                if (_.isEmpty(state.search) && !_.isNull(response.data.search))
                    commit('SET_SEARCH', response.data.search);
                return response;
            }).finally(() => {
                if (_.isEqual(source, state.loadSource)) {
                    commit('CHANGE_LOAD_SOURCE', null);
                    if (use_loading) {
                        commit('CHANGE_LOADING', false);
                    }
                }
            });
        },
        CHANGE_SEARCH({state, commit}, prop_val) {
            commit('SET_SEARCH', prop_val);
        },
        DELETE_RULE_BY_ID({commit}, id) {
            commit('DELETE_RULE_BY_ID', id);
        }
    },
    mutations: {
        SET_LIST: (state, value) => {
            Vue.set(state, 'list', value);
        },
        SET_PAGINATION: (state, value) => {
            Vue.set(state, 'pagination', value);
        },
        SET_SEARCH: (state, value) => {
            Vue.set(state, 'search', value);
        },
        CHANGE_LOADING: (state, value) => {
            Vue.set(state, 'loading', value);
        },
        CHANGE_LOAD_SOURCE: (state, value) => {
            Vue.set(state, 'loadSource', value);
        },
        DELETE_RULE_BY_ID: (state, id) => {
            let index = _.findIndex(state.list, {id: id});
            Vue.delete(state.list, index);
        }
    }
};