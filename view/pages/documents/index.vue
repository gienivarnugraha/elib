<template>
  <v-row align="center" justify="center">
    <NuxtChild />
    <v-card class="mx-auto my-6">
      <v-card-title class="secondary white--text">
        <v-row align="center" justify="space-around">
          <v-col md="2" sm="9" xs="9">
            <p class="text-center mb-0">Document List</p>
          </v-col>

          <!--  SEARCH COLUMN -->
          <v-col md="5" sm="9" xs="9">
            <v-text-field
              v-model.lazy="searchTimeOut"
              append-icon="mdi-magnify"
              label="Search Document"
              background-color="white"
              clearable
              hide-details
              outlined
              single-line
              dense
              placeholder="Start typing to search.."
            >
            </v-text-field>
          </v-col>

          <!-- GRUP BY -->
          <v-col md="2" sm="9" xs="9">
            <v-select
              v-model="group.groupBy"
              :items="group.groupList"
              item-text="text"
              item-value="value"
              label="Group By"
              placeholder="Group By"
              background-color="white"
              menu-props="offsetY"
              dense
              hide-details
              outlined
              single-line
              clearable
            ></v-select>
          </v-col>

          <!-- FILTER COLUMN -->
          <v-col md="1" sm="9" xs="9">
            <v-menu
              v-model="filter.menu"
              offset-y
              :close-on-content-click="false"
            >
              <template v-slot:activator="{ on: menu, attrs }">
                <v-tooltip top>
                  <template v-slot:activator="{ on: tooltip }">
                    <v-btn
                      color="accent"
                      v-bind="attrs"
                      v-on="{ ...tooltip, ...menu }"
                      ><v-icon>mdi-filter</v-icon>
                    </v-btn>
                  </template>
                  <span>Filter By</span>
                </v-tooltip>
              </template>

              <v-card class="px-4 pt-4">
                <v-select
                  v-model="filter.items"
                  :items="filter.list"
                  item-text="text"
                  label="Filter By"
                  return-object
                  clearable
                  menu-props="offsetY, closeOnClick"
                  @click:clear="cancelFilter"
                  multiple
                  outlined
                  dense
                  prepend-icon="mdi-filter-outline"
                  chips
                  deletable-chips
                  @input="filterUserOnly"
                >
                </v-select>
                <v-divider></v-divider>
                <v-card-actions>
                  <v-btn
                    text
                    color="primary"
                    @click="cancelFilter, (filter.menu = false)"
                    >Close</v-btn
                  >
                  <v-spacer></v-spacer>
                  <v-btn
                    :loading="loading"
                    class="white--text"
                    color="primary"
                    @click="filterDoc"
                    depressed
                    >Filter</v-btn
                  >
                </v-card-actions>
              </v-card>
            </v-menu>
          </v-col>

          <!-- DOWNLOAD EXCEL -->
          <v-col md="1" sm="9" xs="9">
            <v-tooltip top>
              <template v-slot:activator="{ on, attrs }">
                <v-btn color="primary" v-bind="attrs" v-on="on">
                  <excel
                    :data="docs"
                    :export-fields="exportFields"
                    :name="`elib_export_` + today"
                  >
                    <v-icon>mdi-download</v-icon>
                  </excel>
                </v-btn>
              </template>
              <span>Export to Excel</span>
            </v-tooltip>
          </v-col>

          <!-- ADD NEW DOCUMENT COLUMN -->
          <v-col md="1" sm="9" xs="9">
            <v-dialog v-model="dialog">
              <template v-slot:activator="{ on: menu, attrs }">
                <v-tooltip top>
                  <template v-slot:activator="{ on: tooltip }">
                    <v-btn
                      color="primary"
                      v-bind="attrs"
                      class="d-block"
                      v-on="{ ...tooltip, ...menu }"
                    >
                      <v-icon>mdi-plus</v-icon>
                    </v-btn>
                  </template>
                  <span>Add</span>
                </v-tooltip>
              </template>

              <v-row
                justify="center"
                class="ml-0 my-0"
                style="background-color: white"
              >
                <!-- AIRCRAFT COLUMN-->
                <v-col md="6" xs="9" sm="9">
                  <v-card outlined class="my-4 ml-4 px-4">
                    <v-card-title>
                      <span class="headline"
                        >{{ aircraft.new ? 'Add' : 'Select' }} Aircraft</span
                      >
                      <v-spacer></v-spacer>
                      <div v-if="isAdmin">
                        <v-btn
                          fab
                          elevation="0"
                          x-small
                          color="primary"
                          v-if="!aircraft.new"
                          slot="append-outer"
                          @click="
                            ;(aircraft.new = true),
                              (aircraft.model = aircraft.default),
                              (aircraft.show = false)
                          "
                        >
                          <v-tooltip top>
                            <template v-slot:activator="{ on, attrs }">
                              <v-icon class="ma-2" v-bind="attrs" v-on="on"
                                >mdi-plus</v-icon
                              >
                            </template>
                            Add New Aircraft
                          </v-tooltip>
                        </v-btn>

                        <v-btn
                          fab
                          elevation="0"
                          x-small
                          v-else
                          color="primary"
                          slot="append-outer"
                          @click="clear"
                          ><v-icon outlined>mdi-minus</v-icon></v-btn
                        >
                      </div>
                    </v-card-title>

                    <v-form
                      ref="aircraftSearch"
                      v-if="!aircraft.new"
                      class="px-4"
                    >
                      <v-autocomplete
                        v-model="aircraft.model"
                        :items="aircrafts"
                        item-text="reg_code"
                        return-object
                        label="Select Aircraft"
                        hint="Fill with Registration Code!"
                        placeholder="Ex: A2304"
                        header="Available Aircraft"
                        divider
                        :search-input.sync="selectAircraft"
                        :error-messages="errors.aircraft_id"
                        :loading="loading"
                        menu-props="closeOnClick=true, closeOnContentClick,offsetY"
                        clearable
                        dense
                        @input="aircraft.show = true"
                        @click:clear="clearAircraftForm"
                        outlined
                        :rules="[(v) => !!v || 'Select Aircraft']"
                        required
                      >
                        <template v-slot:item="data">
                          <v-list-item-content>
                            <v-list-item-title
                              v-html="
                                data.item.type + ' - REG: ' + data.item.reg_code
                              "
                            ></v-list-item-title>
                            <v-list-item-subtitle
                              v-html="
                                'S/N: ' +
                                data.item.serial_num +
                                ' - Owner: ' +
                                data.item.owner
                              "
                            ></v-list-item-subtitle>
                          </v-list-item-content>
                        </template>
                      </v-autocomplete>
                    </v-form>

                    <!-- NEW AIRCRAFT -->
                    <div v-if="aircraft.new && isAdmin">
                      <v-expand-transition>
                        <v-form
                          ref="formAircraft"
                          v-if="aircraft.new"
                          v-model="aircraft.valid"
                          lazy-validation
                          @submit.prevent
                        >
                          <v-list>
                            <v-list-item
                              v-for="(item, i) in aircraft.model"
                              :key="i"
                            >
                              <v-text-field
                                :value="item"
                                v-model="aircraft.model[i].value"
                                :label="aircraft.model[i].label"
                                :placeholder="aircraft.model[i].placeholder"
                                :error-messages="errors[i]"
                                hide-details="auto"
                                :rules="[
                                  (v) => !!v || 'This field is required',
                                ]"
                                outlined
                                required
                                dense
                              ></v-text-field>
                            </v-list-item>
                          </v-list>
                          <v-divider></v-divider>
                          <v-card-actions>
                            <v-btn text @click="$refs.formAircraft.reset()"
                              >Clear</v-btn
                            >
                            <v-spacer></v-spacer>
                            <v-btn
                              :loading="loading"
                              class="white--text"
                              color="primary"
                              :disabled="!aircraft.valid"
                              @click="postAircraft"
                              depressed
                              >Add</v-btn
                            >
                          </v-card-actions>
                        </v-form>
                      </v-expand-transition>
                    </div>

                    <!-- SHOW AIRCRAFT -->
                    <div v-if="aircraft.show">
                      <v-expand-transition>
                        <v-list>
                          <v-list-item
                            v-for="(item, i) in aircraftDetails"
                            :key="i"
                          >
                            <v-text-field
                              :value="item"
                              v-model="aircraftDetails[i]"
                              :label="i.toUpperCase()"
                              :rules="[(v) => !!v || 'This field is required']"
                              hide-details="auto"
                              disabled
                              outlined
                              required
                              dense
                            ></v-text-field>
                          </v-list-item>
                        </v-list>
                      </v-expand-transition>
                    </div>
                  </v-card>
                </v-col>

                <!-- DOCUMENT COLUMN-->
                <v-col md="6" xs="9" sm="9">
                  <v-card outlined class="my-4 mr-4 px-4">
                    <v-card-title>Add Document</v-card-title>

                    <v-form
                      ref="formDocument"
                      v-model="document.valid"
                      class="px-4 mb-4"
                      lazy-validation
                      @submit.prevent
                    >
                      <!-- SELECT USER ON ADMIN -->
                      <div v-if="isAdmin">
                        <v-autocomplete
                          v-model="document.form.assignee_id"
                          :rules="[(v) => !!v || 'Select User']"
                          required
                          :error-messages="errors.assignee_id"
                          :items="users"
                          item-text="name"
                          item-value="id"
                          label="Select User"
                          header="Available User"
                          divider
                          :search-input.sync="selectUser"
                          :loading="loading"
                          menu-props="closeOnClick=true, closeOnContentClick,offsetY"
                          clearable
                          :allow-overflow="false"
                          outlined
                          dense
                        >
                          <template v-slot:item="data">
                            <v-list-item-content>
                              <v-list-item-title
                                v-text="data.item.name"
                              ></v-list-item-title>
                              <v-list-item-subtitle
                                v-html="'Org: ' + data.item.org_code"
                              ></v-list-item-subtitle>
                            </v-list-item-content>
                          </template>
                        </v-autocomplete>
                      </div>
                      <div v-else>
                        <p class="mb-0 text-left caption">Assignee:</p>
                        <p class="mb-2 text-center overline">
                          {{ user.name }}
                        </p>
                      </div>

                      <v-select
                        :items="document.select.office"
                        outlined
                        dense
                        clearable
                        v-model="document.form.office"
                        :rules="[(v) => !!v || 'Select Office']"
                        required
                        :error-messages="errors.office"
                        menu-props="closeOnClick=true, closeOnContentClick,offsetY"
                        label="Document Office"
                        placeholder="DOA/AMO"
                      ></v-select>
                      <v-select
                        :items="documentType"
                        v-model="document.form.type"
                        :rules="[(v) => !!v || 'Select Type']"
                        required
                        :error-messages="errors.type"
                        outlined
                        dense
                        no-data-text="Select Document Office First!"
                        clearable
                        menu-props="closeOnClick=true,offsetY"
                        label="Document Type"
                        placeholder="EO/TD/JE/ES"
                      ></v-select>
                      <v-text-field
                        v-model="document.form.reference"
                        outlined
                        dense
                        hint="(OPTIONAL) Fill with reference"
                        clearable
                        label="Reference"
                        placeholder="Ex: From Sales/Contract No"
                      ></v-text-field>

                      <!-- ADD SUBJECT(S) -->
                      <v-btn
                        block
                        class="mb-2"
                        :color="errors.subject ? 'error' : 'primary'"
                        @click="addSubject"
                        >Add Subject
                        <v-icon>mdi-plus</v-icon>
                      </v-btn>
                      <v-fade-transition>
                        <p
                          class="mb-0 text-left caption red--text"
                          v-if="errors.subject"
                        >
                          Fill the subject!
                        </p>
                      </v-fade-transition>

                      <div v-if="document.moreSubject">
                        <div
                          v-for="(subject, i) in document.form.subject"
                          :key="i"
                        >
                          <v-expand-transition>
                            <v-textarea
                              label="Subject"
                              placeholder="Document Subject"
                              v-model="document.form.subject[i]"
                              :rules="[(v) => !!v || 'Fill the subject']"
                              required
                              :error-messages="errors.subject"
                              auto-grow
                              outlined
                              dense
                              prepend-icon="mdi-minus"
                              rows="3"
                              row-height="15"
                              counter="100"
                              @click:prepend="removeSubject(i)"
                            ></v-textarea>
                          </v-expand-transition>
                        </div>
                      </div>
                    </v-form>

                    <!-- DOCUMENT FORM ACTIONS -->
                    <v-divider></v-divider>
                    <v-card-actions>
                      <v-btn text @click="$refs.formDocument.reset()"
                        >Clear</v-btn
                      >
                      <v-spacer></v-spacer>
                      <v-btn
                        :loading="loading"
                        class="white--text"
                        color="primary"
                        :disabled="!document.valid"
                        @click="postDoc"
                        depressed
                        >Add</v-btn
                      >
                    </v-card-actions>
                  </v-card>
                </v-col>
              </v-row>
            </v-dialog>
          </v-col>
        </v-row>
      </v-card-title>

      <v-card-text class="mt-4">
        <v-data-table
          :headers="headers"
          :items="filter.results.length > 0 ? filter.results : docs"
          :loading="loading"
          loading-text="Loading... Please wait"
          :search="search"
          :groupBy="group.groupBy"
          multi-sort
          :footer-props="{
            'items-per-page-options': [25, 50],
          }"
          :expanded.sync="document.expand"
          single-expand
          show-expand
          @click:row="expand"
        >
          <!-- EXPANDED DATA -->
          <template v-slot:expanded-item="{ headers, item }">
            <td :colspan="headers.length" class="px-0">
              <v-card tile outlined class="elevation-0">
                <v-card-title>
                  <span class="headline pb-0"
                    >Document No : {{ item.no }}
                  </span>
                </v-card-title>

                <v-card-text>
                  <v-row class="my-2" justify="center">
                    <!-- EXPANDED AIRCRAFT COLUMN -->
                    <v-col md="4" xs="9">
                      <v-card outlined class="pa-2">
                        <v-card-title>
                          <span class="headline"
                            >Aircraft
                            {{
                              aircraft.edit == true ? 'Select ' : 'Data '
                            }}</span
                          >
                        </v-card-title>

                        <!-- AIRCRAFT ON EDIT AUTOCOMPLETE -->
                        <div v-if="aircraft.edit" class="px-4 mt-4">
                          <v-autocomplete
                            v-model="aircraft.model"
                            :items="aircrafts"
                            item-text="reg_code"
                            return-object
                            label="Select Aircraft"
                            hint="Fill with Registration Code!"
                            persistent-hint
                            header="Available Aircraft"
                            divider
                            :search-input.sync="selectAircraft"
                            :loading="loading"
                            menu-props="closeOnClick=true, closeOnContentClick,offsetY"
                            clearable
                            dense
                            outlined
                            :rules="[(v) => !!v || 'Select Aircraft']"
                            required
                          >
                            <template v-slot:item="data">
                              <v-list-item-content>
                                <v-list-item-title
                                  v-html="
                                    data.item.type +
                                    ' - REG: ' +
                                    data.item.reg_code
                                  "
                                ></v-list-item-title>
                                <v-list-item-subtitle
                                  v-html="
                                    'S/N: ' +
                                    data.item.serial_num +
                                    ' - Owner: ' +
                                    data.item.owner
                                  "
                                ></v-list-item-subtitle>
                              </v-list-item-content>
                            </template>
                          </v-autocomplete>
                        </div>

                        <!-- AIRCRAFT DETAILS -->
                        <v-list class="mt-2">
                          <v-list-item
                            v-for="(item, i) in aircraftDetails"
                            :key="i"
                          >
                            <v-text-field
                              v-model="aircraft.model[i]"
                              :label="aircraft.default[i].label"
                              :placeholder="aircraft.default[i].placeholder"
                              disabled
                              dense
                              class="my-2"
                            ></v-text-field>
                          </v-list-item>
                        </v-list>
                      </v-card>
                    </v-col>

                    <!-- EXPANDED DOCUMENT COLUMN -->
                    <v-col md="4" xs="9">
                      <v-card outlined class="pa-2">
                        <v-card-title>
                          <span class="headline"
                            >Document
                            {{ document.edit ? 'Edit' : 'Details' }}</span
                          >
                        </v-card-title>
                        <v-card-text>
                          <v-select
                            :items="document.select.office"
                            :clearable="document.edit"
                            :outlined="document.edit"
                            :dense="document.edit"
                            v-model="document.editedItem.office"
                            :disabled="!document.edit"
                            menu-props="closeOnClick=true, closeOnContentClick,offsetY"
                            label="Document Office"
                          ></v-select>
                          <v-select
                            :items="documentType"
                            v-model="document.editedItem.type"
                            :clearable="document.edit"
                            :disabled="!document.edit"
                            :outlined="document.edit"
                            :dense="document.edit"
                            menu-props="closeOnClick=true,offsetY"
                            label="Document Type"
                          ></v-select>
                          <v-textarea
                            label="Subject"
                            v-model="document.editedItem.subject"
                            :clearable="document.edit"
                            auto-grow
                            rows="2"
                            row-height="15"
                            counter="100"
                            :disabled="!document.edit"
                            :outlined="document.edit"
                            :dense="document.edit"
                          ></v-textarea>

                          <div v-if="isAdmin">
                            <v-autocomplete
                              v-model="document.editedItem.assignee_id"
                              :items="users"
                              item-text="name"
                              item-value="id"
                              label="Select User"
                              header="Available User"
                              divider
                              :search-input.sync="selectUser"
                              :loading="loadingUser"
                              menu-props="closeOnClick=true, closeOnContentClick,offsetY"
                              :clearable="document.edit"
                              :outlined="document.edit"
                              :dense="document.edit"
                              :disabled="!document.edit"
                            >
                              <template v-slot:item="data">
                                <v-list-item-content>
                                  <v-list-item-title
                                    v-text="data.item.name"
                                  ></v-list-item-title>
                                  <v-list-item-subtitle
                                    v-html="'Org: ' + data.item.org_code"
                                  ></v-list-item-subtitle>
                                </v-list-item-content>
                              </template>
                            </v-autocomplete>
                          </div>
                          <div v-else>
                            <p class="mb-0 text-left caption">Assignee:</p>
                            <p class="mb-0 text-center overline">
                              {{ item.assignee.name }}
                            </p>
                          </div>
                          <div>
                            <p class="mb-0 text-left caption">Created At:</p>
                            <p class="mb-0 text-center overline">
                              {{ document.editedItem.created_at }}
                            </p>
                            <p class="mb-0 text-left caption">Updated At:</p>
                            <p class="mb-0 text-center overline">
                              {{ document.editedItem.updated_at }}
                            </p>
                          </div>
                        </v-card-text>
                      </v-card>
                    </v-col>

                    <!-- EXPANDED REVISION COLUMN -->
                    <v-col xs="9">
                      <v-card outlined class="pa-2">
                        <v-card-title>
                          {{
                            document.revision.add
                              ? 'Add Revision'
                              : 'Revision Lists'
                          }}
                          <v-spacer></v-spacer>
                          <div
                            v-if="
                              belongsToOrAdmin &&
                              item.revision[0].is_closed == true
                            "
                          >
                            <v-tooltip top>
                              <template v-slot:activator="{ on, attrs }">
                                <v-btn
                                  icon
                                  color="primary"
                                  @click="addRevision"
                                  v-bind="attrs"
                                  v-on="on"
                                >
                                  <v-icon v-if="!document.revision.add"
                                    >mdi-plus-circle</v-icon
                                  >
                                  <v-icon v-else>mdi-close-circle</v-icon>
                                </v-btn>
                              </template>
                              {{
                                document.revision.add
                                  ? 'Add Revision'
                                  : 'Cancel'
                              }}
                            </v-tooltip>
                          </div>
                        </v-card-title>
                        <v-card-text>
                          <v-scroll-y-transition>
                            <v-textarea
                              label="Changes"
                              v-model="document.revision.changes"
                              v-if="document.revision.add"
                              auto-grow
                              rows="3"
                              clearable
                              outlined
                              placeholder="Fill changes on FILE!"
                              append-outer-icon="mdi-send"
                              @click:append-outer="postRevision"
                              :rules="[(v) => !!v || 'Fill changes!']"
                              required
                            ></v-textarea>
                          </v-scroll-y-transition>
                          <v-expansion-panels
                            focusable
                            hover
                            flat
                            accordion
                            active-class="grey lighten-5"
                            v-model="document.revision.panel"
                          >
                            <v-expansion-panel
                              v-for="(rev, i) in item.revision"
                              :key="i"
                              @change="getRevision(rev)"
                            >
                              <v-expansion-panel-header
                                disable-icon-rotate
                                ripple
                              >
                                Rev: {{ rev.index }}
                                <template v-slot:actions>
                                  <v-tooltip top>
                                    <template v-slot:activator="{ on, attrs }">
                                      <v-icon
                                        v-bind="attrs"
                                        v-on="on"
                                        :color="
                                          rev.is_closed == 1
                                            ? 'success'
                                            : 'error'
                                        "
                                      >
                                        {{
                                          rev.is_closed == 1
                                            ? 'mdi-check'
                                            : 'mdi-alert-circle'
                                        }}
                                      </v-icon>
                                    </template>
                                    <span>
                                      {{
                                        rev.is_closed == 1 ? 'Closed' : 'Open'
                                      }}
                                    </span>
                                  </v-tooltip>
                                </template>
                              </v-expansion-panel-header>
                              <v-expansion-panel-content>
                                <v-row align="center" class="my-2">
                                  <v-col>
                                    <div>
                                      <p class="mb-0 text-left caption">
                                        Changes:
                                      </p>
                                      <p class="mb-0 text-center overline">
                                        {{ rev.changes }}
                                      </p>
                                      <p class="mb-0 text-left caption">
                                        Created At:
                                      </p>
                                      <p class="mb-0 text-center overline">
                                        {{ rev.created_at }}
                                      </p>
                                      <p class="mb-0 text-left caption">
                                        Updated At:
                                      </p>
                                      <p class="mb-0 text-center overline">
                                        {{ rev.updated_at }}
                                      </p>
                                    </div>
                                  </v-col>
                                  <v-divider vertical></v-divider>
                                  <v-col md="2" class="py-0 px-1 text-center">
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
                                      <v-tooltip top v-if="belongsToOrAdmin">
                                        <template
                                          v-slot:activator="{ on, attrs }"
                                        >
                                          <v-btn
                                            icon
                                            v-bind="attrs"
                                            v-on="on"
                                            color="primary"
                                            @click="
                                              document.revision.changeFile =
                                                !document.revision.changeFile
                                            "
                                          >
                                            <v-icon>
                                              {{
                                                document.revision.changeFile
                                                  ? 'mdi-close-circle'
                                                  : 'mdi-pencil-outline'
                                              }}
                                            </v-icon>
                                          </v-btn>
                                        </template>
                                        <span>
                                          {{
                                            document.revision.changeFile
                                              ? 'Close'
                                              : 'Change File'
                                          }}
                                        </span>
                                      </v-tooltip>
                                      <v-tooltip
                                        top
                                        v-if="
                                          !rev.is_closed && belongsToOrAdmin
                                        "
                                      >
                                        <template
                                          v-slot:activator="{ on, attrs }"
                                        >
                                          <v-btn
                                            icon
                                            v-bind="attrs"
                                            v-on="on"
                                            color="primary"
                                            @click="
                                              ;(document.revision.menu =
                                                !document.revision.menu),
                                                (document.revision.changeFile = false)
                                            "
                                          >
                                            <v-icon>
                                              {{
                                                document.revision.menu
                                                  ? 'mdi-close-circle'
                                                  : 'mdi-alert-circle-check-outline'
                                              }}
                                            </v-icon>
                                          </v-btn>
                                        </template>
                                        <span> Change Status </span>
                                      </v-tooltip>
                                    </div>
                                    <div v-else>
                                      <v-tooltip top v-if="belongsToOrAdmin">
                                        <template
                                          v-slot:activator="{ on, attrs }"
                                        >
                                          <v-btn
                                            icon
                                            v-bind="attrs"
                                            v-on="on"
                                            color="primary"
                                            @click="
                                              document.revision.changeFile =
                                                !document.revision.changeFile
                                            "
                                          >
                                            <v-icon>{{
                                              document.revision.changeFile
                                                ? 'mdi-close-circle'
                                                : 'mdi-plus-circle'
                                            }}</v-icon>
                                          </v-btn>
                                        </template>
                                        <span>
                                          {{
                                            document.revision.changeFile
                                              ? 'Cancel'
                                              : 'Add File'
                                          }}</span
                                        >
                                      </v-tooltip>
                                    </div>
                                  </v-col>
                                </v-row>
                                <v-fade-transition>
                                  <div v-if="document.revision.changeFile">
                                    <v-file-input
                                      v-model="document.revision.file"
                                      truncate-length="10"
                                      label="File"
                                      placeholder="Upload File"
                                      single-line
                                      :append-outer-icon="
                                        document.revision.file
                                          ? 'mdi-file-send-outline'
                                          : 'mdi-close-circle'
                                      "
                                      accept="application/pdf"
                                      small-chips
                                      max-width="20%"
                                      @click:append-outer="
                                        document.revision.file
                                          ? postFile()
                                          : null
                                      "
                                      persistent-hint
                                      hint="must be pdf type"
                                    ></v-file-input>
                                  </div>
                                </v-fade-transition>
                              </v-expansion-panel-content>
                            </v-expansion-panel>
                            <div class="isClosedMenu"></div>
                          </v-expansion-panels>

                          <v-menu
                            v-model="document.revision.menu"
                            offset-y
                            attach=".isClosedMenu"
                            absolute
                            :close-on-content-click="false"
                          >
                            <v-card class="px-4 pt-4">
                              <v-card-title>
                                Status
                                {{
                                  document.revision.is_closed
                                    ? 'Closed'
                                    : 'Open'
                                }}
                              </v-card-title>
                              <v-card-text>
                                <v-switch
                                  v-model="document.revision.is_closed"
                                  :label="
                                    document.revision.is_closed
                                      ? 'Closed'
                                      : 'Open'
                                  "
                                  inset
                                  menu-props="offsetY, closeOnClick"
                                ></v-switch>
                              </v-card-text>
                              <v-card-actions>
                                <v-btn
                                  text
                                  color="primary"
                                  @click="
                                    ;(document.revision.menu = false),
                                      (document.revision.is_closed = false)
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

          <!-- IS CLOSED ICON -->
          <template v-slot:item.is_closed="props">
            <div>
              <v-slide-x-reverse-transition mode="out-in">
                <v-tooltip top>
                  <template v-slot:activator="{ on, attrs }">
                    <v-icon
                      v-bind="attrs"
                      v-on="on"
                      :color="
                        props.item.revision[0].is_closed ? 'success' : 'error '
                      "
                      v-text="
                        props.item.revision[0].is_closed
                          ? 'mdi-check-circle'
                          : 'mdi-alert-circle'
                      "
                    >
                    </v-icon>
                  </template>
                  <span>{{
                    props.item.revision[0].is_closed ? 'Closed' : 'Open '
                  }}</span>
                </v-tooltip>
              </v-slide-x-reverse-transition>
            </div>
          </template>

          <!-- INDEX -->
          <template v-slot:item.index="props">
            {{ props.item.revision[0].index }}
          </template>

          <!-- DELETE ACTION BUTTON-->
          <template v-if="isAdmin" v-slot:item.actions="{ item, header }">
            <div>
              <v-icon @click="deleteItem(item)">mdi-delete</v-icon>
            </div>
          </template>
        </v-data-table>
      </v-card-text>
    </v-card>
  </v-row>
</template>

<script>
import { mapState, mapGetters } from 'vuex'
import excel from 'vue-json-excel'

export default {
  layout: 'dashboard',
  components: { excel },
  data: () => ({
    headers: [
      { text: 'No', value: 'no' },
      { text: 'Office', value: 'office' },
      { text: 'Type', value: 'type' },
      { text: 'Subject', value: 'subject' },
      { text: 'Aircraft', value: 'aircraft.type', align: 'center' },
      { text: 'Index', value: 'index', sortable: false, align: 'center' },
      { text: 'Assignee', value: 'assignee.name' },
      { text: 'From', value: 'reference', width: '5rem' },
      { text: 'Date', value: 'updated_at', width: '5rem' },
      { text: 'Status', value: 'is_closed', align: 'center' },
      { text: 'Actions', value: 'actions', sortable: false, align: 'center' },
    ],

    /* BASIC DATA */
    search: null,
    selectUser: '',
    selectAircraft: '',
    dialog: false,
    today: new Date().toLocaleDateString(),

    /* EXPORT TO EXCEL FIELDS */
    exportFields: {
      'Document ID': 'id',
      No: 'no',
      Office: 'office',
      Type: 'type',
      'Assigned To': 'assignee.name',
      Index: {
        field: 'revision',
        callback: (value) => {
          return `${value[0].index}`
        },
      },
      Subject: 'subject',
      Reference: 'reference',
      Aircraft: {
        field: 'aircraft',
        callback: (value) => {
          return `${value.type} - ${value.reg_code} - ${value.serial_num}  `
        },
      },
      'Closed?': {
        field: 'is_closed',
        callback: (value) => {
          return value == 1 ? 'closed' : 'open'
        },
      },
      Delivery: {
        field: 'kpi_status',
        callback: (value) => {
          return value == 1
            ? 'Too late'
            : value == 2
            ? 'Late'
            : value == 3
            ? 'On Time'
            : null
        },
      },
      'Created at': 'created_at',
      'Updated at': 'updated_at',
    },

    /* DATA FILTER BY*/
    filter: {
      userOnly: false,
      menu: false,
      items: [],
      results: [],
      list: [
        {
          text: 'My Documents',
          value: 'id',
          key: 'assignee_id',
          disabled: false,
        },
        { text: 'Only EO', value: 'EO', key: 'type', disabled: false },
        { text: 'Only JE', value: 'JE', key: 'type', disabled: false },
        { text: 'Only TD', value: 'TD', key: 'type', disabled: false },
        { text: 'Only ES', value: 'ES', key: 'type', disabled: false },
      ],
    },

    /* DATA GROUP BY*/
    group: {
      groupBy: null,
      groupList: [
        { text: 'Office Type', value: 'office' },
        { text: 'Doc Type', value: 'type' },
        { text: 'Aircraft', value: 'aircraft.type' },
        { text: 'Assignee', value: 'assignee.name' },
      ],
    },

    /* AIRCRAFT DATA */
    aircraft: {
      model: {},
      default: {
        type: { value: '', placeholder: 'Ex: CN235', label: 'Aircraft Type' },
        reg_code: {
          value: '',
          placeholder: 'Ex: A2304',
          label: 'Registration Code',
        },
        effectivity: {
          value: '',
          placeholder: 'Ex: B001025',
          label: 'Effectivity',
        },
        serial_num: {
          value: '',
          placeholder: 'Ex: N023',
          label: 'Serial Number',
        },
        owner: {
          value: '',
          placeholder: 'Ex: TNI-AU',
          label: 'Aircraft Owner',
        },
        manuf_date: {
          value: '',
          placeholder: 'Ex: 1993',
          label: 'Manufactured Date',
        },
      },
      form: '',
      edit: false,
      new: false,
      valid: false,
      show: false,
    },

    /* DOCUMENT DATA */
    document: {
      valid: false,
      expand: [],
      form: {
        subject: [],
      },
      revision: {
        id: '',
        panel: '',
        file: null,
        file_id: '',
        changeFile: false,
        changes: '',
        menu: false,
        is_closed: '',
        add: false,
      },
      select: {
        office: ['DOA', 'AMO'],
        type: {
          doa: [
            'EO',
            'TD',
            'JE',
            'ES',
            'CC',
            'PL',
            'CM',
            'MS',
            'MD',
            'CT',
            'TP',
            'DWG',
          ],
          amo: ['EO', 'TD', 'JE', 'ES', 'PDR', 'MDR', 'DWG'],
        },
      },
      moreSubject: false,
      edit: false,
      editedIndex: -1,
      editedItem: {},
      defaultItem: {},
    },
  }),
  mounted() {
    this.docs.length != 0 || this.$store.dispatch('fetch', 'documents')
  },
  watch: {
    selectUser(val) {
      this.users.length != 0 || this.$store.dispatch('fetch', 'users')
    },
    selectAircraft(val) {
      this.aircrafts.length != 0 || this.$store.dispatch('fetch', 'aircrafts')
    },
    dialog(val) {
      //val || this.clearAircraftForm()
      if (val) {
        this.aircraft.model = {}
        this.document.expand.pop()
        this.selectUser = ''
      }
    },
  },
  computed: {
    ...mapState({
      user: (state) => state.auth.user,
      isAdmin: (state) => state.auth.user.is_admin,
      loading: (state) => state.documents.loading,
      loadingUser: (state) => state.users.loading,
      loadingAircraft: (state) => state.aircrafts.loading,
      errors: (state) => state.errors,
      users: (state) => state.users.items,
      aircrafts: (state) => state.aircrafts.items,
      uploading: (state) => state.documents.uploading,
    }),
    ...mapGetters('documents', {
      docs: 'getDocs',
      filterBy: 'filterBy',
    }),
    searchTimeOut: {
      get() {
        return this.search
      },
      set(val) {
        this.$store.commit('documents/loading', true)
        this.debounced(val)
      },
    },
    aircraftDetails() {
      return _.pick(this.aircraft.model, [
        'type',
        'reg_code',
        'effectivity',
        'serial_num',
        'owner',
        'manuf_date',
      ])
    },
    documentType() {
      let from = _.isEmpty(this.document.editedItem)
        ? this.document.form
        : this.document.editedItem
      if (from.office == 'DOA') {
        return this.document.select.type.doa
      } else if (from.office == 'AMO') {
        return this.document.select.type.amo
      } else {
        return []
      }
    },
    belongsToOrAdmin: {
      get() {
        return this.isAdmin
      },
      set(item) {
        return item.assignee.id == this.user.id || this.isAdmin
      },
    },
  },
  methods: {
    debounced: _.debounce(function (val) {
      this.$store.commit('documents/loading', false)
      this.search = val
    }, 1000),
    expand(item) {
      const index = this.document.expand.indexOf(item)
      if (index == 0) {
        this.document.expand.pop()
        this.document.editedItem = {}
      } else {
        this.editItem(item)
        this.clearRevision()
      }
    },
    editItem(item) {
      let index = this.docs.indexOf(item)
      this.belongsToOrAdmin = item
      this.aircraft.model = item.aircraft
      this.document.editedIndex = index
      this.document.expand = [item]
      this.document.editedItem = Object.assign({}, item)
      this.document.defaultItem = Object.assign({}, item)
      this.$store.commit('documents/show', { item, index })
    },
    clearAircraftForm() {
      this.$nextTick(() => {
        this.aircraft.new = false
        this.aircraft.show = false
        this.aircraft.model = this.aircraft.default
      })
    },
    addSubject() {
      this.document.moreSubject = true
      this.document.form.subject.push([])
    },
    removeSubject(index) {
      this.document.form.subject.splice(index, 1)
    },
    deleteItem(item) {
      this.document.expand = []
      this.document.editedItem = {}
      const index = this.docs.indexOf(item)
      swal({
        title: 'Delete ' + `${item.no}` + '?',
        text: 'Are you sure?',
        icon: 'warning',
        buttons: true,
        dangerMode: true,
      }).then((willDelete) => {
        if (willDelete) {
          this.deleteDoc(item)
        }
      })
    },
    addRevision() {
      this.aircraft.edit = !this.aircraft.edit
      this.document.edit = !this.document.edit
      this.document.revision.add = !this.document.revision.add
    },
    getRevision(rev) {
      this.document.revision.file_id = rev.file.id
      this.document.revision.id = rev.id
      this.document.revision.is_closed = rev.is_closed
      this.document.revision.changeFile = false
    },
    postRevision() {
      let documentChanges = []
      _.forOwn(this.document.editedItem, (value, key) => {
        if (this.document.defaultItem[key] != value) {
          documentChanges.push(value)
        }
      })
      this.$store
        .dispatch('post', {
          url: 'documents/revisions/',
          form: {
            changes: this.document.revision.changes,
            document_id: this.document.editedItem.id,
          },
        })
        .then((resp) => {
          console.log(documentChanges.length)
          if (documentChanges.length > 0) {
            this.putDoc()
          }
          this.clearRevision()
        })
    },
    clearRevision() {
      this.document.revision.id = ''
      this.document.revision.file = null
      this.document.revision.file_id = ''
      this.document.revision.changeFile = false
      this.document.revision.changes = ''
      this.document.revision.is_closed = ''
      this.document.revision.panel = -1
      this.document.revision.add = false
      this.aircraft.edit = false
      this.document.edit = false
    },
    postFile() {
      this.$store
        .dispatch('file', {
          url: 'documents/file/',
          form: {
            file: this.document.revision.file,
            id: this.document.revision.file_id,
          },
        })
        .then((res) => {
          this.clearRevision()
        })
    },
    readFile(id) {
      this.$router.push('/documents/' + id)
    },
    postAircraft() {
      this.$store
        .dispatch('post', {
          url: 'aircrafts/',
          form: _.mapValues(this.aircraft.model, 'value'),
        })
        .then((res) => {
          this.$nextTick(() => {
            this.aircraft.new = false
            this.aircraft.show = true
            this.aircraft.model = res.entry
          })
        })
    },
    putStatus() {
      this.$store
        .dispatch('put', {
          url: 'documents/status/',
          form: {
            id: this.document.revision.id,
            is_closed: this.document.revision.is_closed,
          },
        })
        .then((res) => {
          this.document.revision.menu = false
        })
    },
    postDoc() {
      this.$store
        .dispatch('post', {
          url: 'documents/',
          form: {
            aircraft_id: this.aircraft.model.id,
            assignee_id: this.user.id,
            ...this.document.form,
          },
        })
        .then((res) => {
          this.$refs.formDocument.resetValidation()
          this.$refs.formDocument.reset()
          this.dialog = false
        })
    },
    putDoc() {
      this.document.editedItem.aircraft_id = this.aircraft.model.id
      this.$store
        .dispatch('put', {
          url: 'documents/',
          form: this.document.editedItem,
        })
        .catch((err) => {
          console.log(err)
        })
    },
    deleteDoc(item) {
      this.$store
        .dispatch('delete', {
          url: 'documents/',
          form: item,
        })
        .then((res) => {})
    },
    async filterDoc() {
      this.$store.commit('documents/loading', true)
      this.filter.results = []
      let filterMoreThanOne = []
      if (this.filter.userOnly) {
        this.filter.results = await this.filterBy('assignee_id', this.user.id)
      } else {
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
      }
      this.$store.commit('documents/loading', false)
    },
    cancelFilter() {
      this.filter.results = []
      this.filter.items = []
    },
    filterUserOnly(items) {
      this.$nextTick(() => {
        if (this.filter.items.length == 0) {
          this.filter.list.forEach((filter) => {
            _.set(filter, 'disabled', false)
          })
          this.filter.userOnly = false
        } else {
          if (this.filter.items[0].key == 'assignee_id')
            this.filter.userOnly = true
          this.filter.list.forEach((item) => {
            if (this.filter.userOnly) {
              item.key == 'assignee_id'
                ? _.set(item, 'disabled', false)
                : _.set(item, 'disabled', true)
            } else {
              item.key == 'assignee_id'
                ? _.set(item, 'disabled', true)
                : _.set(item, 'disabled', false)
            }
          })
        }
      })
    },
  },
}
</script>

