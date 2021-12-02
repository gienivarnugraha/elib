<template>
  <v-app id="default">
    <snackbar></snackbar>

    <v-app-bar app dark class="custom-app-bar" elevate-on-scroll>
      <v-toolbar-title class="ml-0 pl-16">
        <span class="hidden-sm-and-down">ELIB ACS</span>
      </v-toolbar-title>

      <v-spacer></v-spacer>
      <v-btn text v-if="isLogin" to="/" large>
        Back <v-icon right>mdi-arrow-left</v-icon>
      </v-btn>
      <v-btn v-else text to="/login" large>
        LOGIN<v-icon right>mdi-account</v-icon>
      </v-btn>
    </v-app-bar>
    <v-main class="custom-main">
      <nuxt />
    </v-main>
    <myFooter> </myFooter>
  </v-app>
</template>

<script>
import snackbar from '~/components/snackbar.vue'
import myFooter from '~/components/myFooter.vue'
import { mapState } from 'vuex'

export default {
  name: 'default',
  auth: false,
  components: { snackbar, myFooter },
  computed: {
    isLogin() {
      return this.$route.name == 'login' ? true : false
    },
    loggedIn() {
      return this.$auth.loggedIn
    },
  },
  methods: {
    back() {
      this.$nextTick(() => {
        return this.$router.push('/')
      })
    },
  },
}
</script>

<style scoped lang="scss">
#default {
  background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab);
  background-size: 400% 400%;
  animation: gradient 15s ease infinite;
}

.custom-loader {
  animation: loader 1s infinite;
  display: flex;
}

.custom-main {
  align-items: center;
}

.custom-app,
.custom-app-bar,
.custom-footer {
  background-color: transparent !important;
}

.custom-footer .text-center {
  a {
    color: #f2e9f2 !important;
  }
}

@-moz-keyframes gradient {
  0% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
  100% {
    background-position: 0% 50%;
  }
}
@-webkit-keyframes gradient {
  0% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
  100% {
    background-position: 0% 50%;
  }
}
@-o-keyframes gradient {
  0% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
  100% {
    background-position: 0% 50%;
  }
}
@keyframes gradient {
  0% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
  100% {
    background-position: 0% 50%;
  }
}
</style>
