export const state = () => ({
  items: [],
  page: 1,
  total: 0,
  loading: false,
})

export const mutations = {
  update(state, items) {
    items.map((data) => {
      state.items.push(data)
    })
    state.page++
  },
  delete(state, items) {
    state.items = items
  },
  create(state, item) {
    let data = { data: item, created_at: item.data.created_at }
    state.items.unshift(data)
    state.total++
  },
  set(state, items) {
    state.items = items
    state.page = items.current_page
    state.loading = false
  },
  total(state, total) {
    state.total = total
  },
  loading(state, condition) {
    state.loading = condition
  },
}

export const actions = {
  create({ commit }, data) {
    commit('create', data)
    commit('loading', false)
  },
}

export const getters = {
  notifTake: (state) => (page) => {
    return _.take(state.items, 5 * page)
  },
}
