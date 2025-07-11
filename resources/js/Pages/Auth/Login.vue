<script setup>
import { ref, watch, computed, onMounted } from 'vue';
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';
import Swal from 'sweetalert2';
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import axios from 'axios';

const showNisHelp = ref(false);

// Pastikan CSRF token ada di meta tag
const csrfMetaTag = document.querySelector('meta[name="csrf-token"]');
if (csrfMetaTag) {
  const csrfToken = csrfMetaTag.getAttribute('content');
  axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
  console.log('✅ CSRF token loaded:', csrfToken);
} else {
  console.warn('⚠️ CSRF meta tag not found!');
}

const props = defineProps({
  students: {
    type: Array,
    default: () => [],
  },
  canResetPassword: {
    type: Boolean,
    default: false,
  },
  status: {
    type: String,
    default: '',
  },
  role: {
    type: String,
    default: '', // Role bisa berupa 'student', 'teacher', 'admin', dsb.
  },
});

//console.log('Daftar students dari props:', props.students);

const form = useForm({
  credential: '',
  password: '',
  remember: false,
  role: '',
});

const userName = ref('');
const errorMessage = ref(''); // Untuk menyimpan pesan error jika login gagal
const successMessage = ref('');
const studentId = ref('');
// Fetch session data
const fetchSessionData = async () => {
  try {
    const response = await axios.get('/api/session-data');
    userName.value = response.data.name;
  } catch (error) {
    console.error('There was an error fetching the session data:', error);
  }
};

const studentName = ref('');
// Watch untuk memantau perubahan student_id
watch(
  () => form.student,
  (newValue, oldValue) => {
    //console.log('Student ID berubah:', { oldValue, newValue });
  }
);

// Fungsi submit login
const submit = async () => {
  console.log('🔵 [submit()] Fungsi login dipanggil');
  errorMessage.value = '';
  successMessage.value = '';

  try {
    console.log('🟡 [submit()] Meminta CSRF cookie...');
    await axios.get('/sanctum/csrf-cookie', { withCredentials: true });
    console.log('✅ [submit()] CSRF cookie diterima.');

    let student_id = null;
    let student_name = '';

    if (form.role === 'student') {
      console.log(
        `🟡 [submit()] Role terpilih: 'student', mencari NIS: ${form.credential}`
      );
      try {
        const response = await axios.get(
          `/api/students/by-nis/${form.credential}`
        );
        console.log('✅ [submit()] Respon dari API siswa:', response.data);

        const student = response.data;
        student_id = student.id;
        student_name = student.name;

        console.log(
          `🟢 [submit()] Data siswa ditemukan: ID = ${student_id}, Nama = ${student_name}`
        );
      } catch (e) {
        console.error(
          '❌ [submit()] Siswa tidak ditemukan berdasarkan NIS:',
          form.credential,
          e
        );
        Swal.fire({
          icon: 'error',
          title: 'NIS Tidak Ditemukan',
          text: 'Siswa dengan NIS tersebut tidak ditemukan. Periksa kembali.',
        });
        return;
      }
    } else {
      console.log(
        `🟡 [submit()] Role terpilih: '${form.role}', login menggunakan email.`
      );
    }

    const payload = {
      credential: form.credential,
      password: form.password,
      role: form.role,
    };

    if (form.role === 'student') {
      payload.student_id = student_id;
      payload.student_name = student_name;
    }

    console.log(
      '📦 [submit()] Payload login yang dikirim ke backend:',
      payload
    );

    const loginResponse = await axios.post('/login', payload, {
      withCredentials: true,
    });

    console.log(
      '✅ [submit()] Login berhasil. Respon dari server:',
      loginResponse.data
    );
    successMessage.value = 'Login berhasil!';

    // 🔄 Redirect berdasarkan role
    if (form.role === 'student') {
      console.log(`➡️ [submit()] Redirect ke /student-dashboard/${student_id}`);
      router.visit(
        `/student-dashboard/${student_id}?student_name=${encodeURIComponent(
          student_name
        )}`
      );
    } else if (form.role === 'teacher') {
      router.visit('/teacher-dashboard');
    } else if (form.role === 'admin') {
      router.visit('/admin-dashboard');
    } else if (form.role === 'parent') {
      router.visit('/parent-dashboard');
    } else {
      console.warn('⚠️ [submit()] Role tidak dikenali:', form.role);
    }
  } catch (error) {
    console.error(
      '❌ [submit()] Error saat login:',
      error.response?.data || error.message
    );
    const message =
      error.response?.data?.message || 'Login gagal. Silakan coba lagi.';

    // ✅ SweetAlert2 tampilkan error login
    Swal.fire({
      icon: 'error',
      title: 'Login Gagal',
      text: message,
    });

    errorMessage.value = message;
  }
};

onMounted(async () => {
  try {
  } catch (error) {
    if (
      error.response?.status === 401 &&
      window.location.pathname !== '/login'
    ) {
      console.warn('🚫 User belum login, redirect ke login.');
      router.visit('/login');
    }
  }
});

// Watch role, misal untuk reset form.student jika bukan student
watch(
  () => form.role,
  (newRole) => {
    if (newRole !== 'student') {
      form.student = null;
      form.student_id = null;
    }
  }
);
</script>

<template>
  <!-- --->
  <Head title="Login" />
  <div
    class="min-h-screen bg-gradient-to-tr from-[#12bdee] via-[#6be4f4] to-[#c6f6ff] flex items-center justify-center px-4"
  >
    <div class="px-4 sm:px-6 py-8 sm:py-12">
      <div
        class="bg-white rounded-2xl shadow-2xl flex flex-col md:flex-row w-full max-w-[95%] sm:max-w-[500px] md:max-w-4xl mx-auto overflow-hidden"
      >
        <!-- Left Section (Image + School Info) -->
        <div
          class="md:w-1/2 bg-[#f5f9fc] p-5 flex flex-col justify-center items-center"
        >
          <img
            src="/images/barunawati.webp"
            class="w-2/3 h-auto object-contain mb-6 drop-shadow-md"
            alt="Logo Barunawati"
          />
          <h2 class="text-3xl font-bold text-center text-[#064663]">
            SMA BARUNAWATI SURABAYA
          </h2>
        </div>

        <!-- Right Section (Form) -->
        <div class="md:w-1/2 w-full p-10 bg-white">
          <h2 class="text-2xl font-bold text-center text-[#064663] mb-6">
            Selamat Datang 
          </h2>

          <form @submit.prevent="submit" class="space-y-5">
            <!-- Role -->
            <div>
              <label for="role" class="block text-gray-700 mb-1 font-semibold"
                >Pilih Role</label
              >
              <select
                id="role"
                v-model="form.role"
                class="w-full border border-gray-300 p-2 rounded-lg focus:ring-2 focus:ring-[#12bdee] focus:outline-none"
                required
              >
                <option value="" disabled>Pilih Role</option>
                <option value="student">Siswa</option>
                <option value="teacher">Guru</option>
                <option value="admin">Admin</option>
                <option value="parent">Orang Tua</option>
              </select>
            </div>
            <!-- NIS atau Email -->
            <div class="mb-6 space-y-2">
              <!-- Label Input -->
              <label
                for="credential"
                class="block text-sm font-semibold text-gray-700"
              >
                {{
                  form.role === 'student' ? 'Nomor Induk Siswa (NIS)' : 'Email'
                }}
              </label>

              <!-- Input Field -->
              <TextInput
                id="credential"
                :type="form.role === 'student' ? 'text' : 'email'"
                v-model="form.credential"
                :placeholder="
                  form.role === 'student'
                    ? 'Contoh: 22004567'
                    : 'contoh@email.com'
                "
                class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-[#12bdee] focus:outline-none transition duration-150"
                required
                autocomplete="username"
              />

              <!-- Tombol Bantuan NIS (Hanya untuk siswa) -->
              <div v-if="form.role === 'student'" class="flex justify-end">
                <button
                  @click="showNisHelp = true"
                  type="button"
                  class="mt-2 text-sm text-blue-600 hover:underline"
                >
                  Tidak tahu NIS? Klik di sini.
                </button>
              </div>

              <!-- Modal Bantuan NIS -->
              <div
                v-if="showNisHelp"
                class="fixed inset-0 bg-black/60 z-[9999] flex items-center justify-center"
              >
                <div
                  class="bg-white rounded-2xl shadow-xl w-[90%] max-w-md p-6 relative"
                >
                  <!-- Close Button -->
                  <button
                    @click="showNisHelp = false"
                    class="absolute top-3 right-3 text-gray-500 hover:text-gray-700 text-xl"
                  >
                    &times;
                  </button>

                  <h2 class="text-xl font-bold mb-4 text-center">
                    Cara Mengetahui NIS
                  </h2>

                  <ul class="list-disc list-inside text-gray-700 space-y-2">
                    <li>Lihat di kartu pelajar atau rapor.</li>
                    <li>
                      Tanyakan langsung ke wali kelas atau Tata Usaha (TU).
                    </li>
                    <li>
                      Jika kamu belum menerima NIS, segera hubungi pihak
                      sekolah.
                    </li>
                  </ul>

                  <button
                    @click="showNisHelp = false"
                    class="mt-6 w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 rounded-lg"
                  >
                    Oke, Mengerti
                  </button>
                </div>
              </div>
            </div>

            <!-- Password -->
            <div>
              <label
                for="password"
                class="block text-gray-700 mb-1 font-semibold"
                >Password</label
              >
              <TextInput
                id="password"
                type="password"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#12bdee] focus:outline-none"
                v-model="form.password"
                required
                autocomplete="current-password"
                placeholder="Masukkan Password"
              />
            </div>

            <!-- Submit Button -->
            <PrimaryButton
              type="submit"
              class="w-full bg-[#12bdee] hover:bg-[#0fa8d1] text-white font-semibold py-2 rounded-lg transition duration-200 ease-in-out shadow-md flex items-center justify-center"
              :class="{ 'opacity-50': form.processing }"
              :disabled="form.processing"
            >
              Login
            </PrimaryButton>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>
