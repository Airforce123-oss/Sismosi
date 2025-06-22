<script setup>
import { initFlowbite } from 'flowbite';
import SidebarAdmin from '@/Components/SidebarAdmin.vue';
import Pagination from '../../Components/Pagination3.vue';
import { Link, Head, useForm, usePage, router } from '@inertiajs/vue3';
import { onMounted, ref, watch, computed } from 'vue';
import Swal from 'sweetalert2';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import EditJadwalMapel from './editJadwalMapel.vue';
const props = defineProps({
  auth: { type: Object },
  schedule: {
    type: Array,
    default: () => [],
  },
  master_mapel: {
    type: Object,
    required: true,
  },
  classes_for_student: {
    type: Object,
    required: true,
  },
  wali_kelas: {
    type: Object,
    default: () => ({ data: [] }),
  },
  teachers: Array,
  kelas_id: {
    type: Number,
    required: true,
  },
});

const selectedMapelItem = ref(null);
const showEdit = ref(false);

const showEditJadwal = ref(false);
const selectedMapel = ref(null);
const kelasId = ref(props.kelas_id);

const editJadwal = (item) => {
  selectedMapel.value = {
    jam_ke: item.jam_ke,
    mapel: item.mapel,
    kelas: item.kelas,
    guru_id: item.guru_id,
    wali_kelas: item.wali_kelas,
    day: item.day,
    jam: item.jam,
    tahun: item.tahun,
    mapel_id: item.mapel_id ?? null,
  };
  showEditJadwal.value = true;
};

const pageNumber = ref(props.classes_for_student.meta.current_page || 1);
console.log('isi pagenumber:', pageNumber.value);
const itemsPerPage = ref(10);
const currentPage = ref(1);
const perPage = ref(5);
pageNumber.value = 1;
console.log(pageNumber.value); // Akses dengan .value

const updatedPageNumber = (link) => {
  if (!link.url) {
    console.log('❌ Link tidak memiliki URL, abaikan klik.');
    return;
  }

  const page = new URL(link.url).searchParams.get('page');
  console.log('🔄 Page yang diklik:', page);

  pageNumber.value = Number(page); // ini akan trigger watch() dan fetch ulang data via axios
};

const paginatedFlatSchedule = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  const end = start + itemsPerPage.value;
  return flatSchedule.value.slice(start, end);
});

const teachers = ref([]);

const fetchTeachers = () => {
  try {
    // Menggunakan data yang diterima langsung dari props
    const teachersData = props.teachers; // Asumsi bahwa data guru diteruskan sebagai props

    // Memastikan data guru valid
    if (teachersData && Array.isArray(teachersData)) {
      // Proses data guru (seperti penambahan nama atau atribut lain)
      teachersData.forEach((teacher) => {
        //console.log('Nama Guru:', teacher.name); // Menampilkan nama guru
      });

      // Perbarui data teachers tanpa menghapus data yang ada
      teachersData.forEach((newTeacher) => {
        const index = teachers.value.findIndex(
          (teacher) => teacher.id === newTeacher.id
        );

        if (index === -1) {
          teachers.value.push(newTeacher); // Jika data baru, tambahkan
        } else {
          teachers.value[index] = newTeacher; // Jika ada data lama, update
        }
      });
    } else {
      console.error('Invalid or empty data for teachers:', teachersData);
    }
  } catch (error) {
    console.error('Error fetching teachers:', error);
  }
};

console.log('Classes for Student:', props.classes_for_student);
console.log('Teachers:', props.teachers);
console.log('Classes Data:', props.classes_for_student.data);

const form = useForm({
  name: props.auth?.user?.name || '',
  email: props.auth?.user?.email || '',
  role_type: props.auth?.user?.role_type || '',
});

const searchTerm = ref('');

const waliKelas = ref(props.wali_kelas || { data: [] });
console.log('Wali Kelas:', waliKelas.value);

const kelasUrl = computed(() => {
  const url = new URL(route('matapelajaran.index'));
  console.log('URL manual:', url);
  url.searchParams.set('page', currentPage.value); // Gunakan currentPage
  if (searchTerm.value) {
    url.searchParams.set('search', searchTerm.value);
  }
  return url;
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

const selectedKelas = ref(props.kelas_id || '');
console.log('Selected Kelas:', selectedKelas.value);

// HARI
const days = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'];

const schedule = ref({
  data: [],
  meta: {
    total: 0,
    current_page: 1,
    per_page: 5,
    last_page: 1,
    from: 0,
    to: 0,
  },
});

const baseUrl = 'http://127.0.0.1:8000/jadwal';

const loadSchedule = async (id) => {
  if (id === null || id === undefined) {
    try {
      const response = await axios.get('/api/jadwal', {
        params: { page: pageNumber.value, kelas_id: selectedKelas.value },
      });

      const { data, meta } = response.data;

      const currentPage = Number(meta.current_page);
      const perPage = Number(meta.per_page);
      const total = Number(meta.total);
      const lastPage = Number(meta.last_page);

      schedule.value = {
        data,
        meta: {
          current_page: currentPage,
          per_page: perPage,
          total: total,
          last_page: lastPage,
          from: (currentPage - 1) * perPage + 1,
          to: (currentPage - 1) * perPage + data.length,
          links: {
            first: `${baseUrl}?page=1`,
            last: `${baseUrl}?page=${lastPage}`,
            next:
              currentPage < lastPage
                ? `${baseUrl}?page=${currentPage + 1}`
                : null,
            prev: currentPage > 1 ? `${baseUrl}?page=${currentPage - 1}` : null,
          },
        },
      };

      console.log(
        '📦 Semua jadwal dari semua kelas (paginasi lengkap):',
        schedule.value
      );
    } catch (error) {
      console.error('❌ Gagal memuat semua jadwal:', error);
      schedule.value = {
        data: [],
        meta: {
          current_page: 1,
          per_page: 5,
          total: 0,
          last_page: 1,
          from: 0,
          to: 0,
          links: {
            first: `${baseUrl}?page=1`,
            last: `${baseUrl}?page=1`,
            next: null,
            prev: null,
          },
        },
      };
    }
    return;
  }

  const kelasId = parseInt(id, 10);
  console.log('Selected Kelas (before parsing):', id);

  if (isNaN(kelasId) || kelasId <= 0) {
    console.error(
      `❗ Kelas ID tidak valid: "${id}". Pastikan ID berupa angka lebih besar dari 0.`
    );
    schedule.value = {
      data: [],
      meta: {
        current_page: 1,
        per_page: 5,
        total: 0,
        last_page: 1,
        from: 0,
        to: 0,
        links: {
          first: `${baseUrl}?page=1`,
          last: `${baseUrl}?page=1`,
          next: null,
          prev: null,
        },
      },
    };
    return;
  }

  console.log('Parsed ID:', kelasId);

  try {
    const response = await axios.get(route('jadwal.get'), {
      params: {
        page: pageNumber.value,
        per_page: 5,
        kelas_id: kelasId,
      },
    });

    const { data, meta } = response.data;

    schedule.value = {
      data,
      meta: {
        current_page: Number(meta.current_page),
        per_page: Number(meta.per_page),
        total: Number(meta.total),
        last_page: Number(meta.last_page),
        from: (Number(meta.current_page) - 1) * Number(meta.per_page) + 1,
        to:
          (Number(meta.current_page) - 1) * Number(meta.per_page) + data.length,
        links: {
          first: `${baseUrl}?page=1&kelas_id=${kelasId}`,
          last: `${baseUrl}?page=${meta.last_page}&kelas_id=${kelasId}`,
          next:
            meta.current_page < meta.last_page
              ? `${baseUrl}?page=${meta.current_page + 1}&kelas_id=${kelasId}`
              : null,
          prev:
            meta.current_page > 1
              ? `${baseUrl}?page=${meta.current_page - 1}&kelas_id=${kelasId}`
              : null,
        },
      },
    };

    console.log('✅ Schedule loaded untuk kelas tertentu:', schedule.value);
  } catch (error) {
    console.error('❌ Gagal mengambil jadwal:', error.response?.data || error);
    schedule.value = {
      data: [],
      meta: {
        current_page: 1,
        per_page: 5,
        total: 0,
        last_page: 1,
        from: 0,
        to: 0,
        links: {
          first: `${baseUrl}?page=1`,
          last: `${baseUrl}?page=1`,
          next: null,
          prev: null,
        },
      },
    };
  }
};

function getTeacherNameById(id) {
  //console.log('📌 Mencari nama guru untuk ID:', id);

  if (typeof id === 'string' && id.includes(',')) {
    const ids = id.split(',').map((s) => Number(s.trim()));
    console.log('🔍 Beberapa ID yang akan dicari:', ids);

    const foundTeachers = teachers.value.filter((t) => ids.includes(t.id));
    console.log(
      '✅ Guru ditemukan:',
      foundTeachers.map((t) => t.name)
    );

    return foundTeachers.map((t) => t.name).join(', ');
  } else {
    const teacher = teachers.value.find((t) => t.id === Number(id));
    //console.log('✅ Guru ditemukan:', teacher?.name ?? 'Tidak ditemukan');

    return teacher ? teacher.name : null;
  }
}

const flatSchedule = computed(() => {
  const scheduleArray = Array.isArray(schedule.value)
    ? schedule.value
    : Object.values(schedule.value).flat();

  let counter = 1;

  return scheduleArray.flatMap((slot) => {
    if (
      !slot ||
      typeof slot !== 'object' ||
      !slot.jadwal ||
      typeof slot.jadwal !== 'object' ||
      Array.isArray(slot.jadwal)
    ) {
      return [];
    }

    return days
      .filter((day) => {
        const data = slot.jadwal[day];
        return data && typeof data === 'object' && data.mapel;
      })
      .map((day) => {
        const data = slot.jadwal[day];

        return {
          id: counter++,
          jam_ke: slot.jam_ke ?? '-',
          jam: slot.jam ?? '-',
          day,
          mapel: data?.mapel ?? '-',
          mapel_id: data?.mapel_id ?? null,
          kelas: data?.kelas ?? '-',
          guru: data?.guru ?? null,
          guru_id: data?.guru_id ?? null,
          tahun: data?.tahun ?? '-',
          wali_kelas: data?.wali_kelas ?? 'Tidak ada wali',
        };
      });
  });
});

console.log('DEBUG — days:', days);
const entries = ref([]);
console.log('isi', entries.value);

const selectedMapelModal = ref('');
console.log(
  'Selected Mapel:',
  selectedMapelModal.value,
  typeof selectedMapelModal.value
);

const editingSlot = ref({ jamKe: null, hari: '' });

const isTimeSlotMatching = (timeSlot, targetTime) => {
  const parseTime = (time) => {
    const [hours, minutes] = time.split(':');
    return parseInt(hours) * 60 + parseInt(minutes); // Mengubah waktu menjadi menit
  };

  // Mengubah jam yang ada menjadi format menit
  const [start, end] = timeSlot.split(' - ').map(parseTime);
  const [targetStart, targetEnd] = targetTime.split(' - ').map(parseTime);

  // Memeriksa apakah target time berada dalam rentang waktu yang ada
  return targetStart >= start && targetEnd <= end;
};

// Fungsi untuk mencari apakah ada slot yang sesuai
const openEditModal = (jamKe, day) => {
  const selectedItem = flatSchedule.value.find(
    (item) => item.jam_ke === jamKe && item.day === day
  );

  if (selectedItem && selectedItem.mapel_id) {
    router.visit(route('matapelajaran.edit', selectedItem.mapel_id), {
      data: {
        kelas_id: props.kelas_id,
        currentPage: pageNumber.value,
        itemsPerPage: perPage.value,
      },
    });
  } else {
    console.warn('❌ mapel_id tidak ditemukan atau item kosong');
  }
};

const handleDelete = (id) => {
  Swal.fire({
    title: 'Yakin hapus jadwal ini?',
    text: 'Tindakan ini tidak bisa dibatalkan!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#6b7280',
    confirmButtonText: 'Ya, hapus!',
    cancelButtonText: 'Batal',
  }).then((result) => {
    if (result.isConfirmed) {
      router.delete(route('matapelajaran.deleteJadwal', id), {
        preserveScroll: true,
        onSuccess: (page) => {
          // ✅ Tampilkan alert sukses
          Swal.fire({
            title: 'Terhapus!',
            text: 'Jadwal berhasil dihapus.',
            icon: 'success',
            confirmButtonText: 'OK',
          });
        },
        onError: (errors) => {
          // ❌ Alert jika gagal
          Swal.fire({
            title: 'Gagal!',
            text: 'Terjadi kesalahan saat menghapus.',
            icon: 'error',
          });
        },
      });
    }
  });
};

onMounted(() => {
  fetchTeachers();
  loadSchedule(null);
  initFlowbite();
});

watch(selectedKelas, (newVal) => {
  console.log('Selected Kelas changed to:', newVal);
  const id = parseInt(newVal, 10);
  if (isNaN(id)) {
    console.warn('❗ kelas_id tidak valid. Jadwal tidak dimuat.');
    schedule.value = [];
    return;
  }

  loadSchedule(id); // Pastikan loadSchedule menerima ID numerik
});

watch(
  () => schedule.value.data,
  (newVal) => {
    if (Array.isArray(newVal) && newVal.length > 0) {
      console.log('🎯 Jadwal masuk lewat watch:', newVal);
      // logika lain...
    } else {
      console.log('⚠️ Schedule belum siap atau kosong:', newVal);
    }
  }
);

watch(pageNumber, () => {
  if (selectedKelas.value === '') {
    console.log('📦 Tidak ada kelas dipilih, muat semua jadwal.');
    loadSchedule(null); // ini akan trigger getAllJadwal
  } else {
    console.log('🎯 Kelas dipilih:', selectedKelas.value);
    loadSchedule(selectedKelas.value);
  }
});

console.log('schedule di parent:', schedule.value);
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
      <Head title="Setting Jadwal Mata Pelajaran" />

      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="p-6 space-y-6">
          <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
              <h1 class="text-3xl font-semibold text-gray-900">
                Jadwal Mata Pelajaran
              </h1>
              <p class="mt-2 text-sm text-gray-700">-</p>
            </div>

            <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
              <!-- Link untuk tambah Jadwal Mata Pelajaran -->
              <Link
                :href="route('jadwalmataPelajarans.createjadwalmatapelajaran')"
                class="btn btn-primary modal-title fs-5 inline-flex items-center gap-x-2 py-2 px-4 text-sm font-medium text-white border border-transparent rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
              >
                <i class="fa fa-plus mr-2"></i> Tambah Jadwal Mata Pelajaran
              </Link>
            </div>
          </div>

          <!-- TABEL JADWAL -->
          <div
            v-show="true"
            class="overflow-x-auto bg-white shadow-lg rounded-lg"
          >
            <table class="min-w-full bg-white border border-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th
                    class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6"
                  >
                    ID
                  </th>
                  <th
                    class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6"
                  >
                    Jam Ke
                  </th>
                  <th
                    class="py-3.5 px-3 text-left text-sm font-semibold text-gray-900"
                  >
                    Mata Pelajaran
                  </th>
                  <th
                    class="py-3.5 px-3 text-left text-sm font-semibold text-gray-900"
                  >
                    Kelas
                  </th>
                  <th
                    class="py-3.5 px-3 text-left text-sm font-semibold text-gray-900"
                  >
                    Guru
                  </th>
                  <th
                    class="py-3.5 px-3 text-left text-sm font-semibold text-gray-900"
                  >
                    Wali Kelas
                  </th>
                  <th
                    class="py-3.5 px-3 text-left text-sm font-semibold text-gray-900"
                  >
                    Hari
                  </th>
                  <th
                    class="py-3.5 px-3 text-left text-sm font-semibold text-gray-900"
                  >
                    Jam
                  </th>
                  <th
                    class="py-3.5 px-3 text-left text-sm font-semibold text-gray-900"
                  >
                    Tahun
                  </th>
                  <th
                    class="py-3.5 px-3 text-left text-sm font-semibold text-gray-900"
                  >
                    Aksi
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 bg-white">
                <tr v-if="!flatSchedule.length">
                  <td
                    colspan="10"
                    class="text-center py-4 text-sm text-gray-500"
                  >
                    Tidak ada jadwal tersedia
                  </td>
                </tr>
                <tr
                  v-for="(item, index) in paginatedFlatSchedule"
                  :key="`${item.jam_ke}-${item.day}-${index}`"
                  class="hover:bg-gray-50"
                >
                  <td
                    class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6"
                  >
                    <span v-if="pageNumber && perPage">
                      {{
                        (Number(pageNumber) - 1) * Number(perPage) +
                        Number(index) +
                        1
                      }}
                    </span>
                    <span v-else>Loading...</span>
                  </td>
                  <td
                    class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6"
                  >
                    {{ item.jam_ke }}
                  </td>
                  <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900">
                    {{ item.mapel || '-' }}
                  </td>
                  <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900">
                    {{ item.kelas || '-' }}
                  </td>
                  <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900">
                    {{ getTeacherNameById(item.guru_id) }}
                  </td>
                  <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900">
                    {{ item.wali_kelas || '-' }}
                  </td>
                  <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900">
                    {{ item.day }}
                  </td>
                  <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900">
                    {{ item.jam || '-' }}
                  </td>
                  <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900">
                    {{ item.tahun || '-' }}
                  </td>
                  <td
                    class="whitespace-nowrap px-3 py-4 text-sm text-right text-gray-900"
                  >
                    <Link
                      :href="route('matapelajaran.editJadwal', item.mapel_id)"
                      :data="{
                        jam_ke: item.jam_ke,
                        hari: item.day,
                        guru_id: item.guru_id,
                        wali_kelas: item.wali_kelas,
                        kelas: item.kelas,
                        jam: item.jam,
                        tahun: item.tahun,
                        ...(props.kelas_id !== 0
                          ? { kelas_id: props.kelas_id }
                          : {}),
                        currentPage: pageNumber,
                        itemsPerPage: perPage,
                      }"
                      method="get"
                      class="text-indigo-600 hover:text-indigo-900 mr-3"
                    >
                      Edit
                    </Link>
                    <button
                      @click="handleDelete(item.id)"
                      class="text-red-600 hover:text-red-800"
                    >
                      Hapus
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
            <!--  <pre>{{ JSON.stringify(schedule, null, 2) }}</pre>-->
          </div>
          <Pagination :data="schedule" :updatedPageNumber="updatedPageNumber" />
        </div>
      </div>
      <div v-for="(item, index) in schedule.value" :key="index">
        <p>Jam Ke: {{ item.jam_ke }}</p>
        <p>Jam: {{ item.jam }}</p>
        <div v-for="(hari, day) in item.jadwal" :key="day">
          <p>
            {{ day.charAt(0).toUpperCase() + day.slice(1) }}:
            <span v-if="hari">{{ hari.mapel }} ({{ hari.kelas }})</span>
            <span v-else>-</span>
          </p>
        </div>
      </div>
      <EditJadwalMapel
        v-if="showEditJadwal && selectedMapel"
        :mapel="selectedMapel"
        :kelas_id="kelasId"
        @close="showEditJadwal = false"
      />
    </main>

    <!-- end1-->

    <!-- Sidebar -->
    <SidebarAdmin />
  </div>
</template>
