<script setup>
import { computed, onMounted, watch } from 'vue';

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

function urlForPage(page) {
  const baseUrl = window.location.origin + window.location.pathname;
  return `${baseUrl}?page=${page}`;
}

const meta = computed(() => props.data);
console.log('Pagination data prop:', props.data);

const validLinks = computed(() => {
  if (!props.data || !props.data.links) return [];
  return props.data.links.map((link) => ({
    url: link.url,
    label: link.label,
    active: link.active,
    page: extractPageNumber(link.url),
  }));
});

function extractPageNumber(url) {
  if (!url) return null;
  const match = url.match(/page=(\d+)/);
  return match ? Number(match[1]) : 1;
}

function goToPage(link) {
  if (!link.active) {
    // Panggil callback updatedPageNumber dari parent dengan nomor halaman
    props.updatedPageNumber(link.page);
  }
}
onMounted(() => {
  console.log('🧪 props.updatedPageNumber:', typeof props.updatedPageNumber);
});

watch(
  () => props.data,
  (newVal) => {
    console.log('Pagination8 data updated:', newVal);
  },
  { immediate: true }
);
</script>

<template>
  <div v-if="validLinks && validLinks.length" class="max-w-7xl mx-auto py-6">
    <div class="bg-white overflow-hidden shadow sm:rounded-lg">
      <div
        class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6"
      >
        <div class="mr-10">
          <p class="text-sm text-gray-700">
            Showing
            <span class="font-medium">{{ props.data.from }}</span>
            to
            <span class="font-medium">{{ props.data.to }}</span>
            of
            <span class="font-medium">{{ props.data.total }}</span>
            results
          </p>
        </div>
        <div>
          <nav
            class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px"
            aria-label="Pagination"
          >
            <button
              v-for="(link, index) in validLinks"
              :key="index"
              @click.prevent="goToPage(link)"
              :disabled="link.active || !link.url"
              class="relative inline-flex items-center px-4 py-2 border text-sm font-medium"
              :class="{
                'z-10 bg-indigo-50 border-indigo-500 text-indigo-600':
                  link.active,
                'bg-white border-gray-300 text-gray-500 hover:bg-gray-50':
                  !link.active,
                'cursor-not-allowed': !link.url,
              }"
            >
              <span v-html="link.label"></span>
            </button>
          </nav>
        </div>
      </div>
    </div>
  </div>

  <!-- fallback jika tidak ada data -->
  <div v-else class="py-6 text-center text-gray-500">
    Tidak ada data untuk ditampilkan.
  </div>
</template>
