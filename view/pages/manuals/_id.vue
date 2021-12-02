<template>
  <v-row align="center" justify="center" fluid>
    <v-card class="mx-auto my-6" style="width: 90%; height: 90vh">
      <v-toolbar>
        <v-btn icon @click="back">
          <v-icon>mdi-arrow-left</v-icon>
        </v-btn>
        <v-toolbar-title>My Recipes</v-toolbar-title>
        <v-spacer></v-spacer>
      </v-toolbar>
      <client-only>
        <iframe
          src=""
          type="application/pdf"
          width="100%"
          height="100%"
          style="overflow: auto"
        >
        </iframe>
      </client-only>
    </v-card>
  </v-row>
</template>

<script>
export default {
  layout: 'dashboard',

  data: () => ({
    id: '',
    show: false,
    url: '',
  }),
  mounted() {
    this.get()
  },
  key(route) {
    return route.fullPath
  },
  methods: {
    async get() {
      this.id = this.$route.params.id
      let pdf = await this.$store.dispatch('viewFile', {
        id: this.id,
        type: 'manuals',
      })
      const url = window.URL.createObjectURL(pdf)
      document.querySelector('iframe').src = url
      setTimeout(function () {
        // For Firefox it is necessary to delay revoking the ObjectURL
        window.URL.revokeObjectURL(pdf)
      }, 100)
    },
    back() {
      return this.$router.push('/manuals')
    },
  },
}
</script>
