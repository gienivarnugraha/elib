<template>
  <v-row align="center" justify="center">
    <v-card>
      <v-card-title>
        Document Order List
        <v-spacer></v-spacer>
        <v-text-field
          v-model.lazy="searchTimeOut"
          append-icon="mdi-magnify"
          label="Search"
          single-line
          hide-details
        ></v-text-field>
        <v-spacer></v-spacer>
      </v-card-title>

      <v-data-table
        :items-per-page="25"
        :headers="headers"
        :items="orders"
        :loading="loading"
        :search="search"
      >
        <template v-slot:item.is_closed="{ item }">
          <v-icon :color="item.is_closed == 1 ? 'green' : 'red '">{{
            getIcon(item.is_closed)
          }}</v-icon>
        </template>

        <!--         <template v-slot:item.is_closed="props">
          <v-edit-dialog
            :return-value.sync="props.item.is_returned"
            large
            persistent
            @save="editItem(props.item.is_returned)"
          >
            <div>
              <v-icon>{{ getIcon(props.item.is_returned) }}</v-icon>
            </div>
            <template v-slot:input>
              <v-select
                class="mt-8"
                :items="model.is_returned"
                filled
                clearable
                v-model="props.item.is_returned"
                menu-props="closeOnClick=true, closeOnContentClick,offsetY"
                label="Document Status"
              ></v-select>
            </template>
          </v-edit-dialog>
        </template>-->

        <template v-slot:item.actions="{ item }">
          <v-icon small @click="editItem(item)">mdi-pencil</v-icon>
          <v-icon small @click="deleteItem(item)">mdi-delete</v-icon>
        </template>
      </v-data-table>
    </v-card>
  </v-row>
</template>

<script>
export default {
  layout: 'dashboard',

  data: () => ({
    headers: [
      { text: 'A/C', value: 'manual.type', align: 'center' },

      { text: 'Borrower', value: 'user.name' },
      { text: 'Send Date', value: 'send_date', align: 'center' },
      { text: 'Return Date', value: 'return_date' },
      { text: 'Status', value: 'is_closed', align: 'center' },
      { text: 'Actions', value: 'actions', align: 'center', sortable: false },
    ],
    search: null,

    docType: ['WDM', 'IPC', 'CMM', 'AMM', 'SRM', 'ITEM'],
    modelorders: {},
    orders: [],
    select: '',
    form: '',
    newItem: false,
    editedIndex: -1,
    defaultItem: {},
    expanded: [],
    dialog: false,
    timeout: '',
  }),
  mounted() {
    this.fetch()
  },
  watch: {
    dialog(val) {
      val || this.close()
    },
  },
  computed: {
    searchTimeOut: {
      get() {
        return this.search
      },
      set(val) {
        if (this.timeout) clearTimeout(this.timeout)
        this.$store.commit('loading', true)
        this.timeout = setTimeout(() => {
          this.$store.commit('loading', false)
          this.search = val
        }, 800)
      },
    },
    fields() {
      if (!this.model) return []

      return Object.keys(this.model).map((key) => {
        return {
          key,
          value: this.model[key] || 'n/a',
        }
      })
    },
    formTitle() {
      return this.editedIndex === -1 ? 'New Item' : 'Edit Item'
    },
    loading() {
      return this.$store.state.loading
    },
  },
  methods: {
    getIcon(is_closed) {
      if (is_closed == 1) return ' mdi-check-circle'
      else return 'mdi-alert-circle'
    },

    close() {
      this.dialog = false
      this.newItem = false
      this.$nextTick(() => {
        this.modelorders = Object.assign({}, this.defaultItem)
        this.editedIndex = -1
      })
    },

    save() {
      if (this.editedIndex > -1) {
        this.put()
      } else {
        this.post()
      }
      this.close()
    },

    editItem(item) {
      this.editedIndex = this.orders.indexOf(item)
      this.modelorders = Object.assign({}, item)
      this.dialog = true
    },

    deleteItem(item) {
      const index = this.orders.indexOf(item)
      swal({
        title: `Delete Aircraft Document: ${item.type} for effectivity:${item.aircraft.effectivity} ?`,
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
      let res = this.$store.dispatch('fetch', '/orders').then((data) => {
        this.orders = data
      })
    },
    post() {
      let res = this.$store
        .dispatch('post', {
          url: '/orders',
          form: this.model,
        })
        .then((data) => {
          this.model = ''
          this.fetch()
          this.new = false
        })
    },
    put() {
      let res = this.$store.dispatch('put', {
        url: '/orders/',
        form: this.model,
      })
    },
    delete(item) {
      let res = this.$store.dispatch('delete', {
        url: '/orders/',
        form: item,
      })
    },
  },
}
</script>
