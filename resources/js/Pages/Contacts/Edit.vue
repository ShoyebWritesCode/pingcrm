<template>
  <div>

    <Head :title="`${form.first_name} ${form.last_name}`" />
    <h1 class="mb-8 text-3xl font-bold">
      <Link class="text-indigo-400 hover:text-indigo-600" href="/contacts">Contacts</Link>
      <span class="text-indigo-400 font-medium">/</span>
      {{ form.first_name }} {{ form.last_name }}
    </h1>
    <trashed-message v-if="contact.deleted_at" class="mb-6" @restore="restore"> This contact has been deleted.
    </trashed-message>
    <div class="max-w-3xl bg-white rounded-md shadow overflow-hidden">
      <form @submit.prevent="update">
        <div class="flex flex-wrap -mb-8 -mr-6 p-8">
          <text-input v-model="form.first_name" :error="form.errors.first_name" class="pb-8 pr-6 w-full lg:w-1/2"
            label="First name" />
          <text-input v-model="form.last_name" :error="form.errors.last_name" class="pb-8 pr-6 w-full lg:w-1/2"
            label="Last name" />
          <select-input v-model="form.organization_id" :error="form.errors.organization_id"
            class="pb-8 pr-6 w-full lg:w-1/2" label="Organization">
            <option :value="null" />
            <option v-for="organization in organizations" :key="organization.id" :value="organization.id">{{
              organization.name }}</option>
          </select-input>
          <text-input v-model="form.email" :error="form.errors.email" class="pb-8 pr-6 w-full lg:w-1/2" label="Email" />
          <text-input v-model="form.phone" :error="form.errors.phone" class="pb-8 pr-6 w-full lg:w-1/2" label="Phone" />
          <text-input v-model="form.address" :error="form.errors.address" class="pb-8 pr-6 w-full lg:w-1/2"
            label="Address" />
          <text-input v-model="form.city" :error="form.errors.city" class="pb-8 pr-6 w-full lg:w-1/2" label="City" />
          <text-input v-model="form.region" :error="form.errors.region" class="pb-8 pr-6 w-full lg:w-1/2"
            label="Province/State" />
          <select-input v-model="form.country" :error="form.errors.country" class="pb-8 pr-6 w-full lg:w-1/2"
            label="Country">
            <option :value="null" />
            <option value="CA">Canada</option>
            <option value="US">United States</option>
          </select-input>
          <text-input v-model="form.postal_code" :error="form.errors.postal_code" class="pb-8 pr-6 w-full lg:w-1/2"
            label="Postal code" />
          <div v-for="(column, index) in columns" :key="index" class="pb-8 pr-6 w-full lg:w-1/2">
            <div v-if="column.type === 'date'" class="mt-2">
              <label :for="'input-' + index" class="block text-md text-gray-700">{{ column.name }}:</label>
              <VueDatePicker v-model="columns[index].value" :id="'input-' + index"
                @input="updateCustomColumn(index, $event)" :enable-time-picker="false" format="dd-MM-yyyy" />
            </div>
            <div v-else>
              <text-input v-model="columns[index].value" :label="column.name" :id="'input-' + index"
                @input="updateCustomColumn(index, $event)" />
            </div>
          </div>

        </div>
        <div class="flex items-center px-8 py-4 bg-gray-50 border-t border-gray-100">
          <button v-if="!contact.deleted_at" class="text-red-600 hover:underline" tabindex="-1" type="button"
            @click="destroy">Delete Contact</button>
          <loading-button :loading="form.processing" class="btn-indigo ml-auto" type="submit">Update
            Contact</loading-button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { Head, Link } from '@inertiajs/vue3'
import Layout from '@/Shared/Layout.vue'
import TextInput from '@/Shared/TextInput.vue'
import SelectInput from '@/Shared/SelectInput.vue'
import LoadingButton from '@/Shared/LoadingButton.vue'
import TrashedMessage from '@/Shared/TrashedMessage.vue'
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';

export default {
  components: {
    Head,
    Link,
    LoadingButton,
    SelectInput,
    TextInput,
    TrashedMessage,
    VueDatePicker,
  },
  layout: Layout,
  props: {
    contact: Object,
    organizations: Array,
    customColumns: Array,
    customData: {
      type: Array,
      default: () => [],  // Ensure it's always an array
    },
  },
  remember: 'form',
  data() {
    return {
      form: this.$inertia.form({
        first_name: this.contact.first_name,
        last_name: this.contact.last_name,
        organization_id: this.contact.organization_id,
        email: this.contact.email,
        phone: this.contact.phone,
        address: this.contact.address,
        city: this.contact.city,
        region: this.contact.region,
        country: this.contact.country,
        postal_code: this.contact.postal_code,
      }),
      columns: [],
    }
  },
  mounted() {
    this.initializeCustomColumns();
  },
  methods: {
    initializeCustomColumns() {
      this.columns = this.customColumns.map(column => {
        const data = this.customData.find(item => item.column_id === column.id);
        return {
          ...column,
          value: data ? data.value : ''
        };
      });
    },

    updateCustomColumn(index, event) {
      // Handle update based on input type
      const newValue = event.target.value;
      this.columns[index].value = newValue;
    },

    getCustomColumns() {
      return this.columns.map(column => {
        return {
          name: column.name,
          value: column.value,
          id: column.id
        };
      });
    },
    update() {
      this.form.put(`/contacts/${this.contact.id}`)
      const input = this.getCustomColumns()
      console.log(input)
      this.$inertia.put(`/contacts/${this.contact.id}/custom-columns`, {
        columns: input,
      })
    },
    destroy() {
      if (confirm('Are you sure you want to delete this contact?')) {
        this.$inertia.delete(`/contacts/${this.contact.id}`)
      }
    },
    restore() {
      if (confirm('Are you sure you want to restore this contact?')) {
        this.$inertia.put(`/contacts/${this.contact.id}/restore`)
      }
    },
  },
}
</script>
