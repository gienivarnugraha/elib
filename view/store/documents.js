import Vue from 'vue'

export const state = () => ({
  items: [],
  item: {},
  index: 0,
  loading: false,
  uploading: false,
  errors: [],
  kpi: {},
  total: 0,
})

export const mutations = {
  show(state, { item, index }) {
    Object.assign(state.item, item)
    Vue.set(state, 'index', index)
  },
  delete(state) {
    state.items.splice(state.index, 1)
    state.total--
  },
  create(state, item) {
    state.items.unshift(item)
    state.total++
  },
  set(state, items) {
    state.items = items
    state.loading = false
  },
  clear(state) {
    Object.assign(state.item, {})
  },
  update(state, data) {
    Vue.set(state.items, state.index, data)
  },
  revision(state, data) {
    state.item.revision.unshift(data)
  },
  file(state, { revIndex, data }) {
    Vue.set(state.items[state.index].revision[revIndex], 'file', data)
  },
  status(state, { revIndex, data }) {
    Vue.set(
      state.items[state.index].revision[revIndex],
      'is_closed',
      data.is_closed
    )
  },
  loading(state, condition) {
    state.loading = condition
  },
  total(state, total) {
    state.total = total
  },
  errors(state, errors) {
    state.errors = errors
  },
  kpi(state, data) {
    state.kpi = data
  },
  uploading(state, condition) {
    state.uploading = condition
  },
}

export const actions = {
  async updated({ state, commit }, data) {
    if (state.index >= 0) {
      await commit('update', data)
    }
    await commit('loading', false)
  },
  async file({ state, commit }, data) {
    let revIndex = _.findIndex(state.item.revision, {
      id: data.revision_id,
    })
    if (state.index >= 0 && revIndex >= 0) {
      await commit('file', { revIndex, data })
    }
    await commit('loading', false)
  },
  async status({ state, commit }, data) {
    let revIndex = _.findIndex(state.item.revision, {
      id: data.id,
    })
    if (state.index >= 0 && revIndex >= 0) {
      await commit('status', { revIndex, data })
    }
    await commit('loading', false)
  },
  async revision({ state, commit }, data) {
    await commit('revision', data)
    await commit('loading', false)
  },
  deleted({ state, commit }, data) {
    if (state.index >= 0) {
      commit('delete')
    }
    commit('loading', false)
  },
  async created({ commit }, data) {
    await commit('create', data)
    await commit('loading', false)
  },
  getKpi({ commit }) {
    return new Promise((resolve, reject) => {
      this.$axios
        .get('documents/kpi')
        .then((res) => {
          resolve(res.data)
          commit('kpi', res.data)
        })
        .catch((err) => {
          reject(err)
        })
    })
  },
}

export const getters = {
  total: (state) => {
    return state.items.length
  },
  getDocs: (state) => {
    return state.items
  },
  filterBy: (state) => (key, value) => {
    return state.items.filter((item) => item[key] === value)
  },
}
