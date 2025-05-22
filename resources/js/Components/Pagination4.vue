x
<script setup>
import { computed, watch, ref } from 'vue';
import { router } from '@inertiajs/vue3';

// Props
const props = defineProps({
  data: {
    type: Object,
    required: true,
  },
});

// Meta dan links sebagai computed agar reaktif
const meta = computed(() => props.data?.meta ?? {});
const linksObj = computed(() => props.data?.links ?? {});

// Hitung range data (from - to)
const from = computed(() => {
  if (meta.value.from != null) return meta.value.from;
  if (meta.value.current_page && meta.value.per_page) {
    return (meta.value.current_page - 1) * meta.value.per_page + 1;
  }
  return 0;
});

const to = computed(() => {
  if (meta.value.to != null) return meta.value.to;
  if (meta.value.current_page && meta.value.per_page && meta.value.total) {
    return Math.min(
      meta.value.current_page * meta.value.per_page,
      meta.value.total
    );
  }
  return 0;
});

// Total data
const total = computed(() => meta.value.total ?? 0);

// Buat URL berdasarkan page
function urlForPage(page) {
  const baseUrl = window.location.origin + window.location.pathname;
  return `${baseUrl}?page=${page}`;
}

// Generate valid links
const validLinks = computed(() => {
  if (!meta.value.last_page) return [];

  const pages = [];
  for (let i = 1; i <= meta.value.last_page; i++) {
    pages.push({
      url: urlForPage(i),
      label: i.toString(),
      active: meta.value.current_page === i,
    });
  }

  const links = [];

  if (meta.value.current_page > 1) {
    links.push({
      url: urlForPage(meta.value.current_page - 1),
      label: '&laquo; Previous',
      active: false,
    });
  }

  links.push(...pages);

  if (meta.value.current_page < meta.value.last_page) {
    links.push({
      url: urlForPage(meta.value.current_page + 1),
      label: 'Next &raquo;',
      active: false,
    });
  }

  return links;
});

function goToPage(link) {
  if (link.url && !link.active) {
    const url = new URL(link.url, window.location.origin);
    const page = url.searchParams.get('page');

    console.log('%c[Pagination]', 'color: #22c55e; font-weight: bold');
    console.log(`➡️ Navigasi ke halaman: ${page}`);
    console.log(`🔗 URL tujuan: ${url.pathname + url.search}`);
    console.log(`📌 preserveState: true, preserveScroll: true`);

    router.visit(url.pathname + url.search, {
      preserveState: false,
      preserveScroll: true,
      onStart: () => {
        console.log('⏳ Memulai navigasi...');
      },
      onFinish: () => {
        console.log('✅ Navigasi selesai.');
      },
      onError: () => {
        console.error('❌ Gagal memuat halaman.');
      },
    });
  } else {
    console.log('ℹ️ Halaman saat ini dipilih atau link tidak valid.');
  }
}

// Watch current page, opsional debugging tambahan
watch(
  () => meta.value.current_page,
  (newPage, oldPage) => {
    if (newPage !== undefined && oldPage !== undefined) {
      console.log(`Halaman berubah dari ${oldPage} ke ${newPage}`);
    }
  }
);
</script>

<template>
  <div class="max-w-7xl mx-auto py-6">
    <div class="max-w-none mx-auto">
      <div class="bg-white overflow-hidden shadow sm:rounded-lg">
        <div
          class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6"
        >
          <!-- Informasi jumlah data -->
          <div class="mr-10">
            <p class="text-sm text-gray-700">
              Showing
              <span class="font-medium">{{ from }}</span>
              to
              <span class="font-medium">{{ to }}</span>
              of
              <span class="font-medium">{{ total }}</span>
              results
            </p>
          </div>

          <!-- Navigasi pagination -->
          <div v-if="props.data && props.data.meta && validLinks.length > 0">
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
  </div>
</template>
