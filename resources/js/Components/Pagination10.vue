<script setup>
import { computed, onMounted, watch } from 'vue';

// 👇 Ambil props
const props = defineProps({
  data: {
    type: [Object, null],
    required: true,
  },
  updatedPageNumber: {
    type: Function,
    required: true,
  },
});

// 👇 Tambahkan ini supaya bisa pakai langsung di template
const data = props.data;

function debugPaginationData(data) {
  const unwrapped = data?.value ?? data;

  if (
    !unwrapped ||
    !Array.isArray(unwrapped.links) ||
    !('current_page' in unwrapped)
  ) {
    console.warn('⚠️ Data pagination tidak lengkap:', unwrapped);
  } else {
    console.log('📦 Pagination data:', unwrapped);
    console.log('📦 Pagination current_page:', unwrapped.current_page);
    console.log('📦 Pagination links:', unwrapped.links);
  }
}

// 👇 Extract nomor page dari URL
function extractPageNumber(url) {
  if (!url) return null;
  const match = url.match(/page=(\d+)/);
  return match ? Number(match[1]) : 1;
}

// 👇 Link valid dari props.data.links
const validLinks = computed(() => {
  if (!props.data || !props.data.links) return [];
  return props.data.links.map((link) => ({
    url: link.url,
    label: link.label,
    active: link.active,
    page: extractPageNumber(link.url),
  }));
});

console.log('🔗 Valid links:', validLinks.value);

// 👇 Fungsi ketika tombol pagination diklik
function goToPage(link) {
  if (!link.active && link.url) {
    props.updatedPageNumber(link.page);
  }
}

onMounted(() => {
  debugPaginationData(props.data);
  console.log(
    '🧪 Pagination mounted, updatedPageNumber type:',
    typeof props.updatedPageNumber
  );
});

watch(
  () => props.data,
  (val) => {
    console.log('📦 Pagination data changed:', val);
    console.log('🔗 Links dari props.data:', val?.links);
    console.log('🔗 Valid links (dari computed):', validLinks.value);
  },
  { immediate: true }
);
</script>

<template>
  <div v-if="data && Array.isArray(data.links)" class="max-w-7xl mx-auto py-6">
    <div class="bg-white overflow-hidden shadow sm:rounded-lg">
      <div
        class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6"
      >
        <!-- Info jumlah data -->
        <div class="mr-10">
          <p class="text-sm text-gray-700">
            Showing
            <span class="font-medium">{{ data.meta.from }}</span>
            to
            <span class="font-medium">{{ data.meta.to }}</span>
            of
            <span class="font-medium">{{ data.meta.total }}</span>
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
