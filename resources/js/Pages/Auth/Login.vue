<script setup>
import { ref, watch } from 'vue';
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
axios.defaults.headers.common['X-CSRF-TOKEN'] = document.head.querySelector(
  'meta[name="csrf-token"]'
).content;

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

console.log('Daftar students dari props:', props.students);

const form = useForm({
  email: '',
  password: '',
  remember: false,
  student_id: '',
  role: '',
  student_id: '',
});

const handleStudentSelect = (selected) => {
  form.value.student_id = selected ? selected.id : '';
};

const userName = ref('');
const errorMessage = ref(''); // Untuk menyimpan pesan error jika login gagal
const successMessage = ref('');
const studentName = ref('');
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
    studentName.value = loggedInStudent.name;
  } catch (error) {
    console.error('❌ Error fetching logged-in student:', error);
  }
};

const fetchStudents = async (page = 1) => {
  try {
    const response = await axios.get(`/api/students?page=${page}`);
    if (page === 1) {
      students.value = response.data.data; // Replace with the new data for the first page
    } else {
      students.value = [...students.value, ...response.data.data]; // Append data for subsequent pages
    }
  } catch (error) {
    console.error('Error fetching students:', error);
  }
};

// Call this function to fetch students when the component mounts
fetchStudents();

// Fungsi submit login
const submit = async () => {
  errorMessage.value = '';
  successMessage.value = '';

  console.log('Data login yang dikirim:', {
    email: form.email,
    password: form.password,
    student_id: form.student_id,
    role: form.role,
  });

  try {
    // 1. Ambil CSRF token dari meta (optional)
    const csrfToken = document
      .querySelector('meta[name="csrf-token"]')
      ?.getAttribute('content');

    if (csrfToken) {
      axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
    }

    // 2. Siapkan payload
    const payload = {
      email: form.email,
      password: form.password,
      role: form.role,
    };

    if (form.student_id) {
      payload.student_id = form.student_id;
    }

    // 3. Ambil CSRF Cookie
    await axios.get('/sanctum/csrf-cookie');

    // 4. Kirim POST /login
    const response = await axios.post('/login', payload);

    if (response.data.token) {
      // 5. Simpan token ke localStorage
      localStorage.setItem('token', response.data.token);
      console.log('✅ Token saved:', response.data.token);

      successMessage.value = 'Login berhasil!';

      // 6. Cari siswa berdasarkan ID yang dipilih
      const selectedStudent = props.students.find((student) => {
        console.log('Checking student:', student);
        return student.id === form.student_id;
      });

      if (selectedStudent) {
        localStorage.setItem('student_name', selectedStudent.name);
        console.log('✅ Student name saved:', selectedStudent.name);
      }

      // 7. Panggil fetchLoggedInStudent()
      await fetchLoggedInStudent();

      // 8. Redirect ke dashboard sesuai role
      const role = response.data.role;

      if (role === 'student') {
        router.visit('/dashboard', {
          method: 'get',
          data: { student_id: studentId.value }, // pakai studentId dari fetch
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
    errorMessage.value = 'Login gagal. Silakan coba lagi.';
  }
};
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
            </select>
          </div>

          <div class="mb-4" v-if="form.role === 'student'">
            <label for="student_id" class="block text-gray-700"
              >Pilih Siswa</label
            >
            <v-select
              :options="students"
              label="name"
              :reduce="(student) => student.id"
              v-model="form.student_id"
              placeholder="Cari siswa..."
              class="w-full"
            />
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
            class="w-full px-3 py-2 rounded-lg bg-[#12bdee] items-center justify-center"
            style="text-align: center; text-transform: none"
            :class="{ 'opacity-25': form.processing }"
            :disabled="form.processing"
            @click="submit"
          >
            Login
          </PrimaryButton>
        </form>
      </div>
    </div>
  </div>
</template>
