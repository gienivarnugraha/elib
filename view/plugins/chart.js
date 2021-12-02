import Vue from 'vue'
import { Doughnut, Line } from 'vue-chartjs'

Vue.component('doughnut-chart', {
  extends: Doughnut,
  props: {
    data: {
      type: Object,
      default: null,
    },
    options: {
      type: Object,
      default: null,
    },
  },
  mounted() {
    this.renderChart(this.data, this.options)
  },
})

Vue.component('line-chart', {
  extends: Line,
  props: {
    data: {
      type: Object,
      default: null,
    },
    options: {
      type: Object,
      default: null,
    },
  },
  mounted() {
    this.renderChart(this.data, this.options)
  },
})
