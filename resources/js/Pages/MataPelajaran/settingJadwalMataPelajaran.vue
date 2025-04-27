<script setup>
import { initFlowbite } from 'flowbite';
import Pagination from '../../Components/Pagination.vue';
import MagnifyingGlass from '../../Components/Icons/MagnifyingGlass.vue';
import { Link, Head, useForm, usePage, router } from '@inertiajs/vue3';
import { onMounted, ref, watch, computed } from 'vue';
import Swal from 'sweetalert2';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
const props = defineProps({
  auth: { type: Object },
  master_mapel: {
    type: Object,
    required: true,
  },
  classes_for_student: {
    type: Object,
    required: true,
  },
});

const getKelasForMapel = (mapelId) => {
  if (!mapelId) return '-';
  const m = props.master_mapel.data.find((m) => m.id === mapelId);
  return m?.kelas || '-';
};

console.log('Classes for Student:', props.classes_for_student);
const form = useForm({
  name: props.auth?.user?.name || '',
  email: props.auth?.user?.email || '',
  role_type: props.auth?.user?.role_type || '',
});

const currentPage = ref(1); // Gunakan ini sebagai pengganti pageNumber
const searchTerm = ref('');

const kelasUrl = computed(() => {
  const url = new URL(route('matapelajaran.index'));
  console.log('URL manual:', url);
  url.searchParams.set('page', currentPage.value); // Gunakan currentPage
  if (searchTerm.value) {
    url.searchParams.set('search', searchTerm.value);
  }
  return url;
});

const laporanJadwal = computed(() => {
  const hasil = [];
  schedule.value.forEach((slot) => {
    days.forEach((day) => {
      const item = slot.jadwal[day];
      if (item && (typeof item === 'object' ? item.mapel : item)) {
        hasil.push({
          hari: day,
          jam_ke: slot.jam_ke,
          jam: slot.jam,
          mapel: typeof item === 'object' ? item.mapel : item,
        });
      }
    });
  });
  return hasil;
});

watch(
  () => kelasUrl.value,
  (updatedKelasUrl) => {
    console.log('Navigating to URL:', updatedKelasUrl.toString());
    router.visit(updatedKelasUrl.toString(), {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    });
  }
);

// FILTER DATA
const jurusanList = ['Ilmu Pengetahuan Alam', 'Ilmu Pengetahuan Sosial'];
const tingkatList = ['X', 'XI', 'XII'];
const kelasList = ['1', '2', '3'];

const selectedJurusan = ref('');
const selectedTingkat = ref('');
const selectedKelas = ref('');
const selectedMapelFilter = ref(null);

// HARI
const days = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'];
const schedule = ref([]);

const loadSchedule = async () => {
  const id = parseInt(selectedKelas.value, 10);

  if (isNaN(id) || id <= 0) {
    console.warn('❗ kelas_id tidak valid. Jadwal tidak dimuat.');
    schedule.value = Array.from({ length: 8 }, (_, i) => ({
      jam_ke: i + 1,
      jam: `${String(7 + i).padStart(2, '0')}:00 - ${String(7 + i)}:45`,
      jadwal: {
        senin: null,
        selasa: null,
        rabu: null,
        kamis: null,
        jumat: null,
        sabtu: null,
        minggu: null,
      },
    }));
    return;
  }

  try {
    const response = await axios.get(route('jadwal.get'), {
      params: { kelas_id: id },
    });

    const rawData = response.data; // Data mentah dari API

    // Transformasi data mentah menjadi struktur tabel
    const transformed = Array.from({ length: 8 }, (_, i) => {
      const jamKe = i + 1;
      const jamLabel = `${String(7 + i).padStart(2, '0')}:00 - ${String(
        7 + i
      )}:45`;

      const jadwalPerHari = {};
      days.forEach((day) => {
        // Menyaring data berdasarkan jam_ke dan hari
        const entry = rawData.find(
          (item) => item.jam_ke === jamKe && item.jadwal?.[day]
        );

        // Menentukan jadwal per hari berdasarkan data yang ditemukan
        jadwalPerHari[day] = entry
          ? {
              mapel:
                entry.jadwal[day]?.mapel ||
                `Mapel ID: ${entry.jadwal[day]?.mapel_id}`,
              mapel_id: entry.jadwal[day]?.mapel_id,
              kelas: entry.jadwal[day]?.kelas || '-',
            }
          : null;
      });

      return { jam_ke: jamKe, jam: jamLabel, jadwal: jadwalPerHari };
    });

    schedule.value = transformed;

    // Menyimpan data mapel di modal jika sedang dalam mode edit
    if (editingSlot.value.jamKe && editingSlot.value.hari) {
      const matchedSlot = schedule.value.find(
        (s) => s.jam_ke === editingSlot.value.jamKe
      );
      if (matchedSlot) {
        const mapelDiSlot = matchedSlot.jadwal[editingSlot.value.hari];
        selectedMapelModal.value = mapelDiSlot ? mapelDiSlot.mapel_id : null;
      }
    }

    console.log(
      '✅ Jadwal berhasil dimuat dan ditransformasi:',
      schedule.value
    );
  } catch (error) {
    console.error('❌ Gagal mengambil jadwal:', error.response?.data || error);
  }
};

console.log('DEBUG — schedule:', schedule.value);
console.log('DEBUG — days:', days);

const entries = computed(() => {
  return schedule.value.flatMap((slot) =>
    days
      .map((day) => {
        const item = slot.jadwal[day];
        if (!item) return null;

        const mapelId =
          typeof item === 'string'
            ? Number(item)
            : typeof item === 'number'
            ? item
            : item?.mapel_id || item?.id;

        if (!mapelId) return null;

        return {
          hari: day,
          jam_ke: slot.jam_ke,
          jam: slot.jam || '', // ✅ Tambahkan ini
          mapel_id: mapelId,
        };
      })
      .filter(Boolean)
  );
});

// MODAL STATE
const showModal = ref(false);
const selectedMapel = ref('');
const selectedMapelModal = ref('');
console.log(
  'Selected Mapel:',
  selectedMapelModal.value,
  typeof selectedMapelModal.value
);

const editingSlot = ref({ jamKe: null, hari: '' });

const openEditModal = (jamKe, hari) => {
  editingSlot.value = { jamKe, hari };
  selectedMapel.value = '';
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  editingSlot.value = { jamKe: null, hari: '' };
};

const fetchSchedule = async () => {
  try {
    const response = await axios.get(
      route('jadwal.get', {
        kelas_id: selectedKelas.value,
      })
    );
    schedule.value = response.data;
    console.log('Fetched schedule:', schedule.value);
  } catch (error) {
    console.error('Gagal mengambil jadwal:', error);
  }
};

const mapelLabelById = (id) => {
  return props.master_mapel[id] ?? 'Tidak diketahui';
};

const updateLocalSchedule = () => {
  entries.value.forEach((entry) => {
    const slot = schedule.value.find((s) => s.jam_ke === entry.jam_ke);
    if (slot) {
      slot.jadwal = {
        ...slot.jadwal,
        [entry.hari]: {
          mapel: mapelLabelById(entry.mapel_id),
        },
      };
    }
  });
};

const saveJadwal = async () => {
  console.log('selectedMapelModal:', selectedMapelModal.value);
  console.log('editingSlot.jamKe:', editingSlot.value.jamKe);
  console.log('editingSlot.hari:', editingSlot.value.hari);
  console.log('selectedKelas:', selectedKelas.value);
  console.log(
    'Selected MapelModal:',
    selectedMapelModal.value,
    typeof selectedMapelModal.value
  );

  if (
    !selectedMapelModal.value ||
    !editingSlot.value.jamKe ||
    !editingSlot.value.hari ||
    !selectedKelas.value
  ) {
    Swal.fire({
      icon: 'warning',
      title: 'Form belum lengkap!',
      text: 'Lengkapi semua field sebelum menyimpan.',
    });
    return;
  }

  // Mendapatkan nilai jam untuk jam_ke tertentu
  const jamKeNumber = parseInt(editingSlot.value.jamKe);
  const jam = schedule.value.find((s) => s.jam_ke === jamKeNumber)?.jam || '';
  console.log('editingSlot.jamKe:', editingSlot.value.jamKe);
  console.log('Schedule:', schedule.value);
  console.log(
    'Matched slot:',
    schedule.value.find((s) => s.jam_ke === parseInt(editingSlot.value.jamKe))
  );

  // Validasi jika jam kosong
  if (!jam) {
    Swal.fire({
      icon: 'warning',
      title: 'Jam belum terisi!',
      text: 'Pastikan jadwal untuk jam ini sudah ada.',
    });
    return;
  }

  entries.value.push({
    hari: editingSlot.value.hari,
    jam_ke: editingSlot.value.jamKe,
    jam: jam,
    mapel_id: selectedMapelModal.value,
  });

  showModal.value = false;

  console.log('entries payload:', entries.value);

  try {
    await axios.post(route('jadwal.store'), {
      kelas_id: selectedKelas.value,
      entries: entries.value,
    });

    updateLocalSchedule();

    await fetchSchedule();
    entries.value = [];

    Swal.fire({
      icon: 'success',
      title: 'Berhasil!',
      text: 'Jadwal berhasil disimpan.',
      timer: 2000,
      showConfirmButton: false,
    });
  } catch (error) {
    updateLocalSchedule();
    console.error('POST error:', error.response?.data || error);
    Swal.fire({
      icon: 'error',
      title: 'Gagal menyimpan',
      text: 'Terjadi kesalahan saat menyimpan jadwal.',
    });
  }
};

const hasJadwalForDay = (day) => {
  return schedule.value.some((slot) => {
    const item = slot.jadwal[day];
    // bila item adalah objek, pakai mapel; bila string langsung pakai item
    return item && (typeof item === 'object' ? item.mapel : item);
  });
};

const getMapelForSlot = (slot, day) => {
  const item = slot.jadwal[day];
  if (!item) {
    return '✖';
  }
  return typeof item === 'object' ? item.mapel ?? '✖' : item;
};

const getMapelName = (jamKe, hari) => {
  const slot = entries.value.find((e) => e.jam_ke === jamKe && e.hari === hari);
  if (!slot) return '-';
  const mapel = props.master_mapel.data.find((m) => m.id === slot.mapel_id);
  return mapel ? mapel.mapel : '-';
};

onMounted(() => {
  console.log('Schedule on mount:', schedule.value);
  const savedSchedule = localStorage.getItem('jadwal_mingguan');
  if (savedSchedule) {
    try {
      schedule.value = JSON.parse(savedSchedule);
    } catch (e) {
      console.error('Jadwal tidak valid:', e);
    }
  }
  loadSchedule();
  initFlowbite();
});

watch(selectedKelas, (newVal) => {
  console.log('Selected Kelas updated:', newVal, typeof newVal);
  if (newVal) loadSchedule();
});
watch([selectedJurusan, selectedMapelModal, selectedKelas], () => {
  loadSchedule();
});
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
              class="self-center text-base md:text-lg lg:text-xl xl:text-2xl font-semibold whitespace-nowrap dark:text-white"
              >SMA BARUNAWATI SURABAYA</span
            >
          </a>
        </div>
        <div class="flex items-center lg:order-2">
          <!-- Apps -->
          <button
            type="button"
            class="p-2 text-gray-500 rounded-lg hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-700 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-600"
          ></button>

          <button
            type="button"
            class="flex mx-3 text-sm rounded-full md:mr-0 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-600"
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
            class="hidden w-full sm:w-1/2 lg:w-1/4 text-base list-none bg-white rounded divide-y divide-gray-100 shadow dark:bg-gray-700 dark:divide-gray-600 rounded-xl"
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
                  >{{ form.role_type }}</span
                >
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

    <main class="md:ml-64 pt-20 min-h-screen bg-gray-100">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="p-6 space-y-6">
          <!-- FILTER -->
          <div
            class="bg-white shadow-md rounded-xl p-6 w-full max-w-4xl mx-auto mb-6"
          >
            <h2 class="text-xl text-center font-semibold mb-4 text-gray-700">
              PENGATURAN JADWAL MATA PELAJARAN
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <!-- Jurusan (opsional, untuk filter tampilan) -->
              <div>
                <label class="block text-sm font-semibold text-gray-600 mb-1">
                  Jurusan
                </label>
                <select
                  v-model="selectedJurusan"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                >
                  <option value="">Pilih Jurusan</option>
                  <option
                    v-for="jurusan in jurusanList"
                    :key="jurusan"
                    :value="jurusan"
                  >
                    {{ jurusan }}
                  </option>
                </select>
              </div>

              <!-- Mata Pelajaran -->
              <div>
                <label class="block text-sm font-semibold text-gray-600 mb-1">
                  Mata Pelajaran
                </label>
                <select
                  v-model="selectedMapelModal"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                >
                  <option value="">Pilih Mata Pelajaran</option>
                  <option
                    v-for="mapel in props.master_mapel.data"
                    :key="mapel.id"
                    :value="mapel.id"
                  >
                    {{ mapel.mapel }}
                  </option>
                </select>
              </div>

              <!-- Kelas -->
              <div>
                <label class="block text-sm font-semibold text-gray-600 mb-1">
                  Kelas
                </label>
                <select
                  v-model.number="selectedKelas"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                >
                  <option value="">Pilih Kelas</option>
                  <option
                    v-for="c in props.classes_for_student.data"
                    :key="c.id"
                    :value="c.id"
                  >
                    {{ c.name }}
                  </option>
                </select>
              </div>
            </div>

            <!-- Tombol -->
            <!--    <div class="mt-6 flex justify-end">
                <button
                  class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-lg transition duration-200"
                  @click="loadSchedule"
                >
                  Lihat Jadwal
                </button>
              </div>-->
          </div>

          <!-- TABEL JADWAL -->
          <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
            <table
              class="min-w-full text-sm text-center border border-gray-200"
            >
              <thead class="bg-gray-100">
                <tr>
                  <th class="border p-2">Jam Ke</th>
                  <th class="border p-2">Jam</th>
                  <th
                    v-for="day in days"
                    :key="day"
                    class="border p-2 capitalize"
                  >
                    {{ day }}
                  </th>
                </tr>
              </thead>
              <tbody>
                <!-- Jika schedule kosong, tetap tampilkan row kosong -->
                <tr v-if="schedule.length === 0">
                  <td
                    colspan="100%"
                    class="border p-2 text-gray-500 text-center"
                  >
                    Tidak ada data jadwal
                  </td>
                </tr>

                <!-- Render data jadwal jika ada -->
                <tr
                  v-for="(slot, index) in schedule"
                  :key="index"
                  :class="{ 'bg-gray-50': index % 2 === 0 }"
                >
                  <td class="border p-2">{{ slot.jam_ke }}</td>
                  <td class="border p-2">{{ slot.jam }}</td>
                  <td v-for="day in days" :key="day" class="border p-2">
                    <span
                      v-if="day === 'sabtu' || day === 'minggu'"
                      class="text-red-500 font-semibold"
                    >
                      Libur
                    </span>
                    <button
                      v-else
                      class="w-full h-full px-2 py-1 text-sm rounded transition-all duration-150"
                      :class="
                        slot.jadwal[day]?.mapel
                          ? 'bg-blue-500 text-white'
                          : 'bg-yellow-400 text-white'
                      "
                      @click="openEditModal(slot.jam_ke, day, slot)"
                    >
                      {{ slot.jadwal[day]?.mapel || '✖' }}
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!--LAPORAN JADWAL-->

          <div class="mt-10">
            <h2 class="text-xl text-center font-bold text-gray-800 mb-4">
              Laporan Jadwal Mingguan
            </h2>

            <div
              v-for="day in days"
              :key="day"
              class="mb-8 border border-gray-300 rounded-lg overflow-hidden"
            >
              <div
                class="bg-gray-100 px-4 py-2 text-lg text-center font-semibold capitalize border-b"
              >
                {{ day }}
              </div>

              <table class="w-full text-sm text-left">
                <thead class="bg-gray-200">
                  <tr>
                    <th class="p-3 border">Jam Ke</th>
                    <th class="p-3 border">Waktu</th>
                    <th class="p-3 border">Mata Pelajaran</th>
                    <th class="p-3 border">Kelas</th>
                  </tr>
                </thead>
                <tbody>
                  <!-- Jika hari Sabtu atau Minggu, langsung tampilkan satu baris Libur -->
                  <tr v-if="day === 'sabtu' || day === 'minggu'">
                    <td
                      colspan="3"
                      class="p-3 border text-center text-red-500 font-semibold"
                    >
                      Libur
                    </td>
                  </tr>

                  <!-- Bila ada jadwal, loop slot -->
                  <tr
                    v-else-if="hasJadwalForDay(day)"
                    v-for="slot in schedule"
                    :key="`${day}-${slot.jam_ke}`"
                    v-show="getMapelForSlot(slot, day)"
                  >
                    <td class="p-3 border">{{ slot.jam_ke }}</td>
                    <td class="p-3 border">{{ slot.jam }}</td>
                    <td class="p-3 border">
                      {{ getMapelForSlot(slot, day) }}
                    </td>
                    <td class="p-3 border">
                      {{ slot.jadwal[day]?.kelas || '-' }}
                    </td>
                  </tr>

                  <!-- Bila tidak ada sama sekali -->
                  <tr v-else>
                    <td
                      colspan="3"
                      class="p-3 border text-center text-gray-500 italic"
                    >
                      Tidak ada jadwal
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- MODAL -->
          <div
            v-if="showModal"
            class="fixed inset-0 z-50 bg-black bg-opacity-50 flex items-center justify-center"
          >
            <div class="bg-white p-6 rounded-lg w-[90%] md:w-[400px] space-y-4">
              <h2 class="text-lg font-semibold">Edit Jadwal</h2>
              <p class="text-sm text-gray-600">
                Jam ke: {{ editingSlot.jamKe }}, Hari: {{ editingSlot.hari }}
              </p>

              <!-- Dropdown Mapel -->
              <select v-model="selectedMapel" class="form-select w-full">
                <option value="">Pilih Mapel</option>
                <option
                  v-for="mapel in props.master_mapel.data"
                  :key="mapel.id"
                  :value="mapel"
                >
                  {{ mapel.mapel }}
                </option>
              </select>

              <div class="flex justify-end gap-2">
                <button
                  class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400"
                  @click="closeModal"
                >
                  Batal
                </button>
                <button
                  class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
                  @click="saveJadwal"
                >
                  Simpan
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- end1-->

    <!-- Sidebar -->
    <aside
      class="fixed top-0 left-0 z-40 w-60 h-screen pt-4 transition-transform -translate-x-full bg-white border-r border-gray-200 md:translate-x-0 dark:bg-gray-800 dark:border-gray-900"
      aria-label="Sidenav"
      id="drawer-navigation"
      style=""
    >
      <div class="overflow-y-auto py-5 px-3 h-full bg-white dark:bg-gray-800">
        <ul class="space-y-2">
          <li>
            <a
              href="dashboard"
              class="flex items-center p-2 text-base font-medium text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group"
            >
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
                />
              </svg>
              <span class="ml-3">Beranda</span>
            </a>
          </li>

          <li>
            <button
              type="button"
              class="flex items-center p-2 w-full text-base font-medium text-gray-900 rounded-lg transition duration-75 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
              aria-controls="dropdown-pages"
              data-collapse-toggle="dropdown-pages"
            >
              <svg
                viewBox="0 0 256 256"
                xmlns="http://www.w3.org/2000/svg"
                width="24"
                height="24"
              >
                <rect fill="none" height="256" width="256" />
                <path
                  d="M226.5,56.4l-96-32a8.5,8.5,0,0,0-5,0l-95.9,32h-.2l-1,.5h-.1l-1,.6c0,.1-.1.1-.2.2l-.8.7h0l-.7.8c0,.1-.1.1-.1.2l-.6.9c0,.1,0,.1-.1.2l-.4.9h0l-.3,1.1v.3A3.7,3.7,0,0,0,24,64v80a8,8,0,0,0,16,0V75.1L73.6,86.3A63.2,63.2,0,0,0,64,120a64,64,0,0,0,30,54.2,96.1,96.1,0,0,0-46.5,37.4,8.1,8.1,0,0,0,2.4,11.1,7.9,7.9,0,0,0,11-2.3,80,80,0,0,1,134.2,0,8,8,0,0,0,6.7,3.6,7.5,7.5,0,0,0,4.3-1.3,8.1,8.1,0,0,0,2.4-11.1A96.1,96.1,0,0,0,162,174.2,64,64,0,0,0,192,120a63.2,63.2,0,0,0-9.6-33.7l44.1-14.7a8,8,0,0,0,0-15.2ZM128,168a48,48,0,0,1-48-48,48.6,48.6,0,0,1,9.3-28.5l36.2,12.1a8,8,0,0,0,5,0l36.2-12.1A48.6,48.6,0,0,1,176,120,48,48,0,0,1,128,168Z"
                />
              </svg>

              <span class="flex-1 ml-3 text-left whitespace-nowrap">Siswa</span>
              <svg
                inert
                class="w-6 h-6"
                fill="currentColor"
                viewBox="0 0 20 20"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  fill-rule="evenodd"
                  d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                  clip-rule="evenodd"
                ></path>
              </svg>
            </button>
            <ul id="dropdown-pages" class="hidden py-2 space-y-2">
              <li>
                <a
                  href="students"
                  class="flex items-center p-2 pl-11 w-full text-base font-medium text-gray-900 rounded-lg transition duration-75 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
                  >Data Induk Siswa</a
                >
              </li>
            </ul>
          </li>

          <li>
            <button
              type="button"
              class="flex items-center p-2 w-full text-base font-medium text-gray-900 rounded-lg transition duration-75 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
              aria-controls="dropdown-pages-guru"
              data-collapse-toggle="dropdown-pages-guru"
            >
              <svg
                viewBox="0 0 640 512"
                xmlns="http://www.w3.org/2000/svg"
                width="24"
                height="24"
              >
                <path
                  d="M208 352c-2.39 0-4.78.35-7.06 1.09C187.98 357.3 174.35 360 160 360c-14.35 0-27.98-2.7-40.95-6.91-2.28-.74-4.66-1.09-7.05-1.09C49.94 352-.33 402.48 0 464.62.14 490.88 21.73 512 48 512h224c26.27 0 47.86-21.12 48-47.38.33-62.14-49.94-112.62-112-112.62zm-48-32c53.02 0 96-42.98 96-96s-42.98-96-96-96-96 42.98-96 96 42.98 96 96 96zM592 0H208c-26.47 0-48 22.25-48 49.59V96c23.42 0 45.1 6.78 64 17.8V64h352v288h-64v-64H384v64h-76.24c19.1 16.69 33.12 38.73 39.69 64H592c26.47 0 48-22.25 48-49.59V49.59C640 22.25 618.47 0 592 0z"
                />
              </svg>

              <span class="flex-1 ml-3 text-left whitespace-nowrap">Guru</span>
              <svg
                class="w-6 h-6"
                fill="currentColor"
                viewBox="0 0 20 20"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  fill-rule="evenodd"
                  d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                  clip-rule="evenodd"
                ></path>
              </svg>
            </button>
            <ul id="dropdown-pages-guru" class="hidden py-2 space-y-2">
              <!-- Dropdown Data Induk Guru -->
              <li>
                <a
                  href="teachers"
                  class="flex items-center p-2 pl-11 w-full text-base font-medium text-gray-900 rounded-lg transition duration-75 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
                  >Data Induk Guru</a
                >
              </li>
              <!-- Dropdown Absensi Guru -->
              <li>
                <a
                  href="absensiGuru"
                  class="flex items-center p-2 pl-11 w-full text-base font-medium text-gray-900 rounded-lg transition duration-75 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
                  >Absensi Guru</a
                >
              </li>
              <!-- Dropdown Daftar Absensi Guru -->
              <li>
                <a
                  href="dataAbsensiGuru"
                  class="flex items-center p-2 pl-11 w-full text-base font-medium text-gray-900 rounded-lg transition duration-75 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
                  >Data Absensi Guru</a
                >
              </li>
            </ul>
          </li>
          <li>
            <button
              type="button"
              class="flex items-center p-2 w-full text-base font-medium text-gray-900 rounded-lg transition duration-75 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
              aria-controls="dropdown-sales"
              data-collapse-toggle="dropdown-sales"
            >
              <svg
                id="Icons_Teacher"
                overflow="hidden"
                version="1.1"
                viewBox="0 0 96 96"
                xmlns="http://www.w3.org/2000/svg"
                xmlns:xlink="http://www.w3.org/1999/xlink"
                class="w-5 h-5"
              >
                <path
                  d=" M 87.8 19 L 23.8 19 C 21.6 19 19.8 20.8 19.8 23 L 19.8 37.5 C 20.9 37.2 22.2 37 23.4 37 C 24.2 37 25 37.1 25.8 37.2 L 25.8 25 L 85.8 25 L 85.8 63 L 51.9 63 L 46.2 69 L 87.8 69 C 90 69 91.8 67.2 91.8 65 L 91.8 23 C 91.8 20.8 90 19 87.8 19"
                />
                <path
                  d=" M 23.5 58 C 28.2 58 32 54.2 32 49.5 C 32 44.8 28.2 41 23.5 41 C 18.8 41 15 44.8 15 49.5 C 14.9 54.2 18.8 58 23.5 58"
                />
                <path
                  d=" M 56.2 48.1 C 54.9 46.1 52.3 45.6 50.3 46.8 C 49.9 47 49.7 47.4 49.5 47.6 L 34.9 62.8 C 33.5 62.1 32 61.5 30.5 61 C 28.2 60.6 25.8 60.1 23.5 60.1 C 21.2 60.1 18.8 60.5 16.5 61.2 C 13.1 62.1 10.1 63.8 7.6 65.9 C 7 66.5 6.5 67.4 6.3 68.2 L 4.2 77 L 34.1 77 L 34.1 76.9 L 42.6 67 L 55.7 53.2 C 56.9 52 57.3 49.7 56.2 48.1"
                />
              </svg>
              <span class="flex-1 ml-3 text-left whitespace-nowrap">Kelas</span>
              <svg
                inert
                class="w-6 h-6"
                fill="currentColor"
                viewBox="0 0 20 20"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  fill-rule="evenodd"
                  d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                  clip-rule="evenodd"
                ></path>
              </svg>
            </button>
            <ul id="dropdown-sales" class="hidden py-2 space-y-2">
              <li>
                <a
                  href="kelas"
                  class="flex items-center p-2 pl-11 w-full text-base font-medium text-gray-900 rounded-lg transition duration-75 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
                  >Membuat Kelas</a
                >
              </li>
            </ul>
          </li>

          <li>
            <button
              type="button"
              class="flex items-center p-2 w-full text-base font-medium text-gray-900 rounded-lg transition duration-75 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
              aria-controls="dropdown-authentication"
              data-collapse-toggle="dropdown-authentication1"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 20 20"
                fill="currentColor"
                class="w-5 h-5"
              >
                <path
                  fill-rule="evenodd"
                  d="M9.664 1.319a.75.75 0 0 1 .672 0 41.059 41.059 0 0 1 8.198 5.424.75.75 0 0 1-.254 1.285 31.372 31.372 0 0 0-7.86 3.83.75.75 0 0 1-.84 0 31.508 31.508 0 0 0-2.08-1.287V9.394c0-.244.116-.463.302-.592a35.504 35.504 0 0 1 3.305-2.033.75.75 0 0 0-.714-1.319 37 37 0 0 0-3.446 2.12A2.216 2.216 0 0 0 6 9.393v.38a31.293 31.293 0 0 0-4.28-1.746.75.75 0 0 1-.254-1.285 41.059 41.059 0 0 1 8.198-5.424ZM6 11.459a29.848 29.848 0 0 0-2.455-1.158 41.029 41.029 0 0 0-.39 3.114.75.75 0 0 0 .419.74c.528.256 1.046.53 1.554.82-.21.324-.455.63-.739.914a.75.75 0 1 0 1.06 1.06c.37-.369.69-.77.96-1.193a26.61 26.61 0 0 1 3.095 2.348.75.75 0 0 0 .992 0 26.547 26.547 0 0 1 5.93-3.95.75.75 0 0 0 .42-.739 41.053 41.053 0 0 0-.39-3.114 29.925 29.925 0 0 0-5.199 2.801 2.25 2.25 0 0 1-2.514 0c-.41-.275-.826-.541-1.25-.797a6.985 6.985 0 0 1-1.084 3.45 26.503 26.503 0 0 0-1.281-.78A5.487 5.487 0 0 0 6 12v-.54Z"
                  clip-rule="evenodd"
                />
              </svg>

              <span class="flex-1 ml-3 text-left whitespace-nowrap"
                >Mata Pelajaran</span
              >
              <svg
                inert
                class="w-6 h-6"
                fill="currentColor"
                viewBox="0 0 20 20"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  fill-rule="evenodd"
                  d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                  clip-rule="evenodd"
                ></path>
              </svg>
            </button>

            <ul id="dropdown-authentication1" class="hidden py-2 space-y-2">
              <li>
                <a
                  href="mataPelajaran"
                  class="flex items-center p-2 pl-11 w-full text-base font-medium text-gray-900 rounded-lg transition duration-75 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
                  >Tambah Mata Pelajaran</a
                >
              </li>
              <li>
                <a
                  href="settingJadwalMataPelajaran"
                  class="flex items-center p-2 pl-11 w-full text-base font-medium text-gray-900 rounded-lg transition duration-75 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
                  >Jadwal Mata Pelajaran</a
                >
              </li>
            </ul>
          </li>
        </ul>
      </div>
    </aside>
  </div>
</template>
