import '@mdi/font/css/materialdesignicons.css'

export default {
  icons: {
    iconfont: 'mdi',
  },
  theme: {
    dark: false, // From 2.0 You have to select the theme dark or light here
    themes: {
      light: {
        //primary: '#90a4ae',
        //secondary: '#bdbdbd',
        primary: {
          base: '#1de9b6', // teal accent-3
          lighten1: '#b8f5df',
          darken1: '#00da93',
        },
        secondary: {
          base: '#1db6e9', // cyan accent-3
          lighten1: '#7ed4f1',
          darken1: '#009bd9',
        },
        accent: '#FDD835',
        success: '#1de950', // green accent-3
        error: '#e91d50', // pink accent-3
        warning: '#FF9100', // amber accent-3
      },
    },
  },
}
