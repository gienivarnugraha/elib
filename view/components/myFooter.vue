<template>
  <v-footer padless class="custom-footer">
    <v-col class="text-center" cols="12">
      <span class="overline">
        <v-icon small>mdi-copyright</v-icon>
        {{ new Date().getFullYear() }} - Made with
        <v-icon small color="red">mdi-heart</v-icon>
        by <a href="mailto:mreisyan@indonesian-aerospace.com"> GNVRNGRH</a>
      </span>
      <!--         <div ma-n3>
          <v-btn
            v-for="(item, i) in icons"
            :key="i"
            class="ma-2"
            text
            small
            color="primary"
            :href="item.href"
          >
            <v-icon small>{{ item.icon }}</v-icon>
          </v-btn>
        </div> -->
    </v-col>
  </v-footer>
</template>

<script>
export default {
  name: 'Footer',
  data: () => ({
    icons: [
      { icon: 'mdi-email', href: 'mailto:mreisyan@indonesian-aerospace.com' },
      { icon: 'mdi-facebook', href: 'http://facebook.com/' },
      { icon: 'mdi-twitter', href: 'http://twitter.com/gnvrngrh' },
      { icon: 'mdi-linkedin', href: 'http://linked.in/gienivarnugraha' },
      { icon: 'mdi-instagram', href: 'http://instagram.com/gnvrngrh' },
    ],
  }),
}
</script>
