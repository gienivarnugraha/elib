export const state = () => ({
  items: [],
  total: 0,
  loading: false,
  selected: []
})

export const mutations = {
  show(state, selected) {
    state.selected = selected
  },
  delete(state, items) {
    state.items = items
  },
  create(state, item) {
    state.items.shift(item)
  },
  set(state, items) {
    state.items = items
  },
  loading(state, condition) {
    state.loading = condition
  },
  total(state, total) {
    state.total = total
  }
}
