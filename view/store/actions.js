import { degrees, PDFDocument, rgb, StandardFonts } from 'pdf-lib'

const actions = {
  fetch({ commit }, module) {
    commit(module + '/loading', true)
    return new Promise((resolve, reject) => {
      this.$axios
        .$get('/' + module)
        .then((res) => {
          commit(module + '/total', res.length)
          commit(module + '/set', res)
          resolve(res)
        })
        .catch((err) => {
          reject(err.response)
          commit('error', err.response.data.errors)
          this.$notifier.showMessage({
            content: err.response.data.message,
            color: 'error',
          })
          commit(module[0] + '/loading', false)
        })
    })
  },
  show({ commit }, payload) {
    let module = payload.split('/')
    commit(module[0] + '/loading', true)
    return new Promise((resolve, reject) => {
      this.$axios
        .$get('/' + payload)
        .then((resp) => {
          commit(module[0] + '/show', resp)
          resolve(resp)
        })
        .catch((err) => {
          commit(module[0] + '/loading', false)
          reject(err.response)
          dispatch('error', err.response.data)
        })
        .finally(() => commit(module[0] + '/loading', false))
    })
  },
  post({ commit, dispatch }, payload) {
    let module = payload.url.split('/')
    commit(module[0] + '/loading', true)
    return new Promise((resolve, reject) => {
      this.$axios
        .$post(payload.url, payload.form)
        .then((res) => {
          resolve(res)
        })
        .catch((err) => {
          reject(err.response)
          commit(module[0] + '/loading', false)
          dispatch('error', err.response.data)
        })
    })
  },
  put({ commit, dispatch }, payload) {
    let module = payload.url.split('/')
    commit(module[0] + '/loading', true)
    return new Promise((resolve, reject) => {
      this.$axios
        .$put(payload.url + payload.form.id, payload.form)
        .then((res) => {
          resolve(res)
        })
        .catch((err) => {
          reject(err.response)
          commit(module[0] + '/loading', false)
          dispatch('error', err.response.data)
        })
    })
  },
  delete({ commit, dispatch }, payload) {
    let module = payload.url.split('/')
    commit(module[0] + '/loading', true)
    return new Promise((resolve, reject) => {
      this.$axios
        .$delete(payload.url + payload.form.id)
        .then((res) => {
          resolve(res)
        })
        .catch((err) => {
          reject(err.response)
          commit(module[0] + '/loading', false)
          dispatch('error', err.response.data)
        })
    })
  },
  async logout({ commit }) {
    await commit('loading', true)
    await this.$router.push('/login')
    await this.$auth.logout()
    await this.$auth.$storage.removeUniversal('loggedIn')
    await this.$auth.$storage.removeUniversal('user')
    await commit('loading', false)
  },
  error({ commit }, data) {
    commit('error', data.errors ? data.errors : {})
    setTimeout(() => {
      commit('clearError')
    }, 5000)
    this.$notifier.showMessage({
      content: data.message ? data.message : data.error,
      color: 'error',
    })
  },
  file({ commit }, payload) {
    let module = payload.url.split('/')
    //commit(module[0] + '/uploading', true)
    commit(module[0] + '/loading', true)

    let formData = new FormData()
    let keys = Object.keys(payload.form)
    keys.forEach((key) => formData.append(key, payload.form[key]))

    return new Promise((resolve, reject) => {
      this.$axios
        .post(module[0] + '/' + module[1], formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
          },
          /*         onUploadProgress: function (progressEvent) {
          commit(
            module[0] + '/uploading',
            parseInt(
              Math.round((progressEvent.loaded / progressEvent.total) * 100)
            )
          )
        }.bind(this), */
        })
        .then((res) => {
          resolve(res)
        })
        .catch((err) => {
          commit(module[0] + '/loading', false)
          reject(err)
          dispatch('error', err.response.data)
        })
    })
  },
  viewFile({ commit, dispatch }, payload) {
    console.log(payload)
    return new Promise((resolve, reject) => {
      this.$axios
        .get(`/${payload.type}/file/` + payload.id, {
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
          responseType: 'arraybuffer',
        })
        .then(async (res) => {
          const pdfDoc = await PDFDocument.load(res.data)
          const helveticaFont = await pdfDoc.embedFont(StandardFonts.Helvetica)

          const pages = pdfDoc.getPages()
          for (let page = 0; page < pages.length; page++) {
            const drawPage = pages[page]
            const { width, height } = drawPage.getSize()
            drawPage.drawText('PROPERTY OF AIRCRAFT SERVICES', {
              x: 5,
              y: height / 2 + 300,
              size: 45,
              font: helveticaFont,
              color: rgb(0.95, 0.1, 0.1),
              rotate: degrees(-45),
              opacity: 0.5,
            })
          }

          const pdfBytes = await pdfDoc.save()
          resolve(
            new Blob([pdfBytes], {
              type: 'application/pdf',
            })
          )

          /*           return new Blob([pdfBytes], {
            type: 'application/pdf',
          }) */
        })
        .catch((err) => {
          reject(err)
          console.log(err)
          dispatch('error', err.response.data)
        })
    })
  },
  search({ commit }, payload) {
    commit('loading', true)
    return new Promise((resolve, reject) => {
      this.$axios
        .$get(payload.url + payload.query)
        .then((res) => {
          resolve(res)
        })
        .catch((error) => {
          reject(error)
          commit('loading', false)
        })
    })
  },
}

export default actions
