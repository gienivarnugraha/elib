export const state = () => ({
  loading: false,
  snack: {
    color: '',
    content: '',
  },
  confirm: {
    title: '',
    color: '',
    content: '',
    content: '',
  },
  errors: {},
  errorMessages: '',
})

export const mutations = {
  error: (state, errors) => {
    state.errors = errors
  },
  clearError: (state) => {
    state.errors = {}
  },
  loading: (state, condition) => {
    state.loading = condition
  },
  snack: (state, payload) => {
    state.snack.color = payload.color
    state.snack.content = payload.content
  },
  confirm: (state, payload) => {
    state.confirm.color = payload.color
    state.confirm.title = payload.title
    state.confirm.content = payload.content
  },
}
