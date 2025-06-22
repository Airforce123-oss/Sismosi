<script setup>
import { onMounted, ref, computed, watch, nextTick } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { initFlowbite } from 'flowbite';
import SidebarTeacher from '@/Components/SidebarTeacher.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import Pagination from '@/Components/Pagination11.vue';
import Swal from 'sweetalert2';
import { jsPDF } from 'jspdf';
import html2canvas from 'html2canvas';
import 'jspdf-autotable';

const isVisible = ref(false);
const searchQuery = ref('');

const props = defineProps({
  auth: Object,
  classes_for_student: { type: Object, required: true },
  entries: { type: Object, required: true },
  teachers: { type: Object, default: () => ({ data: [] }) },
  genders: { type: Array, default: () => [] },
});

const entries = ref(props.entries);

// Isi awal entries dari props
entries.value = props.entries;

const fetchEntries = (page = 1) => {
  router.get(
    route('bukuPenghubung1'),
    { page },
    {
      preserveScroll: true,
      preserveState: true,
      only: ['entries'],
      onSuccess: (page) => {
        console.log('✅ Data berhasil dimuat ulang:', page.props.entries);
        entries.value = page.props.entries; // hanya satu halaman
      },
      onError: (err) => {
        console.error('❌ Gagal memuat halaman baru:', err);
      },
    }
  );
};

const modalVisible = ref(false);
const modalTitle = ref('Tambah Data');
const form = ref({
  id: null,
  date: '',
  parentName: '',
  studentName: '',
  gender: '',
  class_id: '',
  issue: '',
  action: '',
  note: '',
});

const filteredEntries = computed(() => {
  return entriesComputed.value.filter((entry) =>
    entry.studentName.toLowerCase().includes(searchQuery.value.toLowerCase())
  );
});

const getClassName = (classId) => {
  const classItem = props.classes_for_student.find(
    (item) => item.id === classId
  );
  return classItem ? classItem.name : '-';
};

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
    isVisible.value = !isVisible.value;
  }
};

const deleteEntry = async (id) => {
  const confirm = await Swal.fire({
    title: 'Yakin ingin menghapus data ini?',
    text: 'Tindakan ini tidak dapat dibatalkan!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Ya, hapus!',
    cancelButtonText: 'Batal',
  });

  if (confirm.isConfirmed) {
    try {
      await axios.delete(`/buku-penghubung/${id}`);
      Swal.fire('Terhapus!', 'Data berhasil dihapus.', 'success');

      // Reload data dari server
      reloadEntries();
    } catch (error) {
      console.error('Gagal menghapus:', error.response?.data || error.message);
      Swal.fire('Gagal', 'Terjadi kesalahan saat menghapus data.', 'error');
    }
  }
};

const openModal = (type, entry = null) => {
  modalVisible.value = true;
  nextTick(() => console.log('Modal siap ditampilkan'));

  if (type === 'add') {
    modalTitle.value = 'Tambah Data';
    form.value = {
      id: null,
      date: '',
      parentName: '',
      studentName: '',
      gender: '',
      class_id: '',
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

const handleSubmit = async () => {
  try {
    console.log('Form yang akan disubmit:', form.value);

    if (form.value.id) {
      console.log(`Update data dengan ID ${form.value.id}`);
      await axios.put(`/buku-penghubung1/${form.value.id}`, form.value);
      console.log('Update berhasil');
      Swal.fire('Berhasil', 'Data berhasil diperbarui.', 'success');
    } else {
      console.log('Tambah data baru');
      const response = await axios.post('/buku-penghubung', form.value);
      console.log('Tambah berhasil, response:', response.data);
      Swal.fire('Berhasil', 'Data berhasil ditambahkan.', 'success');
    }

    // Setelah submit sukses, tutup modal dan reload data dari server
    closeModal();
    console.log('Modal ditutup, akan memuat ulang data...');
    await reloadEntries();
  } catch (error) {
    console.error('Gagal submit:', error.response?.data || error.message);
    Swal.fire('Gagal', 'Terjadi kesalahan saat menyimpan data.', 'error');
  }
};

const reloadEntries = () => {
  console.log('Memuat ulang data dari server...');

  router.get(
    route('bukuPenghubung1'),
    {},
    {
      preserveScroll: true,
      preserveState: true,
      only: ['entries'],
      onSuccess: (page) => {
        console.log('Data baru diterima dari server:', page.props.entries);
        entries.value = page.props.entries;
      },
      onError: (error) => {
        console.error('Gagal memuat ulang data:', error);
      },
    }
  );
};

const entriesComputed = computed(() => {
  return Array.isArray(entries.value?.data) ? entries.value.data : [];
});

onMounted(() => {
  fetchEntries();
  console.log('Loaded entries:', entries.value);
  initFlowbite();
});

// --- PDF Export
const exportToPDF = () => {
  const doc = new jsPDF();
  const pageWidth = doc.internal.pageSize.getWidth();
  const margin = 14;

  doc.addImage('/images/jatimsih.png', 'PNG', margin, 10, 20, 20);
  doc.addImage('/images/barunawati.jpeg', 'JPEG', pageWidth - 34, 10, 20, 20);

  doc.setFontSize(11);
  doc.setFont('helvetica', 'bold');
  const titleLines = [
    'BUKU PENGHUBUNG ORANGTUA/WALI MURID',
    'DENGAN WALI KELAS',
    'SMA BARUNAWATI SURABAYA',
    'DINAS PENDIDIKAN',
    'PROVINSI JAWA TIMUR',
  ];

  let currentY = 40;
  titleLines.forEach((line) => {
    const x = (pageWidth - doc.getTextWidth(line)) / 2;
    doc.text(line, x, currentY);
    currentY += 7;
  });

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

  doc.autoTable({
    head: headers,
    body: rows,
    startY: currentY + 10,
    margin: { left: margin, right: margin },
    styles: { fontSize: 10, cellPadding: 2 },
    theme: 'grid',
  });

  doc.save('buku_penghubung.pdf');
};

// --- JPG Export
const exportToJPG = () => {
  const tableElement = document.getElementById('table-to-export');
  if (!tableElement) return console.error('Tabel tidak ditemukan!');
  html2canvas(tableElement, {
    scale: 2,
    useCORS: true,
  })
    .then((canvas) => {
      const imgData = canvas.toDataURL('image/jpeg');
      const link = document.createElement('a');
      link.href = imgData;
      link.download = 'buku_penghubung.jpg';
      link.click();
    })
    .catch((error) => console.error('Gagal membuat gambar:', error));
};

// Total Entries (misalnya untuk tampilan)
const totalEntries = computed(() => {
  if (Array.isArray(entries.value)) {
    return entries.value.length;
  } else if (
    entries.value &&
    typeof entries.value === 'object' &&
    'total' in entries.value
  ) {
    return entries.value.total;
  } else {
    return 0;
  }
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

    <main class="md:ml-64 pt-10 px-4 bg-gray-50 min-h-screen">
      <Head title="Buku Penghubung" />

      <div id="app" class="p-6 bg-white rounded shadow-md">
        <!-- Trigger Tampilkan Opsi -->
        <div class="mb-4">
          <div
            @click="confirmAction"
            class="cursor-pointer p-3 border rounded-lg bg-gray-100 hover:bg-gray-200 text-center shadow"
          >
            <span class="text-lg font-semibold">Tampilkan Opsi</span>
          </div>
        </div>

        <!-- Header Section -->
        <div class="flex flex-col gap-6 md:gap-4 mb-6">
          <div
            class="flex flex-col md:flex-row items-center justify-between gap-4 text-center md:text-left"
          >
            <img
              src="/images/jatimsih.png"
              alt="Left Logo"
              class="h-20 object-contain"
            />
            <h1 class="text-xl md:text-3xl font-bold leading-tight">
              BUKU PENGHUBUNG ORANGTUA/WALI MURID DENGAN WALI KELAS<br />
              SMA BARUNAWATI SURABAYA<br />
              DINAS PENDIDIKAN PROVINSI JAWA TIMUR
            </h1>
            <img
              src="/images/barunawati.jpeg"
              alt="Right Logo"
              class="h-20 object-contain"
            />
          </div>

          <!-- Filter & Aksi -->
          <div
            v-if="isVisible"
            class="flex flex-col sm:flex-row justify-between items-center gap-4"
          >
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Cari Enrollment..."
              class="w-full sm:w-1/3 px-4 py-2 border rounded-md"
            />
            <div class="flex flex-wrap gap-2 w-full sm:w-auto justify-end">
              <button
                @click="openModal('add')"
                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
              >
                <i class="fa fa-plus mr-2"></i> Tambah Data
              </button>
              <button
                @click="exportToPDF"
                class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"
              >
                <i class="fa fa-download mr-2"></i> Ekspor PDF
              </button>
              <button
                @click="exportToJPG"
                class="px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700"
              >
                <i class="fa fa-image mr-2"></i> Ekspor JPG
              </button>
            </div>
          </div>
        </div>

        <!-- Table Section -->
        <div
          class="w-full overflow-x-auto rounded-lg border border-gray-200 shadow-md"
        >
          <table class="w-full text-sm md:text-base text-left bg-white">
            <thead
              class="bg-gray-100 text-gray-700 uppercase text-xs md:text-sm"
            >
              <tr>
                <th class="px-4 py-3 whitespace-nowrap border">ID</th>
                <th class="px-4 py-3 whitespace-nowrap border">Hari/Tanggal</th>
                <th class="px-4 py-3 whitespace-nowrap border">
                  Orang Tua/Wali
                </th>
                <th class="px-4 py-3 whitespace-nowrap border">Nama Siswa</th>
                <th class="px-4 py-3 whitespace-nowrap border">L/P</th>
                <th class="px-4 py-3 whitespace-nowrap border">Kelas</th>
                <th class="px-4 py-3 whitespace-nowrap border">Masalah</th>
                <th class="px-4 py-3 whitespace-nowrap border">
                  Tindak Lanjut
                </th>
                <th class="px-4 py-3 whitespace-nowrap border">Keterangan</th>
                <th class="px-4 py-3 whitespace-nowrap border text-center">
                  Aksi
                </th>
              </tr>
            </thead>
            <tbody class="text-gray-800">
              <tr
                v-for="(entry, index) in filteredEntries"
                :key="entry.id"
                class="hover:bg-gray-50 transition"
              >
                <td class="px-4 py-2 border">
                  {{ (entries.from || 0) + index }}
                </td>
                <td class="px-4 py-2 border">{{ entry.date }}</td>
                <td class="px-4 py-2 border">{{ entry.parentName }}</td>
                <td class="px-4 py-2 border">{{ entry.studentName }}</td>
                <td class="px-4 py-2 border">{{ entry.gender }}</td>
                <td class="px-4 py-2 border">
                  {{ getClassName(entry.class_id) || '-' }}
                </td>
                <td class="px-4 py-2 border">{{ entry.issue }}</td>
                <td class="px-4 py-2 border">{{ entry.action }}</td>
                <td class="px-4 py-2 border">{{ entry.note }}</td>
                <td class="px-4 py-2 border text-center">
                  <div class="flex flex-wrap justify-center gap-1">
                    <button
                      @click="openModal('edit', entry)"
                      class="min-w-[60px] px-2 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600 text-xs text-center"
                    >
                      Edit
                    </button>
                    <button
                      @click="deleteEntry(entry.id)"
                      class="min-w-[60px] px-2 py-1 bg-red-500 text-white rounded hover:bg-red-600 text-xs text-center"
                    >
                      Hapus
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
          <div class="w-full px-4 py-3 bg-white border-t border-gray-200">
            <Pagination :data="entries" :updatedPageNumber="fetchEntries" />
          </div>
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
</template>
