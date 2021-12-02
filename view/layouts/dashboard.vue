<template>
  <v-app id="inspire">
    <snackbar></snackbar>
    <v-navigation-drawer app permanent expand-on-hover>
      <v-img height="64px" src="/images/material.jpg">
        <v-list-item link>
          <v-list-item-content>
            <v-row align="end" class="lightbox white--text px-4 fill-height">
              <v-list-item-title class="title">{{
                user.name || ''
              }}</v-list-item-title>
              <v-list-item-subtitle>{{
                user.email || ''
              }}</v-list-item-subtitle>
            </v-row>
          </v-list-item-content>
        </v-list-item>
      </v-img>

      <v-divider></v-divider>

      <v-list nav dense>
        <v-list-item link to="/" nuxt>
          <v-list-item-icon>
            <v-icon>mdi-home</v-icon>
          </v-list-item-icon>
          <v-list-item-title>Dashboard</v-list-item-title>
        </v-list-item>
        <v-list-item link to="/documents" nuxt>
          <v-list-item-icon>
            <v-icon>mdi-file-document-multiple-outline</v-icon>
          </v-list-item-icon>
          <v-list-item-title>Document </v-list-item-title>
        </v-list-item>
        <v-list-item link to="/aircrafts" nuxt>
          <v-list-item-icon>
            <v-icon>mdi-airplane-takeoff</v-icon>
          </v-list-item-icon>
          <v-list-item-title>Aircraft </v-list-item-title>
        </v-list-item>
        <v-list-item link to="/manuals" nuxt>
          <v-list-item-icon>
            <v-icon>mdi-airplane-takeoff</v-icon>
          </v-list-item-icon>
          <v-list-item-title>Manual </v-list-item-title>
        </v-list-item>
        <v-list-item link to="/notifications" nuxt>
          <v-list-item-icon>
            <v-icon>mdi-bell</v-icon>
          </v-list-item-icon>
          <v-list-item-title>Notifications </v-list-item-title>
        </v-list-item>
        <v-list-item link to="/users" nuxt>
          <v-list-item-icon>
            <v-icon>mdi-account</v-icon>
          </v-list-item-icon>
          <v-list-item-title>Users</v-list-item-title>
        </v-list-item>
      </v-list>
    </v-navigation-drawer>

    <v-app-bar :clipped-left="true" app color="primary" dark>
      <v-row align="center" justify="center" no-gutters>
        <!-- TOOLBAR TITLE -->
        <v-col md="3" sm="3">
          <v-toolbar-title class="ml-0 pl-16">
            <span>ELIB ACS</span>
          </v-toolbar-title>
        </v-col>
        <v-spacer></v-spacer>

        <!-- TOOLBAR SEARCH COLUMN -->
        <!--         <v-col>
          <v-autocomplete
            v-model.lazy="searchAll"
            :items="items"
            item-text="type"
            hide-details
            flat
            menu-props="closeOnClick=true, closeOnContentClick,offsetY"
            clearable
            prepend-inner-icon="mdi-magnify"
            placeholder="Search everything here.."
            solo-inverted
          >
            <template v-slot:item="data">
              <v-list-item-content>
                <v-list-item-title v-html="data.item.title"></v-list-item-title>
                <v-list-item-subtitle
                  v-html="data.item.type"
                ></v-list-item-subtitle>
              </v-list-item-content>
            </template>
          </v-autocomplete>
        </v-col>
 -->
        <!-- TOOLBAR ICON -->
        <v-col md="2" sm="2" align-self="center">
          <div class="text-right">
            <!-- TOOLBAR CONNECTION -->
            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-btn
                  icon
                  class="hidden-sm-and-down"
                  :color="socket ? 'white' : 'error'"
                  :loading="!socket"
                  v-bind="attrs"
                  v-on="on"
                  ><v-icon>mdi-cloud</v-icon>
                </v-btn>
              </template>
              <span> You are {{ socket ? 'Online' : 'Offline' }} </span>
            </v-tooltip>

            <!-- TOOLBAR NOTIFICATION -->
            <v-badge :content="notifCount" color="secondary darken-1" overlap>
              <v-menu
                v-model="menu"
                :close-on-content-click="false"
                offset-y
                min-width="25rem"
                max-width="30rem"
              >
                <template v-slot:activator="{ on, attrs }">
                  <v-btn icon v-bind="attrs" v-on="on">
                    <v-icon>mdi-bell</v-icon>
                  </v-btn>
                </template>

                <v-card class="mx-auto">
                  <v-card-title class="white--text secondary darken-1">
                    Notification
                  </v-card-title>

                  <v-list three-line dense>
                    <v-virtual-scroll
                      :items="notif(increment)"
                      item-height="100"
                      height="300"
                    >
                      <template v-slot="{ item, index }">
                        <v-list-item>
                          <v-list-item-avatar>
                            <v-avatar color="primary">
                              {{ index + 1 }}
                            </v-avatar>
                          </v-list-item-avatar>

                          <v-list-item-content>
                            <v-list-item-title
                              >{{ item.data.user.name }}
                            </v-list-item-title>
                            <v-list-item-subtitle>
                              {{ item.data.model }} - {{ item.data.event }}
                            </v-list-item-subtitle>
                            <v-list-item-subtitle>
                              at : {{ item.created_at }}
                            </v-list-item-subtitle>
                          </v-list-item-content>

                          <v-list-item-action>
                            <v-btn depressed @click="seeNotif(item)" small>
                              See

                              <v-icon color="orange darken-4" right>
                                mdi-open-in-new
                              </v-icon>
                            </v-btn>
                          </v-list-item-action>
                        </v-list-item>
                        <v-divider></v-divider>
                        <v-divider></v-divider>
                      </template>
                    </v-virtual-scroll>

                    <div v-if="notifCount >= 5" class="text-center">
                      <v-btn
                        class="ma-2"
                        :loading="notifLoading"
                        :disabled="notifLoading"
                        @click="moreNotif"
                      >
                        Load More
                        <template v-slot:loader>
                          <span class="custom-loader">
                            <v-icon light>mdi-cached</v-icon>
                          </span>
                        </template>
                      </v-btn>
                    </div>
                  </v-list>
                </v-card>
              </v-menu>
            </v-badge>

            <!-- NOTIFICATION DIALOG -->
            <v-dialog v-model="dialog" max-width="50vh">
              <v-card>
                <v-card-title class="secondary">
                  <span class="headline white--text">
                    Notification Details</span
                  >
                </v-card-title>

                <v-card-text>
                  <v-list>
                    <v-list-item>
                      <v-list-item-content>
                        <p class="mb-0 text-left caption">Model :</p>
                        <p class="mb-0 text-center overline">
                          {{ model.model }}
                        </p>
                      </v-list-item-content>
                      <v-list-item-content>
                        <p class="mb-0 text-left caption">Type :</p>
                        <p class="mb-0 text-center overline">
                          {{ model.event }}
                        </p>
                      </v-list-item-content>
                      <v-list-item-content>
                        <p class="mb-0 text-left caption">Changes By :</p>
                        <p class="mb-0 text-center overline">
                          {{ model.user == null ? null : model.user.name }}
                        </p>
                      </v-list-item-content>
                    </v-list-item>
                    <v-list-item v-if="model.event == 'Updated'">
                      <v-list-item-content> BEFORE </v-list-item-content>
                      <v-list-item-content> AFTER </v-list-item-content>
                    </v-list-item>
                  </v-list>
                  <v-list
                    v-if="model.event == 'Updated'"
                    color="grey lighten-2"
                  >
                    <v-list-item v-for="(item, i) in model.changes" :key="i">
                      <v-list-item-content class="py-0 px-2">
                        <p class="mb-0 text-left caption">
                          {{ i.toUpperCase() }} :
                        </p>
                        <p class="mb-0 text-center overline">{{ item }}</p>
                      </v-list-item-content>
                      <v-divider vertical></v-divider>
                      <v-list-item-content class="py-0 px-2">
                        <p class="mb-0 text-left caption">
                          {{ i.toUpperCase() }} :
                        </p>
                        <p class="mb-0 text-center overline">
                          {{ model.data[i] }}
                        </p>
                      </v-list-item-content>
                    </v-list-item>
                  </v-list>
                  <v-list v-else color="grey lighten-2">
                    <v-list-item v-for="(item, i) in model.data" :key="i">
                      <v-list-item-content
                        v-if="notObject(item)"
                        class="py-0 px-8"
                      >
                        <p class="mb-0 text-left caption">
                          {{ i.toUpperCase() }} :
                        </p>
                        <p class="mb-0 text-center overline">{{ item }}</p>
                      </v-list-item-content>
                    </v-list-item>
                  </v-list>
                </v-card-text>
                <v-divider></v-divider>
                <v-card-actions>
                  <v-btn @click="dialogClose">Close</v-btn>
                  <v-spacer></v-spacer>
                </v-card-actions>
              </v-card>
            </v-dialog>

            <!-- ADD DIALOG -->
            <v-dialog v-model="userData.dialog" max-width="50vh">
              <v-card>
                <v-card-title class="secondary">
                  <span class="headline"> Edit Account </span>
                </v-card-title>

                <v-form
                  v-if="userData.dialog"
                  ref="userForm"
                  lazy-validation
                  @submit.prevent
                >
                  <v-list-group :value="true" prepend-icon="mdi-account-circle">
                    <template v-slot:activator>
                      <v-list-item-title>User Data</v-list-item-title>
                    </template>
                    <v-list-item v-for="(item, i) in userData.model" :key="i">
                      <v-text-field
                        class="mt-2"
                        :value="item"
                        v-model="userData.model[i].value"
                        :label="userData.model[i].label"
                        :placeholder="userData.model[i].placeholder"
                        :error-messages="errors[i]"
                        outlined
                        dense
                      ></v-text-field>
                    </v-list-item>
                  </v-list-group>
                  <v-divider></v-divider>
                  <v-list-group prepend-icon="mdi-lock">
                    <template v-slot:activator>
                      <v-list-item-title>Change Password</v-list-item-title>
                    </template>
                    <v-list-item
                      v-for="(item, i) in userData.password"
                      :key="i"
                    >
                      <v-text-field
                        :type="userData.hidePassword ? 'password' : 'text'"
                        :append-icon="
                          userData.hidePassword ? 'mdi-eye' : 'mdi-eye-off'
                        "
                        :label="userData.password[i].label"
                        v-model="userData.password[i].value"
                        :rules="
                          i == 'password_confirmation'
                            ? [
                                (v) => !!v || 'This field is required.',
                                passwordConfirmationRule,
                              ]
                            : [(v) => !!v || 'This field is required.']
                        "
                        :error-messages="errors[i]"
                        @click:append="
                          userData.hidePassword = !userData.hidePassword
                        "
                        outlined
                        dense
                        class="mt-2"
                      />
                    </v-list-item>
                  </v-list-group>
                </v-form>

                <v-divider></v-divider>
                <v-card-actions>
                  <v-btn text @click="$refs.userForm.reset()">Clear</v-btn>
                  <v-spacer></v-spacer>
                  <v-btn
                    :loading="userLoading"
                    class="white--text"
                    color="primary"
                    @click="putUser"
                    depressed
                    >Save</v-btn
                  >
                </v-card-actions>
              </v-card>
            </v-dialog>
            <!-- TOOLBAR USER MENU -->
            <v-menu :close-on-content-click="true" offset-y>
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon large v-bind="attrs" v-on="on">
                  <v-avatar size="36px" item>
                    <v-img src="/images/logo.png" alt="elib"></v-img>
                  </v-avatar>
                </v-btn>
              </template>

              <v-card>
                <v-list>
                  <v-list-item>
                    <v-list-item-avatar>
                      <img src="/images/logo.png" />
                    </v-list-item-avatar>

                    <v-list-item-content>
                      <v-list-item-title>
                        {{ user.name || '' }}</v-list-item-title
                      >
                      <v-list-item-subtitle>{{
                        user.nik || ''
                      }}</v-list-item-subtitle>
                    </v-list-item-content>
                  </v-list-item>
                </v-list>

                <v-divider></v-divider>

                <v-list>
                  <v-list-item @click="dialogUserOpen">
                    <v-list-item-title>User Setting</v-list-item-title>
                  </v-list-item>

                  <v-list-item>
                    <v-list-item-title>Document Submited</v-list-item-title>
                  </v-list-item>
                </v-list>

                <v-card-actions>
                  <v-spacer></v-spacer>

                  <v-btn text @click="menu = false"> Cancel </v-btn>
                  <v-btn color="primary" text @click="logout"> Logout </v-btn>
                </v-card-actions>
              </v-card>
            </v-menu>
          </div>
        </v-col>
      </v-row>
    </v-app-bar>
    <v-main>
      <v-container class="fill-height" fluid>
        <nuxt />
      </v-container>
    </v-main>
    <myFooter></myFooter>
  </v-app>
</template>

<script>
import { mapState, mapGetters } from 'vuex'
import snackbar from '~/components/snackbar.vue'
import myFooter from '~/components/myFooter.vue'
import excel from 'vue-json-excel'

export default {
  components: { snackbar, myFooter, excel },
  data: () => ({
    model: {},
    search: null,
    total: null,
    items: [],
    menu: false,
    timeout: '',
    increment: 1,
    socket: true,
    userData: {
      model: {
        name: { label: 'Name', placeholder: 'John ', value: '' },
        nik: {
          label: 'NIK',
          placeholder: 'ex: 1900XX',
          value: '',
        },
        org_code: {
          label: 'Organization Code',
          placeholder: 'ex: MS1200',
          value: '',
        },
        dept: {
          label: 'Departement',
          placeholder: 'ex: Airframe',
          value: '',
        },
      },
      password: {
        password_current: {
          label: 'Current Password',
          placeholder: 'Current Password',
          value: '',
        },
        password: {
          label: 'New Password',
          placeholder: 'New Password',
          value: '',
        },
        password_confirmation: {
          label: ' Password Confirmation',
          placeholder: ' Password Confirmation',
          value: '',
        },
      },
      hidePassword: true,
      dialog: false,
      edit: false,
    },
    dialog: false,
  }),

  computed: {
    ...mapState({
      user: (state) => state.auth.user,
      userLoading: (state) => state.users.loading,
      isAdmin: (state) => state.auth.user.is_admin,
      errors: (state) => state.errors,
      notifCount: (state) => state.notifications.total,
      page: (state) => state.notifications.page,
      notifLoading: (state) => state.notifications.loading,
      notifications: (state) => state.notifications.items,
      documents: (state) => state.documents.items,
      users: (state) => state.users.items,
      aircrafts: (state) => state.aircrafts.items,
    }),
    ...mapGetters('notifications', {
      notif: 'notifTake',
    }),
    searchAll: {
      get() {
        return this.search
      },
      set(val) {
        this.debounced(val)
      },
    },
    passwordConfirmationRule() {
      return () =>
        this.model.password === this.model.password_confirmation ||
        'Password must match'
    },
  },
  mounted() {
    this.notifications.length != 0 ||
      this.$store.dispatch('fetch', 'notifications')
    this.$echo.connector.socket
      .on('reconnecting', (e) => {
        this.socket = false
        this.$notifier.showMessage({
          content: 'Cant connect to Server',
          color: 'error',
        })
      })
      .on('connect', (e) => {
        this.socket = true
      })

    let id = this.user.id

    if (id) {
      this.$echo.private(`App.User.${id}`).notification((notif) => {
        console.log(notif)
        this.notification(notif)
      })

      this.$echo
        .join(`user`)
        .joining((user) => {
          this.$store.dispatch('users/auth', { id: user.id, is_active: true })
          this.$notifier.showMessage({
            content: `${user.name} is Logged in`,
            color: 'warning',
          })
        })
        .leaving((user) => {
          this.$store.dispatch('put', {
            url: 'users/status/',
            form: {
              id: user.id,
              is_active: false,
            },
          })
          this.$store.dispatch('users/auth', { id: user.id, is_active: false })
          this.$notifier.showMessage({
            content: `${user.name} is Logged out`,
            color: 'warning',
          })
        })
    }
  },
  watch: {
    dialog(val) {
      val || this.dialogClose()
    },
  },
  methods: {
    debounced: _.debounce(function (val) {
      this.search = val
      this.$store.commit('loading', false)
    }, 1000),
    moreNotif() {
      this.increment++
    },
    logout() {
      this.$store.dispatch('logout')
    },
    notObject(item) {
      return typeof item !== 'object'
    },
    async seeNotif(item) {
      Object.keys(item.data.data).forEach((val) => {
        if (typeof item.data.data[val] == 'object') delete item.data.data[val]
      })
      this.model = item.data
      this.dialog = true
      this.menu = false
    },
    dialogClose() {
      this.dialog = false
      this.model = {}
    },
    dialogUserClose() {
      this.userData.dialog = false
      this.dialog = false
      _.mapValues(this.userData.model, (item) => {
        item.value = ''
      })
      _.mapValues(this.userData.password, (item) => {
        item.value = ''
      })
    },
    dialogUserOpen() {
      this.userData.dialog = true
      this.dialog = false
      _.mapValues(this.userData.model, (item) => {
        item.value = this.user[item.name]
      })
    },
    putUser() {
      let user = _.mapValues(this.userData.model, 'value')
      let password = _.mapValues(this.userData.password, 'value')
      this.$store
        .dispatch('put', {
          url: 'users/',
          form: {
            id: this.user.id,
            ...user,
            ...password,
          },
        })
        .then((res) => {
          this.dialogUserClose()
          this.$store.dispatch('users/update', res.entry)
        })
    },
    notification(notif) {
      this.$store.dispatch('notifications/create', notif)
      const models = {
        document: (event) => {
          this.$store.dispatch('documents/' + event, notif.data)
          return `Document ${notif.data.subject ?? 'not found'} - ${
            notif.event
          }`
        },
        manual: (event) => {
          this.$store.dispatch('manuals/' + event, notif.data)
          return `Manual ${notif.data.subject ?? 'not found'} - ${notif.event}`
        },
        file: () => {
          if (notif.data.revision.revisable_type == 'App\\Manual') {
            this.$store.dispatch('manuals/file', notif.data)
          } else {
            this.$store.dispatch('documents/file', notif.data)
          }
          return `File for ${notif.data.filename ?? 'not found'} - ${
            notif.event
          }`
        },
        revision: (event) => {
          switch (event) {
            case 'created':
              if (notif.data.revision[0].revisable_type == 'App\\Manual') {
                this.$store.dispatch('manuals/revision', notif.data.revision[0])
              } else {
                this.$store.dispatch(
                  'documents/revision',
                  notif.data.revision[0]
                )
              }
              return `New index: ${
                notif.data.revision[0].index ?? 'not found'
              } for document ${notif.data.subject ?? 'not found'}`
            case 'status':
              if (notif.data.revision[0].revisable_type == 'App\\Manual') {
                this.$store.dispatch('manuals/status', notif.data.revision[0])
              } else {
                this.$store.dispatch('documents/status', notif.data.revision[0])
              }
              return `Revision index : ${
                notif.data.revision[0].index ?? 'not found'
              } for document ${notif.data.subject ?? 'not found'} ${
                notif.data.revision[0].is_closed == 1 ? 'closed' : ' open '
              }`
          }
        },
        order: (event) => {
          switch (event) {
            case 'created':
              this.$store.dispatch('manuals/order', notif.data)
              return `New order for: ${
                notif.data.manual.subject ?? 'not found'
              }`
            case 'status':
              this.$store.dispatch('manuals/status', notif.data)
              return `Manual : ${
                notif.data.manual.subject ?? 'not found'
              } is returned`
          }
        },
        user: (event) => this.$store.dispatch('users/' + event, notif.data),
        aircraft: (event) => {
          this.$store.dispatch('aircrafts/' + event, notif.data)
          return `Aircraft ${notif.data.reg_code ?? 'not found'} - ${
            notif.event
          }`
        },
      }
      this.$notifier.showMessage({
        content: models[notif.model]?.(notif.event) ?? 'notification error', //this calls the function, no need to return
        color: 'success',
      })
      //return models[notif.model]?.(notif.event) ?? 'notification error'
    },
  },
}
</script>
