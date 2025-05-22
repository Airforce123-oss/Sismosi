<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
  jabatan: Object,
});

const form = useForm({
  nama_jabatan: props.jabatan.nama_jabatan,
  deskripsi: props.jabatan.deskripsi,
});
</script>

<template>
  <div class="max-w-3xl mx-auto py-10">
    <h1 class="text-2xl font-bold mb-4">Edit Jabatan</h1>

    <form
      @submit.prevent="
        form.put(
          route('master-jabatan.update', { master_jabatan: props.jabatan.id })
        )
      "
    >
      <div class="mb-4">
        <label class="block font-medium text-sm text-gray-700"
          >Nama Jabatan</label
        >
        <input
          v-model="form.nama_jabatan"
          type="text"
          class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm"
        />
      </div>
      <div class="mb-4">
        <label class="block font-medium text-sm text-gray-700">Deskripsi</label>
        <textarea
          v-model="form.deskripsi"
          class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm"
        ></textarea>
      </div>
      <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md">
        Simpan
      </button>
    </form>
  </div>
</template>
