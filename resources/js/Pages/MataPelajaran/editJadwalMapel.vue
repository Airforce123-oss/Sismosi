<script setup>
import { initFlowbite } from 'flowbite';
import { Link, useForm, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { ref, onMounted, watch, computed } from 'vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import SidebarAdmin from '@/Components/SidebarAdmin.vue';

// ✅ Props lengkap dari controller
const props = defineProps({
  jabatans: Array,
  mapel: Object,
  teacher: Object,
  prefill: Object,
  master_mapel: Object,
  jadwal: Array,
  kelas_id: Number,
  schedule: Object,
  filter: Object,
  classes_for_student: Object,
  wali_kelas: Object,
  teachers: Array,
});

// ✅ Logging untuk debugging
console.log('✅ Props received in edit.vue:', props);

console.log('✅ Prefill data:', props.prefill);

// ✅ Reactive refs untuk select dropdown
const mapels = ref(props.mapel || []);
const teachers = ref(props.teachers || []);

const selectedMapel = ref(null);
const showEditJadwal = ref(false);

const classes = computed(() => props.classes_for_student?.data || []);

// ✅ useForm untuk edit jadwal mapel
const formData = useForm({
  mapel_id: props.mapel?.data?.id ?? '',
  mapel: props.mapel?.data?.mapel ?? '',
  jam_ke: props.prefill?.jam_ke ?? '',
  hari: props.prefill?.hari ?? '',
  kelas_id: props.prefill?.kelas_id ?? '',
  guru_id: props.prefill?.guru_id ?? '',
  teacher_id: props.prefill?.guru_id ?? '',
  jam: props.prefill?.jam ?? '',
  tahun_ajaran: props.prefill?.tahun_ajaran ?? '',
});

// ✅ Isi data awal saat mount
onMounted(() => {
  initFlowbite();
  if (props.prefill?.kelas) {
    const matched = classes.value.find((k) => k.name === props.prefill.kelas);
    if (matched) {
      formData.kelas_id = matched.id;
    }
  }
});

// ✅ Watch mapel jika berubah
watch(
  () => props.mapel,
  (newMapels) => {
    if (Array.isArray(newMapels) && newMapels.length > 0) {
      mapels.value = newMapels;
    } else {
      console.warn('❗ mapel kosong atau tidak valid');
    }
  },
  { immediate: true }
);

watch(
  classes,
  (newClasses) => {
    console.log('📦 classes:', newClasses);
    console.log(
      '📦 Semua kelas (dari classes):',
      newClasses.map((k) => k.name)
    ); // 🔍 Tambahan log di sini
    console.log('🎯 props.prefill.kelas:', props.prefill?.kelas);

    if (props.prefill?.kelas && newClasses.length > 0) {
      const matched = newClasses.find(
        (k) =>
          k.name.trim().toLowerCase() ===
          props.prefill.kelas.trim().toLowerCase()
      );
      console.log('🔍 Matched kelas:', matched);

      if (matched) {
        formData.kelas_id = matched.id;
        formData.tahun_ajaran = matched.tahun_ajaran;
        console.log(
          '📘 formData.tahun_ajaran di-set ke:',
          formData.tahun_ajaran
        );
        console.log('✅ formData.kelas_id di-set ke:', formData.kelas_id);
      } else {
        console.warn('⚠️ Tidak ditemukan kelas yang cocok');
      }
    }
  },
  { immediate: true }
);

// ✅ Submit form
const submit = async () => {
  console.log('📤 Mengirim form data update jadwal:');
  console.table({
    mapel_id: formData.mapel_id,
    mapel: formData.mapel,
    hari: formData.hari,
    jam_ke: formData.jam_ke,
    kelas_id: formData.kelas_id,
    teacher_id: formData.teacher_id,
    tahun_ajaran: formData.tahun_ajaran,
  });

  // ✅ Log data sebenarnya yang akan dikirim ke server
  console.log('📦 Data lengkap yg dikirim ke server:', formData.data());

  // ✅ Log ID jadwal yang akan digunakan dalam route PUT
  const jadwalId = props.prefill?.id;
  console.log('📌 Jadwal ID yang akan diupdate:', jadwalId);

  // ✅ Validasi ID jadwal sebelum update
  if (!jadwalId) {
    console.error('⛔ Jadwal ID tidak valid untuk update:', jadwalId);
    Swal.fire('Error!', 'ID jadwal tidak valid.', 'error');
    return;
  }

  try {
    const response = await axios.put(
      route('matapelajaran.updateJadwal', jadwalId),
      formData.data()
    );

    console.log('✅ Jadwal berhasil diperbarui:', response.data);

    Swal.fire('Berhasil!', 'Jadwal berhasil diperbarui.', 'success');

    // 🔄 Redirect ke halaman list jadwal
    router.visit(route('matapelajaran.JadwalMataPelajaran'));
  } catch (error) {
    if (error.response?.status === 422) {
      const validationErrors = error.response.data.errors;
      formData.setError(validationErrors);
      console.error('❌ Validasi gagal:', validationErrors);
      Swal.fire('Gagal!', 'Periksa kembali inputan yang belum benar.', 'error');
    } else if (error.response?.status === 404) {
      console.error('❌ Jadwal tidak ditemukan:', error.response.data);
      Swal.fire('Gagal!', 'Jadwal tidak ditemukan.', 'error');
    } else if (error.response?.status === 409) {
      const msg = error.response.data.message ?? 'Jadwal bentrok!';
      console.warn('⚠️ Jadwal bentrok:', error.response.data);
      Swal.fire({
        icon: 'warning',
        title: 'Jadwal Bentrok!',
        text: msg,
      });
    } else {
      console.error('❌ Error tidak terduga:', error);
      Swal.fire('Error!', 'Terjadi kesalahan saat menyimpan data.', 'error');
    }
  }
};
</script>

<template>
  <div class="antialiased bg-gray-50 dark:bg-gray-900">
    <nav
      class="bg-white border-b border-gray-200 px-4 py-2.5 dark:bg-gray-800 dark:border-gray-700 fixed left-0 right-0 top-0 z-50"
    >
      <div class="flex flex-wrap justify-between items-center">
        <div class="flex justify-start items-center">
          <button
            data-drawer-target="drawer-navigation"
            data-drawer-toggle="drawer-navigation"
            aria-controls="drawer-navigation"
            class="p-2 mr-2 text-gray-600 rounded-lg cursor-pointer md:hidden hover:text-gray-900 hover:bg-gray-100 focus:bg-gray-100 dark:focus:bg-gray-700 focus:ring-2 focus:ring-gray-100 dark:focus:ring-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white"
          >
            <svg
              class="w-6 h-6"
              fill="currentColor"
              viewBox="0 0 20 20"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                fill-rule="evenodd"
                d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h6a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"
                clip-rule="evenodd"
              ></path>
            </svg>
            <svg
              class="hidden w-6 h-6"
              fill="currentColor"
              viewBox="0 0 20 20"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                fill-rule="evenodd"
                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                clip-rule="evenodd"
              ></path>
            </svg>
            <span class="sr-only">Toggle sidebar</span>
          </button>
          <a href="" class="flex items-center justify-between mr-4">
            <img src="/images/barunawati.jpeg" class="mr-3 h-8" alt="" />
            <span
              class="self-center text-2xl font-semibold whitespace-nowrap dark:text-white"
              >SMA BARUNAWATI SURABAYA</span
            >
          </a>
        </div>
        <div class="flex items-center lg:order-2">
          <button
            type="button"
            data-drawer-toggle="drawer-navigation"
            aria-controls="drawer-navigation"
            class="p-2 mr-1 text-gray-500 rounded-lg md:hidden hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-700 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-600"
          >
            <span class="sr-only">Toggle search</span>
            <svg
              aria-hidden="true"
              class="w-6 h-6"
              fill="currentColor"
              viewBox="0 0 20 20"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                clip-rule="evenodd"
                fill-rule="evenodd"
                d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
              ></path>
            </svg>
          </button>

          <!-- Apps -->

          <button
            type="button"
            class="flex mx-3 text-sm bg-gray-800 rounded-full md:mr-0 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-600"
            id="user-menu-button"
            aria-expanded="false"
            data-dropdown-toggle="dropdown"
          >
            <span class="sr-only">Open user menu</span>
            <svg
              baseProfile="tiny"
              height="24px"
              id="Layer_1"
              version="1.2"
              viewBox="0 0 24 24"
              width="24px"
              xml:space="preserve"
              xmlns="http://www.w3.org/2000/svg"
              xmlns:xlink="http://www.w3.org/1999/xlink"
            >
              <path
                d="M12,3c0,0-6.186,5.34-9.643,8.232C2.154,11.416,2,11.684,2,12c0,0.553,0.447,1,1,1h2v7c0,0.553,0.447,1,1,1h3  c0.553,0,1-0.448,1-1v-4h4v4c0,0.552,0.447,1,1,1h3c0.553,0,1-0.447,1-1v-7h2c0.553,0,1-0.447,1-1c0-0.316-0.154-0.584-0.383-0.768  C18.184,8.34,12,3,12,3z"
                fill="black"
              />
            </svg>
          </button>

          <!-- Dropdown menu -->
          <div
            class="hidden z-50 my-4 w-56 text-base list-none bg-white rounded divide-y divide-gray-100 shadow dark:bg-gray-700 dark:divide-gray-600 rounded-xl"
            id="dropdown"
          >
            <div class="py-3 px-3">
              <div
                class="'block w-full ps-3 pe-4 py-2 border-l-4 border-indigo-400 text-start text-base text-indigo-700 focus:outline-none focus:text-indigo-800 focus:bg-indigo-100 focus:border-indigo-700 transition duration-150 ease-in-out text-[12px]'"
              >
                <span
                  class="block text-sm font-semibold text-gray-900 dark:text-white"
                  >{{ $page.props.auth.user.email }}
                </span>
                <span
                  class="block text-sm text-gray-900 truncate dark:text-white"
                >
                  {{ $page.props.auth.user.name }}
                </span>
                <span
                  class="block text-sm text-gray-900 truncate dark:text-white"
                ></span>
              </div>
            </div>
            <div class="mt-3 space-y-1">
              <ResponsiveNavLink :href="route('profile.edit')">
                Profil Saya
              </ResponsiveNavLink>
              <ResponsiveNavLink
                :href="route('logout')"
                method="post"
                as="button"
              >
                Log Out
              </ResponsiveNavLink>
            </div>
          </div>
        </div>
      </div>
    </nav>

    <!-- start1 -->

    <main class="p-4 md:ml-64 h-auto pt-20">
      <div class="max-w-full mx-auto py-6 sm:px-6 lg:px-8 mt-10">
        <div class="lg:grid lg:grid-cols-12 lg:gap-x-5">
          <div class="space-y-6 sm:px-6 lg:px-0 lg:col-span-12">
            <!-- FORM EDIT GURU -->
            <form @submit.prevent="submit">
              <div class="shadow sm:rounded-md sm:overflow-hidden">
                <div class="bg-white py-6 px-4 space-y-6 sm:p-6">
                  <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                      Informasi Jadwal Mata Pelajaran
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                      Gunakan Form ini untuk mengisi data Jadwal Mata Pelajaran.
                    </p>
                  </div>

                  <div class="grid grid-cols-6 gap-6">
                    <!-- Jam Ke -->
                    <div class="col-span-6 sm:col-span-2">
                      <label
                        for="jam_ke"
                        class="block text-sm font-medium text-gray-700"
                        >Jam Ke</label
                      >
                      <input
                        v-model="formData.jam_ke"
                        type="number"
                        id="jam_ke"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 sm:text-sm"
                        :class="formData.errors.jam_ke ? 'border-red-500' : ''"
                      />
                      <div
                        v-if="formData.errors.jam_ke"
                        class="text-sm text-red-600 mt-1"
                      >
                        {{ formData.errors.jam_ke }}
                      </div>
                    </div>

                    <!-- Mata Pelajaran -->
                    <div class="col-span-6 sm:col-span-4">
                      <label
                        for="mapel_id"
                        class="block text-sm font-medium text-gray-700"
                        >Mata Pelajaran</label
                      >
                      <select
                        v-model="formData.mapel_id"
                        id="mapel_id"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 sm:text-sm"
                        :class="
                          formData.errors.mapel_id ? 'border-red-500' : ''
                        "
                      >
                        <option value="">Pilih Mapel</option>
                        <option
                          v-for="mapel in mapels"
                          :key="mapel.id"
                          :value="mapel.id"
                        >
                          {{ mapel.mapel }}
                        </option>
                      </select>
                      <div
                        v-if="formData.errors.mapel_id"
                        class="text-sm text-red-600 mt-1"
                      >
                        {{ formData.errors.mapel_id }}
                      </div>
                    </div>

                    <!-- Kelas -->
                    <div class="col-span-6 sm:col-span-3">
                      <label
                        for="kelas_id"
                        class="block text-sm font-medium text-gray-700"
                        >Kelas</label
                      >
                      <select
                        v-model="formData.kelas_id"
                        id="kelas_id"
                        class="mt-1 block w-full border rounded-md shadow-sm py-2 px-3 sm:text-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                        :class="
                          formData.errors?.kelas_id
                            ? 'border-red-500'
                            : 'border-gray-300'
                        "
                      >
                        <option value="">Pilih Kelas</option>
                        <option
                          v-for="kelas in classes"
                          :key="kelas.id"
                          :value="kelas.id"
                        >
                          {{ kelas.name }}
                        </option>
                      </select>
                      <div
                        v-if="formData.errors.kelas_id"
                        class="text-sm text-red-600 mt-1"
                      >
                        {{ formData.errors.kelas_id }}
                      </div>
                    </div>

                    <!-- Guru -->
                    <div class="col-span-6 sm:col-span-3">
                      <label
                        for="guru_id"
                        class="block text-sm font-medium text-gray-700"
                        >Guru</label
                      >
                      <select
                        v-model="formData.guru_id"
                        id="guru_id"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 sm:text-sm"
                        :class="formData.errors.guru_id ? 'border-red-500' : ''"
                      >
                        <option value="">Pilih Guru</option>
                        <option
                          v-for="guru in teachers"
                          :key="guru.id"
                          :value="guru.id"
                        >
                          {{ guru.name }}
                        </option>
                      </select>
                      <div
                        v-if="formData.errors.guru_id"
                        class="text-sm text-red-600 mt-1"
                      >
                        {{ formData.errors.guru_id }}
                      </div>
                    </div>

                    <!-- Hari -->
                    <div class="col-span-6 sm:col-span-2">
                      <label
                        for="hari"
                        class="block text-sm font-medium text-gray-700"
                        >Hari</label
                      >
                      <select
                        v-model="formData.hari"
                        id="hari"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 sm:text-sm"
                        :class="formData.errors.hari ? 'border-red-500' : ''"
                      >
                        <option value="">Pilih Hari</option>
                        <option value="senin">Senin</option>
                        <option value="selasa">Selasa</option>
                        <option value="rabu">Rabu</option>
                        <option value="kamis">Kamis</option>
                        <option value="jumat">Jumat</option>
                        <option value="sabtu">Sabtu</option>
                      </select>
                      <div
                        v-if="formData.errors.hari"
                        class="text-sm text-red-600 mt-1"
                      >
                        {{ formData.errors.hari }}
                      </div>
                    </div>

                    <!-- Jam -->
                    <div class="col-span-6 sm:col-span-2">
                      <label
                        for="jam"
                        class="block text-sm font-medium text-gray-700"
                        >Jam</label
                      >
                      <input
                        v-model="formData.jam"
                        type="text"
                        id="jam"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 sm:text-sm"
                        placeholder="Contoh: 07:00 - 07:45"
                        :class="formData.errors.jam ? 'border-red-500' : ''"
                      />
                      <div
                        v-if="formData.errors.jam"
                        class="text-sm text-red-600 mt-1"
                      >
                        {{ formData.errors.jam }}
                      </div>
                    </div>

                    <!-- Tahun Ajaran -->
                    <div class="col-span-6 sm:col-span-2">
                      <label
                        for="tahun_ajaran"
                        class="block text-sm font-medium text-gray-700"
                        >Tahun Ajaran</label
                      >
                      <input
                        v-model="formData.tahun_ajaran"
                        type="text"
                        id="tahun_ajaran"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 sm:text-sm"
                        placeholder="Contoh: 2024/2025"
                        :class="
                          formData.errors.tahun_ajaran ? 'border-red-500' : ''
                        "
                      />
                      <div
                        v-if="formData.errors.tahun_ajaran"
                        class="text-sm text-red-600 mt-1"
                      >
                        {{ formData.errors.tahun_ajaran }}
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Tombol -->
                <div
                  class="px-4 py-3 bg-gray-50 text-right sm:px-6 flex justify-end"
                >
                  <Link
                    :href="route('matapelajaran.JadwalMataPelajaran')"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md bg-indigo-100 hover:bg-indigo-200"
                  >
                    Batal
                  </Link>
                  <button
                    type="submit"
                    class="ml-4 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700"
                  >
                    Simpan
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </main>

    <!-- end1-->

    <!-- Sidebar -->
    <SidebarAdmin />
  </div>
</template>
