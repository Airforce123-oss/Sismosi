<script setup>
import { ref, onMounted, nextTick, watch, computed } from 'vue';
import { initFlowbite } from 'flowbite';
import Swal from 'sweetalert2';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import SidebarParent from '@/Components/SidebarParent.vue';
import { Link, useForm, usePage, Head } from '@inertiajs/vue3';
import axios from 'axios';
const userName = ref('');
const { props } = usePage();
const showAddModal = ref(false);
const form = useForm({
  name: props.auth.user.name,
  email: props.auth.user.email,
  role_type: props.auth.user.role_type,
});

const students = ref(props.students || []);

const kelasFilter = ref('');
console.log('Students:', students.value);
const siswaFilter = ref('');
const kelasList = ref(props.kelasList || []);

const filteredStudentsByKelas = computed(() => {
  if (!form.selected_kelas) return [];
  return students.value.filter(
    (s) => s.class && s.class.name === form.selected_kelas
  );
});

const filteredStudents = computed(() => {
  let result = students.value;
  if (kelasFilter.value) {
    result = result.filter(
      (s) => kelasList.value[s.class_id - 1] === kelasFilter.value
    );
  }
  if (siswaFilter.value) {
    result = result.filter((s) =>
      s.name.toLowerCase().includes(siswaFilter.value.toLowerCase())
    );
  }
  return result;
});

const showEditModal = ref(false);
const komentarIdToEdit = ref(null);
console.log('komentarIdToEdit:', komentarIdToEdit.value);

const editKomentar = (student) => {
  console.log('Dipanggil editKomentar dengan student:', student);

  const komentarObj = student.komentar_siswas?.[0];

  if (komentarObj) {
    console.log('Komentar ditemukan:', komentarObj);

    komentarIdToEdit.value = komentarObj.id;
    form.komentar = komentarObj.komentar;
    form.selected_kelas = kelasList.value[student.class_id - 1] || '';
    form.student_id = student.id;

    console.log('Form setelah diisi:');
    console.log('komentarIdToEdit:', komentarIdToEdit.value);
    console.log('form.komentar:', form.komentar);
    console.log('form.selected_kelas:', form.selected_kelas);
    console.log('form.student_id:', form.student_id);

    showEditModal.value = true;
    console.log('showEditModal:', showEditModal.value);
  } else {
    console.warn('Tidak ada komentar ditemukan untuk student:', student.name);
    Swal.fire('Info', 'Siswa ini belum memiliki komentar.', 'info');
  }
};

const updateKomentar = async () => {
  if (!form.student_id || !form.komentar) {
    Swal.fire('Peringatan', 'Siswa dan komentar harus diisi!', 'warning');
    return;
  }

  try {
    await axios.put(`/api/komentar-siswa/${komentarIdToEdit.value}`, {
      komentar: form.komentar,
      parent_id: props.parent_id,
    });

    await Swal.fire({
      icon: 'success',
      title: 'Berhasil!',
      text: 'Komentar berhasil diperbarui!',
      timer: 1500,
      showConfirmButton: false,
    });

    showAddModal.value = false;
    form.komentar = '';
    form.student_id = '';
    form.selected_kelas = '';

    // TODO: Refresh data komentar di tabel, jika perlu
  } catch (e) {
    Swal.fire('Gagal', 'Gagal memperbarui komentar', 'error');
    console.error(e.response?.data || e.message);
  }
};

const hapusKomentar = async (student) => {
  const confirmed = await Swal.fire({
    title: 'Hapus komentar?',
    text: `Anda yakin ingin menghapus semua komentar untuk ${student.name}?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Ya, hapus',
    cancelButtonText: 'Batal',
    reverseButtons: true,
  });

  if (confirmed.isConfirmed) {
    try {
      // Menghapus semua komentar yang terkait dengan siswa
      const komentarIds = student.komentar_siswas?.map((k) => k.id) || [];
      if (komentarIds.length === 0) {
        Swal.fire('Info', 'Tidak ada komentar yang bisa dihapus.', 'info');
        return;
      }

      // Lakukan penghapusan satu per satu (atau buat endpoint batch delete jika ingin lebih efisien)
      await Promise.all(
        komentarIds.map((id) => axios.delete(`/api/komentar-siswa/${id}`))
      );

      await Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: 'Komentar telah dihapus.',
        timer: 1500,
        showConfirmButton: false,
      });

      // Refresh data jika diperlukan
      // Misal: fetch ulang data siswa dari backend atau hapus komentar dari students.value
      student.komentar_siswas = [];
    } catch (e) {
      Swal.fire('Gagal', 'Gagal menghapus komentar.', 'error');
      console.error(e.response?.data || e.message);
    }
  }
};

const submitKomentar = async () => {
  if (!form.student_id || !form.komentar) {
    Swal.fire('Peringatan', 'Student dan komentar harus diisi!', 'warning');
    return;
  }
  try {
    await axios.post(
      '/api/komentar-siswa',
      {
        student_id: form.student_id,
        komentar: form.komentar,
      },
      {
        withCredentials: true,
      }
    );

    await Swal.fire({
      icon: 'success',
      title: 'Berhasil!',
      text: 'Komentar berhasil disimpan!',
      timer: 1500,
      showConfirmButton: false,
    });

    form.komentar = '';
    form.student_id = '';
    showAddModal.value = false;
  } catch (e) {
    Swal.fire('Gagal', 'Gagal menyimpan komentar', 'error');
    console.error(e.response?.data || e.message);
  }
};

onMounted(() => {
  initFlowbite();
});

watch(
  kelasList,
  (val) => {
    console.log('kelasList:', val);
  },
  { immediate: true }
);
</script>

<style scoped>
@import url('https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css');
.bg-primary1 {
  background-color: #0e70cc;
}

.bg-success {
  background-color: #28a745;
}

.bg-warning {
  background-color: #ffc107;
}

.bg-cyan {
  background-color: #10b0cc;
}
</style>

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

    <!-- Main -->

    <main class="p-7 md:ml-64 h-screen pt-20">
      <Head title="Komentar Siswa" />
      <h2
        class="text-2xl font-bold mb-2 text-blue-700 text-center tracking-wide"
      >
        Input Komentar untuk Siswa
      </h2>
      <div
        class="mb-6 bg-blue-50 px-4 py-4 rounded-xl shadow-sm w-full max-w-5xl mx-auto"
      >
        <div
          class="flex flex-col md:flex-row md:flex-wrap md:items-center gap-3"
        >
          <!-- Label -->
          <div class="flex items-center gap-2 min-w-max md:w-auto">
            <svg
              class="w-5 h-5 text-blue-500"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              viewBox="0 0 24 24"
            >
              <path d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <label class="font-semibold text-blue-700 whitespace-nowrap"
              >Filter Kelas:</label
            >
          </div>

          <!-- Dropdown Kelas -->
          <div class="flex-1 md:max-w-[160px]">
            <select
              v-model="kelasFilter"
              class="w-full border border-blue-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400 focus:border-blue-400 text-blue-700 bg-white shadow-sm"
            >
              <option value="">Semua Kelas</option>
              <option v-for="kelas in kelasList" :key="kelas" :value="kelas">
                {{ kelas }}
              </option>
            </select>
          </div>

          <!-- Input Cari Nama Siswa -->
          <div class="flex-1 min-w-[180px]">
            <input
              v-model="siswaFilter"
              type="text"
              placeholder="Cari nama siswa..."
              class="w-full border border-blue-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400 focus:border-blue-400 text-blue-700 bg-white shadow-sm"
            />
          </div>

          <!-- Tombol Reset -->
          <div class="flex md:w-auto">
            <button
              v-if="kelasFilter || siswaFilter"
              @click="
                kelasFilter = '';
                siswaFilter = '';
              "
              class="w-full px-4 py-2 rounded bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm transition whitespace-nowrap"
              title="Reset filter"
            >
              Reset
            </button>
          </div>

          <!-- Tombol Tambah Komentar -->
          <div class="flex md:w-auto">
            <button
              @click="showAddModal = true"
              class="w-full flex items-center justify-center gap-2 bg-gradient-to-r from-blue-500 to-blue-700 hover:from-blue-600 hover:to-blue-800 text-white px-5 py-2 rounded shadow transition-all duration-200 whitespace-nowrap"
            >
              <svg
                class="w-5 h-5"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                viewBox="0 0 24 24"
              >
                <path d="M12 4v16m8-8H4" />
              </svg>
              Tambah Komentar
            </button>
          </div>
        </div>
      </div>

      <div
        class="overflow-x-auto rounded-xl shadow-md ring-1 ring-indigo-200 bg-white"
      >
        <hr class="border-blue-200 mb-2" />

        <table
          class="min-w-[900px] w-full border-collapse text-sm leading-normal"
        >
          <thead class="bg-blue-100 text-blue-900 text-xs uppercase">
            <tr class="text-left">
              <th class="px-4 py-3 border-b border-gray-200 align-top w-1/4">
                Nama Siswa
              </th>
              <th
                class="px-4 py-3 border-b border-gray-200 align-top w-[100px]"
              >
                Kelas
              </th>
              <th class="px-4 py-3 border-b border-gray-200 align-top">
                Komentar
              </th>
              <th
                class="px-4 py-3 border-b border-gray-200 align-top w-[180px]"
              >
                Aksi
              </th>
            </tr>
          </thead>

          <tbody>
            <tr
              v-for="student in filteredStudents"
              :key="student.id"
              class="even:bg-blue-50 odd:bg-white hover:bg-blue-100 transition-colors duration-150 group align-top"
            >
              <td
                class="px-4 py-3 border-b border-gray-100 font-medium group-hover:text-blue-800"
              >
                {{ student.name }}
              </td>

              <td class="px-4 py-3 border-b border-gray-100">
                <span
                  class="inline-block px-2 py-0.5 rounded bg-blue-200 text-blue-800 text-xs font-semibold"
                >
                  {{ kelasList[student.class_id - 1] || '-' }}
                </span>
              </td>

              <td class="px-4 py-3 border-b border-gray-100">
                <template v-if="student.komentar_siswas?.length">
                  <div class="space-y-1">
                    <span
                      v-for="komentar in student.komentar_siswas"
                      :key="komentar.id"
                      class="block px-2 py-1 rounded bg-blue-50 text-gray-700 text-sm"
                    >
                      {{ komentar.komentar }}
                    </span>
                  </div>
                </template>
                <span v-else class="text-gray-400">Belum ada komentar</span>
              </td>

              <td class="px-4 py-3 border-b border-gray-100">
                <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                  <button
                    @click="editKomentar(student)"
                    class="flex items-center justify-center gap-1 bg-yellow-400 hover:bg-yellow-500 text-white px-3 py-1.5 rounded shadow text-sm transition"
                    title="Edit"
                  >
                    <svg
                      class="w-4 h-4"
                      fill="none"
                      stroke="currentColor"
                      stroke-width="2"
                      viewBox="0 0 24 24"
                    >
                      <path
                        d="M15.232 5.232l3.536 3.536M9 13l6-6 3 3-6 6H9v-3z"
                      />
                    </svg>
                    Edit
                  </button>

                  <button
                    @click="hapusKomentar(student)"
                    class="flex items-center justify-center gap-1 bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded shadow text-sm transition"
                    title="Hapus"
                  >
                    <svg
                      class="w-4 h-4"
                      fill="none"
                      stroke="currentColor"
                      stroke-width="2"
                      viewBox="0 0 24 24"
                    >
                      <path d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Hapus
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <transition name="fade">
        <div
          v-if="showAddModal"
          class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        >
          <div
            class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-md animate-fadeIn relative"
          >
            <!-- Modal Title -->
            <div class="flex items-center gap-2 mb-4">
              <svg
                class="w-7 h-7 text-blue-600"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                viewBox="0 0 24 24"
              >
                <path
                  d="M17 8h2a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2h2"
                ></path>
                <circle cx="12" cy="7" r="4"></circle>
              </svg>
              <h3 class="text-xl text-center font-bold text-blue-700 flex-1">
                Tambah Komentar Siswa
              </h3>
              <button
                @click="showAddModal = false"
                class="text-gray-400 hover:text-red-500 transition"
                title="Tutup"
              >
                <svg
                  class="w-6 h-6"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                  viewBox="0 0 24 24"
                >
                  <path d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
            <hr class="mb-4 border-blue-100" />
            <form @submit.prevent="submitKomentar">
              <!-- Dropdown Kelas -->
              <div class="mb-3">
                <label class="block mb-1 font-medium">Kelas</label>
                <select
                  v-model="form.selected_kelas"
                  class="w-full border rounded px-2 py-1 focus:ring-2 focus:ring-blue-400"
                >
                  <option value="" disabled>Pilih kelas</option>
                  <option
                    v-for="kelas in kelasList"
                    :key="kelas"
                    :value="kelas"
                  >
                    {{ kelas }}
                  </option>
                </select>
              </div>
              <!-- Dropdown Siswa -->
              <div class="mb-3">
                <label class="block mb-1 font-medium">Siswa</label>
                <select
                  v-model="form.student_id"
                  class="w-full border rounded px-2 py-1 focus:ring-2 focus:ring-blue-400"
                  :disabled="!form.selected_kelas"
                >
                  <option value="" disabled>Pilih siswa</option>
                  <option
                    v-for="student in students.filter(
                      (s) => kelasList[s.class_id - 1] === form.selected_kelas
                    )"
                    :key="student.id"
                    :value="student.id"
                  >
                    {{ student.name }}
                  </option>
                </select>
              </div>
              <div class="mb-3">
                <label class="block mb-1 font-medium">Komentar</label>
                <input
                  v-model="form.komentar"
                  type="text"
                  class="w-full border rounded px-2 py-1 focus:ring-2 focus:ring-blue-400"
                  placeholder="Tulis komentar..."
                />
              </div>
              <div class="flex gap-2 justify-end mt-4">
                <button
                  type="button"
                  @click="showAddModal = false"
                  class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300"
                >
                  Batal
                </button>
                <button
                  type="submit"
                  class="px-4 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white"
                >
                  Simpan
                </button>
              </div>
            </form>
          </div>
        </div>
      </transition>

      <transition name="fade">
        <div
          v-if="showEditModal"
          class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        >
          <div
            class="bg-white rounded-xl shadow-2xl p-6 sm:max-w-lg w-full animate-fadeIn relative"
          >
            <div class="flex items-center gap-2 mb-4">
              <svg
                class="w-7 h-7 text-yellow-600"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                viewBox="0 0 24 24"
              >
                <path d="M15.232 5.232l3.536 3.536M9 13l6-6 3 3-6 6H9v-3z" />
              </svg>
              <h3 class="text-xl font-bold text-blue-700 flex-1 text-center">
                Edit Komentar Siswa
              </h3>
              <button
                @click="showEditModal = false"
                class="text-gray-400 hover:text-red-500 transition"
                title="Tutup"
              >
                <svg
                  class="w-6 h-6"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                  viewBox="0 0 24 24"
                >
                  <path d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <form @submit.prevent="updateKomentar">
              <div class="mb-3">
                <label class="block mb-1 font-medium">Komentar</label>
                <input
                  v-model="form.komentar"
                  type="text"
                  class="w-full border rounded px-2 py-1 focus:ring-2 focus:ring-yellow-400"
                  placeholder="Tulis komentar..."
                />
              </div>
              <div class="flex gap-2 justify-end mt-4">
                <button
                  type="button"
                  @click="showEditModal = false"
                  class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300"
                >
                  Batal
                </button>
                <button
                  type="submit"
                  class="px-4 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white"
                >
                  Simpan
                </button>
              </div>
            </form>
          </div>
        </div>
      </transition>
    </main>

    <!-- Sidebar -->
    <SidebarParent />
  </div>
</template>

<script>
export default {
  setup() {},
};
</script>
