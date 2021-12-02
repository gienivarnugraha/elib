<template>
  <v-row align="center" justify="center">
    <v-card>
      <v-card-title>
        Aircraft Documents List

        <v-spacer></v-spacer>
        <v-text-field
          v-model.lazy="searchTimeOut"
          append-icon="mdi-magnify"
          label="Search"
          single-line
          hide-details
        ></v-text-field>
        <v-spacer></v-spacer>
        <v-dialog v-model="dialog" max-width="500px">
          <template v-slot:activator="{ on, attrs }">
            <v-btn color="primary" v-bind="attrs" v-on="on">
              Add
              <v-icon right>mdi-plus</v-icon>
            </v-btn>
          </template>
          <v-card>
            <v-card-title class="teal lighten-4">
              <span class="headline">{{ formTitle }}</span>
            </v-card-title>

            <v-form ref="formAircraftDoc" class="pa-4 pt-6" @submit.prevent>
              <v-autocomplete
                v-model="modelAircraftDocs.aircraft_id"
                :items="modelAircraft"
                item-value="id"
                item-text="type"
                label="Select Aircraft"
                header="Available Aircraft"
                divider
                hide-no-data
                :loading="loading"
                menu-props="closeOnClick=true, closeOnContentClick,offsetY"
                clearable
                filled
              >
                <template v-slot:item="data">
                  <v-list-item-content>
                    <v-list-item-title
                      v-html="data.item.type + ' - REG: ' + data.item.reg_code"
                    ></v-list-item-title>
                    <v-list-item-subtitle
                      v-html="
                        'S/N: ' +
                        data.item.serial_num +
                        ' - Owner: ' +
                        data.item.owner
                      "
                    ></v-list-item-subtitle>
                  </v-list-item-content>
                </template>
                <v-btn
                  text
                  icon
                  color="primary"
                  v-if="newAircraft == false"
                  slot="append-outer"
                  @click="newAircraft = true"
                >
                  <v-tooltip top>
                    <template v-slot:activator="{ on, attrs }">
                      <v-icon class="ma-2" v-bind="attrs" v-on="on"
                        >mdi-plus</v-icon
                      >
                    </template>
                    Add New Aircraft
                  </v-tooltip>
                </v-btn>

                <v-icon
                  v-else
                  color="primary"
                  slot="append-outer"
                  @click="newAircraft = false"
                  >mdi-minus</v-icon
                >
              </v-autocomplete>

              <v-expand-transition>
                <v-card class="mb-8 mr-8" v-if="newAircraft">
                  <v-card-title>
                    <span class="headline">Add New Aircraft Data</span>
                  </v-card-title>
                  <v-form ref="formAircraft" @submit.prevent>
                    <v-list>
                      <v-list-item>
                        <v-list-item-content>
                          <v-text-field
                            filled
                            label="Aircraft Type"
                            v-model="modelAircraft.type"
                            dense
                          ></v-text-field>
                          <v-text-field
                            filled
                            label="Aircraft Registration"
                            v-model="modelAircraft.reg_code"
                            dense
                          ></v-text-field>
                          <v-text-field
                            filled
                            label="Aircraft Effectivity"
                            v-model="modelAircraft.effectivity"
                            dense
                          ></v-text-field>
                          <v-text-field
                            filled
                            label="Aircraft Serial Number"
                            v-model="modelAircraft.serial_num"
                            dense
                          ></v-text-field>
                          <v-text-field
                            filled
                            label="Aircraft Owner"
                            v-model="modelAircraft.owner"
                            dense
                          ></v-text-field>
                          <v-text-field
                            filled
                            label="Aircraft Manufactured Date"
                            v-model="modelAircraft.manuf_date"
                            dense
                          ></v-text-field>
                        </v-list-item-content>
                      </v-list-item>
                    </v-list>
                  </v-form>
                  <v-divider></v-divider>
                  <v-card-actions class="teal lighten-5">
                    <v-btn
                      text
                      color="grey darken-3"
                      @click=";(newAircraft = false), (modelAircraft = '')"
                      >Cancel</v-btn
                    >
                    <v-spacer></v-spacer>
                    <v-btn
                      text
                      :disabled="formAircraft != null"
                      color="grey darken-3"
                      @click="$refs.formAircraft.reset()"
                      >Clear</v-btn
                    >
                    <v-spacer></v-spacer>
                    <v-btn
                      :disabled="modelAircraft == null"
                      :loading="loading"
                      class="white--text"
                      color="primary"
                      @click="postAircraft()"
                      depressed
                      >Add Aircraft</v-btn
                    >
                  </v-card-actions>
                </v-card>
              </v-expand-transition>

              <v-combobox
                v-model="modelAircraftDocs.type"
                :items="docType"
                label="Select Document Type"
                multiple
                filled
                chips
              ></v-combobox>
              <v-textarea
                label="Subject"
                v-model="modelAircraftDocs.subject"
                auto-grow
                rows="1"
                row-height="15"
                counter="100"
              ></v-textarea>
              <v-text-field
                filled
                label="Document Index"
                v-model="modelAircraftDocs.index"
                dense
              ></v-text-field>
              <v-text-field
                filled
                label="Document Index Date"
                v-model="modelAircraftDocs.index_date"
                dense
              ></v-text-field>
            </v-form>
            <v-divider></v-divider>
            <v-card-actions>
              <v-btn text @click="$refs.formAircraftDoc.reset()">Clear</v-btn>
              <v-spacer></v-spacer>
              <v-btn
                :loading="loading"
                class="white--text"
                color="primary"
                @click="save"
                depressed
                >Save</v-btn
              >
            </v-card-actions>
          </v-card>
        </v-dialog>
      </v-card-title>

      <v-data-table
        :headers="headers"
        :items="aircraftDocs"
        :loading="loading"
        :search="search"
        :expanded.sync="expanded"
        show-expand
        @click:row="expand"
        groupBy="aircraft.type"
        groupDesc
      >
        <template v-slot:group.header="{ group, headers, toggle, isOpen }">
          <td style="width: 200px" :colspan="headers.length">
            <v-btn @click="toggle" small icon :ref="group">
              <v-icon v-if="isOpen">mdi-minus</v-icon>
              <v-icon v-else>mdi-plus</v-icon>
            </v-btn>
            {{ group }}
          </td>
        </template>

        <template v-slot:expanded-item="{ headers, item }">
          <td :colspan="headers.length">
            <v-row no-gutters class="my-6">
              <v-col cols="3">
                <v-card outlined>
                  <v-card-title>
                    <span class="headline">{{
                      editAircraft == true ? 'Edit Aircraft' : 'Aircraft Data'
                    }}</span>
                    <v-tooltip top>
                      <template v-slot:activator="{ on, attrs }">
                        <v-btn
                          icon
                          @click="editAircraft = !editAircraft"
                          v-bind="attrs"
                          v-on="on"
                        >
                          <v-icon v-if="!editAircraft">mdi-pencil</v-icon>
                          <v-icon v-else>mdi-close-circle</v-icon>
                        </v-btn>
                      </template>
                      Edit Aircraft Data
                    </v-tooltip>
                  </v-card-title>

                  <v-form ref="formAircraft" @submit.prevent>
                    <v-list>
                      <v-list-item>
                        <v-list-item-content>
                          <v-text-field
                            label="Aircraft Type"
                            v-model="modelAircraft.type"
                            :disabled="!editAircraft"
                          ></v-text-field>
                          <v-text-field
                            label="Aircraft Registration"
                            v-model="modelAircraft.reg_code"
                            :disabled="!editAircraft"
                          ></v-text-field>
                          <v-text-field
                            label="Aircraft Effectivity"
                            v-model="modelAircraft.effectivity"
                            :disabled="!editAircraft"
                          ></v-text-field>
                          <v-text-field
                            label="Aircraft Serial Number"
                            v-model="modelAircraft.serial_num"
                            :disabled="!editAircraft"
                          ></v-text-field>
                          <v-text-field
                            label="Aircraft Owner"
                            v-model="modelAircraft.owner"
                            :disabled="!editAircraft"
                          ></v-text-field>
                          <v-text-field
                            label="Aircraft Manufactured Date"
                            v-model="modelAircraft.manuf_date"
                            :disabled="!editAircraft"
                          ></v-text-field>
                        </v-list-item-content>
                      </v-list-item>
                    </v-list>
                  </v-form>
                  <v-divider v-if="editAircraft"></v-divider>
                  <v-card-actions v-if="editAircraft">
                    <v-btn
                      text
                      color="grey darken-3"
                      v-if="editAircraft"
                      @click="editAircraft = false"
                      >Cancel</v-btn
                    >
                    <v-spacer></v-spacer>
                    <v-btn
                      :disabled="!editAircraft"
                      :loading="loading"
                      class="white--text"
                      color="primary"
                      @click="putAircraft()"
                      depressed
                      >Save</v-btn
                    >
                  </v-card-actions>
                </v-card>
              </v-col>
              <v-spacer></v-spacer>
              <v-col cols="3">
                <v-card outlined>
                  <v-card-title>
                    <span class="headline">Order Document</span>
                  </v-card-title>
                  <v-form ref="formOrder" @submit.prevent>
                    <v-list>
                      <v-list-item>
                        <v-list-item-content>
                          <v-autocomplete
                            v-model="modelOrder.user_id"
                            :items="users"
                            item-text="name"
                            item-value="id"
                            label="Select User"
                            hide-no-data
                            :loading="loading"
                            @focus="selectUser"
                            menu-props="closeOnClick=true, closeOnContentClick,offsetY"
                            :disabled="disableOrder"
                            clearable
                            filled
                          ></v-autocomplete>
                        </v-list-item-content>
                      </v-list-item>
                      <v-list-item>
                        <v-list-item-content>
                          <v-menu
                            ref="menuSend"
                            v-model="menuSend"
                            :return-value.sync="modelOrder.send_date"
                            :close-on-content-click="false"
                            transition="scale-transition"
                            offset-y
                            min-width="290px"
                          >
                            <template v-slot:activator="{ on, attrs }">
                              <v-text-field
                                v-model="modelOrder.send_date"
                                label="Borrowed Date"
                                prepend-icon="mdi-calendar-month"
                                readonly
                                v-bind="attrs"
                                v-on="on"
                                :disabled="disableOrder"
                              ></v-text-field>
                            </template>
                            <v-date-picker
                              v-model="modelOrder.send_date"
                              :min="today"
                              no-title
                              scrollable
                            >
                              <v-spacer></v-spacer>
                              <v-btn
                                text
                                color="primary"
                                @click="menuSend = false"
                                >Cancel</v-btn
                              >
                              <v-btn
                                text
                                color="primary"
                                @click="
                                  $refs.menuSend.save(modelOrder.send_date)
                                "
                                >Save</v-btn
                              >
                            </v-date-picker>
                          </v-menu>
                        </v-list-item-content>
                      </v-list-item>
                      <v-list-item>
                        <v-list-item-content>
                          <v-menu
                            ref="menuReturn"
                            v-model="menuReturn"
                            :return-value.sync="modelOrder.return_date"
                            transition="scale-transition"
                            :close-on-content-click="false"
                            offset-y
                            min-width="290px"
                          >
                            <template v-slot:activator="{ on, attrs }">
                              <v-text-field
                                v-model="modelOrder.return_date"
                                label="Return Date"
                                prepend-icon="mdi-calendar-month"
                                readonly
                                v-bind="attrs"
                                v-on="on"
                                :disabled="!disableOrder"
                              ></v-text-field>
                            </template>
                            <v-date-picker
                              v-model="modelOrder.return_date"
                              :min="today"
                              no-title
                              scrollable
                            >
                              <v-spacer></v-spacer>
                              <v-btn
                                text
                                color="primary"
                                @click="menuReturn = false"
                                >Cancel</v-btn
                              >
                              <v-btn
                                text
                                color="primary"
                                @click="
                                  $refs.menuReturn.save(modelOrder.return_date)
                                "
                                >Save</v-btn
                              >
                            </v-date-picker>
                          </v-menu>
                        </v-list-item-content>
                      </v-list-item>
                    </v-list>
                  </v-form>
                  <v-divider></v-divider>
                  <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn
                      :loading="loading"
                      class="white--text"
                      color="primary"
                      @click="saveOrder(item)"
                      depressed
                      >{{ item.is_available == 1 ? 'Order' : 'Return' }}</v-btn
                    >
                  </v-card-actions>
                </v-card>
              </v-col>
              <v-spacer></v-spacer>
              <v-col cols="3">
                <v-card outlined>
                  <v-card-title>
                    <span class="headline">Order History</span>
                  </v-card-title>
                  <v-card-text
                    v-if="item.orders.length == 0"
                    class="align-center justify-center text-center"
                  >
                    <v-btn @click="getOrder(item)" color="primary">
                      <v-icon left>mdi-plus</v-icon>Load History
                    </v-btn>
                  </v-card-text>

                  <v-virtual-scroll
                    v-else
                    :items="item.orders"
                    :item-height="90"
                    height="300"
                  >
                    <template v-slot="{ item }">
                      <v-list-item three-line>
                        <v-list-item-content>
                          <v-list-item-title>
                            <v-icon left>mdi-plus</v-icon>
                            {{ item.user.name }}
                          </v-list-item-title>
                          <v-list-item-subtitle>
                            <v-icon left>mdi-plus</v-icon>
                            {{ item.send_date }}
                          </v-list-item-subtitle>
                          <v-list-item-subtitle>
                            <v-icon left>mdi-plus</v-icon>
                            {{
                              item.return_date == null
                                ? 'Not Returned'
                                : item.return_date
                            }}
                          </v-list-item-subtitle>
                        </v-list-item-content>
                      </v-list-item>
                      <v-divider></v-divider>
                    </template>
                  </v-virtual-scroll>
                </v-card>
              </v-col>
            </v-row>
          </td>
        </template>

        <template v-slot:item.is_available="{ item }">
          <v-icon :color="item.is_available == 1 ? 'green' : 'red '">{{
            getIcon(item.is_available)
          }}</v-icon>
        </template>

        <template v-slot:item.actions="{ item }">
          <v-icon small @click="editItem(item)">mdi-pencil</v-icon>
          <v-icon small @click="deleteItem(item)">mdi-delete</v-icon>
        </template>
      </v-data-table>
    </v-card>
  </v-row>
</template>

<script>
import { mapState, mapGetters } from 'vuex'

export default {
  layout: 'dashboard',

  /*   activated() {
    // Call get again if last get more than 30 sec ago
    if (this.$getState.timestamp <= Date.now() - 30000) {
      this.$get()
    }
  }, */

  mounted() {
    this.getmanual()
  },

  data: () => ({
    headers: [
      { text: 'A/C', value: 'aircraft.type', align: 'center' },
      { text: 'Document Type', value: 'type' },
      { text: 'Subject', value: 'subject' },
      { text: 'Index', value: 'index', align: 'center', sortable: false },
      { text: 'Index Date', value: 'index_date' },
      {
        text: 'Availability',
        value: 'is_available',
        align: 'center',
        sortable: false,
      },
      { text: 'Borrowed Date', value: 'last_order.send_date' },
      { text: 'Actions', value: 'actions', align: 'center', sortable: false },
    ],

    search: null,

    //aircraft document data
    docType: ['WDM', 'IPC', 'CMM', 'AMM', 'SRM', 'ITEM'],
    modelAircraftDocs: {},
    // aircraftDocs: [],
    editAircraftDoc: false,
    formAircraftDoc: '',

    //aircraft data
    modelAircraft: {},
    formAircraft: '',
    editAircraft: false,
    newAircraft: false,

    //user data
    //modelUsers: [],

    //order data
    formOrder: '',
    modelOrder: {
      user_id: '',
      send_date: '',
      return_date: '',
    },
    today: new Date().toISOString().substr(0, 10),
    showOrder: false,
    menuSend: false,
    menuReturn: false,
    disableOrder: true,

    //edit data
    editedIndex: -1,
    defaultItem: {},
    expanded: [],
    dialog: false,
    timeout: '',
  }),
  watch: {
    dialog(val) {
      val || this.close()
      val == true ? this.selectAircraft() : (this.modelAircraft = {})
    },
  },
  computed: {
    ...mapState({
      loading: (state) => state.manuals.loading,
      aircraftDocs: (state) => state.manuals.items,
      users: (state) => state.users.items,
      aircrafts: (state) => state.aircrafts.items,
      //aircraftDocsCount: (state) => state.manuals.total,
    }),
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

    formTitle() {
      return this.editedIndex === -1 ? 'New Item' : 'Edit Item'
    },

    loading() {
      return this.$store.state.manuals.loading
    },
  },
  methods: {
    selectAircraft() {
      Array.isArray(this.modelAircraft)
        ? this.modelAircraft
        : this.getAircraft()
    },
    selectUser() {
      this.users.length != 0 ? this.users : this.getUser()
    },
    getIcon(is_available) {
      if (is_available == 1) return ' mdi-check-circle'
      else return 'mdi-close-circle'
    },

    close() {
      this.dialog = false
      this.newAircraft = false
      this.$nextTick(() => {
        this.modelAircraftDocs = Object.assign({}, this.defaultItem)
        this.editedIndex = -1

        this.expanded = []
        this.modelOrder = {}
        this.modelAircraft = {}
      })
    },

    save() {
      if (this.editedIndex > -1) {
        this.put()
      } else {
        this.post()
      }
      this.close()
      this.getmanual()
    },
    saveOrder(item) {
      if (item.is_available == 1) {
        this.postOrder(item)
      } else {
        this.putOrder(item)
      }
    },
    lastOrder(item) {
      if (item.is_available == 0 && item.last_order != null) {
        this.modelOrder = item.last_order
        this.disableOrder = true
      } else {
        this.modelOrder.manual_id = item.id
        this.disableOrder = false
      }
      this.modelAircraft = item.aircraft
    },

    expand(item) {
      const index = this.expanded.indexOf(item)
      if (this.expanded.length != 0 || index == 0) {
        this.expanded.pop()
        this.selectUser()
        this.modelOrder = {}
        this.modelAircraft = {}
        if (index == -1) {
          this.expanded.push(item)
          this.lastOrder(item)
        }
      } else {
        this.expanded.push(item)
        this.expanded.push(item)
        this.lastOrder(item)
        this.selectUser()
      }
      /*
      index = -1 expanded = 0 > push
      index = -1 expanded = 1 > pop
      index = 0 expanded = 1 > pop

      index -1 || index 0 && expanded 0 push
      index -1 || index 0 && expanded 1 pop
      } */
    },

    editItem(item) {
      this.editedIndex = this.aircraftDocs.indexOf(item)
      this.modelAircraftDocs = Object.assign({}, item)
      this.dialog = true
    },

    deleteItem(item) {
      const index = this.aircraftDocs.indexOf(item)
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
    getmanual() {
      this.$store.dispatch('fetch', 'manuals')
    },
    getAircraft() {
      this.$store.dispatch('fetch', 'aircrafts')
    },
    getUser() {
      this.$store.dispatch('fetch', 'users')
    },
    async getOrder(item) {
      const index = this.aircraftDocs.indexOf(item)
      let res = await this.$store.dispatch('show', 'manuals/' + item.id)
      if (res.orders.length > 0) {
        //this.aircraftDocs[index].orders = res.orders
        this.$store.commit('manuals/update', {
          id: index,
          key: 'orders',
          data: res.orders,
        })
      } else {
        this.$notifier.showMessage({
          content: 'Order History Not Found',
          color: 'error',
        })
      }
    },

    postOrder(item) {
      const index = this.aircraftDocs.indexOf(item)
      let res = this.$store.dispatch('post', {
        url: '/orders/',
        form: this.modelOrder,
      })
      this.disableOrder = true
    },
    putOrder(item) {
      const index = this.aircraftDocs.indexOf(item)
      let res = this.$store.dispatch('put', {
        url: '/orders/',
        form: this.modelOrder,
      })
      this.disableOrder = false
    },
    post() {
      let res = this.$store
        .dispatch('post', {
          url: '/manuals',
          form: this.modelAircraftDocs,
        })
        .then((data) => {
          this.modelAircraftDocs = ''
        })
    },
    put() {
      let res = this.$store.dispatch('put', {
        url: '/manuals/',
        form: this.modelAircraftDoc,
      })
    },
    putAircraft() {
      let res = this.$store
        .dispatch('put', {
          url: '/aircrafts/',
          form: this.modelAircraft,
        })
        .then((res) => {
          this.editAircraft = false
        })
    },
    delete(item) {
      let res = this.$store.dispatch('delete', {
        url: '/manuals/',
        form: item,
      })
    },
  },
}
</script>

<style lang="css">
.v-data-table tbody tr.v-data-table__expanded__content {
  box-shadow: none;
}
</style>
