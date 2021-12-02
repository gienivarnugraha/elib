<template>
  <v-row class="px-4">
    <v-row
      class="mt-4"
      align="stretch"
      justify="space-around"
      style="width: auto"
    >
      <v-col md="4" xs="9">
        <v-card
          v-if="charts.kpi.labels.length > 0"
          :loading="!charts.kpi.labels.length > 0"
          class="mx-auto"
        >
          <client-only>
            <doughnut-chart
              max-width="calc(100% - 48px)"
              class="pa-6"
              :data="kpiChart"
              :options="charts.kpi.options"
            ></doughnut-chart>
          </client-only>

          <v-card-text class="pt-0">
            <div class="text-h6 font-weight-light mb-2">
              Key Performance Index, Total {{ documentsTotal }}
            </div>
            <v-divider class="my-2"></v-divider>
            <v-icon class="mr-2" small> mdi-clock </v-icon>
            <span class="text-caption grey--text font-weight-light"
              >last document submitted:
              {{ this.documents[0].updated_at || '' }}
            </span>
          </v-card-text>
        </v-card>
      </v-col>
      <v-col md="4" xs="9">
        <v-card
          v-if="charts.perMonth.labels.length > 0"
          :loading="!charts.perMonth.labels.length > 0"
          class="mx-auto"
        >
          <client-only>
            <line-chart
              max-width="calc(100% - 48px)"
              class="pa-6"
              :data="chartPerMonth"
              :options="charts.perMonth.options"
            ></line-chart>
          </client-only>

          <v-card-text class="pt-0">
            <div class="text-h6 font-weight-light mb-2">
              Key Performance Index, Total {{ documentsTotal }}
            </div>
            <v-divider class="my-2"></v-divider>
            <v-icon class="mr-2" small> mdi-clock </v-icon>
            <span class="text-caption grey--text font-weight-light"
              >last document submitted:
              {{ this.documents[0].updated_at || '' }}
            </span>
          </v-card-text>
        </v-card>
      </v-col>
      <v-col md="4" xs="9">
        <v-card>
          <v-card-title class="secondary white--text">
            KPI Status
          </v-card-title>
          <v-card-text>
            <client-only>
              <doughnut-chart :data="charts.kpiChart"></doughnut-chart>
            </client-only>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
    <v-row align="stretch" justify="space-between">
      <v-col md="6" xs="9">
        <v-card class="mx-auto my-6">
          <v-card-title class="secondary white--text">
            Latest Notification
            <v-spacer></v-spacer>
            <v-avatar color="accent" size="40">
              <v-icon dark>{{ notificationsTotal }}</v-icon>
            </v-avatar>
          </v-card-title>

          <v-card-text class="mt-4">
            <v-data-table
              :headers="headers.notification"
              :items="notifications"
              :loading="loading"
              loading-text="Loading... Please wait"
              hide-default-footer
            >
            </v-data-table>
          </v-card-text>
        </v-card>
      </v-col>
      <v-col md="6" xs="9">
        <v-card class="mx-auto my-6">
          <v-card-title class="secondary white--text">
            Latest Orders
            <v-spacer></v-spacer>
            <v-avatar color="accent" size="40">
              <v-icon dark></v-icon>
            </v-avatar>
          </v-card-title>
          <v-card-text class="mt-4">
            <v-data-table
              :headers="headers.order"
              :items="orders"
              :loading="loading"
              loading-text="Loading... Please wait"
              hide-default-footer
            >
            </v-data-table>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-row>
</template>


<script>
import { mapState, mapGetters } from 'vuex'

export default {
  layout: 'dashboard',

  data: () => ({
    headers: {
      notification: [
        { text: 'Model', value: 'data.model', align: 'center' },
        { text: 'Type', value: 'data.event', align: 'center' },
        { text: 'Changes By', value: 'data.user.name', align: 'center' },
        { text: 'Time', value: 'created_at', align: 'center' },
      ],
      order: [
        { text: 'Type', value: 'manual.type', align: 'center' },
        { text: 'User', value: 'user.name', align: 'center' },
        { text: 'Time', value: 'created_at', align: 'center' },
        { text: 'Closed', value: 'is_closed', align: 'center' },
      ],
    },
    dialog: false,
    expanded: [],
    charts: {
      kpi: {
        labels: [],
        datasets: [
          {
            //label: 'Data One',
            backgroundColor: [
              '#F50057', // pink
              '#00B0FF', // blue
            ],
            data: [],
          },
        ],
        total: 0,
        options: {
          legend: {
            display: true,
            position: 'bottom',
          },
        },
        loading: false,
      },
      perMonth: {
        labels: [],
        datasets: [
          {
            label: 'KPI Index',
            backgroundColor: [
              '#F50057', // pink
              '#00B0FF', // blue
            ],
            data: [],
            fill: false,
          },
        ],
        /*         options: {
          legend: {
            display: true,
            position: 'bottom',
          },
        }, */
        loading: false,
      },
    },
  }),
  async mounted() {
    this.charts.kpi.loading = true

    this.documents.length != 0 ||
      (await this.$store.dispatch('fetch', 'documents'))
    this.orders.length != 0 || (await this.$store.dispatch('fetch', 'orders'))

    await this.$store.dispatch('documents/getKpi')

    this.kpiChart = await this.kpiTotal
    this.chartPerMonth = await this.kpiPerMonth

    this.charts.kpi.loading = false
  },
  computed: {
    ...mapState({
      user: (state) => state.auth.user,
      isAdmin: (state) => state.auth.user.is_admin,
      documents: (state) => state.documents.items,
      documentsTotal: (state) => state.documents.total,
      kpiTotal: (state) => state.documents.kpi.total,
      kpiPerMonth: (state) => state.documents.kpi.perMonth,
      orders: (state) => state.orders.items,
      notifications: (state) => state.notifications.items,
      notificationsTotal: (state) => state.notifications.total,
      loading: (state) => state.loading,
      errors: (state) => state.errors,
    }),
    kpiChart: {
      get() {
        return this.charts.kpi
      },
      set(val) {
        this.charts.kpi.labels = Object.keys(val)
        this.charts.kpi.datasets[0].data = Object.values(val)
        this.charts.kpi.total = this.documentsTotal
      },
    },
    chartPerMonth: {
      get() {
        return this.charts.perMonth
      },
      set(val) {
        this.charts.perMonth.labels = Object.keys(val)
        this.charts.perMonth.datasets[0].data = Object.values(val)
      },
    },
  },
  methods: {
    debounced: _.debounce(function (val) {
      this.$store.commit('notifications/loading', false)
      this.search = val
    }, 1000),
  },
}
</script>
