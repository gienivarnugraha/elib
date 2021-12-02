import Cookies from 'js-cookie'

export default function ({ $axios, $auth, redirect, error: nuxtError }) {
  $axios.onError((error) => {
    console.log(error.response)
    if (error.response.status === 401) {
      redirect('/login')
    }
  })
  /* else if (error.request) {
      console.log(error.request)
    } else {
      console.log('Error', error.message)
    }
    console.log(error.config) */
}
