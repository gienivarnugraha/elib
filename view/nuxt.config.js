import webpack from 'webpack'

const BASE_URL = 'http://elib.acs.id/'
const SOCKET_URL = 'http://elib.acs.id/'

export default {
  /* SERVER DEVELOPMENT HOTMODULE RELOADING*/
  /*   watchers: {
    chokidar: {
      usePolling: true,
      useFsEvents: false,
    },
    webpack: {
      aggregateTimeout: 300,
      poll: 1000,
    },
  }, */
  server: {
    //host: '0.0.0.0', //<-- making this dev accessible by network
    host: '192.168.10.1',
    port: 3000,
  },

  proxy: {
    '/api': {
      target: 'http://api.elib.acs.id',
      pathRewrite: { '^/api': '' },
      changeOrigin: true,
      logLevel: 'debug',
    },
    '/socket.io': {
      target: 'http://api.elib.acs.id/',
      changeOrigin: true,
      logLevel: 'debug',
    },
  },

  /*
   ** Nuxt target
   ** See https://nuxtjs.org/api/configuration-target
   */
  target: 'static',
  /*
   ** Headers of the page
   ** See https://nuxtjs.org/api/configuration-head
   */
  head: {
    title: process.env.npm_package_name || '',
    script: [
      {
        src: SOCKET_URL + 'socket.io/socket.io.js',
      },
    ],
    meta: [
      {
        charset: 'utf-8',
      },
      {
        name: 'viewport',
        content: 'width=device-width, initial-scale=1',
      },
      {
        hid: 'description',
        name: 'description',
        content: process.env.npm_package_description || '',
      },
    ],
    link: [
      {
        rel: 'icon',
        type: 'image/x-icon',
        href: '/favicon.ico',
      },
    ],
  },
  /*
   ** Global CSS
   */
  css: [],
  /*
   ** Plugins to load before mounting the App
   ** https://nuxtjs.org/guide/plugins
   */
  plugins: [
    '@plugins/notifier',
    '@plugins/vue-swal',
    '@plugins/axios',
    { src: '@plugins/chart', mode: 'client' },
  ],
  /*
   ** Auto import components
   ** See https://nuxtjs.org/api/configuration-components
   */
  components: true,

  /*
   ** Nuxt.js dev-modules
   */
  buildModules: [
    '@nuxt/typescript-build',
    '@nuxtjs/vuetify',
    '@nuxtjs/stylelint-module', // Doc: https://github.com/nuxt-community/stylelint-module
    ['@nuxtjs/laravel-echo'],
  ],

  /*
   ** Nuxt.js modules
   */
  modules: [
    '@nuxtjs/axios', // Doc: https://axios.nuxtjs.org/usage
    '@nuxtjs/auth',
    '@nuxtjs/proxy',
  ],

  router: {
    middleware: ['auth', 'authenticated'],
  },

  /**
   * Laravel Echo configuration
   */
  echo: {
    broadcaster: 'socket.io',
    wsHost: SOCKET_URL,
    authEndpoint: BASE_URL + 'broadcasting/auth',
    authModule: true,
  },

  /**
   * Vuetify configuration
   */
  vuetify: {
    optionsPath: '~/plugins/vuetify.js',
    defaultAssets: false,
    //treeShake: true,
  },

  /*
   ** Auth module configuration
   */
  auth: {
    redirect: {
      logout: '/',
      home: '/dashboard',
      login: '/login',
    },
    strategies: {
      local: {
        endpoints: {
          login: {
            url: 'login',
            method: 'post',
            propertyName: 'token',
          },
          user: false,
          logout: {
            url: 'logout',
            method: 'post',
          },
        },
      },
    },
  },

  /*
   ** Axios module configuration
   ** See https://axios.nuxtjs.org/options
   */
  axios: {
    baseURL: BASE_URL, //process.env.BASE_URL,
    debug: process.env.APP_DEBUG,
    credentials: true,
    proxy: true,
    prefix: '/api',
  },
  /*   publicRuntimeConfig: {
    axios: {
      browserBaseURL: BASE_URL, //process.env.BASE_URL,
    },
  },
  privateRuntimeConfig: {
    axios: {
      baseURL: BASE_URL, //process.env.BASE_URL,
    },
  }, */
  /*
   ** Build configuration
   ** See https://nuxtjs.org/api/configuration-build/
   */
  build: {
    analyze: false, //webpack-bundle-analyzer
    plugins: [
      new webpack.ProvidePlugin({
        // global modules
        _: 'lodash',
      }),
    ],
  },
  vue: {
    config: {
      productionTip: false,
      devtools: process.env.APP_DEBUG,
    },
  },
}
