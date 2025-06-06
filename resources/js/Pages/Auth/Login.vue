<script setup>
import { ref, watch, computed, onMounted } from 'vue';
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import axios from 'axios';

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
  email: '',
  password: '',
  remember: false,
  student: null,
  role: '',
});

console.log('Role terpilih:', form.role);

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

const students = ref([]);

const studentName = ref('');
// Watch untuk memantau perubahan student_id
watch(
  () => form.student,
  (newValue, oldValue) => {
    console.log('Student ID berubah:', { oldValue, newValue });
  }
);

const fetchLoggedInStudent = async () => {
  console.log('📥 fetchLoggedInStudent dipanggil');

  try {
    const token = localStorage.getItem('token');
    console.log('Token:', token);

    if (!token) {
      console.error('❗ Token is missing');
      return;
    }

    const response = await axios.get('/api/logged-in-student', {
      headers: {
        Authorization: `Bearer ${token}`,
      },
    });

    console.log('✅ Axios berhasil');
    const loggedInStudent = response.data;
    console.log('✅ Response dari server:', loggedInStudent);

    studentId.value = loggedInStudent.id;
    studentName.value =
      students.value.find((s) => s.id === form.student_id)?.name || '';
  } catch (error) {
    console.error('❌ Error fetching logged-in student:', error);
  }
};

const fetchAllStudents = async () => {
  try {
    console.log('memulai fetchAllStudents, isi awal: ', students.value);
    // Ambil CSRF cookie dulu supaya Laravel Sanctum meng-set session cookie
    await axios.get('/sanctum/csrf-cookie', { withCredentials: true });

    // Request data siswa dengan cookie session otomatis terkirim
    const response = await axios.get('/api/fetch-all-students', {
      withCredentials: true, // Kirim cookie session
    });
    console.log('✅ Response dari server:', response.data); // <- Ini penting
    students.value = response.data.students;
    console.log('✅ Data siswa berhasil diambil:', students.value);
  } catch (error) {
    console.error(
      '❌ Gagal mengambil data siswa:',
      error.response?.data || error.message
    );
    // Jangan langsung redirect di sini agar tidak loop
    throw error; // biarkan error dilempar ke caller
  }
};

// Fungsi submit login
const submit = async () => {
  errorMessage.value = '';
  successMessage.value = '';

  try {
    // Ambil CSRF cookie & session
    await axios.get('/sanctum/csrf-cookie', { withCredentials: true });

    // Ambil data siswa yang dipilih (berisi ID dan nama)
    let student_id = null;
    let student_name = '';

    if (form.role === 'student' && form.student) {
      // Ambil dari selected object
      student_id = form.student.id;
      student_name = form.student.name;
    }

    // Kirim login ke endpoint Laravel
    await axios.post(
      '/login',
      {
        email: form.email,
        password: form.password,
        role: form.role,
        student_id,
        student_name,
      },
      {
        withCredentials: true,
      }
    );

    successMessage.value = 'Login berhasil!';

    // Setelah login, fetch ulang data siswa (opsional)
    await fetchAllStudents();

    // Redirect sesuai role
    if (form.role === 'student') {
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
    }
  } catch (error) {
    console.error(
      '❌ Error during login:',
      error.response?.data || error.message
    );
    errorMessage.value =
      error.response?.data?.message || 'Login gagal. Silakan coba lagi.';
  }
};

onMounted(async () => {
  try {
    await fetchAllStudents();
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

watch(
  () => form.student,
  (newStudent) => {
    if (!newStudent) {
      form.student_id = null;
      console.log('❌ Siswa tidak ditemukan karena nilai baru null/undefined');
      return;
    }

    const matched = students.value.find((s) => s.id === newStudent.id);

    if (matched) {
      form.student_id = matched.id;
      console.log('👤 Siswa dipilih (dari watch):', {
        student_id: matched.id,
        student_name: matched.name,
      });
    } else {
      form.student_id = null;
      console.log('❌ Siswa tidak ditemukan untuk ID:', newStudent.id);
    }
  }
);

watch(
  () => form.student,
  (selected) => {
    if (form.role === 'student' && selected) {
      const url = `/student-dashboard/${
        selected.id
      }?student_name=${encodeURIComponent(selected.name)}`;
      console.log('📌 URL yang akan digunakan untuk redirect:', url);
    }
  }
);
</script>

<template>
  <!-- --->
  <Head title="Login" />
  <div class="bg-[#12bdee] flex items-center justify-center min-h-screen">
    <div class="bg-white shadow-md rounded-lg flex max-w-4xl w-full">
      <div class="w-1/2 p-8 flex flex-col items-center justify-center">
        <img
          src="/images/barunawati.webp"
          class="w-2/3 h-auto object-contain mb-4"
          alt="Gambar Barunawati"
        />
        <h2 class="text-2xl font-bold text-center">SMA BARUNAWATI SURABAYA</h2>
      </div>
      <div class="w-1/2 p-8">
        <h2 class="text-2xl font-bold text-center">SELAMAT DATANG</h2>
        <p class="text-center text-gray-500 mb-6">
          <a href="register" class="text-blue-500">Sign Up</a>
        </p>
        <form @submit.prevent="submit">
          <div class="mb-4">
            <label for="email" class="block text-gray-700">Email</label>
            <TextInput
              id="email"
              type="email"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              v-model="form.email"
              required
              autocomplete="username"
              placeholder="Masukkan Email"
            />
          </div>

          <div class="mb-4">
            <label for="role" class="block text-gray-700">Pilih Role</label>
            <select
              v-model="form.role"
              class="w-full border border-gray-300 p-2 rounded"
            >
              <option value="" disabled>Pilih Role</option>
              <option value="student">Siswa</option>
              <option value="teacher">Guru</option>
              <option value="admin">Admin</option>
              <option value="parent">Orang Tua</option>
            </select>
          </div>

          <div class="mb-4" v-if="form.role === 'student'">
            <label for="student_id" class="block text-gray-700 mb-1"
              >Pilih Siswa</label
            >

            <v-select
              v-if="students.length"
              v-model="form.student"
              :options="students"
              label="name"
              placeholder="Cari siswa..."
              class="w-full"
              searchable
              clearable
              @focus="fetchAllStudents"
            />

            <div v-else class="text-sm text-gray-500">
              Tidak ada data siswa tersedia.
            </div>

            <small class="text-gray-500">Ketik nama siswa untuk mencari.</small>
          </div>

          <div class="mb-4">
            <label for="password" class="block text-gray-700">Password</label>
            <TextInput
              id="password"
              type="password"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              v-model="form.password"
              required
              autocomplete="current-password"
              placeholder="Masukkan Password"
            />
          </div>
          <PrimaryButton
            type="submit"
            class="w-full px-3 py-2 rounded-lg bg-[#12bdee] items-center justify-center"
            style="text-align: center; text-transform: none"
            :class="{ 'opacity-25': form.processing }"
            :disabled="form.processing"
          >
            Login
          </PrimaryButton>
        </form>
      </div>
    </div>
  </div>
</template>
