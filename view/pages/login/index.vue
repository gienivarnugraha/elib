<template>
  <v-row justify="center">
    <v-overlay :value="pageLoading">
      <v-progress-circular indeterminate size="64"></v-progress-circular>
    </v-overlay>
    <v-col cols="6" lg="6" md="6" sm="9">
      <v-card class="elevation-3 pa-3">
        <v-card-text>
          <div class="layout column align-center">
            <img
              src="~/assets/images/logo.png"
              alt="Engineering Data Management"
              width="250"
              height="250"
            />
            <h1 class="flex my-4 primary--text">Engineering Data Management</h1>
          </div>
          <v-form ref="form" lazy-validation v-model="valid">
            <v-text-field
              prepend-icon="mdi-account"
              label="Login"
              hint="Fill with indonesian-aerospace email"
              v-model="email"
              :error-messages="errors.email"
              suffix="@indonesian-aerospace.com"
              :rules="[rules.required, rules.min]"
            />
            <v-text-field
              prepend-icon="mdi-lock"
              :type="hidePassword ? 'password' : 'text'"
              :append-icon="hidePassword ? 'mdi-eye' : 'mdi-eye-off'"
              name="password"
              label="Password"
              id="password"
              v-model="password"
              :rules="[rules.required, rules.min]"
              :error-messages="errors.password"
              @click:append="hidePassword = !hidePassword"
            />
          </v-form>
        </v-card-text>
        <v-card-actions>
          <v-btn
            block
            :disabled="!valid || email.length == 0 || password.length == 0"
            color="primary"
            @click="login"
            :loading="loading"
            >Login</v-btn
          >
        </v-card-actions>
      </v-card>
    </v-col>
  </v-row>
</template>

<script>
export default {
  layout: 'default',

  auth: false,
  data() {
    return {
      /*       isLoggedin: this.$store.state.auth.user.isLoggedin, */
      loading: false,
      pageLoading: false,
      email: '',
      password: '',
      hidePassword: true,
      valid: true,
      errors: [],
      rules: {
        required: (v) => !!v || 'This field is required.',
        min: (v) => v.length >= 4 || 'Min 4 characters',
      },
    }
  },
  methods: {
    login() {
      this.loading = true
      this.pageLoading = true
      this.$axios.$get('sanctum/csrf-cookie').then(() => {
        this.$auth
          .loginWith('local', {
            data: {
              email: this.email + '@indonesian-aerospace.com',
              password: this.password,
            },
          })
          .then((res) => {
            this.$axios.$get('user').then((user) => {
              this.$auth.setUser(user)
              this.$auth.$storage.setUniversal('user', user)
              this.$auth.$storage.setUniversal('loggedIn', true)
              this.loading = false
              this.pageLoading = false
              this.$router.push('/dashboard')
            })
          })
          .catch((err) => {
            this.loading = false
            this.pageLoading = false
            this.$nextTick(() => {
              this.errors = err.response.data.errors
              setTimeout(() => {
                this.errors = []
              }, 5000)
            })
            this.$notifier.showMessage({
              content: err.response.data.message,
              color: 'error',
            })
          })
      })
    },
  },
}
</script>


