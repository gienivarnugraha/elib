import {
  shallowMount,
  createLocalVue
} from "@vue/test-utils";
import Aircraft from "@/pages/aircrafts/index";
import axios from 'axios'
import Vuex from 'vuex'
import actions from '@/store/actions'

const localVue = createLocalVue()
localVue.use(Vuex)

jest.mock('axios');



describe('Aircrafts.vue Implementation Test', () => {
  let wrapper = null
  let actions

  // SETUP - run before to each unit test
  beforeEach(() => {
    // render the component
    const responseGet = {
      data: {
        id: 'id_asdqwe',
        type: 'CN235',
        reg_code: '123',
        serial_num: '123',
        effectivity: '123',
        owner: '123',
        manuf_date: '123',
      }
    }
    axios.get.mockResolvedValue(responseGet)
    wrapper = shallowMount(Aircraft {
      localVue,
      store: new Vuex.Store({
        actions
      })
    })
  })

  // TEARDOWN - run after to each unit test
  afterEach(() => {
    jest.resetModules()
    jest.clearAllMocks()
  })

  it('idoes load the data when a successful HTTP GET occurs', () => {
    expect(axios.get).toHaveBeenCalledTimes(1)
    expect(axios.get).toBeCalledWith(expect.stringMatching(/aircrafts/))

    wrapper.vm.$nextTick().then(function () {
      // check that the user data is properly set
      expect(wrapper.vm.aircrafts.reg_code).toMatch('123')
    })
  })

})
