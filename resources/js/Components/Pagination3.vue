<script setup>
import { computed } from 'vue';

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

console.log(
  'Pagination props.data.meta.total:',
  props.data?.meta?.total ?? 'Data belum tersedia'
);

const from = computed(() => {
  if (
    props.data &&
    props.data.meta &&
    props.data.meta.from !== undefined &&
    props.data.meta.from !== null
  ) {
    return props.data.meta.from;
  }
  if (
    props.data &&
    props.data.meta &&
    props.data.meta.current_page &&
    props.data.meta.per_page
  ) {
    return (props.data.meta.current_page - 1) * props.data.meta.per_page + 1;
  }
  return 0;
});

const to = computed(() => {
  if (
    props.data &&
    props.data.meta &&
    props.data.meta.to !== undefined &&
    props.data.meta.to !== null
  ) {
    return props.data.meta.to;
  }
  if (
    props.data &&
    props.data.meta &&
    props.data.meta.current_page &&
    props.data.meta.per_page &&
    props.data.meta.total
  ) {
    return Math.min(
      props.data.meta.current_page * props.data.meta.per_page,
      props.data.meta.total
    );
  }
  return 0;
});

function urlForPage(page) {
  const baseUrl = window.location.origin + window.location.pathname;
  return `${baseUrl}?page=${page}`;
}

const validLinks = computed(() => {
  const linksObj = props.data?.meta?.links;
  if (!linksObj) return [];

  const pages = [];
  for (let i = 1; i <= props.data.meta.last_page; i++) {
    const url = urlForPage(i);
    pages.push({
      url,
      label: i.toString(),
      active: props.data.meta.current_page === i,
    });
  }

  return [
    {
      url: linksObj.prev,
      label: '&laquo; Previous',
      active: false,
    },
    ...pages,
    {
      url: linksObj.next,
      label: 'Next &raquo;',
      active: false,
    },
  ].filter((link) => link.url !== null);
});

const total = computed(() => props.data?.meta?.total ?? 0);

console.log('props.data:', props.data);
console.log('isi validLinks: ', validLinks.value);
console.log('props.data.meta:', props.data?.meta);
console.log('Meta Links:', props.data.meta.links);
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
              <span class="font-medium">{{ from }}</span>
              to
              <span class="font-medium">{{ to }}</span>
              of
              <span class="font-medium">{{ total }}</span>
              results
            </p>
          </div>
          <div v-if="props.data && props.data.meta && validLinks.length > 0">
            <nav
              class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px"
              aria-label="Pagination"
            >
              <button
                v-for="(link, index) in validLinks"
                :key="index"
                @click.prevent="updatedPageNumber(link)"
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
