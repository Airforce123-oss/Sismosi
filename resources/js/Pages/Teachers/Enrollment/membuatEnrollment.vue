<script setup>
import { ref, computed, onMounted } from "vue";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";
import axios from "axios";
import { Link, useForm, usePage } from "@inertiajs/vue3";

const students = ref([]);
const courses = ref([]);

// Data dummy untuk debugging
const enrollments = ref([
    {
        id: 1,
        student: { name: "John Doe" },
        course: { name: "Mathematics" },
        enrollment_date: "2024-12-27T00:00:00Z",
        status: "active",
        mark: 90,
    },
    {
        id: 2,
        student: { name: "Jane Smith" },
        course: { name: "Physics" },
        enrollment_date: "2024-11-15T00:00:00Z",
        status: "inactive",
        mark: 85,
    },
    {
        id: 3,
        student: { name: "Alice Johnson" },
        course: { name: "Chemistry" },
        enrollment_date: "2024-10-05T00:00:00Z",
        status: "active",
        mark: 88,
    },
    {
        id: 4,
        student: { name: "Bob Brown" },
        course: { name: "Biology" },
        enrollment_date: "2024-09-22T00:00:00Z",
        status: "inactive",
        mark: 92,
    },
    {
        id: 5,
        student: { name: "Charlie Green" },
        course: { name: "History" },
        enrollment_date: "2024-08-12T00:00:00Z",
        status: "active",
        mark: 85,
    },
    {
        id: 6,
        student: { name: "David White" },
        course: { name: "Geography" },
        enrollment_date: "2024-07-25T00:00:00Z",
        status: "inactive",
        mark: 80,
    },
    {
        id: 7,
        student: { name: "David Green Screen" },
        course: { name: "Bio" },
        enrollment_date: "2024-07-25T00:00:00Z",
        status: "inactive",
        mark: 80,
    },
]);

const currentPage = ref(1);
const perPage = 5;

const totalPages = computed(() =>
    Math.ceil(enrollments.value.length / perPage)
);

const paginatedEnrollments = computed(() => {
    const start = (currentPage.value - 1) * perPage;
    const end = start + perPage;
    return enrollments.value.slice(start, end);
});

const goToPage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
    }
};

const formatDate = (date) => {
    if (!date) return "N/A";
    const options = { year: "numeric", month: "long", day: "numeric" };
    return new Date(date).toLocaleDateString("en-US", options);
};

const totalEnrollments = ref(0);
const activeEnrollments = ref(0);
const inactiveEnrollments = ref(0);

const isModalVisible = ref(false);
const newEnrollment = ref({
    studentId: null,
    courseId: null,
    enrollmentDate: "",
    status: "active",
});

const showAddModal = () => {
    isModalVisible.value = true;
};

const hideAddModal = () => {
    isModalVisible.value = false;
};

const addEnrollment = () => {
    if (newEnrollment.value.studentId && newEnrollment.value.courseId) {
        enrollments.value.push({
            id: enrollments.value.length + 1,
            student: {
                name: students.value.find(
                    (s) => s.id === newEnrollment.value.studentId
                )?.name,
            },
            course: {
                name: courses.value.find(
                    (c) => c.id === newEnrollment.value.courseId
                )?.name,
            },
            enrollment_date: newEnrollment.value.enrollmentDate,
            status: newEnrollment.value.status,
            mark: 0,
        });
        hideAddModal();
    } else {
        alert("Silakan pilih siswa dan mata pelajaran.");
    }
};

onMounted(() => {
    fetchStudents();
    fetchCourses();
});

computed(() => {
    totalEnrollments.value = students.value.length;
    activeEnrollments.value = students.value.filter(
        (student) => student.isActive
    ).length;
    inactiveEnrollments.value = students.value.filter(
        (student) => !student.isActive
    ).length;
});

const fetchStudents = async () => {
    try {
        const response = await axios.get("/api/students");
        students.value = response.data.data;
    } catch (error) {
        console.error("Error fetching students:", error);
    }
};

const fetchCourses = async () => {
    try {
        const response = await axios.get("/api/courses");
        courses.value = response.data;
    } catch (error) {
        console.error("Error fetching courses:", error);
    }
};
</script>
<style scoped>
/* Add any custom styles for your table here */
table {
    width: 100%;
    border-collapse: collapse;
}

td,
th {
    padding: 12px 16px;
    text-align: left;
}

button {
    cursor: pointer;
    transition: all 0.2s ease;
}

button:hover {
    opacity: 0.8;
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
                        <img
                            src="/images/barunawati.jpeg"
                            class="mr-3 h-8"
                            alt=""
                        />
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
                        class="p-2 text-gray-500 rounded-lg hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-700 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-600"
                    >
                        <span class="sr-only">View notifications</span>
                    </button>

                    <button
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
        <!-- Main content -->
        <main class="p-4 sm:p-6 lg:p-8 md:ml-64 h-screen pt-20">
            <h2
                class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-800 mb-6 text-center"
            >
                Enrollment List
            </h2>

            <div class="container mx-auto px-4 py-6">
                <div
                    class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4"
                >
                    <!-- Search filter -->
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Cari Enrollment..."
                        class="w-full sm:w-auto px-4 py-2 border rounded-md"
                    />
                    <button
                        class="btn btn-primary modal-title fs-5 w-full sm:w-auto"
                        @click="showAddModal"
                    >
                        <i class="fa fa-plus mr-2"></i> Tambah New Enrollment
                    </button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6"
            >
                <div class="bg-white p-6 rounded-lg shadow-lg text-center">
                    <p
                        class="text-xl sm:text-2xl lg:text-3xl font-bold text-blue-600"
                    >
                        Total Enrollments: {{ totalEnrollments }}
                    </p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-lg text-center">
                    <p
                        class="text-xl sm:text-2xl lg:text-3xl font-bold text-green-600"
                    >
                        Active Enrollments: {{ activeEnrollments }}
                    </p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-lg text-center">
                    <p
                        class="text-xl sm:text-2xl lg:text-3xl font-bold text-red-600"
                    >
                        Inactive Enrollments: {{ inactiveEnrollments }}
                    </p>
                </div>
            </div>

            <!-- Enrollments Table -->
            <div class="overflow-x-auto bg-white rounded-lg shadow-md mb-6">
                <table class="min-w-full table-auto">
                    <thead>
                        <tr class="bg-gray-100">
                            <th
                                class="px-4 py-3 text-left text-sm font-semibold text-gray-700"
                            >
                                ID
                            </th>
                            <th
                                class="px-4 py-3 text-left text-sm font-semibold text-gray-700"
                            >
                                Student Name
                            </th>
                            <th
                                class="px-4 py-3 text-left text-sm font-semibold text-gray-700"
                            >
                                Course
                            </th>
                            <th
                                class="px-4 py-3 text-left text-sm font-semibold text-gray-700"
                            >
                                Enrollment Date
                            </th>
                            <th
                                class="px-4 py-3 text-left text-sm font-semibold text-gray-700"
                            >
                                Status
                            </th>
                            <th
                                class="px-4 py-3 text-left text-sm font-semibold text-gray-700"
                            >
                                Mark
                            </th>
                            <th
                                class="px-4 py-3 text-left text-sm font-semibold text-gray-700"
                            >
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="enrollment in paginatedEnrollments"
                            :key="enrollment.id"
                            class="border-t"
                        >
                            <td class="px-4 py-3 text-sm text-gray-800">
                                {{ enrollment.id }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-800">
                                {{ enrollment.student.name }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-800">
                                {{ enrollment.course.name }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-800">
                                {{ formatDate(enrollment.enrollment_date) }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-800">
                                <span
                                    :class="{
                                        'text-green-500':
                                            enrollment.status === 'active',
                                        'text-red-500':
                                            enrollment.status === 'inactive',
                                    }"
                                    >{{ enrollment.status }}</span
                                >
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-800">
                                {{ enrollment.mark }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-800">
                                <div class="flex space-x-2">
                                    <button
                                        @click="editEnrollment(enrollment.id)"
                                        class="bg-blue-500 text-white py-1 px-4 rounded-lg hover:bg-blue-700"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        @click="deleteEnrollment(enrollment.id)"
                                        class="bg-red-500 text-white py-1 px-4 rounded-lg hover:bg-red-700"
                                    >
                                        Delete
                                    </button>
                                    <button
                                        @click="markEnrollment(enrollment.id)"
                                        class="bg-green-500 text-white py-1 px-4 rounded-lg hover:bg-green-700"
                                    >
                                        Mark
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="flex justify-between items-center">
                <button
                    @click="goToPage(currentPage - 1)"
                    :disabled="currentPage === 1"
                    class="px-4 py-2 bg-gray-300 rounded-md hover:bg-gray-400"
                >
                    Previous
                </button>
                <span class="text-gray-700"
                    >Page {{ currentPage }} of {{ totalPages }}</span
                >
                <button
                    @click="goToPage(currentPage + 1)"
                    :disabled="currentPage === totalPages"
                    class="px-4 py-2 bg-gray-300 rounded-md hover:bg-gray-400"
                >
                    Next
                </button>
            </div>
        </main>

        <div v-if="students && students.length > 0">
            <!-- Menampilkan ID siswa pertama jika ada -->
            ID: {{ students[0]?.id }}
        </div>

        <!-- Sidebar -->
        <aside
            class="fixed top-0 left-0 z-40 w-60 h-screen pt-14 transition-transform -translate-x-full bg-white border-r border-gray-200 md:translate-x-0 dark:bg-gray-800 dark:border-gray-900"
            aria-label="Sidenav"
            id="drawer-navigation"
            style=""
        >
            <div
                class="overflow-y-auto py-5 px-3 h-full bg-white dark:bg-gray-800"
            >
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
                        <a
                            href="absensiSiswa"
                            class="flex items-center p-2 text-base font-medium text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group"
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
                            <span class="ml-3">Absensi Siswa</span>
                        </a>
                    </li>
                    <li>
                        <a
                            href="#"
                            class="flex items-center p-2 text-base font-medium text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group"
                        >
                            <svg
                                viewBox="0 0 512 512"
                                width="24"
                                height="24"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <g id="E-learning_notification">
                                    <path
                                        d="M243.0771,299.7515V251.3271a12.5756,12.5756,0,0,0-12.5615-12.5615H212.44a5,5,0,0,0,0,10h18.0752a2.5646,2.5646,0,0,1,2.5615,2.5615v48.4244a2.5645,2.5645,0,0,1-2.5615,2.5615H102.127a2.5645,2.5645,0,0,1-2.5616-2.5615V251.3271a2.5646,2.5646,0,0,1,2.5616-2.5615h83.8183a5,5,0,1,0,0-10H102.127a12.5757,12.5757,0,0,0-12.5616,12.5615v48.4244A12.5757,12.5757,0,0,0,102.127,312.313H230.5156A12.5756,12.5756,0,0,0,243.0771,299.7515Z"
                                        stroke="black"
                                        stroke-width="4"
                                    />
                                    <path
                                        d="M305.1309,238.7656H270.8574a10.4457,10.4457,0,0,0-10.4336,10.4336v52.68a10.4458,10.4458,0,0,0,10.4336,10.4341h34.2735a10.4458,10.4458,0,0,0,10.4336-10.4341v-52.68A10.4457,10.4457,0,0,0,305.1309,238.7656Zm.4336,63.1133a.4343.4343,0,0,1-.4336.4341H270.8574a.4343.4343,0,0,1-.4336-.4341v-52.68a.4339.4339,0,0,1,.4336-.4336h34.2735a.4339.4339,0,0,1,.4336.4336Z"
                                        stroke="black"
                                        stroke-width="4"
                                    />
                                    <path
                                        d="M309.1992,360.7461H215.2568a5,5,0,1,0,0,10h93.9424a5,5,0,0,0,0-10Z"
                                        stroke="black"
                                        stroke-width="4"
                                    />
                                    <path
                                        d="M309.1992,335.2017H215.2568a5,5,0,1,0,0,10h93.9424a5,5,0,0,0,0-10Z"
                                        stroke="black"
                                        stroke-width="4"
                                    />
                                    <path
                                        d="M467.8184,122.0205a109.7113,109.7113,0,0,0-219.3941-2.4991H145.1484V119.15a12.48,12.48,0,0,0-12.4658-12.4658H102.0312A12.48,12.48,0,0,0,89.5654,119.15v1.07a54.0392,54.0392,0,0,0-45.3833,53.2611V459.8264l0,.0193a39.83,39.83,0,0,0,39.8467,39.8467H355.6045a5.0406,5.0406,0,0,0,5-5.0474V459.8457a5,5,0,0,0-10,0v29.0819a29.8445,29.8445,0,0,1,5.24-58.8871,5.01,5.01,0,0,0,4.3484-3.0642c.0051-.0115.0122-.0215.0171-.0329a5.0159,5.0159,0,0,0,.2688-.8653c.0059-.0254.0168-.0483.022-.0738A5.0241,5.0241,0,0,0,360.6045,425l-.0022-.0217V231.7017A109.84,109.84,0,0,0,467.8184,122.0205ZM358.1055,22.3076a99.7129,99.7129,0,1,1-99.7129,99.7129A99.8261,99.8261,0,0,1,358.1055,22.3076ZM99.5654,119.15a2.4687,2.4687,0,0,1,2.4658-2.4658h30.6514a2.4686,2.4686,0,0,1,2.4658,2.4658l-.0019,61.2065-6.9883-9.3046a12.7468,12.7468,0,0,0-10.0557-5.1226c-.0693-.0015-.1386-.002-.2089-.002a12.7429,12.7429,0,0,0-10.003,4.8038L99.5654,181.11Zm217.91,340.6958a39.6352,39.6352,0,0,0,11.6631,28.1777l.0039.0035q.8613.8621,1.7695,1.6655H84.0283A29.8521,29.8521,0,0,1,55.084,467.1733H238.0771a5,5,0,1,0,0-10H54.3074A29.8832,29.8832,0,0,1,84.0283,430H330.8867A39.77,39.77,0,0,0,317.4756,459.8457ZM84.0283,420a39.7524,39.7524,0,0,0-29.8476,13.4894l.0014-.0276v-259.98A44.0226,44.0226,0,0,1,89.5654,130.37v51.1742a9.7374,9.7374,0,0,0,6.583,9.2915,10.007,10.007,0,0,0,3.3233.5733,9.7314,9.7314,0,0,0,7.624-3.7036l8.5957-10.7188a2.827,2.827,0,0,1,2.2529-1.0591,2.7824,2.7824,0,0,1,2.2178,1.13l7.2647,9.6709a9.8479,9.8479,0,0,0,17.7216-5.915V129.5214H248.6541a109.8717,109.8717,0,0,0,101.9482,101.95V420Z"
                                        stroke="black"
                                        stroke-width="4"
                                    />
                                    <path
                                        d="M309.2578,171.2344h21.2686a25.572,25.572,0,0,0,50.65-5,5,5,0,0,0-5-5h-66.919a4.84,4.84,0,0,1-4.8349-4.835V145.6577a23.5977,23.5977,0,0,0,18.5556-21.7641c1.1221-19.8521,8.1309-43.5943,35.127-44.0254,26.9961.4311,34.0049,24.1733,35.1269,44.0254a23.6,23.6,0,0,0,18.5557,21.7641v10.7417a4.84,4.84,0,0,1-4.834,4.835h-9.5557a5,5,0,0,0,0,10h9.5557a14.8511,14.8511,0,0,0,14.834-14.835V141.1709a5,5,0,0,0-5-5h-.1192a13.5457,13.5457,0,0,1-13.4521-12.8418c-1.4985-26.5106-11.5112-43.8823-28.6445-50.4795V65.6013a16.4352,16.4352,0,0,0-16.41-16.42h-1.04a16.4439,16.4439,0,0,0-16.42,16.42v7.6323c-16.5585,6.8655-26.237,24.0745-27.708,50.096a13.5441,13.5441,0,0,1-13.4511,12.8413h-.12a5,5,0,0,0-5,5v15.2285A14.8518,14.8518,0,0,0,309.2578,171.2344Zm46.3467,10.5718a15.5882,15.5882,0,0,1-14.7337-10.5718h29.4673A15.588,15.588,0,0,1,355.6045,181.8062ZM350.7021,65.6013a6.4274,6.4274,0,0,1,6.42-6.42h1.04a6.4188,6.4188,0,0,1,6.41,6.42v4.7431a53.7425,53.7425,0,0,0-6.3946-.4762c-.0488-.001-.0957-.001-.1445,0a53.2611,53.2611,0,0,0-7.3311.597Z"
                                        stroke="black"
                                        stroke-width="4"
                                    />
                                    <path
                                        d="M300.7432,101.8721c.122.0088.2431.0127.3632.0127a5.0007,5.0007,0,0,0,4.9825-4.6416c1.1465-15.9453,11.3965-21.0577,11.9209-21.3086A5,5,0,0,0,313.874,66.83c-.6592.2959-16.164,7.5039-17.76,29.6963A5,5,0,0,0,300.7432,101.8721Z"
                                        stroke="black"
                                        stroke-width="4"
                                    />
                                    <path
                                        d="M397.9336,75.9316c.4394.2085,10.7773,5.295,11.9277,21.3116a5.0007,5.0007,0,0,0,4.9825,4.6416c.12,0,.2412-.0039.3632-.0127a5.0012,5.0012,0,0,0,4.6289-5.3457c-1.5947-22.1924-17.1-29.4-17.76-29.6963a5,5,0,1,0-4.1426,9.1015Z"
                                        stroke="black"
                                        stroke-width="4"
                                    />
                                </g>
                            </svg>
                            <span class="ml-3">Enrollment Tugas</span>
                        </a>
                    </li>
                    <li>
                        <a
                            href="membuatTugasSiswa"
                            class="flex items-center p-2 text-base font-medium text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group"
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
                            <span class="ml-3">Tugas Siswa</span>
                        </a>
                    </li>

                    <li>
                        <a
                            href="bukuPenghubung"
                            class="flex items-center p-2 text-base font-medium text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group"
                        >
                            <svg
                                viewBox="0 0 576 512"
                                class="w-6 h-6"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    d="M144.3 32.04C106.9 31.29 63.7 41.44 18.6 61.29c-11.42 5.026-18.6 16.67-18.6 29.15l0 357.6c0 11.55 11.99 19.55 22.45 14.65c126.3-59.14 219.8 11 223.8 14.01C249.1 478.9 252.5 480 256 480c12.4 0 16-11.38 16-15.98V80.04c0-5.203-2.531-10.08-6.781-13.08C263.3 65.58 216.7 33.35 144.3 32.04zM557.4 61.29c-45.11-19.79-88.48-29.61-125.7-29.26c-72.44 1.312-118.1 33.55-120.9 34.92C306.5 69.96 304 74.83 304 80.04v383.1C304 468.4 307.5 480 320 480c3.484 0 6.938-1.125 9.781-3.328c3.925-3.018 97.44-73.16 223.8-14c10.46 4.896 22.45-3.105 22.45-14.65l.0001-357.6C575.1 77.97 568.8 66.31 557.4 61.29z"
                                />
                            </svg>
                            <span class="ml-3">Buku Penghubung</span>
                        </a>
                    </li>
                </ul>
            </div>
        </aside>
    </div>
</template>
