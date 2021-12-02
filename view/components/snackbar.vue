
<template>
  <v-snackbar v-model="show" :color="color">
    {{ message }}
    <template v-slot:action="{ attrs }">
      <v-btn text icon v-bind="attrs" @click="show = false">
        <v-icon>mdi-close-thick</v-icon>
      </v-btn>
    </template>
  </v-snackbar>
</template>

<script>
export default {
  data() {
    return {
      show: false,
      message: '',
      /*
      success : green
      error : red
      warning : amber
      */
      color: '',
    }
  },

  created() {
    this.$store.subscribe((mutation, state) => {
      if (mutation.type === 'snack') {
        this.message = state.snack.content
        this.color = state.snack.color
        this.show = true
      }
    })
  },
}
</script>
