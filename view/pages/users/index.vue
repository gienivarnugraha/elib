<template>
  <v-row align="center" justify="center">
    <v-card class="mx-auto my-6" min-width="75%">
      <v-card-title class="secondary white--text">
        <v-row align="center" justify="space-around">
          <v-col md="2" sm="9" xs="9">
            <p class="text-center mb-0">User Lists</p>
          </v-col>

          <!--  SEARCH COLUMN -->
          <v-col>
            <v-text-field
              v-model.lazy="searchTimeOut"
              append-icon="mdi-magnify"
              label="Search User"
              background-color="white"
              clearable
              hide-details
              single-line
              solo
              flat
              dense
              placeholder="Start typing to search.."
            >
            </v-text-field>
          </v-col>

          <!-- ADD NEW USER COLUMN -->
          <v-col md="1" sm="9" xs="9">
            <v-dialog v-model="dialog" max-width="50vh">
              <template v-slot:activator="{ on: menu, attrs }">
                <v-tooltip top>
                  <template v-slot:activator="{ on: tooltip }">
                    <v-btn
                      color="primary"
                      v-bind="attrs"
                      class="d-block"
                      v-on="{ ...tooltip, ...menu }"
                    >
                      <v-icon>mdi-plus</v-icon>
                    </v-btn>
                  </template>
                  <span>Add</span>
                </v-tooltip>
              </template>

              <!-- ADD DIALOG -->
              <v-card>
                <v-card-title class="secondary">
                  <span class="headline">Add User</span>
                </v-card-title>

                <v-form ref="form" lazy-validation @submit.prevent>
                  <v-list class="mt-2">
                    <v-list-item v-for="(item, i) in model" :key="i">
                      <v-text-field
                        :value="item"
                        v-model="model[i].value"
                        :label="model[i].label"
                        :placeholder="model[i].placeholder"
                        :error-messages="errors[i]"
                        :rules="[(v) => !!v || i + ' field is required.']"
                        outlined
                        dense
                      ></v-text-field>
                    </v-list-item>
                  </v-list>
                </v-form>

                <v-divider></v-divider>
                <v-card-actions>
                  <v-btn text @click="$refs.form.reset()">Clear</v-btn>
                  <v-spacer></v-spacer>
                  <v-btn
                    :loading="loading"
                    class="white--text"
                    color="primary"
                    @click="post"
                    depressed
                    >Save</v-btn
                  >
                </v-card-actions>
              </v-card>
            </v-dialog>
          </v-col>
        </v-row>
      </v-card-title>

      <v-data-table
        :items-per-page="25"
        :headers="headers"
        :items="users"
        :loading="loading"
        :search="search"
      >
        <!-- IS ONLINE ICON -->
        <template v-slot:item.is_active="props">
          <div>
            <v-slide-x-transition>
              <v-tooltip top>
                <template v-slot:activator="{ on, attrs }">
                  <v-icon
                    v-bind="attrs"
                    v-on="on"
                    :color="props.item.is_active ? 'success' : 'error '"
                    v-text="
                      props.item.is_active
                        ? 'mdi-check-circle'
                        : 'mdi-alert-circle'
                    "
                  >
                  </v-icon>
                </template>
                <span>{{ props.item.is_active ? 'Online' : 'Offline ' }}</span>
              </v-tooltip>
            </v-slide-x-transition>
          </div>
        </template>
        <template v-slot:item.actions="{ item }">
          <v-icon @click="deleteItem(item)">mdi-delete</v-icon>
        </template>
      </v-data-table>
    </v-card>
  </v-row>
</template>

<script>
import { mapState } from 'vuex'

export default {
  layout: 'dashboard',

  data: () => ({
    headers: [
      { text: 'NIK', value: 'nik' },
      { text: 'Name', value: 'name' },
      { text: 'Org Code', value: 'org_code' },
      { text: 'Dept', value: 'dept' },
      { text: 'Email', value: 'email' },
      { text: 'Online', value: 'is_active', align: 'center', sortable: false },
      { text: 'Last Login', value: 'last_login', width: '10%' },
      { text: 'Actions', value: 'actions', align: 'center', sortable: false },
    ],
    search: null,
    form: '',
    model: {
      name: { label: 'Name', placeholder: 'John ', value: '' },
      nik: { label: 'NIK', placeholder: 'ex: 1900XX', value: '' },
      org_code: {
        label: 'Organization Code',
        placeholder: 'ex: MS1200',
        value: '',
      },
      dept: { label: 'Departement', placeholder: 'ex: Airframe', value: '' },
      email: {
        label: 'Email',
        placeholder: 'ex: ad@indo-aero.com',
        value: '',
      },
    },
    valid: false,
    dialog: false,
  }),
  mounted() {
    this.users.length != 0 || this.$store.dispatch('fetch', 'users')
  },
  watch: {
    dialog(val) {
      val || this.close()
    },
  },
  computed: {
    ...mapState({
      user: (state) => state.auth.user,
      isAdmin: (state) => state.auth.user.is_admin,
      errors: (state) => state.errors,
      loading: (state) => state.users.loading,
      users: (state) => state.users.items,
    }),
    searchTimeOut: {
      get() {
        return this.search
      },
      set(val) {
        this.$store.commit('users/loading', true)
        this.debounced(val)
      },
    },
  },
  methods: {
    debounced: _.debounce(function (val) {
      this.search = val
      this.$store.commit('users/loading', false)
    }, 1000),

    close() {
      this.dialog = false
      this.$refs.form.reset()
      this.$refs.form.resetValidation()
    },

    deleteItem(item) {
      swal({
        title: `Delete User: ${item.name} NIK:${item.nik} ?`,
        icon: 'warning',
        buttons: true,
        dangerMode: true,
        className: 'primary',
      }).then((willDelete) => {
        if (willDelete) {
          this.delete(item)
        }
      })
    },
    fetch() {
      this.$store.dispatch('fetch', 'users')
    },
    post() {
      this.$store
        .dispatch('post', {
          url: 'users/',
          form: _.mapValues(this.model, 'value'),
        })
        .then((data) => {
          this.close()
          console.log(data)
          this.$store.dispatch('users/create', data.entry)
        })
    },
    delete(item) {
      this.$store
        .dispatch('delete', {
          url: 'users/',
          form: item,
        })
        .then((res) => {
          this.$store.dispatch('users/delete', res)
        })
    },
  },
}
</script>
