<template>
  <div>
    <div class="mb-4">
      <Link class="group flex items-center py-3" href="/">
      <icon name="dashboard" class="mr-2 w-4 h-4"
        :class="isUrl('') ? 'fill-white' : 'fill-indigo-400 group-hover:fill-white'" />
      <div :class="isUrl('') ? 'text-white' : 'text-indigo-300 group-hover:text-white'">Dashboard</div>
      </Link>
    </div>
    <div class="mb-4">
      <Link class="group flex items-center py-3" href="/organizations">
      <icon name="office" class="mr-2 w-4 h-4"
        :class="isUrl('organizations') ? 'fill-white' : 'fill-indigo-400 group-hover:fill-white'" />
      <div :class="isUrl('organizations') ? 'text-white' : 'text-indigo-300 group-hover:text-white'">Organizations</div>
      </Link>
    </div>
    <div class="mb-4">
      <Link class="group flex items-center py-3" :href="`/contacts/home`">
      <icon name="users" class="mr-2 w-4 h-4"
        :class="isUrl('contacts') ? 'fill-white' : 'fill-indigo-400 group-hover:fill-white'" />
      <div :class="isUrl('contacts') ? 'text-white' : 'text-indigo-300 group-hover:text-white'">Contacts</div>
      </Link>
    </div>
    <div class="mb-4">
      <Link class="group flex items-center py-3" href="/reports">
      <icon name="printer" class="mr-2 w-4 h-4"
        :class="isUrl('reports') ? 'fill-white' : 'fill-indigo-400 group-hover:fill-white'" />
      <div :class="isUrl('reports') ? 'text-white' : 'text-indigo-300 group-hover:text-white'">Reports</div>
      </Link>
    </div>
    <div class="mb-4">
      <Link class="group flex items-center py-3" href="/products">
      <icon name="product" class="mr-2 w-4 h-4"
        :class="isUrl('Products') ? 'fill-white' : 'fill-indigo-400 group-hover:fill-white'" />
      <div :class="isUrl('products') ? 'text-white' : 'text-indigo-300 group-hover:text-white'">Products</div>
      </Link>
    </div>
    <div class="mb-4">
      <Link class="group flex items-center py-3" href="/invoices">
      <icon name="invoice" class="mr-2 w-4 h-4"
        :class="isUrl('Invoices') ? 'fill-white' : 'fill-indigo-400 group-hover:fill-white'" />
      <div :class="isUrl('invoices') ? 'text-white' : 'text-indigo-300 group-hover:text-white'">Invoices</div>
      </Link>
    </div>

  </div>
</template>

<script>
import { Link } from '@inertiajs/vue3'
import Icon from '@/Shared/Icon.vue'

export default {
  components: {
    Icon,
    Link,
  },
  data() {
    return {
      param: '',
      storedHash: '',
    };
  },
  mounted() {
    let storedHash = window.sessionStorage.getItem('selectedhash');
    console.log('Stored selectedhash on this page:', storedHash);
    //if doesnot exist, set it to #today
    if (!storedHash) {
      window.sessionStorage.setItem('selectedhash', '#today');
    }
    this.storedHash = storedHash;
    this.param = storedHash.replace('#', '');

  },
  methods: {
    isUrl(...urls) {
      let currentUrl = this.$page.url.substr(1)
      if (urls[0] === '') {
        return currentUrl === ''
      }
      return urls.filter((url) => currentUrl.startsWith(url)).length
    },
  },
}
</script>
