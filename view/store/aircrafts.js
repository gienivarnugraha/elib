import Vue from 'vue'

export const state = () => ({
  items: [],
  total: 0,
  loading: false,
  item: [],
  types: [],
})

export const mutations = {
  show(state, item) {
    Object.assign(state.item, item)
  },
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
  update(state, { index, data }) {
    Vue.set(state.items, index, data)
  },
  loading(state, condition) {
    state.loading = condition
  },
  total(state, total) {
    state.total = total
  },
}

export const actions = {
  async created({ commit }, data) {
    await commit('create', data)
    await commit('loading', false)
  },
  async updated({ state, commit }, data) {
    let index = await _.findIndex(state.items, {
      id: data.id,
    })
    if (index >= 0) {
      await commit('update', { index, data })
    }
    await commit('loading', false)
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
}

export const getters = {
  aircraftTypes: (state) => {
    return [...new Set(state.items.map((item) => item.type))]
  },
  filterBy: (state) => (key, value) => {
    return state.items.filter((item) => item[key] == value)
  },
}
