<script setup>
import { computed, onMounted, watch, toRaw, ref } from 'vue';

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

const data = props.data;
const paginationData = ref(null);
console.log(paginationData.value);

// Debug helper
function debugPaginationData(data) {
  const unwrapped = data?.value ?? data;

  // Ambil isi dari data.data jika ada
  const pagination = unwrapped?.data;

  if (
    !pagination ||
    !Array.isArray(pagination.links) ||
    !('current_page' in pagination)
  ) {
    console.warn('⚠️ Data pagination tidak lengkap:', unwrapped);
  } else {
    console.log('📦 Pagination data:', toRaw(pagination));
    console.log('📦 current_page:', pagination.current_page);
    console.log('📦 links:', pagination.links);
  }
}

// Extract nomor halaman dari URL
function extractPageNumber(url) {
  if (!url) return null;
  const match = url.match(/page=(\d+)/);
  return match ? Number(match[1]) : 1;
}

// Links valid
const validLinks = computed(() => {
  if (!data || !Array.isArray(data.links)) return [];
  return data.links.map((link) => ({
    ...link,
    page: extractPageNumber(link.url),
  }));
});

// Navigasi ke halaman lain
function goToPage(link) {
  if (!link.active && link.url && typeof link.page === 'number') {
    props.updatedPageNumber(link.page);
  } else {
    console.warn(
      '⚠️ Tidak bisa berpindah halaman karena link.page tidak valid:',
      link
    );
  }
}

onMounted(() => {
  debugPaginationData(data);
  console.log('🧪 updatedPageNumber type:', typeof props.updatedPageNumber);
});

watch(
  () => props.data,
  (val) => {
    console.log('📦 Pagination updated:', val);
    paginationData.value = val; // hanya ini yang diperlukan
    debugPaginationData(val); // tambahan debugging
  },
  { immediate: true }
);
</script>

<template>
  <div
    v-if="paginationData && Array.isArray(paginationData.links)"
    class="max-w-7xl mx-auto py-6"
  >
    <div class="bg-white overflow-hidden shadow sm:rounded-lg">
      <div
        class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6"
      >
        <!-- Info jumlah data -->
        <div
          class="mr-10"
          v-if="
            paginationData.from !== undefined &&
            paginationData.to !== undefined &&
            paginationData.total !== undefined
          "
        >
          <p class="text-sm text-gray-700">
            Showing
            <span class="font-medium">{{ data.from }}</span>
            to
            <span class="font-medium">{{ data.to }}</span>
            of
            <span class="font-medium">{{ data.total }}</span>
            results
          </p>
        </div>

        <!-- Tombol navigasi -->
        <div>
          <nav
            class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px"
            aria-label="Pagination"
          >
            <button
              v-for="(link, index) in validLinks"
              :key="index"
              @click.prevent="goToPage(link)"
              :disabled="!link.url"
              class="relative inline-flex items-center px-4 py-2 border text-sm font-medium transition"
              :class="{
                'z-10 bg-indigo-50 border-indigo-500 text-indigo-600':
                  link.active,
                'bg-white border-gray-300 text-gray-500 hover:bg-gray-50':
                  !link.active && link.url,
                'cursor-not-allowed opacity-50': !link.url,
              }"
            >
              <span v-html="link.label" />
            </button>
          </nav>
        </div>
      </div>
    </div>
  </div>

  <!-- Jika tidak ada data -->
  <div v-else class="py-6 text-center text-gray-500">
    Tidak ada data untuk ditampilkan.
  </div>
</template>
