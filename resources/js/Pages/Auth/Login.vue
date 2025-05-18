<script setup>
import { ref, watch, computed } from 'vue';
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
  () => form.student_id,
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
    const token = localStorage.getItem('token');
    if (!token) {
      console.warn(
        '⚠️ Token tidak ditemukan di localStorage, melewati fetchAllStudents'
      );
      return;
    }
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;

    const response = await axios.get('/api/fetch-all-students', {
      headers: {
        Authorization: `Bearer ${token}`,
      },
      withCredentials: true,
    });

    students.value = response.data.students;
    console.log('✅ Data siswa berhasil diambil:', students.value);
    console.log('📊 Jumlah siswa yang diambil:', students.value.length);
    console.log('🔍 Isi lengkap students:', students.value);
  } catch (error) {
    console.error(
      '❌ Gagal mengambil data siswa:',
      error.response?.data || error.message
    );
  }
};

// Fungsi submit login
const submit = async () => {
  console.log('>>> submit function dipanggil');
  errorMessage.value = '';
  successMessage.value = '';

  console.log('Data login yang dikirim:', {
    email: form.email,
    password: form.password,
    student_id: form.student_id,
    student_name:
      students.value.find((s) => s.id === form.student_id)?.name || null,
    role: form.role,
  });

  try {
    // 1. Ambil CSRF token dari meta
    const csrfToken = document
      .querySelector('meta[name="csrf-token"]')
      ?.getAttribute('content');
    if (csrfToken) {
      axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
    }

    // 2. Payload login
    const payload = {
      email: form.email,
      password: form.password,
      role: form.role,
    };

    if (form.student) {
      payload.student_id = form.student.id;
      payload.student_name = form.student.name;
    }

    // 3. Ambil CSRF Cookie
    await axios.get('/sanctum/csrf-cookie');

    // 4. Kirim request login
    const response = await axios.post('/api/login', payload);

    // 5. Jika berhasil, simpan token
    if (response.data.token) {
      const token = response.data.token;
      if (form.student) {
        localStorage.setItem('student_name', form.student.name);
      }

      localStorage.setItem('token', token);
      axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
      console.log('✅ Token berhasil disimpan dan diset:', token);
      console.log(
        'Token di localStorage sebelum fetchAllStudents:',
        localStorage.getItem('token')
      );

      await fetchAllStudents();

      const role = response.data.role;

      if (role === 'student') {
        router.visit('/dashboard', {
          method: 'get',
          data: {
            student_id: form.student?.id,
            student_name: form.student?.name,
          },
        });
      } else if (role === 'teacher') {
        router.visit('/teacher-dashboard');
      } else if (role === 'admin') {
        router.visit('/admin-dashboard');
      }
    } else {
      errorMessage.value = 'Login gagal. Cek email dan password Anda.';
    }
  } catch (error) {
    console.error('❌ Error during login:', error);
    errorMessage.value =
      error.response?.data?.message || 'Login gagal. Silakan coba lagi.';
  }
};

fetchAllStudents();

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
  (newStudentId) => {
    const matched = students.value.find((s) => s.id === newStudentId);

    if (matched) {
      form.student_id = matched.id;
      console.log('👤 Siswa dipilih (dari watch):', {
        student_id: matched.id,
        student_name: matched.name,
      });
    } else {
      form.student_id = null;
      console.log('❌ Siswa tidak ditemukan untuk ID:', newStudentId);
    }
  }
);
</script>

<template>
  <!-- --->
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
              :reduce="(student) => student.id"
              label="name"
              placeholder="Cari siswa..."
              class="w-full"
              searchable
              clearable
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
