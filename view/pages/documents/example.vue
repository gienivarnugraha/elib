<template>
  <v-row align="center" justify="center" fluid>
    <v-card v-if="show" class="mx-auto my-6" style="width: 90%">
      <v-toolbar>
        <v-btn icon @click="back">
          <v-icon>mdi-arrow-left</v-icon>
        </v-btn>

        <v-toolbar-title>My Recipes</v-toolbar-title>
        <!--         <v-progress-linear
          :value="Math.floor((page / numPages) * 100)"
          absolute
          bottom
        ></v-progress-linear> -->
        <v-spacer></v-spacer>
        <v-text-field
          outlined
          label="Page"
          v-model.number="page"
          min="1"
          :max="numPages"
          type="number"
          hide-details
          dense
          style="max-width: 75px"
        ></v-text-field>
        {{ ' / ' + numPages }}

        <v-spacer></v-spacer>
        <v-btn-toggle>
          <v-btn icon @click="rotate -= 90"
            ><v-icon>mdi-rotate-left</v-icon></v-btn
          >
          <v-btn icon @click="rotate += 90"
            ><v-icon>mdi-rotate-right</v-icon></v-btn
          >
          <v-btn icon @click="$refs.pdf.print()"
            ><v-icon>mdi-printer</v-icon></v-btn
          >
        </v-btn-toggle>
      </v-toolbar>

      <v-divider class="ma-4"></v-divider>
      <pdf
        ref="pdf"
        :src="src"
        :page="page"
        :rotate="rotate"
        @password="password"
        @error="error"
        @num-pages="numPages = $event"
        @link-clicked="page = $event"
      ></pdf>
    </v-card>
  </v-row>
</template>

<script>
export default {
  layout: 'dashboard',

  data: () => ({
    id: '',
    show: false,
    src: '',
    loadedRatio: 0,
    page: 1,
    numPages: 0,
    rotate: 0,
  }),
  mounted() {
    this.get()
  },
  key(route) {
    return route.fullPath
  },

  methods: {
    get() {
      this.id = this.$route.params.id
      return this.$axios
        .get('/documents/file/' + this.id, {
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
          responseType: 'blob',
        })
        .then((res) => {
          this.show = true
          const blob = new Blob([res.data], { type: 'application/pdf' })
          const objectUrl = URL.createObjectURL(blob)
          this.src = objectUrl
          console.log(blob.stream())
        })
        .catch((err) => {
          this.show = false
          console.log(err)
        })
    },
    password(updatePassword, reason) {
      updatePassword(prompt('password is "test"'))
    },
    error(err) {
      console.log(err)
      this.show = false
    },
    back() {
      return this.$router.push('/documents')
    },
  },
}
</script>
