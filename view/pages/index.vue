<template>
  <v-row justify="center">
    <v-col cols="10" px-16>
      <div class="layout column align-center py-10">
        <img
          src="~/assets/images/logo.png"
          alt="Electronic Library"
          width="200"
          height="200"
        />
        <p class="mt-4 mb-0 white--text text-h5">ELECTRONIC LIBRARY</p>
        <p class="white--text mb-0 overline">SUB DIT AIRCRAFT SERVICES</p>
      </div>
      <v-tabs class="elevation-3 rounded-t" v-model="tab">
        <v-tab class="">Document</v-tab>
        <v-tab mx-6 class="">Submit</v-tab>
        <v-tabs-slider color="primary"></v-tabs-slider>
      </v-tabs>
      <v-tabs-items class="elevation-3 rounded-b" v-model="tab">
        <!-- AUTOCOMPLETE TAB -->
        <v-tab-item>
          <v-autocomplete
            style="padding: 24px 24px 0"
            v-model="model"
            :items="items"
            :loading="loading"
            :search-input.sync="search"
            item-text="title"
            item-value="searchable"
            label="Search Document"
            hint="Start typing and release to serach"
            placeholder="ex: Weight and Balance"
            header="Available Document"
            divider
            no-data-text="Nothing found"
            :hide-no-data="nodata"
            clearable
            outlined
            @click:clear="model = null"
            prepend-inner-icon="mdi-magnify"
          >
            <template v-slot:item="props">
              <v-list-item-content>
                <v-list-item-title v-text="props.item.title">
                </v-list-item-title>
                <v-list-item-subtitle v-html="props.item.searchable.no">
                </v-list-item-subtitle>
              </v-list-item-content>
            </template>
            <template v-slot:append>
              <v-fade-transition leave-absolute>
                <v-progress-circular
                  v-if="loading"
                  size="24"
                  color="info"
                  indeterminate
                ></v-progress-circular>
              </v-fade-transition>
            </template>
          </v-autocomplete>

          <v-expand-transition v-if="model">
            <v-row class="mb-4 px-6">
              <v-col md="4" xs="8">
                <v-card outlined class="px-2">
                  <v-card-title class="headline"> Aircraft Data</v-card-title>
                  <v-card-subtitle class="caption">
                    Aircraft data belongs to this document</v-card-subtitle
                  >
                  <v-list>
                    <v-list-item v-for="(item, i) in aircraftDetails" :key="i">
                      <v-text-field
                        :value="item"
                        :label="i.toUpperCase()"
                        readonly
                        outlined
                        dense
                      ></v-text-field>
                    </v-list-item>
                  </v-list>
                </v-card>
              </v-col>
              <v-col md="4" xs="8">
                <v-card outlined class="px-2">
                  <v-card-title>
                    <span class="headline"> Document Data </span>
                    <v-spacer></v-spacer>
                    <v-tooltip top>
                      <template v-slot:activator="{ on, attrs }">
                        <v-icon
                          right
                          v-bind="attrs"
                          v-on="on"
                          :color="model.is_closed == 1 ? 'green' : 'red'"
                          >{{
                            model.is_closed == 1
                              ? 'mdi-check-circle'
                              : 'mdi-close-circle'
                          }}</v-icon
                        >
                      </template>
                      Document is
                      {{ model.is_closed == 1 ? 'Closed' : 'Open' }}
                    </v-tooltip>
                  </v-card-title>
                  <v-card-subtitle class="caption">
                    Details of this document
                  </v-card-subtitle>
                  <v-list>
                    <!-- -->
                    <v-list-item v-for="(item, i) in docFields" :key="i">
                      <v-text-field
                        :value="i == 'assignee' ? item.name : item"
                        :label="i.toUpperCase()"
                        readonly
                        outlined
                        dense
                      ></v-text-field>
                    </v-list-item>
                  </v-list>
                </v-card>
              </v-col>
              <v-col md="4" xs="8">
                <v-card outlined class="px-2">
                  <v-card-title class="headline">File</v-card-title>
                  <v-card-subtitle class="caption"
                    >File attached to this document</v-card-subtitle
                  >
                  <v-virtual-scroll
                    :items="model.files"
                    :item-height="75"
                    height="250"
                  >
                    <template v-slot="{ item }">
                      <v-list-item>
                        <!-- @click="readFile(item.id)" -->
                        <v-list-item-avatar class="white--text" color="primary">
                          <v-icon>{{ item.index }}</v-icon>
                        </v-list-item-avatar>

                        <v-list-item-content>
                          <v-list-item-title
                            v-text="item.created_at"
                          ></v-list-item-title>

                          <v-list-item-subtitle
                            v-text="item.changes"
                          ></v-list-item-subtitle>
                        </v-list-item-content>
                        <v-list-item-icon>
                          <v-icon>mdi-information </v-icon>
                        </v-list-item-icon>
                      </v-list-item>
                      <v-divider></v-divider>
                    </template>
                  </v-virtual-scroll>
                </v-card>
              </v-col>
            </v-row>
          </v-expand-transition>
        </v-tab-item>

        <!-- AUTOCOMPLETE TAB -->
        <v-tab-item> </v-tab-item>
      </v-tabs-items>
    </v-col>
  </v-row>
</template>

<script>
import { mapState, mapGetters } from 'vuex'

export default {
  auth: 'guest', //make it accessible without login
  layout: 'default',

  data: () => ({
    /* Autocomplete data */
    search: null, // Search document autocomplete

    expanded: [],
    items: [], //autocomplete items
    model: null, //autocomplete model binding
    nodata: true, //a ut

    /* MISCELLANOUS */
    tab: null,
  }),
  watch: {
    search(val) {
      if (this.model != null) return // Items have already been loaded

      if (val !== null) {
        // Clear autocomplete items on clear or re input
        this.items = [] // Clear Autocomplete items
        this.debounced(val) // Search debounce initiated
      } else {
        this.model = null
        this.items = [] //clear autocomplete item
        this.nodata = true
      }
    },
    tab(val) {
      val || (this.model = null)
    },
  },

  computed: {
    ...mapState({
      loading: (state) => state.loading,
    }),
    docFields() {
      if (!this.model) return []
      return _.pick(this.model, [
        'no',
        'office',
        'type',
        'subject',
        'assignee',
        'created_at',
        'updated_at',
      ])
    },

    aircraftDetails() {
      return _.pick(this.model.aircraft, [
        'type',
        'reg_code',
        'effectivity',
        'serial_num',
        'owner',
        'manuf_date',
      ])
    },
  },
  methods: {
    debounced: _.debounce(async function (val) {
      let resp = await this.$store.dispatch('search', {
        url: '/documents/search/',
        query: val,
      })
      if (resp == undefined) {
        this.$notifier.showMessage({
          content: 'Network Error: Please contact administrator',
          color: 'error',
        })
      } else {
        if (resp.length > 0) {
          this.items = resp
          this.count = resp.length
        } else {
          this.nodata = false
        }
      }
    }, 1000),
  },
}
</script>

