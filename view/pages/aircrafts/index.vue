<template>
  <v-row align="center" justify="center">
    <v-card class="mx-auto my-6">
      <v-card-title class="secondary white--text">
        <v-row align="center" justify="space-between">
          <v-col md="2"> Aircraft List </v-col>

          <!-- SEARCH COLUMN -->
          <v-col>
            <v-text-field
              v-model.lazy="searchTimeOut"
              append-icon="mdi-magnify"
              label="Search"
              placeholder="Type something to search..."
              clearable
              single-line
              hide-details
              solo
              flat
              dense
              background-color="white"
            ></v-text-field>
          </v-col>

          <!-- GROUP BY COLUMN -->
          <v-col md="3">
            <v-select
              v-model="group.groupBy"
              :items="group.groupList"
              item-text="text"
              item-value="value"
              label="Group By"
              placeholder="Group By"
              @input="filter.results = []"
              single-line
              menu-props="offsetY"
              clearable
              hide-details
              solo
              flat
              dense
            ></v-select>
          </v-col>

          <!-- FILTER BY COLUMN -->
          <v-col md="1">
            <v-menu
              v-model="filter.menu"
              offset-y
              :close-on-content-click="false"
            >
              <template v-slot:activator="{ on: menu, attrs }">
                <v-tooltip top>
                  <template v-slot:activator="{ on: tooltip }">
                    <v-btn
                      block
                      color="primary"
                      v-bind="attrs"
                      v-on="{ ...tooltip, ...menu }"
                      ><v-icon>mdi-filter</v-icon>
                    </v-btn>
                  </template>
                  <span>Filter Aircraft</span>
                </v-tooltip>
              </template>

              <v-card class="px-4 pt-4 pb-0">
                <v-select
                  v-model="filter.items"
                  :items="filterList"
                  item-text="text"
                  label="Filter By"
                  placeholder="Filter By"
                  return-object
                  clearable
                  prepend-icon="mdi-filter-outline"
                  outlined
                  dense
                  menu-props="offsetY, closeOnClick"
                  @click:clear="cancelFilter"
                  multiple
                  chips
                  deletable-chips
                >
                </v-select>
                <v-divider></v-divider>
                <v-card-actions>
                  <v-btn
                    text
                    color="accent"
                    @click="cancelFilter, (filter.menu = false)"
                    >Cancel</v-btn
                  >
                  <v-spacer></v-spacer>
                  <v-btn
                    :loading="loading"
                    class="white--text"
                    color="accent"
                    @click="doFilter"
                    depressed
                    >Filter</v-btn
                  >
                </v-card-actions>
              </v-card>
            </v-menu>
          </v-col>

          <!-- ADD AIRCRAFT COLUMN -->
          <v-col v-if="isAdmin" md="1">
            <v-dialog v-model="dialog" max-width="500px">
              <template v-slot:activator="{ on: dialog, attrs }">
                <v-tooltip top>
                  <template v-slot:activator="{ on: tooltip }">
                    <v-btn
                      block
                      color="accent"
                      v-bind="attrs"
                      v-on="{ ...dialog, ...tooltip }"
                      @click="open"
                    >
                      <v-icon>mdi-plus</v-icon>
                    </v-btn>
                  </template>
                  <span>Add New Aircraft</span>
                </v-tooltip>
              </template>
              <v-card>
                <v-card-title class="secondary">
                  <span class="headline white--text"
                    >{{ editedIndex === -1 ? 'New ' : 'Edit ' }} Aircraft</span
                  >
                </v-card-title>

                <v-form
                  class="mt-6"
                  ref="form"
                  v-model="valid"
                  lazy-validation
                  @submit.prevent
                >
                  <v-list>
                    <v-list-item v-for="(item, i) in model" :key="i">
                      <v-text-field
                        :value="item"
                        v-model="model[i].value"
                        :label="model[i].label"
                        :placeholder="model[i].placeholder"
                        :error-messages="errors[i]"
                        :required="model[i].required"
                        :rules="[(v) => !!v || 'This field is required']"
                        outlined
                        dense
                      ></v-text-field>
                    </v-list-item>
                  </v-list>
                </v-form>

                <v-divider></v-divider>
                <v-card-actions>
                  <v-btn
                    v-if="editedIndex === -1"
                    text
                    @click="$refs.form.reset()"
                    >Clear</v-btn
                  >
                  <v-spacer></v-spacer>
                  <v-btn
                    :loading="loading"
                    class="white--text"
                    color="accent"
                    @click="save"
                    depressed
                    >Save</v-btn
                  >
                </v-card-actions>
              </v-card>
            </v-dialog>
          </v-col>
        </v-row>
      </v-card-title>
      <v-card-text>
        <v-data-table
          :headers="headers"
          :items="filter.results.length > 0 ? filter.results : aircrafts"
          :loading="loading"
          :search="search"
          :footer-props="{
            'items-per-page-options': [25, 50],
          }"
          :groupBy="group.groupBy"
          multi-sort
        >
          <template v-if="isAdmin" v-slot:item.actions="{ item }">
            <v-icon @click="editItem(item)">mdi-pencil</v-icon>
            <v-icon @click="deleteItem(item)">mdi-delete</v-icon>
          </template>
        </v-data-table>
      </v-card-text>
    </v-card>
  </v-row>
</template>

<script>
import { mapState, mapGetters } from 'vuex'

export default {
  layout: 'dashboard',

  data: () => ({
    headers: [
      { text: 'Aircraft Type', value: 'type', align: 'center' },
      { text: ' Serial Number', value: 'serial_num', align: 'center' },
      { text: 'Registration Code', value: 'reg_code', align: 'center' },
      { text: 'Effectivity', value: 'effectivity', align: 'center' },
      { text: 'Owner', value: 'owner', align: 'center' },
      { text: 'Manufactured', value: 'manuf_date', align: 'center' },
      { text: 'Actions', value: 'actions', sortable: false, align: 'center' },
    ],
    search: null,
    select: '',
    form: '',
    valid: false,
    editedIndex: -1,
    model: {
      type: { value: '', placeholder: 'Ex: CN235', label: 'Aircraft Type' },
      reg_code: {
        value: '',
        placeholder: 'Ex: A2304',
        label: 'Registration Code',
        required: true,
      },
      effectivity: {
        value: '',
        placeholder: 'Ex: B001025',
        label: 'Effectivity',
        required: true,
      },
      serial_num: {
        value: '',
        placeholder: 'Ex: N023',
        label: 'Serial Number',
        required: true,
      },
      manuf_date: {
        value: '',
        placeholder: 'Ex: 1993',
        label: 'Manufactured Date',
        required: true,
      },
      owner: {
        value: '',
        placeholder: 'Ex: TNI-AU',
        label: 'Aircraft Owner',
        required: false,
      },
    },
    /* DATA CUSTOMIZE */
    filter: {
      menu: false,
      items: [],
      results: [],
    },
    group: {
      groupBy: null,
      groupList: [
        { text: 'Aircraft', value: 'type' },
        { text: 'Owner', value: 'owner' },
      ],
    },
    dialog: false,
  }),
  mounted() {
    this.aircrafts.length != 0 || this.$store.dispatch('fetch', 'aircrafts')
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
      loading: (state) => state.aircrafts.loading,
      aircrafts: (state) => state.aircrafts.items,
    }),
    ...mapGetters('aircrafts', {
      types: 'aircraftTypes',
      filterBy: 'filterBy',
    }),
    searchTimeOut: {
      get() {
        return this.search
      },
      set(val) {
        this.$store.commit('aircrafts/loading', true)
        this.debounced(val)
      },
    },
    filterList() {
      let lists = []
      this.types.forEach((item) => {
        let type = { text: 'Only ' + item, key: 'type', value: item }
        lists.push(type)
      })
      lists.push({ text: 'Only Owner', key: 'owner', value: 'owner' })
      return lists
    },
  },
  methods: {
    debounced: _.debounce(function (val) {
      this.search = val
      this.$store.commit('aircrafts/loading', false)
    }, 1000),
    close() {
      this.$nextTick(() => {
        this.editedIndex = -1
        this.$refs.form.resetValidation()
        this.$refs.form.reset()
        this.dialog = false
      })
    },
    open() {
      this.dialog = true
    },
    save() {
      if (this.editedIndex > -1) {
        this.putAircraft()
      } else {
        this.postAircraft()
      }
    },
    editItem(item) {
      this.editedIndex = this.aircrafts.indexOf(item)
      _.forOwn(this.model, (val, key) => {
        val.value = item[key]
      })
      this.dialog = true
    },
    deleteItem(item) {
      const index = this.aircrafts.indexOf(item)
      swal({
        title: `Delete Aircraft Reg: ${item.reg_code} ?`,
        icon: 'warning',
        buttons: true,
        dangerMode: true,
        className: 'accent',
      }).then((willDelete) => {
        if (willDelete) {
          this.deleteAircraft(item)
        }
      })
    },
    postAircraft() {
      if (this.isAdmin) {
        this.$store
          .dispatch('post', {
            url: 'aircrafts/',
            form: _.mapValues(this.model, 'value'),
          })
          .then((data) => {
            this.close()
          })
      }
    },
    putAircraft() {
      if (this.isAdmin) {
        let form = _.mapValues(this.model, 'value')
        form['id'] = this.aircrafts[this.editedIndex].id
        this.$store
          .dispatch('put', {
            url: 'aircrafts/',
            form: form,
          })
          .then((data) => {
            this.close()
          })
      }
    },
    deleteAircraft(item) {
      if (this.isAdmin) {
        this.$store.dispatch('delete', {
          url: 'aircrafts/',
          form: item,
        })
      }
    },

    async doFilter() {
      this.$store.commit('aircrafts/loading', true)
      if (this.group.groupBy) {
        this.cancelFilter()
      }
      this.group.groupBy = null
      let filterMoreThanOne = []

      if (this.filter.items.length == 1) {
        this.filter.results = await this.filterBy(
          this.filter.items[0].key,
          this.filter.items[0].value
        )
      } else {
        await this.filter.items.forEach(async (item) => {
          let filter = await this.filterBy(item.key, item.value)
          await filterMoreThanOne.push(filter)
        })
        this.filter.results = await _.flatten(filterMoreThanOne)
      }
      this.dialog = false
      this.$store.commit('aircrafts/loading', false)
    },
    cancelFilter() {
      this.filter.results = []
      this.filter.items = []
      this.dialog = false
    },
  },
}
</script>
