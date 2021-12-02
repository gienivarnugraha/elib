<template>
  <v-row align="center" justify="center">
    <v-card class="mx-auto my-6">
      <v-card-title class="secondary white--text">
        <v-row align="center" justify="space-between">
          <v-col md="2"> Manual List </v-col>

          <!-- SEARCH COLUMN -->
          <v-col>
            <v-text-field
              v-model.lazy="searchTimeOut"
              append-icon="mdi-magnify"
              label="Search"
              placeholder="Type something to search..."
              clearable
              single-line
              hide-details
              solo
              flat
              dense
              background-color="white"
            ></v-text-field>
          </v-col>

          <!-- GROUP BY COLUMN -->
          <v-col md="3">
            <v-select
              v-model="group.groupBy"
              :items="group.groupList"
              item-text="text"
              item-value="value"
              label="Group By"
              placeholder="Group By"
              @input="filter.results = []"
              single-line
              menu-props="offsetY"
              clearable
              hide-details
              solo
              flat
              dense
            ></v-select>
          </v-col>

          <!-- FILTER BY COLUMN -->
          <v-col md="1">
            <v-menu
              v-model="filter.menu"
              offset-y
              :close-on-content-click="false"
            >
              <template v-slot:activator="{ on: menu, attrs }">
                <v-tooltip top>
                  <template v-slot:activator="{ on: tooltip }">
                    <v-btn
                      block
                      color="primary"
                      v-bind="attrs"
                      v-on="{ ...tooltip, ...menu }"
                      ><v-icon>mdi-filter</v-icon>
                    </v-btn>
                  </template>
                  <span>Filter Manual</span>
                </v-tooltip>
              </template>

              <v-card class="px-4 pt-4 pb-0">
                <v-select
                  v-model="filter.items"
                  :items="filterList"
                  item-text="text"
                  label="Filter By"
                  placeholder="Filter By"
                  return-object
                  clearable
                  prepend-icon="mdi-filter-outline"
                  outlined
                  dense
                  menu-props="offsetY, closeOnClick"
                  @click:clear="cancelFilter"
                  multiple
                  chips
                  deletable-chips
                >
                </v-select>
                <v-divider></v-divider>
                <v-card-actions>
                  <v-btn
                    text
                    color="accent"
                    @click="cancelFilter, (filter.menu = false)"
                    >Cancel</v-btn
                  >
                  <v-spacer></v-spacer>
                  <v-btn
                    :loading="loading"
                    class="white--text"
                    color="accent"
                    @click="doFilter"
                    depressed
                    >Filter</v-btn
                  >
                </v-card-actions>
              </v-card>
            </v-menu>
          </v-col>

          <!-- ADD MANUNAL COLUMN -->
          <v-col v-if="isAdmin" md="1">
            <v-dialog v-model="dialog" max-width="500px">
              <template v-slot:activator="{ on: dialog, attrs }">
                <v-tooltip top>
                  <template v-slot:activator="{ on: tooltip }">
                    <v-btn
                      block
                      color="accent"
                      v-bind="attrs"
                      v-on="{ ...dialog, ...tooltip }"
                    >
                      <v-icon>mdi-plus</v-icon>
                    </v-btn>
                  </template>
                  <span>Add New Manual</span>
                </v-tooltip>
              </template>
              <v-card>
                <v-card-title class="secondary">
                  <span class="headline white--text">New Manual</span>
                </v-card-title>

                <v-form
                  class="mt-6"
                  ref="form"
                  v-model="manual.valid"
                  lazy-validation
                  @submit.prevent
                >
                  <v-list>
                    <v-list-item>
                      <v-text-field
                        :label="revision.form.index.label"
                        :placeholder="revision.form.index.placeholder"
                        v-model="revision.form.index.value"
                        clearable
                        dense
                        hide-details="auto"
                        :error-messages="errors.index"
                        outlined
                        :rules="[(v) => !!v || 'Fill index!']"
                        required
                      ></v-text-field>
                    </v-list-item>
                    <v-list-item>
                      <v-text-field
                        :label="revision.form.index_date.label"
                        :placeholder="revision.form.index_date.placeholder"
                        v-model="revision.form.index_date.value"
                        clearable
                        dense
                        outlined
                        hide-details="auto"
                        :error-messages="errors.index_date"
                        readonly
                        class="dateMenuAdd"
                        @click="revision.menu_date = !revision.menu_date"
                      ></v-text-field>

                      <v-menu
                        ref="menu_date"
                        v-model="revision.menu_date"
                        :return-value.sync="revision.form.index_date.value"
                        :close-on-content-click="false"
                        transition="scale-transition"
                        offset-y
                        absolute
                        :position-x="200"
                        attach=".dateMenuAdd"
                        min-width="290px"
                      >
                        <v-date-picker
                          v-model="revision.form.index_date.value"
                          no-title
                          scrollable
                        >
                          <v-spacer></v-spacer>
                          <v-btn
                            text
                            color="primary"
                            @click="revision.menu_date = false"
                            >Cancel</v-btn
                          >
                          <v-btn
                            text
                            color="primary"
                            @click="
                              $refs.menu_date.save(
                                revision.form.index_date.value
                              )
                            "
                            >Save</v-btn
                          >
                        </v-date-picker>
                      </v-menu>
                    </v-list-item>
                    <v-list-item v-for="(item, i) in manual.model" :key="i">
                      <v-autocomplete
                        v-if="item.type == 'auto'"
                        v-model="item.value"
                        :items="aircrafts"
                        item-text="type"
                        item-value="id"
                        :label="item.label"
                        header="Available Aircraft"
                        divider
                        :search-input.sync="selectAircraft"
                        :loading="loading"
                        menu-props="closeOnClick=true, closeOnContentClick,offsetY"
                        clearable
                        dense
                        :error-messages="errors[i]"
                        outlined
                        hide-details="auto"
                        :rules="[(v) => !!v || 'Select Aircraft']"
                        required
                      >
                        <template v-slot:item="data">
                          <v-list-item-content>
                            <v-list-item-title
                              v-text="data.item.type"
                            ></v-list-item-title>
                            <v-list-item-subtitle
                              v-html="'Effectivity ' + data.item.effectivity"
                            ></v-list-item-subtitle>
                          </v-list-item-content>
                        </template>
                      </v-autocomplete>
                      <v-text-field
                        v-if="item.type == 'text'"
                        v-model="item.value"
                        :label="item.label"
                        :placeholder="item.placeholder"
                        :error-messages="errors[i]"
                        outlined
                        required
                        :rules="[(v) => !!v || `Fill ${item.label} !`]"
                        hide-details="auto"
                        dense
                        clearable
                      ></v-text-field>
                      <v-select
                        v-if="item.type == 'select'"
                        v-model="item.value"
                        :items="manual[i]"
                        :label="item.label"
                        :placeholder="item.placeholder"
                        :error-messages="errors[i]"
                        outlined
                        required
                        hide-details="auto"
                        item-text="text"
                        item-value="value"
                        menu-props="offsetY"
                        clearable
                        dense
                      ></v-select>
                      <v-switch
                        v-if="item.type == 'switch'"
                        v-model="item.value"
                        :label="item.value == true ? 'Caplist' : 'Not Caplist '"
                        :error-messages="errors[i]"
                        outlined
                        inset
                        menu-props="offsetY, closeOnClick"
                      ></v-switch>
                    </v-list-item>
                  </v-list>
                </v-form>

                <v-divider></v-divider>
                <v-card-actions>
                  <v-btn text @click="$refs.form.reset()">Clear</v-btn>
                  <v-spacer></v-spacer>
                  <v-btn
                    :loading="loading"
                    class="white--text"
                    color="accent"
                    @click="postManual"
                    depressed
                    >Save</v-btn
                  >
                </v-card-actions>
              </v-card>
            </v-dialog>
          </v-col>
        </v-row>
      </v-card-title>

      <v-card-text>
        <v-data-table
          :headers="headers"
          :items="filter.results.length > 0 ? filter.results : manuals"
          :loading="loading"
          :search="search"
          :footer-props="{
            'items-per-page-options': [25, 50],
          }"
          :groupBy="group.groupBy"
          multi-sort
          :expanded.sync="manual.expand"
          single-expand
          show-expand
          @click:row="expand"
        >
          <!-- EXPANDED DATA -->
          <template v-slot:expanded-item="{ headers, item }">
            <td :colspan="headers.length" class="px-0">
              <v-card tile outlined class="elevation-0">
                <v-card-text>
                  <v-row class="my-2" justify="center">
                    <!-- EXPANDED MANUAL COLUMN -->
                    <v-col md="4" xs="9">
                      <v-card outlined class="pa-2">
                        <v-card-title>
                          <span class="headline"
                            >Manual {{ manual.edit ? 'Edit' : 'Details' }}</span
                          >

                          <v-spacer></v-spacer>

                          <div v-if="isAdmin">
                            <v-tooltip top>
                              <template v-slot:activator="{ on, attrs }">
                                <v-btn
                                  icon
                                  color="primary"
                                  @click="manual.edit = !manual.edit"
                                  v-bind="attrs"
                                  v-on="on"
                                >
                                  <v-icon v-if="!manual.edit"
                                    >mdi-pencil-circle</v-icon
                                  >
                                  <v-icon v-else>mdi-close-circle</v-icon>
                                </v-btn>
                              </template>
                              {{ manual.edit ? 'Cancel' : 'Edit' }}
                            </v-tooltip>
                          </div>
                        </v-card-title>
                        <v-card-text>
                          <v-form
                            ref="editManual"
                            v-model="manual.valid"
                            lazy-validation
                            @submit.prevent
                          >
                            <v-list class="mt-2">
                              <v-list-item
                                v-for="(item, i) in manual.model"
                                :key="i"
                              >
                                <v-autocomplete
                                  v-if="item.type == 'auto'"
                                  v-model="item.value"
                                  :items="aircrafts"
                                  @change="item.dirty = true"
                                  item-text="type"
                                  item-value="id"
                                  label="Select Aircraft"
                                  header="Available Aircraft"
                                  divider
                                  :search-input.sync="selectAircraft"
                                  :loading="loading"
                                  menu-props="closeOnClick=true, closeOnContentClick,offsetY"
                                  clearable
                                  dense
                                  :error-messages="errors[i]"
                                  :outlined="manual.edit"
                                  :disabled="!manual.edit"
                                  hide-details="auto"
                                  :rules="[(v) => !!v || 'Select Aircraft']"
                                  required
                                >
                                  <template v-slot:item="data">
                                    <v-list-item-content>
                                      <v-list-item-title
                                        v-text="data.item.type"
                                      ></v-list-item-title>
                                      <v-list-item-subtitle
                                        v-html="
                                          'Effectivity ' + data.item.effectivity
                                        "
                                      ></v-list-item-subtitle>
                                    </v-list-item-content>
                                  </template>
                                </v-autocomplete>
                                <v-text-field
                                  v-if="item.type == 'text'"
                                  v-model="item.value"
                                  :label="item.label"
                                  :placeholder="item.placeholder"
                                  :disabled="!manual.edit"
                                  :error-messages="errors[i]"
                                  @change="item.dirty = true"
                                  required
                                  hide-details="auto"
                                  :outlined="manual.edit"
                                  dense
                                  clearable
                                ></v-text-field>
                                <v-select
                                  v-if="item.type == 'select'"
                                  v-model="item.value"
                                  :items="manual[i]"
                                  :label="item.label"
                                  @change="item.dirty = true"
                                  :placeholder="item.placeholder"
                                  :disabled="!manual.edit"
                                  :error-messages="errors[i]"
                                  required
                                  hide-details="auto"
                                  :outlined="manual.edit"
                                  item-text="text"
                                  item-value="value"
                                  menu-props="offsetY"
                                  clearable
                                  dense
                                ></v-select>
                                <v-switch
                                  v-if="item.type == 'switch'"
                                  @change="item.dirty = true"
                                  v-model="item.value"
                                  :label="
                                    item.value == true
                                      ? 'Caplist'
                                      : 'Not Caplist '
                                  "
                                  :disabled="!manual.edit"
                                  :error-messages="errors[i]"
                                  inset
                                  menu-props="offsetY, closeOnClick"
                                ></v-switch>
                              </v-list-item>
                            </v-list>
                          </v-form>
                        </v-card-text>
                        <v-card-actions>
                          <v-btn text @click="editCancel">Cancel</v-btn>
                          <v-spacer></v-spacer>
                          <v-btn
                            :loading="loading"
                            class="white--text"
                            color="primary"
                            :disabled="!manual.valid"
                            @click="putManual"
                            depressed
                            >Edit</v-btn
                          >
                        </v-card-actions>
                      </v-card>
                    </v-col>

                    <!-- EXPANDED REVISION COLUMN -->
                    <v-col md="4" xs="9">
                      <v-card outlined class="pa-2">
                        <v-card-title>
                          {{ revision.add ? 'Add Revision' : 'Revision Lists' }}
                          <v-spacer></v-spacer>
                          <div v-if="isAdmin">
                            <v-tooltip top>
                              <template v-slot:activator="{ on, attrs }">
                                <v-btn
                                  icon
                                  color="primary"
                                  @click="addRevision"
                                  v-bind="attrs"
                                  v-on="on"
                                >
                                  <v-icon v-if="!revision.add"
                                    >mdi-plus-circle</v-icon
                                  >
                                  <v-icon v-else>mdi-close-circle</v-icon>
                                </v-btn>
                              </template>
                              {{ revision.add ? 'Add Revision' : 'Cancel' }}
                            </v-tooltip>
                          </div>
                        </v-card-title>
                        <v-card-text>
                          <v-scroll-y-transition>
                            <v-form v-if="revision.add">
                              <v-row>
                                <v-col lg="12">
                                  <v-text-field
                                    :label="revision.form.changes.label"
                                    :placeholder="
                                      revision.form.changes.placeholder
                                    "
                                    v-model="revision.form.changes.value"
                                    dense
                                    :error-messages="errors.index"
                                    outlined
                                    :rules="[(v) => !!v || 'Fill reference!']"
                                    required
                                  ></v-text-field>
                                </v-col>
                              </v-row>
                              <v-row dense justify="space-between">
                                <v-col lg="4">
                                  <v-text-field
                                    :label="revision.form.index.label"
                                    :placeholder="
                                      revision.form.index.placeholder
                                    "
                                    v-model="revision.form.index.value"
                                    dense
                                    :error-messages="errors.index"
                                    outlined
                                    :rules="[(v) => !!v || 'Fill new index!']"
                                    required
                                  ></v-text-field
                                ></v-col>
                                <v-col lg="6">
                                  <v-text-field
                                    :label="revision.form.index_date.label"
                                    :placeholder="
                                      revision.form.index_date.placeholder
                                    "
                                    v-model="revision.form.index_date.value"
                                    dense
                                    outlined
                                    :error-messages="errors.index_date"
                                    readonly
                                    class="dateMenu"
                                    @click="
                                      revision.menu_date = !revision.menu_date
                                    "
                                  ></v-text-field>

                                  <v-menu
                                    ref="menu_date"
                                    v-model="revision.menu_date"
                                    :return-value.sync="
                                      revision.form.index_date.value
                                    "
                                    :close-on-content-click="false"
                                    transition="scale-transition"
                                    absolute
                                    style="margin-top: 200px"
                                    attach=".dateMenu"
                                    min-width="290px"
                                  >
                                    <v-date-picker
                                      v-model="revision.form.index_date.value"
                                      no-title
                                      scrollable
                                    >
                                      <v-spacer></v-spacer>
                                      <v-btn
                                        text
                                        color="primary"
                                        @click="revision.menu_date = false"
                                        >Cancel</v-btn
                                      >
                                      <v-btn
                                        text
                                        color="primary"
                                        @click="
                                          $refs.menu_date.save(
                                            revision.form.index_date.value
                                          )
                                        "
                                        >Save</v-btn
                                      >
                                    </v-date-picker>
                                  </v-menu>
                                </v-col>
                                <v-col lg="2">
                                  <v-btn
                                    block
                                    color="primary"
                                    @click="postRevision"
                                    ><v-icon>mdi-plus</v-icon>
                                  </v-btn>
                                </v-col>
                              </v-row>
                            </v-form>
                          </v-scroll-y-transition>

                          <v-expansion-panels
                            focusable
                            hover
                            flat
                            accordion
                            active-class="grey lighten-5"
                            v-model="revision.panel"
                          >
                            <v-expansion-panel
                              v-for="(rev, i) in item.revision"
                              :key="i"
                              @change="getRevision(rev)"
                            >
                              <!-- HEADER -->
                              <v-expansion-panel-header
                                disable-icon-rotate
                                ripple
                              >
                                Rev: {{ rev.index }}
                              </v-expansion-panel-header>

                              <!-- CONTENT -->
                              <v-expansion-panel-content>
                                <v-row align="center" class="my-2">
                                  <v-col>
                                    <div>
                                      <p class="mb-0 text-left caption">
                                        Revision Reference:
                                      </p>
                                      <p class="mb-0 text-center overline">
                                        {{ rev.changes }}
                                      </p>
                                      <p class="mb-0 text-left caption">
                                        Index:
                                      </p>
                                      <p class="mb-0 text-center overline">
                                        {{ rev.index }}
                                      </p>
                                      <p class="mb-0 text-left caption">
                                        Index Date:
                                      </p>
                                      <p class="mb-0 text-center overline">
                                        {{ rev.index_date }}
                                      </p>
                                    </div>
                                  </v-col>
                                  <v-divider vertical></v-divider>

                                  <v-col md="2" class="py-0 px-1 text-center">
                                    <!-- FILE -->
                                    <div v-if="rev.file.filename != null">
                                      <v-tooltip top>
                                        <template
                                          v-slot:activator="{ on, attrs }"
                                        >
                                          <v-btn
                                            icon
                                            v-bind="attrs"
                                            v-on="on"
                                            color="primary"
                                            @click="readFile(rev.id)"
                                          >
                                            <v-icon>mdi-file</v-icon>
                                          </v-btn>
                                        </template>
                                        <span
                                          >See File
                                          {{ rev.file.filename }}
                                        </span>
                                      </v-tooltip>

                                      <!-- CHANGE FILE -->
                                      <v-tooltip top v-if="isAdmin">
                                        <template
                                          v-slot:activator="{ on, attrs }"
                                        >
                                          <v-btn
                                            icon
                                            v-bind="attrs"
                                            v-on="on"
                                            color="primary"
                                            @click="
                                              revision.changeFile =
                                                !revision.changeFile
                                            "
                                          >
                                            <v-icon>
                                              {{
                                                revision.changeFile
                                                  ? 'mdi-close-circle'
                                                  : 'mdi-pencil-outline'
                                              }}
                                            </v-icon>
                                          </v-btn>
                                        </template>
                                        <span>
                                          {{
                                            revision.changeFile
                                              ? 'Close'
                                              : 'Change File'
                                          }}
                                        </span>
                                      </v-tooltip>
                                    </div>
                                    <div v-else>
                                      <v-tooltip top v-if="isAdmin">
                                        <template
                                          v-slot:activator="{ on, attrs }"
                                        >
                                          <v-btn
                                            icon
                                            v-bind="attrs"
                                            v-on="on"
                                            color="primary"
                                            @click="
                                              revision.changeFile =
                                                !revision.changeFile
                                            "
                                          >
                                            <v-icon>{{
                                              revision.changeFile
                                                ? 'mdi-close-circle'
                                                : 'mdi-plus-circle'
                                            }}</v-icon>
                                          </v-btn>
                                        </template>
                                        <span>
                                          {{
                                            revision.changeFile
                                              ? 'Cancel'
                                              : 'Add File'
                                          }}</span
                                        >
                                      </v-tooltip>
                                    </div>
                                  </v-col>
                                </v-row>

                                <!-- CHANGE FILE -->
                                <v-fade-transition>
                                  <div v-if="revision.changeFile">
                                    <v-file-input
                                      v-model="revision.file"
                                      truncate-length="10"
                                      label="File"
                                      placeholder="Upload File"
                                      single-line
                                      :append-outer-icon="
                                        revision.file
                                          ? 'mdi-file-send-outline'
                                          : 'mdi-close-circle'
                                      "
                                      accept="application/pdf"
                                      small-chips
                                      max-width="20%"
                                      @click:append-outer="
                                        revision.file ? postFile() : null
                                      "
                                      persistent-hint
                                      hint="must be pdf type"
                                    ></v-file-input>
                                  </div>
                                </v-fade-transition>
                              </v-expansion-panel-content>
                            </v-expansion-panel>
                          </v-expansion-panels>
                        </v-card-text>
                      </v-card>
                    </v-col>

                    <!-- EXPANDED ORDER COLUMN -->
                    <v-col md="4" xs="9">
                      <v-card outlined class="pa-2">
                        <v-card-title>
                          {{ order.add ? 'New Order' : 'Order History' }}
                          <v-spacer></v-spacer>
                          <div v-if="isAdmin">
                            <v-tooltip top>
                              <template v-slot:activator="{ on, attrs }">
                                <v-btn
                                  icon
                                  color="primary"
                                  @click="addOrder"
                                  v-bind="attrs"
                                  v-on="on"
                                >
                                  <v-icon>{{
                                    order.add
                                      ? 'mdi-close-circle'
                                      : 'mdi-plus-circle'
                                  }}</v-icon>
                                </v-btn>
                              </template>
                              {{ order.add ? 'New Order' : 'Cancel' }}
                            </v-tooltip>
                          </div>
                        </v-card-title>
                        <v-card-text>
                          <v-scroll-y-transition>
                            <v-form v-if="order.add">
                              <v-row dense justify="space-between">
                                <v-col lg="6" class="menuSend">
                                  <v-autocomplete
                                    v-model="order.form.user_id.value"
                                    :items="users"
                                    item-text="name"
                                    item-value="id"
                                    :label="order.form.user_id.label"
                                    :placeholder="
                                      order.form.user_id.placeholder
                                    "
                                    header="Available Aircraft"
                                    divider
                                    :search-input.sync="selectUser"
                                    :loading="loading"
                                    menu-props="closeOnClick=true, closeOnContentClick,offsetY"
                                    dense
                                    :error-messages="errors.user_id"
                                    outlined
                                    hide-details="auto"
                                    :rules="[(v) => !!v || 'Select Aircraft']"
                                    required
                                  >
                                  </v-autocomplete>
                                </v-col>
                                <v-col lg="4">
                                  <v-text-field
                                    :label="order.form.send_date.label"
                                    :placeholder="
                                      order.form.send_date.placeholder
                                    "
                                    readonly
                                    v-model="order.form.send_date.value"
                                    dense
                                    :error-messages="errors.send_date"
                                    outlined
                                    :rules="[(v) => !!v || 'Fill Date!']"
                                    @click="order.menu_send = !order.menu_send"
                                    required
                                  ></v-text-field>

                                  <v-menu
                                    ref="menu_send"
                                    v-model="order.menu_send"
                                    :return-value.sync="
                                      order.form.send_date.value
                                    "
                                    :close-on-content-click="false"
                                    transition="scale-transition"
                                    absolute
                                    style="margin-top: 200px"
                                    attach=".menuSend"
                                    min-width="290px"
                                  >
                                    <v-date-picker
                                      v-model="order.form.send_date.value"
                                      no-title
                                      scrollable
                                    >
                                      <v-spacer></v-spacer>
                                      <v-btn
                                        text
                                        @click="order.menu_send = false"
                                        >Cancel</v-btn
                                      >
                                      <v-btn
                                        text
                                        color="primary"
                                        @click="
                                          $refs.menu_send.save(
                                            order.form.send_date.value
                                          )
                                        "
                                        >Save</v-btn
                                      >
                                    </v-date-picker>
                                  </v-menu>
                                </v-col>
                                <v-col lg="2">
                                  <v-btn
                                    block
                                    color="primary"
                                    @click="postOrder"
                                    ><v-icon>mdi-plus</v-icon>
                                  </v-btn>
                                </v-col>
                              </v-row>
                            </v-form>
                          </v-scroll-y-transition>

                          <v-expansion-panels
                            focusable
                            hover
                            flat
                            accordion
                            active-class="grey lighten-5"
                            v-model="order.panel"
                          >
                            <v-expansion-panel
                              v-for="(order, index) in item.order"
                              :key="index"
                              @change="getOrder(order)"
                            >
                              <!-- HEADER -->
                              <v-expansion-panel-header
                                disable-icon-rotate
                                ripple
                              >
                                Borrowed By: {{ order.user.name }}
                                <template v-slot:actions>
                                  <v-tooltip top>
                                    <template v-slot:activator="{ on, attrs }">
                                      <v-icon
                                        v-bind="attrs"
                                        v-on="on"
                                        :color="
                                          order.is_closed == 1
                                            ? 'success'
                                            : 'error'
                                        "
                                      >
                                        {{
                                          order.is_closed == 1
                                            ? 'mdi-check'
                                            : 'mdi-alert-circle'
                                        }}
                                      </v-icon>
                                    </template>
                                    <span>
                                      {{
                                        order.is_closed == 1
                                          ? 'Available'
                                          : 'Not Available'
                                      }}
                                    </span>
                                  </v-tooltip>
                                </template>
                              </v-expansion-panel-header>

                              <!-- CONTENT -->
                              <v-expansion-panel-content>
                                <v-row align="center" class="my-2">
                                  <v-col>
                                    <div>
                                      <p class="mb-0 text-left caption">
                                        Ordered By:
                                      </p>
                                      <p class="mb-0 text-center overline">
                                        {{ order.user.name }}
                                      </p>
                                      <p class="mb-0 text-left caption">
                                        Send Date:
                                      </p>
                                      <p class="mb-0 text-center overline">
                                        {{ order.send_date }}
                                      </p>
                                      <p class="mb-0 text-left caption">
                                        Returned at:
                                      </p>
                                      <p class="mb-0 text-center overline">
                                        {{
                                          order.return_date ||
                                          'not returned yet'
                                        }}
                                      </p>
                                    </div>
                                  </v-col>
                                  <v-divider vertical></v-divider>

                                  <v-col md="2" class="py-0 px-1 text-center">
                                    <div
                                      v-if="isAdmin && order.is_closed === 0"
                                    >
                                      <v-tooltip top>
                                        <template
                                          v-slot:activator="{ on, attrs }"
                                        >
                                          <v-btn
                                            icon
                                            v-bind="attrs"
                                            v-on="on"
                                            color="primary"
                                            @click="changeStatus"
                                          >
                                            <v-icon>
                                              {{
                                                order.menu
                                                  ? 'mdi-close-circle'
                                                  : 'mdi-alert-circle-check-outline'
                                              }}
                                            </v-icon>
                                          </v-btn>
                                        </template>
                                        <span> Change Status </span>
                                      </v-tooltip>
                                    </div>
                                  </v-col>
                                </v-row>
                              </v-expansion-panel-content>
                            </v-expansion-panel>
                            <div class="isAvailableMenu"></div>
                          </v-expansion-panels>

                          <!-- IS CLOSED MENU -->
                          <v-menu
                            v-model="order.menu"
                            offset-y
                            attach=".isAvailableMenu"
                            absolute
                            :menu-props="{ top: true, offsetY: true }"
                            :close-on-content-click="false"
                          >
                            <v-card class="px-4 pt-4">
                              <v-card-title>
                                Status
                                {{ order.is_closed ? 'Closed' : 'Open' }}
                              </v-card-title>
                              <v-card-text>
                                <v-switch
                                  v-model="order.is_closed"
                                  :label="order.is_closed ? 'Closed' : 'Open'"
                                  inset
                                  menu-props="offsetY, closeOnClick"
                                ></v-switch>
                              </v-card-text>
                              <v-card-actions>
                                <v-btn
                                  text
                                  @click="
                                    ;(order.menu = false),
                                      (order.is_closed = false)
                                  "
                                  >Cancel</v-btn
                                >
                                <v-spacer></v-spacer>
                                <v-btn
                                  :loading="loading"
                                  class="white--text"
                                  color="primary"
                                  @click="putStatus"
                                  depressed
                                  >Update</v-btn
                                >
                              </v-card-actions>
                            </v-card>
                          </v-menu>
                        </v-card-text>
                      </v-card>
                    </v-col>
                  </v-row>
                </v-card-text>
              </v-card>
            </td>
          </template>

          <!-- CAPLIST ICON -->
          <template v-slot:item.caplist="props">
            <v-tooltip top>
              <template v-slot:activator="{ on, attrs }">
                <v-icon
                  v-bind="attrs"
                  v-on="on"
                  :color="props.item.caplist ? 'success' : 'error '"
                  v-text="
                    props.item.caplist ? 'mdi-check-circle' : 'mdi-alert-circle'
                  "
                >
                </v-icon>
              </template>
              <span>{{ props.item.caplist ? 'Caplist' : 'Not Caplist' }}</span>
            </v-tooltip>
          </template>

          <!-- AVAILABILITY ICON -->
          <template v-slot:item.availability="props">
            <v-tooltip top v-if="props.item.order.length > 0">
              <template v-slot:activator="{ on, attrs }">
                <v-icon
                  v-bind="attrs"
                  v-on="on"
                  :color="props.item.order[0].is_closed ? 'success' : 'error '"
                >
                  {{
                    props.item.order[0].is_closed
                      ? 'mdi-check-circle'
                      : 'mdi-alert-circle'
                  }}
                </v-icon>
              </template>
              <span>{{
                props.item.order[0].is_closed ? 'Available' : 'Not Avail'
              }}</span>
            </v-tooltip>
            <v-tooltip top v-else>
              <template v-slot:activator="{ on, attrs }">
                <v-icon v-bind="attrs" v-on="on" color="success">
                  {{ 'mdi-check-circle' }}
                </v-icon>
              </template>
              <span> Available</span>
            </v-tooltip>
          </template>

          <!-- INDEX -->
          <template v-slot:item.index="props">
            {{ props.item.revision[0].index }}
          </template>

          <!-- INDEX  DATE-->
          <template v-slot:item.index_date="props">
            {{ props.item.revision[0].index_date }}
          </template>

          <template v-if="isAdmin" v-slot:item.actions="{ item }">
            <v-icon @click="deleteItem(item)">mdi-delete</v-icon>
          </template>
        </v-data-table>
      </v-card-text>
    </v-card>
  </v-row>
</template>

<script>
import { mapState, mapGetters } from 'vuex'

export default {
  layout: 'dashboard',

  data: () => ({
    headers: [
      { text: 'P/N', value: 'part_num' },
      { text: 'Type', value: 'type', align: 'center' },
      { text: 'Aircraft', value: 'aircraft.type' },
      { text: 'Subject', value: 'subject' },
      { text: 'Index', value: 'index', sortable: false, align: 'center' },
      {
        text: 'Index Date',
        value: 'index_date',
        sortable: false,
        align: 'center',
      },
      { text: 'Caplist', value: 'caplist', align: 'center' },
      { text: 'Availability', value: 'availability', align: 'center' },
      { text: 'Vendor', value: 'vendor' },
      { text: 'Actions', value: 'actions', sortable: false, align: 'center' },
    ],
    search: null,
    selectAircraft: '',
    selectUser: '',
    /* DOCUMENT DATA */
    manual: {
      valid: false,
      expand: [],
      form: {},
      edit: false,
      model: {
        aircraft_id: {
          type: 'auto',
          value: '',
          label: 'Aircraft',
          placeholder: 'Aircraft',
          dirty: false,
        },
        part_num: {
          type: 'text',
          value: '',
          label: 'Part Number',
          placeholder: 'Part Number',
          dirty: false,
        },
        lib_call: {
          type: 'text',
          value: '',
          label: 'Library Call Number',
          placeholder: 'Library Call Number',
          dirty: false,
        },
        subject: {
          type: 'text',
          value: '',
          label: 'Subject',
          placeholder: 'Subject',
          dirty: false,
        },
        volume: {
          type: 'text',
          value: '',
          label: 'Volume',
          placeholder: 'Volume',
          dirty: false,
        },
        vendor: {
          type: 'text',
          value: '',
          label: 'Vendor',
          placeholder: 'Vendor',
          dirty: false,
        },
        type: {
          type: 'select',
          value: '',
          label: 'Manual Type',
          placeholder: 'Manual Type',
          dirty: false,
        },
        collector: {
          type: 'select',
          value: '',
          label: 'Collector',
          placeholder: 'Collector',
          dirty: false,
        },
        caplist: {
          type: 'switch',
          value: '',
          label: 'Caplist',
          placeholder: 'Caplist',
          dirty: false,
        },
      },
      type: ['WDM', 'IPC', 'CMM', 'SRM'],
      collector: ['AMO1', 'AMO2', 'AMO3'],
    },
    revision: {
      id: '',
      panel: '',
      file: null,
      file_id: '',
      form: {
        changes: {
          value: '',
          label: 'Revision Reference',
          placeholder: 'SB Part Number',
        },
        index: {
          value: '',
          label: 'Index',
          placeholder: 'A/B/C',
        },
        index_date: {
          value: '',
          label: 'Index Date',
          placeholder: 'YYYY-MM-DD',
        },
      },
      changeFile: false,
      menu: false,
      menu_date: false,
      is_closed: '',
      add: false,
      lists: [],
    },
    order: {
      id: '',
      panel: '',
      form: {
        user_id: {
          value: '',
          label: 'User',
          placeholder: 'Alex',
        },
        send_date: {
          value: '',
          label: 'Send Date',
          placeholder: 'YYYY-MM-DD',
        },
      },
      menu: false,
      menu_send: false,
      is_closed: '',
      add: false,
    },
    /* DATA CUSTOMIZE */
    filter: {
      menu: false,
      items: [],
      results: [],
    },
    group: {
      groupBy: null,
      groupList: [
        { text: 'Type', value: 'type' },
        { text: 'Aircraft Type', value: 'aircraft.type' },
        { text: 'Aircraft Effectivity', value: 'aircraft.effectivity' },
        { text: 'Vendor', value: 'vendor' },
      ],
    },
    dialog: false,
  }),
  mounted() {
    this.manuals.length != 0 || this.$store.dispatch('fetch', 'manuals')
  },
  watch: {
    selectAircraft(val) {
      this.aircrafts.length != 0 || this.$store.dispatch('fetch', 'aircrafts')
    },
    selectUser(val) {
      this.users.length != 0 || this.$store.dispatch('fetch', 'users')
    },
    orderAdd(val) {
      val || this.clearOrder()
    },
    revisionAdd(val) {
      val || this.clearRevision()
    },
    dialog(val) {
      //val || this.clearAircraftForm()
      if (val) {
        this.manual.expand.pop()
        this.clearManual()
        this.$nextTick(() => {
          this.$refs.form.resetValidation()
        })
      }
    },
  },
  computed: {
    ...mapState({
      user: (state) => state.auth.user,
      isAdmin: (state) => state.auth.user.is_admin,
      errors: (state) => state.errors,
      loading: (state) => state.manuals.loading,
      manuals: (state) => state.manuals.items,
      aircrafts: (state) => state.aircrafts.items,
      users: (state) => state.users.items,
      loadingAircraft: (state) => state.aircrafts.loading,
    }),
    ...mapGetters('manuals', {
      filterBy: 'filterBy',
    }),
    searchTimeOut: {
      get() {
        return this.search
      },
      set(val) {
        this.$store.commit('manuals/loading', true)
        this.debounced(val)
      },
    },
    filterList() {
      let lists = []
      this.manual.type.forEach((item) => {
        let type = { text: 'Only ' + item, key: 'type', value: item }
        lists.push(type)
      })
      lists.push({ text: 'Only Vendor', key: 'vendor', value: 'vendor' })
      return lists
    },
    orderAdd() {
      return this.order.add
    },
    revisionAdd() {
      return this.revision.add
    },
  },
  methods: {
    debounced: _.debounce(function (val) {
      this.search = val
      this.$store.commit('manuals/loading', false)
    }, 1000),
    expand(item) {
      const index = this.manual.expand.indexOf(item)
      if (index == 0) {
        this.manual.expand.pop()
      } else {
        this.editItem(item)
        this.clearRevision()
        this.clearOrder()
      }
    },
    editItem(item) {
      let index = this.manuals.indexOf(item)
      this.$store.commit('manuals/show', { item, index })
      this.manual.expand = [item]
      _.forOwn(this.manual.model, (val, key) => {
        val.value = item[key]
      })
      this.revision.lists = []

      item.revision.forEach((rev, index) => {
        this.revision.lists[index] = { text: rev.index, value: rev.id }
      })
      console.log(this.revision.lists)
    },
    editCancel() {
      this.manual.edit = false
      _.forOwn(this.manual.model, (val, key) => {
        if (val.dirty === true) val.value = this.manual.expand[0][key]
      })
    },
    addOrder() {
      this.order.add = !this.order.add
      this.order.panel = -1
    },
    postOrder() {
      let form = _.mapValues(this.order.form, 'value')
      this.$store
        .dispatch('post', {
          url: 'manuals/order/',
          form: {
            manual_id: this.manual.expand[0].id,
            ...form,
          },
        })
        .then((resp) => {
          this.clearOrder()
        })
    },
    getOrder(order) {
      console.log(order)
      this.order.id = order.id
      this.order.menu = false
    },
    clearOrder() {
      this.order.id = ''
      _.forOwn(this.order.form, (val, key) => {
        val.value = ''
      })
      this.order.is_closed = ''
      this.order.panel = -1
      this.order.add = false
    },
    addRevision() {
      this.revision.add = !this.revision.add
      this.revision.panel = -1
    },
    getRevision(rev) {
      this.revision.file_id = rev.file.id
      this.revision.id = rev.id
      this.order.is_closed = rev.is_closed
      this.order.is_closed = rev.is_closed
      this.revision.changeFile = false
    },
    postRevision() {
      let form = _.mapValues(this.revision.form, 'value')
      console.log(form)
      this.$store
        .dispatch('post', {
          url: 'manuals/revisions/',
          form: {
            manual_id: this.manual.expand[0].id,
            ...form,
          },
        })
        .then((resp) => {
          this.clearRevision()
        })
    },
    clearRevision() {
      this.revision.id = ''
      this.revision.file = null
      this.revision.file_id = ''
      this.revision.changeFile = false
      _.forOwn(this.revision.form, (val, key) => {
        val.value = ''
      })
      this.revision.is_closed = ''
      this.revision.panel = -1
      this.revision.add = false
      this.manual.edit = false
    },
    postFile() {
      this.$store
        .dispatch('file', {
          url: 'manuals/file/',
          form: {
            file: this.revision.file,
            id: this.revision.file_id,
            change_file: this.revision.changeFile,
          },
        })
        .then((res) => {
          this.clearRevision()
        })
    },
    clearManual() {
      this.$nextTick(() => {
        _.forOwn(this.manual.model, (val, key) => {
          val.value = ''
        })
        this.clearRevision()
      })
    },
    postManual() {
      if (this.isAdmin) {
        let form = _.mapValues(this.manual.model, 'value')
        this.$store
          .dispatch('post', {
            url: 'manuals/',
            form: {
              index: this.revision.form.index.value,
              index_date: this.revision.form.index_date.value,
              ...form,
            },
          })
          .then((res) => {
            this.clearManual()
            this.dialog = false
          })
      }
    },
    putManual() {
      if (this.isAdmin) {
        let form = _.mapValues(this.manual.model, 'value')
        form['id'] = this.manual.expand[0].id
        this.$store
          .dispatch('put', {
            url: 'manuals/',
            form: form,
          })
          .then((res) => {
            this.manual.edit = false
          })
      }
    },
    changeStatus() {
      this.order.menu = !this.order.menu
    },
    putStatus() {
      this.$store
        .dispatch('put', {
          url: 'manuals/status/',
          form: {
            id: this.order.id,
            is_closed: this.order.is_closed,
          },
        })
        .then((res) => {
          this.order.menu = false
        })
    },
    deleteItem(item) {
      swal({
        title: `Delete Manual Reg: ${item.part_num} ?`,
        icon: 'warning',
        buttons: true,
        dangerMode: true,
        className: 'accent',
      }).then((willDelete) => {
        if (willDelete) {
          this.deleteManual(item)
        }
      })
    },
    deleteManual(item) {
      if (this.isAdmin) {
        this.$store
          .dispatch('delete', {
            url: 'manuals/',
            form: item,
          })
          .then((res) => {
            this.clearManual()
          })
      }
    },
    async doFilter() {
      this.$store.commit('manuals/loading', true)
      if (this.group.groupBy) {
        this.cancelFilter()
      }
      this.group.groupBy = null
      let filterMoreThanOne = []

      if (this.filter.items.length == 1) {
        this.filter.results = await this.filterBy(
          this.filter.items[0].key,
          this.filter.items[0].value
        )
      } else {
        await this.filter.items.forEach(async (item) => {
          let filter = await this.filterBy(item.key, item.value)
          await filterMoreThanOne.push(filter)
        })
        this.filter.results = await _.flatten(filterMoreThanOne)
      }
      this.dialog = false
      this.$store.commit('manuals/loading', false)
    },
    cancelFilter() {
      this.filter.results = []
      this.filter.items = []
      this.dialog = false
    },
    readFile(id) {
      this.$router.push('/manuals/' + id)
    },
  },
}
</script>
