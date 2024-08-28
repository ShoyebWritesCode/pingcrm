<template>
  <div>

    <Head title="Contacts" />
    <div class="flex items-center justify-between mb-8">
      <h1 class="text-3xl font-bold">Contacts</h1>
      <nav class="flex space-x-8 bg-white py-3 px-6 rounded-full">
        <a v-for="link in links" :key="link.name" :href="link.url" :id="link.id"
          class="text-indigo-600 target:bg-indigo-600 target:text-white px-3 py-2 rounded-full">
          {{ link.name }}
        </a>

      </nav>
    </div>
    <div class="flex items-center justify-between mb-6">
      <search-filter v-model="form.search" class="mr-4 w-2/5 max-w-md" @reset="reset">
        <label class="block text-gray-700">Trashed:</label>
        <select v-model="form.trashed" class="form-select mt-1 w-full">
          <option :value="null" />
          <option value="with">With Trashed</option>
          <option value="only">Only Trashed</option>
        </select>
      </search-filter>
      <button @click="toggleSelectAll" class="btn-yellow mx-4 px-3 py-2 mr-auto" title="Select All">
        {{ allSelected ? 'Unselect All' : 'Select All' }}
      </button>
      <p v-if="deleteButtonVisibility" class="text-xs text-gray-400">{{ countSelected }} of {{ totalContacts }} contacts
        selected</p>
      <div>
        <button v-if="deleteButtonVisibility" @click="showConfirmation = true" class="btn-red mx-4 px-3 py-2"
          title="Delete Selected">
          <font-awesome-icon icon="trash" />
        </button>

        <div v-if="showConfirmation" tabindex="-1"
          class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center z-50"
          @click.self="hideConfirmation">

          <div class="relative p-4 w-full max-w-md max-h-full">
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
              <button type="button"
                class="absolute top-3 end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
                @click="hideConfirmation">
                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                  viewBox="0 0 14 14">
                  <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                </svg>
                <span class="sr-only">Close modal</span>
              </button>
              <div class="p-4 md:p-5 text-center">
                <svg class="mx-auto mb-4 text-gray-400 w-12 h-12 dark:text-gray-200" aria-hidden="true"
                  xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                  <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">Are you sure you want to delete
                  these contacts?</h3>
                <button @click="deleteSelected" type="button"
                  class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center">
                  Yes, I'm sure
                </button>
                <button @click="hideConfirmation" type="button"
                  class="py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">
                  No, cancel
                </button>
              </div>
            </div>
          </div>
        </div>

        <button @click="showModal = true" class="btn-indigo mx-4 px-3 py-2" title="Visible Columns">
          <font-awesome-icon icon="table-cells" />
        </button>
        <div v-if="showModal" class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center z-50">
          <div class="bg-white rounded-lg shadow-lg p-6 w-96">
            <div class="flex justify-between items-center mb-4">
              <h2 class="text-xl font-bold">Select Columns</h2>
              <button @click="handleColumnManager" class="btn-yellow p-2 text-xs">
                Column Manager
              </button>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div v-for="column in columns" :key="column.name" class="flex items-center">
                <input type="checkbox" v-model="column.visible" class="mr-2" :disabled="column.disabled" checked />
                <label>{{ column.label }}</label>
              </div>
            </div>

            <div class="flex justify-end mt-6">
              <button @click="applyChangesDummy" class="btn-green px-4 py-2">Apply</button>
              <button @click="showModal = false" class="ml-4 btn-red px-4 py-2">Cancel</button>
            </div>
          </div>
        </div>


        <!-- <button @click="showModal = true" class="btn-indigo mx-4 px-3 py-2" title="Add New Columns">
          <font-awesome-icon icon="table-cells" />
        </button> -->
        <div v-if="showModalNewColumn"
          class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center z-50">
          <div class="bg-white rounded-lg shadow-lg p-4 w-96">
            <h2 class="text-xl font-bold mb-4">Add New Columns</h2>
            <div class="flex flex-col">
              <div class="flex items-center justify-between">
                <label for="name" class="block text-sm font-medium text-gray-700">Column Name</label>
                <input type="text" id="name"
                  class="mt-1 block w-1/2 py-1  px-2 text-base border-2 border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" />
              </div>
              <div class="flex items-center justify-between mt-4">
                <label for="type" class="block text-sm font-medium text-gray-700">Data Type</label>
                <select id="type"
                  class="mt-1 block w-1/2 py-1 border-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                  <option>string</option>
                  <option>number</option>
                  <option>date</option>
                  <option>boolean</option>
                </select>
              </div>
            </div>
            <div class="flex justify-end mt-6">
              <button @click="applyChanges" class="btn-green px-4 py-2">Apply</button>
              <button @click="showModalNewColumn = false" class="ml-4 btn-red px-4 py-2">Cancel</button>
            </div>
          </div>
        </div>

        <div v-if="showModalColumn"
          class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center z-50">
          <div class="bg-white rounded-lg shadow-lg p-6 w-96">
            <div class="flex justify-between items-center mb-4">
              <h2 class="text-xl font-bold">Column Manager</h2>
              <button @click="showModalNewColumnManager" class="btn-yellow p-2 text-xs">
                Add New
              </button>
            </div>
            <div v-for="column in additionalColumns" :key="column.id"
              class="mb-0 flex items-center justify-between border-[1px]">
              <span class="px-4 py-4">{{ column.name }}</span>
              <div class="flex space-x-2 mx-4">
                <button @click="renameColumn(column.id)" class="bg-transparent px-2 py-3">
                  <font-awesome-icon icon="edit" class="text-yellow-500" />
                </button>
                <button @click="deleteColumn(column.id)" class="btn-transparent px-2 py-2">
                  <font-awesome-icon icon="trash" class="text-red-500" />
                </button>
              </div>
            </div>
            <div v-for="column in columns" :key="column.name" class="flex items-center justify-between border-[1px]">
              <label class="px-4">{{ column.label }}</label>
              <div class="flex space-x-2 px-2 py-2">
                <button class="bg-transparent text-gray-600 border-2 py-1 px-3 rounded-md text-xs mx-4">Default</button>
              </div>
            </div>



            <div class="flex justify-end mt-6">
              <!-- <button @click="editColumn" class="btn-yellow px-4 py-2 mr-8">Edit </button>
              <button @click="applyChanges" class="btn-green px-4 py-2">Apply</button> -->
              <button @click="showModalColumn = false" class="ml-4 btn-red px-4 py-2">Cancel</button>
            </div>
          </div>
        </div>

        <div v-if="editColumnModal"
          class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center z-50">
          <div class="bg-white rounded-lg shadow-lg p-4 w-72">
            <h2 class="text-xl font-bold mb-4">Edit Columns</h2>
            <div v-for="column in additionalColumns" :key="column.id" class="mb-4 flex items-center justify-between">
              <span>{{ column.name }}</span>
              <div class="flex space-x-2">
                <button @click="renameColumn(column.id)" class="btn-yellow px-4 py-2">Rename</button>
                <button @click="deleteColumn(column.id)" class="btn-red px-4 py-2">Delete</button>
              </div>
            </div>
            <div class="flex justify-end mt-6">
              <button @click="editColumnModal = false" class="btn-red px-4 py-2">Close</button>
            </div>
          </div>
        </div>

        <button class="btn-indigo mr-4  px-3 py-2" title="Import CSV" @click="triggerFileInput">
          <font-awesome-icon icon="file-import" />
        </button>
        <input type="file" ref="fileInput" accept=".csv" @change="handleFileUpload" style="display: none;" />

        <div v-if="showCsvModal" class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center z-50">
          <div
            class="bg-white rounded-lg shadow-lg p-4 w-auto min-w-[60vh] min-h-[45vh] max-h-[90vh] max-w-4xl relative">

            <h2 class="text-xl font-bold mb-2">CSV Columns</h2>
            <hr class="border-t-1 border-gray-300 mb-4">
            <button @click="handleCancel" class="absolute top-2 right-2 bg-transparent px-3 py-3 mr-2" title="Go Back">
              <font-awesome-icon icon="xmark" class="text-black" />
            </button>
            <div class="overflow-y-auto max-h-96">
              <table class="w-full table-auto border-collapse">
                <thead>
                  <tr>
                    <th class="border font-bold text-left p-2">CSV Column</th>
                    <th class="border font-bold text-left p-2">DB Column</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="csvColumn in csvColumns" :key="csvColumn" :class="{
                    'bg-green-500': matchingColumn(csvColumn),
                    'bg-yellow-500': !matchingColumn(csvColumn)
                  }">
                    <td class="border p-2">{{ csvColumn }}</td>
                    <td class="border p-2 w-1/2">
                      <select v-model="selectedDbColumns[csvColumn]"
                        style="width: 120px; background-color:transparent;">
                        <option v-if="matchingColumn(csvColumn)">
                          {{ matchingColumn(csvColumn).name }}
                        </option>
                        <option v-else v-for="dbColumn in availableDbColumns(csvColumn)" :key="dbColumn.name"
                          :value="dbColumn.name">
                          {{ dbColumn.name }}
                        </option>
                      </select>

                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <hr class="border-t-1 border-gray-300 mt-4">
            <div class="flex justify-end mt-2">
              <button @click="handleCancel" class="btn-red px-4 py-2">Cancel</button>
              <button @click="applyCsvChanges" class="ml-4 btn-green px-4 py-2">Continue</button>

            </div>
          </div>
        </div>

        <div v-if="PreviewModal" class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center z-50">
          <div
            class="bg-white rounded-lg shadow-lg p-4 w-auto min-w-[60vh] min-h-[45vh] max-h-[90vh] max-w-4xl relative">
            <!-- Back Button -->
            <button @click="handleCancel" class="absolute top-2 right-2 bg-transparent px-3 py-3 mr-2">
              <font-awesome-icon icon="xmark" class="text-black" />
            </button>
            <h2 class="text-xl font-bold mb-2">Preview Data <span class="text-xs">(Upto 100 Rows)</span></h2>
            <hr class="border-t-1 border-gray-300 mb-4">
            <!-- Preview Table -->
            <div class="bg-white rounded-md shadow overflow-x-auto max-h-64">
              <table class="w-full table-auto border-collapse">
                <thead>
                  <tr>
                    <th v-for="csvColumn in csvColumns" :key="csvColumn" class="border-b font-bold text-left p-2">
                      <span v-if="selectedDbColumns[csvColumn] || matchingColumn(csvColumn)">{{
                        matchingColumn(csvColumn) ? matchingColumn(csvColumn).name : selectedDbColumns[csvColumn]
                      }}</span>
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(row, rowIndex) in csvData.slice(0, 100)" :key="rowIndex">
                    <td v-for="csvColumn in csvColumns" :key="csvColumn" class="border-b p-2">
                      <span v-if="selectedDbColumns[csvColumn] || matchingColumn(csvColumn)">{{
                        getValueForColumn(row, csvColumn) !== 'N/A' ? getValueForColumn(row, csvColumn) : ''
                      }}</span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <!-- Organization Dropdown -->
            <div class="mt-4">
              <label for="organization" class="block text-sm font-medium text-gray-700">Select Organization</label>
              <select id="organization" v-model="selectedOrganization"
                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                <option disabled value="">Please select an organization</option>
                <option v-for="organization in organizations" :key="organization.id" :value="organization.id">
                  {{ organization.name }}
                </option>
              </select>
            </div>
            <!-- Buttons -->
            <hr class="border-t-1 border-gray-300 mt-4">
            <div class="flex justify-end mt-2">
              <button @click="goBack" class="btn-yellow px-4 py-2 mr-auto">
                Back
              </button>
              <button @click="handleCancel" class="ml-4 btn-red px-4 py-2">Cancel</button>
              <button @click="applyPreviewChanges" class="btn-green px-4 py-2 ml-4">Import</button>
            </div>
          </div>
        </div>

        <Link class="btn-indigo   px-3 py-2" href="/contacts/create" title="Create Contact">
        <font-awesome-icon icon="plus" />
        </Link>
      </div>
    </div>
    <div class="bg-white rounded-md shadow overflow-x-auto">
      <table class="w-full whitespace-nowrap ">
        <thead>
          <!-- <tr class="text-left font-bold"> -->
          <!-- <th class="pb-4 pt-6 px-4">
              <input type="checkbox" id="selectAll" @change="toggleSelectAllPage" />
            </th> -->
          <draggable v-model="columns" tag="tr" class="cursor-pointer" :item-key="column => column.name">
            <template #header>
              <th class="pb-4 pt-6 px-8 text-left ">
                <input type="checkbox" id="selectAll" @change="toggleSelectAllPage" />
              </th>
            </template>
            <template #item="{ element: column }">
              <th class="pb-4 pt-6 pl-3 sticky top-0 z-10 text-left">
                <font-awesome-icon icon="grip-vertical" class="text-xs text-gray-600 pb-[1px]" />
                {{ column.label }}
              </th>
            </template>
          </draggable>
          <!-- Additional static columns -->
          <!-- <th v-for="column in additionalColumns" :key="column.id" class="pb-4 pt-6 px-6">
              {{ column.name }}
            </th> -->
          <!-- </tr> -->
        </thead>
        <tbody>
          <tr v-for="contact in selectedContacts" :key="contact.id" class="hover:bg-gray-100 focus-within:bg-gray-100">
            <td class="border-t px-4 py-2">
              <input type="checkbox" :id="'contact-' + contact.id" v-model="contact.selected" class="ml-4" />
            </td>
            <td v-for="column in columns" :key="column.name" class="border-t">
              <Link class="flex items-center pl-3 py-4 focus:text-indigo-500" :href="`/contacts/${contact.id}/edit`">
              <span v-if="column.name == 'organization'">{{ contact.organization.name }}</span>
              <span v-else>{{ contact[column.name] }}</span>
              <span v-if="column.additional"> {{ getValue(column.label, contact) }}</span>
              <icon v-if="contact.deleted_at" name="trash" class="shrink-0 ml-2 w-3 h-3 fill-gray-400" />
              </Link>
            </td>


            <!-- Dynamic columns for additionalColumns -->
            <!-- <td v-for="column in additionalColumns" :key="column.id" class="border-t px-6 py-2">
              <Link class="flex items-center" :href="`/contacts/${contact.id}/edit`" tabindex="-1">
              {{ getValue(column.name, contact) }}
              </Link>
            </td> -->
            <!-- Additional static column -->
            <td class="border-t px-4 py-2 w-px">
              <Link class="flex items-center px-4" :href="`/contacts/${contact.id}/edit`" tabindex="-1">
              <icon name="cheveron-right" class="block w-6 h-6 fill-gray-400" />
              </Link>
            </td>
          </tr>
          <tr v-if="selectedContacts.length === 0">
            <td class="px-6 py-4 border-t" colspan="6">No contacts found.</td>
          </tr>
        </tbody>


      </table>

    </div>
    <pagination class="mt-6" :links="contacts.links" />
  </div>
</template>

<script>
import { Head, Link } from '@inertiajs/vue3'
import Icon from '@/Shared/Icon.vue'
import pickBy from 'lodash/pickBy'
import Layout from '@/Shared/Layout.vue'
import throttle from 'lodash/throttle'
import mapValues from 'lodash/mapValues'
import Pagination from '@/Shared/Pagination.vue'
import SearchFilter from '@/Shared/SearchFilter.vue'
import Papa from 'papaparse'
import draggable from 'vuedraggable'
import { get } from 'lodash'
import { icon } from '@fortawesome/fontawesome-svg-core'

export default {
  components: {
    Head,
    Icon,
    Link,
    Pagination,
    SearchFilter,
    draggable,
  },
  layout: Layout,
  props: {
    filters: Object,
    contacts: Object,
    organizations: Object,
    additionalColumns: Object,
    totalContacts: Number,
  },
  data() {
    return {
      form: {
        search: this.filters.search,
        trashed: this.filters.trashed,
      },
      showModal: false,
      showCsvModal: false,
      PreviewModal: false,
      editColumnModal: false,
      showConfirmation: false,
      allSelected: false,
      showModalColumn: false,
      showModalNewColumn: false,
      // deleteButtonVisibility: false,
      csvData: [],
      csvColumns: [],
      selectedDbColumns: {},
      selectedOrganization: '',
      columns: [
        { name: 'name', label: 'Name', additional: false },
        { name: 'organization', label: 'Organization', additional: false },
        { name: 'city', label: 'City', additional: false },
        { name: 'phone', label: 'Phone', additional: false },
        ...this.additionalColumns.map(column => ({
          name: column.id,
          label: column.name,
          additional: true,
        }))
      ],
      selectedContacts: this.contacts.data.map(contact => ({
        ...contact,
        selected: false,
      })),
      links: [
        { name: 'Today', url: '/contacts?filter=today#today', id: 'today' },
        { name: 'Yesterday', url: '/contacts?filter=yesterday#yesterday', id: 'yesterday' },
        { name: 'Last 7 days', url: '/contacts?filter=last7days#last7days', id: 'last7days' },
        { name: 'Last 30 days', url: '/contacts?filter=last30days#last30days', id: 'last30days' },
        { name: 'Last 90 days', url: '/contacts?filter=last90days#last90days', id: 'last90days' }
      ],
    }
  },
  computed: {
    deleteButtonVisibility() {
      return this.allSelected || this.selectedContacts.some(contact => contact.selected);
    },
    countSelected() {
      return this.allSelected ? this.totalContacts : this.selectedContacts.filter(contact => contact.selected).length;
    },
  },
  watch: {
    form: {
      deep: true,
      handler: throttle(function () {
        this.$inertia.get('/contacts', pickBy(this.form), { preserveState: true })
      }, 150),
    },
  },
  methods: {
    deleteSelected() {
      this.showConfirmation = false;

      if (this.allSelected) {
        this.$inertia.get('contacts/delall', {
          preserveScroll: true,
          onSuccess: () => {
            window.location.reload();
          },
        });
      } else {
        const selectedIds = this.selectedContacts
          .filter(contact => contact.selected)
          .map(contact => contact.id);

        if (selectedIds.length > 0) {
          this.$inertia.delete(`contacts/delete/${selectedIds.join(',')}`, {
            preserveScroll: true,
            onSuccess: () => {
              window.location.reload();
            },
          });
        } else {
          alert('No contacts selected.');
        }
      }
    },

    toggleSelectAllPage(event) {
      const isChecked = event.target.checked;
      this.selectedContacts.forEach(contact => {
        contact.selected = isChecked;
      });
      this.deleteButtonVisibility = isChecked;

    },

    editColumn() {
      this.showModal = false,
        this.editColumnModal = true
    },
    reset() {
      this.form = mapValues(this.form, () => null)
    },
    triggerFileInput() {
      this.$refs.fileInput.click();
    },
    handleFileUpload(event) {
      const file = event.target.files[0];
      if (file) {
        Papa.parse(file, {
          header: true,
          complete: (results) => {
            console.log("CSV file contents:", results.meta.fields);
            this.csvColumns = results.meta.fields;
            this.csvData = results.data;
            this.showCsvModal = true
            console.log(this.csvData)
          },
          error: (error) => {
            console.error("Error parsing CSV file:", error);
          }
        });
      }
    },
    matchingColumn(csvColumn) {
      return this.columns.find(col => col.name === csvColumn);
    },
    availableDbColumns(csvColumn) {
      const selected = new Set(Object.values(this.selectedDbColumns));
      return this.columns.filter(col => !selected.has(col.name) || col.name === this.selectedDbColumns[csvColumn]);
    },

    mapCsvToDbColumns() {
      return this.csvData.map(row => {
        let mappedRow = {};
        for (let csvColumn of this.csvColumns) {
          let dbColumn = this.selectedDbColumns[csvColumn];
          if (dbColumn) {
            mappedRow[dbColumn] = row[csvColumn];
          }
        }

        if (this.selectedOrganization) {
          mappedRow.organization_id = this.selectedOrganization;
        }

        return mappedRow;
      });
    },

    applyCsvChanges() {
      const dataToInsert = this.mapCsvToDbColumns();
      console.log(dataToInsert)
      this.showCsvModal = false
      this.PreviewModal = true
    },

    getValueForColumn(row, csvColumn) {
      return row[csvColumn] || 'N/A';
    },

    async applyPreviewChanges() {
      const dataToInsert = this.mapCsvToDbColumns();
      console.log(dataToInsert)

      this.$inertia.post('/contacts/import-csv', { data: dataToInsert }, {
        onSuccess: () => {
          this.PreviewModal = false;
          window.location.reload();
        },
        onError: (error) => {
          console.error("Error occurred while processing data:", error);
        }
      });

    },

    goBack() {
      this.PreviewModal = false;
      this.showCsvModal = true;
    },
    handleCancel() {
      window.location.reload()
    },

    applyChanges() {
      const name = document.getElementById('name').value;
      const type = document.getElementById('type').value;

      this.$inertia.post('/contacts/add-column', { name, type }, {
        onSuccess: () => {
          this.showModal = false;
          window.location.reload();
        },
        onError: (error) => {
          console.error("Error occurred while adding column:", error);
        }
      });

    },

    getValue(columnName, contact) {
      // Ensure additional_data is parsed or initialized
      const additionalData = JSON.parse(contact.additional_data || '{}');

      // Return the value for the specified column name, or an empty string if the key doesn't exist
      return additionalData[columnName] || '';
    },

    deleteColumn(columnId) {
      this.$inertia.delete(`contacts/${columnId}/delete`, {
        onSuccess: () => {
          this.editColumnModal = false;
          window.location.reload();
        },
        onError: (error) => {
          console.error("Error occurred while deleting column:", error);
        }
      });
    },
    hideConfirmation() {
      this.showConfirmation = false;
    },

    toggleSelectAll() {
      if (this.allSelected) {
        this.selectedContacts.forEach(contact => {
          contact.selected = false;
        });
        this.allSelected = false;
        this.deleteButtonVisibility = false;
      } else {
        this.selectedContacts.forEach(contact => {
          contact.selected = true;
        });
        this.allSelected = true;
        this.deleteButtonVisibility = true;
      }
    },

    // applyChanges() {
    //   this.showModal = false;

    //   const selectedColumns = this.columns
    //     .filter(column => column.visible)
    //     .map(column => column.name);


    //   // this.$inertia.post('/contacts/column', {
    //   //   columns: selectedColumns,
    //   // });
    // },
    handleColumnManager() {
      this.showModalColumn = true;
      this.showModal = false;
    },
    showModalNewColumnManager() {
      this.showModalColumn = false;
      this.showModalNewColumn = true;
    },

  },
}
</script>
