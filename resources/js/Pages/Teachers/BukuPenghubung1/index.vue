<script setup>
import { onMounted, ref, computed, watch, nextTick } from 'vue';
import { Head } from '@inertiajs/vue3';
import { initFlowbite } from 'flowbite';
import SidebarTeacher from '@/Components/SidebarTeacher.vue';
import Swal from 'sweetalert2';
import { jsPDF } from 'jspdf';
import html2canvas from 'html2canvas';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import 'jspdf-autotable';

// Reactive references
const entriesToShow = ref(10);
const entries = ref([]);
const isVisible = ref(false);
const searchQuery = ref('');
const currentPage = ref(1);

const props = defineProps({
  auth: { type: Object },
  classes_for_student: {
    type: Object,
    required: true,
  },
  teachers: { type: Object, default: () => ({ data: [] }) },
  genders: {
    type: Array,
    default: () => [], // Pastikan `genders` adalah array meskipun kosong
  },
});

console.log(
  'Tipe data classes_for_student:',
  Array.isArray(props.classes_for_student)
    ? 'Array'
    : typeof props.classes_for_student
);
console.log('Isi classes_for_student:', props.classes_for_student);

const modalVisible = ref(false);
const modalTitle = ref('Tambah Data');
const form = ref({
  id: null,
  date: '',
  parentName: '',
  studentName: '',
  gender: '',
  class: '',
  issue: '',
  action: '',
  note: '',
});

console.log('Classes for student:', props.classes_for_student);

const classesForStudent = props.classes || {};
console.log(classesForStudent);

const getClassName = (classId) => {
  const classItem = props.classes_for_student.find(
    (item) => item.id === classId
  );
  return classItem ? classItem.name : '-';
};

watch(
  () => props.classes_for_student,
  (newValue) => {
    console.log('Data classes_for_student:', newValue);
  }
);

watch(
  entries,
  () => {
    saveEntriesToLocalStorage(); // Simpan perubahan ke localStorage
  },
  { deep: true } // Pastikan perubahan mendalam pada objek di dalam array juga terdeteksi
);
// Method to save entries to localStorage
const saveEntriesToLocalStorage = () => {
  localStorage.setItem('entries', JSON.stringify(entries.value));
};

// Method to load entries from localStorage
const loadEntriesFromLocalStorage = () => {
  const savedEntries = localStorage.getItem('entries');

  if (savedEntries) {
    // Jika ada data di localStorage, parsing dan tambahkan `class_name`
    entries.value = JSON.parse(savedEntries).map((entry) => {
      // Cari nama kelas berdasarkan class_id
      const classItem = props.classes_for_student.find(
        (item) => item.id === entry.class_id
      );
      return {
        ...entry,
        class_name: classItem ? classItem.name : '-', // Tambahkan `class_name`
      };
    });
  } else {
    // Jika tidak ada data di localStorage, gunakan data default
    const defaultEntries = [
      {
        date: '11 Okt 2023',
        parentName: 'Ahmad Hidayat',
        studentName: 'Amelia Nuzul Ramadhani',
        gender: 'Perempuan',
        class_id: 'X-1',
        issue: 'Terlibat perdebatan keras dengan teman',
        action: 'Diminta menulis surat permintaan maaf dan melakukan refleksi',
        note: 'Konflik berhasil diselesaikan',
      },
      {
        date: '16 Nov 2023',
        parentName: 'Siti Fatimah',
        studentName: 'Annisa Maqfiroh',
        gender: 'Perempuan',
        class_id: 'X-3',
        issue: 'Membuat keributan di perpustakaan',
        action: 'Diberikan teguran lisan dan diminta untuk tidak mengulangi',
        note: 'Siswa menyatakan penyesalan',
      },
      {
        date: '20 Nov 2023',
        parentName: 'Budi Santoso',
        studentName: 'Aryandhra Nathanael Gustiano',
        gender: 'Laki-laki',
        class_id: 'X-1',
        issue: 'Tidak memakai seragam sesuai aturan',
        action:
          'Diminta mengganti seragam sesuai ketentuan sebelum memasuki kelas',
        note: 'Orang tua diberi pemberitahuan',
      },
    ];

    // Tambahkan properti `class_name` untuk data default
    entries.value = defaultEntries.map((entry) => ({
      ...entry,
      class_name: '-', // Default jika kelas belum ditemukan
    }));

    // Simpan data default ke localStorage
    saveEntriesToLocalStorage();
  }
};

// Method to handle actions confirmation
const confirmAction = async () => {
  const result = await Swal.fire({
    title: 'Apakah Anda ingin menampilkan opsi?',
    text: "Klik 'Ya' untuk menampilkan tombol-tombol.",
    icon: 'question',
    showCancelButton: true,
    cancelButtonText: 'Batal',
    confirmButtonText: 'Ya',
    reverseButtons: true,
  });

  if (result.isConfirmed) {
    isVisible.value = !isVisible.value; // Menampilkan tombol jika dikonfirmasi
  }
};

// Computed property to get unique classes from entries
const uniqueClasses = computed(() => {
  return [...new Set(entries.value.map((entry) => entry.class))];
});

// Methods for pagination and marking as read
const markAsRead = (entry) => {
  entry.dibacaWali = true;
};

const deleteEntry = (entry) => {
  entries.value = entries.value.filter((e) => e.id !== entry.id);
};

const prevPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--;
  }
};

const nextPage = () => {
  if (currentPage.value * entriesToShow.value < filteredEntries.value.length) {
    currentPage.value++;
  }
};

const openModal = (type, entry = null) => {
  modalVisible.value = true;
  nextTick(() => {
    console.log('Modal sudah siap untuk ditampilkan');
  });
  if (type === 'add') {
    modalTitle.value = 'Tambah Data';
    form.value = {
      id: null,
      date: '',
      parentName: '',
      studentName: '',
      gender: '',
      class: '',
      issue: '',
      action: '',
      note: '',
    };
  } else if (type === 'edit' && entry) {
    modalTitle.value = 'Edit Data';
    form.value = { ...entry };
  }
};
const closeModal = () => {
  modalVisible.value = false;
};

const handleSubmit = () => {
  if (form.value.id) {
    // Edit existing entry
    const index = entries.value.findIndex((e) => e.id === form.value.id);
    if (index !== -1) entries.value[index] = { ...form.value };
  } else {
    // Add new entry
    form.value.id = Date.now();
    entries.value.push({ ...form.value });
  }
  closeModal();
};

// Initialize Flowbite on component mount
onMounted(() => {
  loadEntriesFromLocalStorage();
  console.log('Loaded entries:', entries.value);
  initFlowbite();
});

// Export to PDF
const exportToPDF = () => {
  console.log('Ekspor ke PDF');
  const doc = new jsPDF();

  // Menentukan ukuran halaman
  const pageWidth = doc.internal.pageSize.getWidth();

  // Menentukan margin
  const margin = 14;

  // Menambahkan logo kiri (jatimsih.png)
  const logoWidth = 20; // lebar logo kiri
  const logoHeight = 20; // tinggi logo kiri
  const logoY = 10; // posisi vertikal logo
  doc.addImage(
    '/images/jatimsih.png',
    'PNG',
    margin,
    logoY,
    logoWidth,
    logoHeight
  );

  // Menambahkan logo kanan (barunawati.jpeg)
  const logoRightWidth = 20; // lebar logo kanan
  const logoRightHeight = 20; // tinggi logo kanan
  const logoRightX = pageWidth - logoRightWidth - margin; // posisi logo kanan
  doc.addImage(
    '/images/barunawati.jpeg',
    'JPEG',
    logoRightX,
    logoY,
    logoRightWidth,
    logoRightHeight
  );

  // Menambahkan judul
  doc.setFontSize(11);
  const titleLines = [
    'BUKU PENGHUBUNG ORANGTUA/WALI MURID',
    'DENGAN WALI KELAS',
    'SMA BARUNAWATI SURABAYA',
    'DINAS PENDIDIKAN',
    'PROVINSI JAWA TIMUR',
  ];

  doc.setFont('helvetica', 'bold');

  let currentY = 40; // Posisi vertikal awal untuk judul (setelah logo)
  titleLines.forEach((line) => {
    const lineWidth =
      (doc.getStringUnitWidth(line) * doc.internal.getFontSize()) /
      doc.internal.scaleFactor;
    const xPos = (pageWidth - lineWidth) / 2; // Posisi horizontal (tengah)
    doc.text(line, xPos, currentY);
    currentY += 7; // Jarak antar baris
  });

  // Menentukan posisi awal tabel
  const startY = currentY + 10; // Jarak antara judul dan tabel

  // Header tabel
  const headers = [
    [
      'No.',
      'Hari/Tanggal',
      'Nama Orang Tua/Wali Murid',
      'Nama Siswa',
      'L/P',
      'Kelas',
      'Uraian Masalah',
      'Tindak Lanjut',
      'Keterangan',
    ],
  ];

  // Isi tabel
  const rows = entries.value.map((entry, index) => [
    index + 1,
    entry.date,
    entry.parentName,
    entry.studentName,
    entry.gender,
    getClassName(entry.class_id) || '-',
    entry.issue,
    entry.action,
    entry.note,
  ]);

  // Menambahkan tabel
  doc.autoTable({
    head: headers,
    body: rows,
    startY: startY,
    margin: { left: margin, right: margin },
    styles: { fontSize: 10, cellPadding: 2 },
    theme: 'grid',
  });

  // Simpan file PDF
  doc.save('buku_penghubung.pdf');
};

// Export to JPG
const exportToJPG = () => {
  console.log('Ekspor ke JPG');
  const tableElement = document.getElementById('table-to-export');

  if (!tableElement) {
    console.error('Tabel tidak ditemukan!');
    return;
  }

  html2canvas(tableElement, {
    scale: 2, // Resolusi lebih tinggi
    useCORS: true, // Mengatasi masalah CORS
  })
    .then((canvas) => {
      const imgData = canvas.toDataURL('image/jpeg');
      const link = document.createElement('a');
      link.href = imgData;
      link.download = 'buku_penghubung.jpg';
      link.click();
    })
    .catch((error) => {
      console.error('Gagal membuat gambar:', error);
    });
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
              aria-hidden="true"
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
              aria-hidden="true"
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
              >SISTEM MONITORING SISWA</span
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
          </button>
          <!-- Apps -->
          <button
            type="button"
            class="p-2 text-gray-500 rounded-lg hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-700 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-600"
          >
            <span class="sr-only">View notifications</span>
          </button>

          <button
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
            class="hidden z-50 my-4 w-56 text-base list-none bg-white rounded divide-y divide-gray-100 shadow dark:bg-gray-700 dark:divide-gray-600 rounded-xl"
            id="dropdown"
          >
            <div class="py-3 px-4">
              <span
                class="block text-sm font-semibold text-gray-900 dark:text-white"
                >{{ $page.props.auth.user.email }}</span
              >
              <span
                class="block text-sm text-gray-900 truncate dark:text-white"
                >{{ $page.props.auth.user.name }}</span
              >
              <span
                class="block text-sm text-gray-900 truncate dark:text-white"
                >{{ $page.props.auth.user.role_type }}</span
              >
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

    <!-- Main -->

    <main class="p-7 md:ml-64 h-screen pt-20">
      <Head title="Buku Penghubung" />
      <div class="container mx-auto px-4 py-6">
        <div class="container mx-auto px-4 py-6">
          <!-- Div trigger untuk menampilkan tombol -->
          <div
            @click="confirmAction"
            class="cursor-pointer mb-4 p-3 border rounded-lg bg-gray-100 hover:bg-gray-200"
          >
            <span class="text-lg text font-semibold">Tampilkan Opsi</span>
          </div>

          <!-- Div yang berisi tombol, hanya muncul jika isVisible true -->
          <div
            v-if="isVisible"
            class="flex flex-col sm:flex-row justify-end sm:justify-between items-center mb-6 gap-4"
          >
            <!-- Filter Pencarian -->
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Cari Enrollment..."
              class="w-full sm:w-auto px-4 py-2 border rounded-md"
            />

            <!-- Tombol Tambah Data -->
            <button
              class="btn btn-primary modal-title fs-5 w-full sm:w-auto"
              @click="openModal('add')"
            >
              <i class="fa fa-plus mr-2"></i> Tambah Data
            </button>

            <!-- Tombol Ekspor ke PDF -->
            <button
              class="btn btn-success modal-title fs-5 w-full sm:w-auto"
              @click="exportToPDF"
            >
              <i class="fa fa-download mr-2"></i> Ekspor PDF
            </button>

            <!-- Tombol Ekspor ke JPG -->
            <button
              class="btn btn-primary modal-title fs-5 w-full sm:w-auto"
              @click="exportToJPG"
            >
              <i class="fa fa-image mr-2"></i> Ekspor JPG
            </button>
          </div>
        </div>
      </div>

      <div id="app" class="p-6 bg-gray-100 min-h-screen">
        <!-- Header Section -->
        <div
          class="flex flex-col md:flex-row items-center justify-between mb-6"
        >
          <img
            src="/images/jatimsih.png"
            alt="Left Logo"
            class="h-20 w-auto object-contain mb-4 md:mb-0"
          />
          <h1 class="text-xl md:text-2xl font-bold text-center md:text-left">
            BUKU PENGHUBUNG ORANGTUA/WALI MURID DENGAN WALI KELAS
            <br />
            SMA BARUNAWATI SURABAYA
            <br />
            DINAS PENDIDIKAN PROVINSI JAWA TIMUR
          </h1>
          <img
            src="/images/barunawati.jpeg"
            alt="Right Logo"
            class="h-20 w-auto object-contain"
          />
        </div>

        <!-- Table Section -->
        <div class="overflow-x-auto">
          <table
            class="min-w-full bg-white border border-gray-200 rounded shadow text-sm md:text-base"
          >
            <thead class="bg-gray-100">
              <tr>
                <th class="border px-2 py-2 md:px-4 md:py-2">No.</th>
                <th class="border px-2 py-2 md:px-4 md:py-2">Hari/Tanggal</th>
                <th class="border px-2 py-2 md:px-4 md:py-2">
                  Nama Orang Tua/Wali Murid
                </th>
                <th class="border px-2 py-2 md:px-4 md:py-2">Nama Siswa</th>
                <th class="border px-2 py-2 md:px-4 md:py-2">L/P</th>
                <th class="border px-2 py-2 md:px-4 md:py-2">Kelas</th>
                <th class="border px-2 py-2 md:px-4 md:py-2">Uraian Masalah</th>
                <th class="border px-2 py-2 md:px-4 md:py-2">Tindak Lanjut</th>
                <th class="border px-2 py-2 md:px-4 md:py-2">Keterangan</th>
                <th class="border px-2 py-2 md:px-4 md:py-2">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(entry, index) in entries"
                :key="entry.id"
                class="hover:bg-gray-50"
              >
                <td class="border px-2 py-2 md:px-4 md:py-2">
                  {{ index + 1 }}
                </td>
                <td class="border px-2 py-2 md:px-4 md:py-2">
                  {{ entry.date }}
                </td>
                <td class="border px-2 py-2 md:px-4 md:py-2">
                  {{ entry.parentName }}
                </td>
                <td class="border px-2 py-2 md:px-4 md:py-2">
                  {{ entry.studentName }}
                </td>
                <td class="border px-2 py-2 md:px-4 md:py-2">
                  {{ entry.gender }}
                </td>
                <td class="border px-2 py-2 md:px-4 md:py-2">
                  {{ getClassName(entry.class_id) || '-' }}
                </td>
                <td class="border px-2 py-2 md:px-4 md:py-2">
                  {{ entry.issue }}
                </td>
                <td class="border px-2 py-2 md:px-4 md:py-2">
                  {{ entry.action }}
                </td>
                <td class="border px-2 py-2 md:px-4 md:py-2">
                  {{ entry.note }}
                </td>
                <td
                  class="border px-2 py-2 md:px-4 md:py-2 flex space-x-1 md:space-x-2"
                >
                  <button
                    class="px-2 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600 text-xs md:text-sm"
                    @click="openModal('edit', entry)"
                  >
                    Edit
                  </button>
                  <button
                    class="px-2 py-1 bg-red-500 text-white rounded hover:bg-red-600 text-xs md:text-sm"
                    @click="deleteEntry(entry.id)"
                  >
                    Hapus
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <!--modal add -->
        <div
          v-if="modalVisible"
          class="fixed inset-0 z-50 bg-black bg-opacity-50 flex justify-center items-center"
        >
          <div class="bg-white p-6 rounded-lg shadow-lg w-96">
            <h2 class="text-lg sm:text-xl font-bold mb-4">
              {{ modalTitle }}
            </h2>
            <form @submit.prevent="handleSubmit">
              <div class="mb-4">
                <label class="block text-sm font-medium mb-1"
                  >Hari/Tanggal</label
                >
                <input
                  type="date"
                  v-model="form.date"
                  class="w-full border px-3 py-2 rounded text-xs sm:text-sm"
                />
              </div>
              <div class="mb-4">
                <label class="block text-sm font-medium mb-1"
                  >Nama Orang Tua/Wali Murid</label
                >
                <input
                  type="text"
                  v-model="form.parentName"
                  class="w-full border px-3 py-2 rounded text-xs sm:text-sm"
                />
              </div>
              <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Nama Siswa</label>
                <input
                  type="text"
                  v-model="form.studentName"
                  class="w-full border px-3 py-2 rounded text-xs sm:text-sm"
                />
              </div>
              <div class="mb-4">
                <label class="block text-sm font-medium mb-1">L/P</label>
                <select
                  v-model="form.gender"
                  class="w-full border px-3 py-2 rounded text-xs sm:text-sm"
                >
                  <option value="L">Laki-laki</option>
                  <option value="P">Perempuan</option>
                </select>
              </div>
              <div class="mb-4">
                <label for="class" class="block mb-2">Kelas</label>
                <select
                  v-model="form.class_id"
                  id="class_id"
                  class="w-full p-2 border rounded"
                  required
                >
                  <option value="">Pilih Kelas</option>
                  <!-- Check if classes data is available before looping -->
                  <option
                    v-for="classItem in props.classes_for_student || []"
                    :key="classItem.id"
                    :value="classItem.id"
                  >
                    {{ classItem.name }}
                  </option>
                  <!-- Fallback message if no data is available -->
                  <option
                    v-if="props.classes_for_student?.length === 0"
                    disabled
                  >
                    No classes available
                  </option>
                </select>
              </div>

              <div class="mb-4">
                <label class="block text-sm font-medium mb-1"
                  >Uraian Masalah</label
                >
                <textarea
                  v-model="form.issue"
                  class="w-full border px-3 py-2 rounded text-xs sm:text-sm"
                ></textarea>
              </div>
              <div class="mb-4">
                <label class="block text-sm font-medium mb-1"
                  >Tindak Lanjut</label
                >
                <textarea
                  v-model="form.action"
                  class="w-full border px-3 py-2 rounded text-xs sm:text-sm"
                ></textarea>
              </div>
              <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Keterangan</label>
                <textarea
                  v-model="form.note"
                  class="w-full border px-3 py-2 rounded text-xs sm:text-sm"
                ></textarea>
              </div>
              <div class="flex justify-end space-x-2">
                <button
                  type="button"
                  class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 text-xs sm:text-sm"
                  @click="closeModal"
                >
                  Batal
                </button>
                <button
                  type="submit"
                  class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 text-xs sm:text-sm"
                >
                  Simpan
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </main>

    <!-- Sidebar -->
    <SidebarTeacher />
  </div>

  <!--

        <div class="p-6 bg-gray-100 min-h-screen">
        <div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow-lg">
            <h1 class="text-2xl font-bold mb-4">Dashboard Wali Murid</h1>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
         
                <div
                    class="p-4 bg-green-100 border border-green-300 rounded-lg flex items-center justify-between"
                >
                    <div>
                        <h2 class="text-lg font-semibold">
                            Informasi/Catatan Saya
                        </h2>
                        <p class="text-sm text-gray-600">0 Catatan</p>
                    </div>
                    <div class="text-green-600">
                        <i class="fas fa-check-circle text-3xl"></i>
                    </div>
                </div>
     
                <div
                    class="p-4 bg-red-100 border border-red-300 rounded-lg flex items-center justify-between"
                >
                    <div>
                        <h2 class="text-lg font-semibold">Belum Saya Baca</h2>
                        <p class="text-sm text-gray-600">0 Catatan</p>
                    </div>
                    <div class="text-red-600">
                        <i class="fas fa-times-circle text-3xl"></i>
                    </div>
                </div>

                <div
                    class="p-4 bg-teal-100 border border-teal-300 rounded-lg flex items-center justify-between"
                >
                    <div>
                        <h2 class="text-lg font-semibold">
                            Informasi/Catatan Guru
                        </h2>
                        <p class="text-sm text-gray-600">0 Catatan</p>
                    </div>
                    <div class="text-teal-600">
                        <i class="fas fa-info-circle text-3xl"></i>
                    </div>
                </div>
   
                <div
                    class="p-4 bg-blue-100 border border-blue-300 rounded-lg flex items-center justify-between"
                >
                    <div>
                        <h2 class="text-lg font-semibold">
                            Semua Informasi/Catatan
                        </h2>
                        <p class="text-sm text-gray-600">0 Catatan</p>
                    </div>
                    <div class="text-blue-600">
                        <i class="fas fa-check-double text-3xl"></i>
                    </div>
                </div>
            </div>
            <div class="mt-6 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                <h2 class="text-xl font-semibold mb-2">
                    BubungON (Buku Penghubung Online)
                </h2>
                <p class="text-gray-700">
                    BubungON (Buku Penghubung Online) adalah sistem yang
                    menghubungkan antara orang tua dengan guru (wali kelas) di
                    sekolah. BubungON (Buku Penghubung Online) merupakan sebuah
                    administrasi yang dibuat sebagai media komunikasi tidak
                    langsung dalam rangka menyampaikan atau memberitahukan
                    hal-hal penting yang menyangkut perkembangan anak di sekolah
                    dan di rumah.
                </p>
            </div>
        </div>
    </div>
    -->
</template>
