import Vue from 'vue'

export const state = () => ({
  items: [],
  total: 0,
  loading: false,
})

export const mutations = {
  delete(state, index) {
    state.items.splice(index, 1)
  },
  create(state, item) {
    state.items.unshift(item)
  },
  set(state, items) {
    state.items = items
    state.loading = false
  },
  loading(state, condition) {
    state.loading = condition
  },
  total(state, total) {
    state.total = total
  },
  auth(state, { index, data }) {
    Vue.set(state.items[index], 'is_active', data.is_active)
  },
  update(state, { index, data }) {
    Vue.set(state.items, index, data)
  },
}

export const actions = {
  async auth({ state, commit }, data) {
    let index = await _.findIndex(state.items, { id: data.id })
    if (index >= 0) {
      await commit('auth', { index, data })
    }
    commit('loading', false)
  },
  async updated({ state, commit }, data) {
    let index = await _.findIndex(state.items, { id: data.id })
    if (index >= 0) {
      await commit('update', { index, data })
    }
  },
  deleted({ state, commit }, data) {
    let index = _.findIndex(state.items, {
      id: data.id,
    })
    if (index >= 0) {
      commit('delete', index)
    }
    commit('loading', false)
  },
  created({ commit }, data) {
    commit('create', data)
    commit('loading', false)
  },
}
