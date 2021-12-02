import _ from 'lodash'

export default function ({ app, store, route, redirect }) {
  // If the user is  authenticated
  let user = app.$auth.$storage.getCookie('user')
  if (app.$auth.$storage.getCookie('loggedIn')) {
    if (_.isEmpty(store.state.auth.user)) app.$auth.setUser(user)
  }
}
