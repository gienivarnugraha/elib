<template>
  <v-row align="center" justify="center">
    <v-card class="mx-auto my-6" min-width="70%">
      <v-card-title class="secondary white--text">
        <v-col md="4" sm="4" xs="4"> All Notification </v-col>
        <!--  SEARCH COLUMN -->
        <v-col>
          <v-text-field
            v-model.lazy="searchTimeOut"
            append-icon="mdi-magnify"
            label="Search Notification"
            clearable
            dense
            solo
            flat
            hide-details
            placeholder="Start typing to search.."
          >
          </v-text-field>
        </v-col>
      </v-card-title>

      <v-card-text class="mt-4">
        <v-data-table
          :headers="headers"
          :items="notifications"
          :loading="loading"
          loading-text="Loading... Please wait"
          multi-sort
          :search="search"
        >
          <template v-slot:item.data.changes="{ item }">
            <v-list
              v-if="item.data.event == 'updated'"
              color="grey lighten-4"
              max-width="25rem"
              class="pa-0"
            >
              <v-list-item v-for="(data, i) in item.data.changes" :key="i">
                <v-list-item-content class="py-0 px-2">
                  <p class="mb-0 text-left caption">From :</p>
                  <p class="mb-0 text-left caption">{{ i.toUpperCase() }} :</p>
                  <p class="mb-0 text-center overline">
                    {{ item.data.changes[i] }}
                  </p>
                </v-list-item-content>
                <v-divider vertical></v-divider>
                <v-list-item-content class="py-0 px-2">
                  <p class="mb-0 text-left caption">To :</p>
                  <p class="mb-0 text-left caption">{{ i.toUpperCase() }} :</p>
                  <p class="mb-0 text-center overline">
                    {{ item.data.data[i] }}
                  </p>
                </v-list-item-content>
              </v-list-item>
            </v-list>
          </template>
        </v-data-table></v-card-text
      ></v-card
    ></v-row
  >
</template>


<script>
import { mapState, mapGetters } from 'vuex'

export default {
  layout: 'dashboard',

  data: () => ({
    headers: [
      { text: 'Model', value: 'data.model', align: 'center' },
      { text: 'Type', value: 'data.event', align: 'center' },
      { text: 'Changes By', value: 'data.user.name', align: 'center' },
      { text: 'Changes', value: 'data.changes' },
      { text: 'Date', value: 'created_at', align: 'center', width: '15%' },
    ],
    search: null,
    dialog: false,
    /* expanded: [], */
  }),
  mounted() {},
  watch: {
    dialog(val) {
      //val || this.clear()
    },
  },
  computed: {
    ...mapState({
      user: (state) => state.auth.user,
      isAdmin: (state) => state.auth.user.is_admin,
      notifications: (state) => state.notifications.items,
      loading: (state) => state.notifications.loading,
      errors: (state) => state.errors,
    }),
    searchTimeOut: {
      get() {
        return this.search
      },
      set(val) {
        this.$store.commit('notifications/loading', true)
        this.debounced(val)
      },
    },
  },
  methods: {
    debounced: _.debounce(function (val) {
      this.$store.commit('notifications/loading', false)
      this.search = val
    }, 1000),
    /*     expand(item) {
      const index = this.expanded.indexOf(item)
      if (index == 0) {
        this.expanded.pop()
      } else {
        this.expanded = [item]
      }
    }, */
  },
}
</script>
