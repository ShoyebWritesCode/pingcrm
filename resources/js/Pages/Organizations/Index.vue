<template>
  <div>

    <Head title="Organizations" />
    <h1 class="mb-8 text-3xl font-bold">Organizations</h1>
    <div class="flex items-center justify-between mb-6">
      <search-filter v-model="form.search" class="mr-4 w-2/5 max-w-md" @reset="reset">
        <label class="block text-gray-700">Trashed:</label>
        <select v-model="form.trashed" class="form-select mt-1 w-full">
          <option :value="null" />
          <option value="with">With Trashed</option>
          <option value="only">Only Trashed</option>
        </select>
      </search-filter>
      <div>
        <button @click="showModal = true" class="btn-indigo mx-4 px-3 py-2" title="Visible Columns">
          <font-awesome-icon icon="table-cells" />
        </button>
        <div v-if="showModal" class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center z-50">
          <div class="bg-white rounded-lg shadow-lg p-6 w-96">
            <h2 class="text-xl font-bold mb-4">Select Columns</h2>

            <div class="grid grid-cols-2 gap-4">
              <div v-for="column in columns" :key="column.name" class="flex items-center">
                <input type="checkbox" v-model="column.visible" class="mr-2" :disabled="column.disabled" />
                <label>{{ column.label }}</label>
              </div>
            </div>

            <div class="flex justify-end mt-6">
              <button @click="applyChanges" class="btn-green px-4 py-2">Apply</button>
              <button @click="showModal = false" class="ml-4 btn-red px-4 py-2">Cancel</button>
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
            <h2 class="text-xl font-bold mb-2">Preview Data <span class="text-xs">(Up to 100 Rows)</span></h2>
            <hr class="border-t-1 border-gray-300 mb-4">
            <!-- Preview Table -->
            <div class="bg-white rounded-md shadow overflow-x-auto max-h-[60vh]">
              <table class="w-full table-auto border-collapse">
                <thead>
                  <tr>
                    <th v-for="csvColumn in csvColumns" :key="csvColumn" class="border-b font-bold text-left p-2">
                      <span v-if="selectedDbColumns[csvColumn] || matchingColumn(csvColumn)">
                        {{ matchingColumn(csvColumn) ? matchingColumn(csvColumn).name : selectedDbColumns[csvColumn] }}
                      </span>
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(row, rowIndex) in csvData.slice(0, 100)" :key="rowIndex">
                    <td v-for="csvColumn in csvColumns" :key="csvColumn" class="border-b p-2">
                      <span v-if="selectedDbColumns[csvColumn] || matchingColumn(csvColumn)">
                        {{ getValueForColumn(row, csvColumn) !== 'N/A' ? getValueForColumn(row, csvColumn) : '' }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <!-- Buttons -->
            <hr class="border-t-1 border-gray-300 mt-4">
            <div class="flex justify-end mt-2">
              <button @click="goBack" class="btn-yellow px-4 py-2 mr-auto">Back</button>
              <button @click="handleCancel" class="ml-4 btn-red px-4 py-2">Cancel</button>
              <button @click="applyPreviewChanges" class="btn-green px-4 py-2 ml-4">Import</button>
            </div>
          </div>
        </div>


        <button @click="downloadCSV" class="btn-indigo  px-3 py-2" title="Export as CSV">
          <font-awesome-icon icon="file-export" />
        </button>

        <Link class="btn-indigo mx-4  px-3 py-2" href="/organizations/create" title="Create Organization">
        <font-awesome-icon icon="plus" />
        </Link>
      </div>
    </div>
    <div class="bg-white rounded-md shadow overflow-x-auto max-h-96">
      <table class="w-full whitespace-nowrap">
        <thead class="bg-white sticky top-0 z-10">
          <draggable v-model="columns" tag="tr" class="cursor-pointer text-left font-bold text-sm hover:cursor-grab">
            <template #item="{ element: column }">
              <th v-if="isVisible(column.name)" class="pb-4 pt-6 pl-3 sticky top-0 z-10">
                <font-awesome-icon icon="grip-vertical" />
                {{ column.label }}
              </th>
            </template>
          </draggable>
        </thead>
        <tbody>
          <tr v-for="organization in organizations.data" :key="organization.id"
            class="hover:bg-gray-100 focus-within:bg-gray-100 text-sm">
            <td v-for="column in columns" :key="column.name" class="border-t">
              <Link class="flex items-center pl-3 py-4 focus:text-indigo-500" v-if="isVisible(column.name)"
                :href="`/organizations/${organization.id}/edit`">
              {{ organization[column.name] }}
              <icon v-if="organization.deleted_at" name="trash" class="shrink-0 ml-2 w-3 h-3 fill-gray-400" />
              </Link>
            </td>
            <td class="border-t">
              <Link class="flex items-center px-4" :href="`/organizations/${organization.id}/edit`" tabindex="-1">
              <icon name="cheveron-right" class="block w-6 h-6 fill-gray-400" />
              </Link>
            </td>
          </tr>
          <tr v-if="organizations.data.length === 0">
            <td class="px-6 py-4 border-t" colspan="4">No organizations found.</td>
          </tr>
        </tbody>
      </table>
    </div>
    <pagination class="mt-6" :links="organizations.links" />
  </div>
</template>

<script>
import { Head, Link, } from '@inertiajs/vue3'
import Icon from '@/Shared/Icon.vue'
import pickBy from 'lodash/pickBy'
import Layout from '@/Shared/Layout.vue'
import throttle from 'lodash/throttle'
import mapValues from 'lodash/mapValues'
import Pagination from '@/Shared/Pagination.vue'
import SearchFilter from '@/Shared/SearchFilter.vue'
import draggable from 'vuedraggable'
import Papa from 'papaparse'
import { data } from 'autoprefixer'

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
    organizations: Object,
    visibleColumns: Array,
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
      columns: [
        { name: 'name', label: 'Name', visible: this.visibleColumns.includes('name') || true, disabled: true },
        { name: 'phone', label: 'Phone', visible: this.visibleColumns.includes('phone') || true, disabled: true },
        { name: 'email', label: 'Email', visible: this.visibleColumns.includes('email') || true, disabled: true },
        { name: 'address', label: 'Address', visible: this.visibleColumns.includes('address') },
        { name: 'city', label: 'City', visible: this.visibleColumns.includes('city') || true, disabled: true },
        { name: 'region', label: 'Region', visible: this.visibleColumns.includes('region') },
        { name: 'country', label: 'Country', visible: this.visibleColumns.includes('country') },
        { name: 'postal_code', label: 'Postal Code', visible: this.visibleColumns.includes('postal_code') },
      ],
      csvColumns: [],
      selectedDbColumns: {},
      csvData: []
    }
  },
  watch: {
    form: {
      deep: true,
      handler: throttle(function () {
        this.$inertia.get('/organizations', pickBy(this.form), { preserveState: true })
      }, 150),
    },
  },
  methods: {
    reset() {
      this.form = mapValues(this.form, () => null)
    },
    isVisible(columnName) {
      return this.columns.find(column => column.name === columnName).visible
    },
    applyChanges() {
      this.showModal = false;

      const selectedColumns = this.columns
        .filter(column => column.visible)
        .map(column => column.name);


      this.$inertia.post('/organizations/column', {
        columns: selectedColumns,
      });
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

      this.$inertia.post('/organizations/import-csv', { data: dataToInsert }, {
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
      window.location.reload();
    },


    //export as csv
    convertToCSV(data, columns) {
      const header = columns
        .filter(column => this.isVisible(column.name))
        .map(column => column.label)
        .join(',');

      const rows = data.map(row =>
        columns
          .filter(column => this.isVisible(column.name))
          .map(column => `"${row[column.name] || ''}"`)
          .join(',')
      );

      return [header, ...rows].join('\n');
    },

    downloadCSV() {
      const csv = this.convertToCSV(this.organizations.data, this.columns);
      const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
      const link = document.createElement('a');

      if (link.download !== undefined) {
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', 'organizations.csv');
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
      }
    },


  },

}
</script>
