<script setup>
import { router } from '@inertiajs/vue3';
const props = defineProps({
  data: {
    type: Object,
    required: true,
  },
  updatedPageNumber: {
    type: Function,
    required: true,
  },
});

// Mengakses data dan meta
const meta = props.data.meta; // Mengakses meta
const items = props.data.items;
console.log('Meta:', meta);
console.log('Items:', items);
console.log('Data:', props.data);
console.log('Links:', props.data.meta.links);

const checkButtonStatus = (label) => {
  let button;

  if (label === 'First')
    button = props.data.meta.links[1]; // First setelah tombol Previous
  else if (label === 'Previous')
    button = props.data.meta.links[0]; // Previous selalu ada di index pertama
  else if (label === 'Next')
    button = props.data.meta.links[props.data.meta.links.length - 1];
  // Next selalu di index terakhir
  else if (label === 'Last')
    button = props.data.meta.links[props.data.meta.links.length - 2]; // Last sebelum Next

  console.log(
    `${label} Button:`,
    button ? (button.url ? 'Visible' : 'Disabled') : 'Not Found'
  );
};

checkButtonStatus('First');
checkButtonStatus('Previous');
checkButtonStatus('Next');
checkButtonStatus('Last');
</script>

<template>
  <div class="max-w-7xl mx-auto py-6">
    <div class="max-w-none mx-auto">
      <div class="bg-white overflow-hidden shadow sm:rounded-lg">
        <div
          class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6"
        >
          <div class="mr-10">
            <p class="text-sm text-gray-700">
              Showing
              <span class="font-medium">{{ data.meta.current_page }}</span>
              to
              <span class="font-medium">{{
                Math.min(meta.current_page * meta.per_page, meta.total)
              }}</span>
              of
              <span class="font-medium">{{ data.meta.total }}</span>
              results
            </p>
          </div>
          <div>
            <nav
              class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px"
              aria-label="Pagination"
            >
              <!-- Tombol First -->
              <button
                v-if="data.meta.current_page > 1"
                @click.prevent="
                  updatedPageNumber({
                    url: `${data.meta.path}?page=1`,
                  })
                "
                class="relative inline-flex items-center px-4 py-2 border text-sm font-medium bg-white border-gray-300 text-gray-500 hover:bg-gray-50"
              >
                First
              </button>

              <!-- Tombol Previous -->
              <button
                v-if="
                  data.meta.links.some(
                    (link) => link.label.includes('Previous') && link.url
                  )
                "
                @click.prevent="
                  updatedPageNumber(
                    data.meta.links.find((link) =>
                      link.label.includes('Previous')
                    )
                  )
                "
                class="relative inline-flex items-center px-4 py-2 border text-sm font-medium bg-white border-gray-300 text-gray-500 hover:bg-gray-50"
                :disabled="
                  !data.meta.links.find((link) =>
                    link.label.includes('Previous')
                  )?.url
                "
              >
                Previous
              </button>

              <!-- Tombol Halaman -->
              <button
                v-for="(link, index) in data.meta.links.filter(
                  (link) => !isNaN(link.label) && link.url
                )"
                :key="'page-' + index"
                @click.prevent="updatedPageNumber(link)"
                :disabled="link.active"
                class="relative inline-flex items-center px-4 py-2 border text-sm font-medium"
                :class="{
                  'z-10 bg-indigo-50 border-indigo-500 text-indigo-600':
                    link.active,
                  'bg-white border-gray-300 text-gray-500 hover:bg-gray-50':
                    !link.active,
                  'cursor-not-allowed': !link.url,
                }"
              >
                <span v-if="link.label" v-html="link.label"></span>
              </button>

              <!-- Tombol Next -->
              <button
                v-if="
                  data.meta.links.some(
                    (link) => link.label.includes('Next') && link.url
                  )
                "
                @click.prevent="
                  updatedPageNumber(
                    data.meta.links.find((link) => link.label.includes('Next'))
                  )
                "
                :disabled="
                  !data.meta.links.find((link) => link.label.includes('Next'))
                    ?.url
                "
                class="relative inline-flex items-center px-4 py-2 border text-sm font-medium bg-white border-gray-300 text-gray-500 hover:bg-gray-50"
              >
                Next
              </button>

              <!-- Tombol Last -->
              <button
                v-if="data.meta.current_page < data.meta.last_page"
                @click.prevent="
                  updatedPageNumber({
                    url: `${data.meta.path}?page=${data.meta.last_page}`,
                  })
                "
                class="relative inline-flex items-center px-4 py-2 border text-sm font-medium bg-white border-gray-300 text-gray-500 hover:bg-gray-50"
              >
                Last
              </button>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
