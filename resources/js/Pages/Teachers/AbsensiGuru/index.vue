<script setup>
import { Link, Head, useForm, usePage, router } from "@inertiajs/vue3";
import { ref, watch, computed, onMounted, toRaw } from "vue";
import axios from "axios";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";

const selectedClassId = ref(null);
const isModalVisible = ref(false);
const statuses = ref(["Hadir", "Alpa", "Sakit", "Izin"]);
const customStatus = ref("");
const isCustomStatus = ref(false);

const selectStatus = (status) => {
    console.log("Selected status:", status); // Log untuk memastikan status yang dipilih
    if (status === "Custom") {
        isCustomStatus.value = true;
    } else {
        customStatus.value = status; // Pastikan status diperbarui di sini
        console.log("Updated status:", customStatus.value);
        closeModal();
    }
};

const closeModal = () => {
    isModalVisible.value = false;
};

const { classes, attendance } = usePage().props;

const selectedClass = classes[0];

const teacherId = selectedClass?.wali_kelas_id;

const getButtonClass = (status) => {
    switch (status) {
        case "Hadir":
            return "btn btn-success"; // Kelas untuk hadir
        case "Alpa":
            return "btn btn-danger"; // Kelas untuk alpa
        case "Sakit":
            return "btn btn-warning"; // Kelas untuk sakit
        case "Izin":
            return "btn btn-info"; // Kelas untuk izin
        default:
            return "btn btn-secondary"; // Default class
    }
};

// Ambil data dari props yang diterima dari Inertia
const { teachers } = usePage().props; // Mengakses data teachers dari Inertia
console.log("Data Teachers dari usePage.props:", teachers);

const triggerTeacherStatusChange = async (teacherId) => {
    console.log("Triggering status change for Teacher ID:", teacherId);

    // Cek jika classes sudah ada, jika belum ambil dari API
    if (classes.value.length === 0) {
        await fetchClasses();
    }

    if (!classes.value.length) {
        alert("No classes available.");
        return;
    }

    // Tentukan rentang tanggal (misalnya 7 hari terakhir)
    const dates = getDateRange("2025-01-01", "2025-01-07");

    // Iterasi setiap kombinasi class_id dan tanggal
    classes.value.forEach((classId) => {
        dates.forEach((date) => {
            console.log(
                "Processing Teacher ID:",
                teacherId,
                "Date:",
                date,
                "Class ID:",
                classId
            );

            // Validasi class_id
            if (!Number.isInteger(classId) || classId <= 0) {
                console.error("Invalid class_id:", classId);
                return; // Skip class_id yang tidak valid
            }

            // Panggil fungsi untuk memproses status
            handleTeacherStatusChange(teacherId, date, classId);
        });
    });
};

/*
// Helper untuk mendapatkan daftar class_id dari API
const fetchClasses = async () => {
    try {
        const response = await axios.get("/api/classes"); // Ganti endpoint sesuai kebutuhan
        classes.value = response.data.map((classItem) => classItem.id);
    } catch (error) {
        console.error("Error fetching classes:", error);
        classes.value = [];
    }
};
*/

const getDateRange = (startDate, endDate) => {
    const start = new Date(startDate);
    const end = new Date(endDate);
    const dates = [];

    while (start <= end) {
        dates.push(start.toISOString().split("T")[0]); // Format YYYY-MM-DD
        start.setDate(start.getDate() + 1);
    }

    return dates;
};

// Properti yang digunakan dalam template
const totalDaysInMonth = Array.from({ length: 31 }, (_, i) => i + 1); // 31 hari dalam sebulan
const currentYear = new Date().getFullYear();
const currentMonth = new Date().getMonth();
//const paginatedTeachers = ref(teachers || []);
const paginatedTeachers = computed(() => {
    if (!teachers || !Array.isArray(teachers)) {
        console.warn("Teachers data is not available or invalid.");
        return [];
    }
    const start = (currentPage.value - 1) * itemsPerPage;
    return teachers.slice(start, start + itemsPerPage);
});

const totalPages = ref(1);
const currentPage = ref(1);
const itemsPerPage = 5;
const pagination = ref({
    current_page: 1, // Default halaman awal
    last_page: 1, // Default jumlah halaman awal
});
console.log("Teachers Data:", toRaw(teachers)); // Untuk mengakses data mentahnya
const rawTeachers = toRaw(paginatedTeachers.value);
console.log(rawTeachers);

console.log(currentPage.value); // Periksa nilai currentPage
console.log(itemsPerPage); // Periksa nilai itemsPerPag

// Properti modal
const isAddModalVisible = ref(false);

// Properti yang diperlukan untuk bulan dan tahun
const currentMonthYear = ref(`${currentYear}-${currentMonth + 1}`);

const changePage = (direction) => {
    let newPage = currentPage.value;

    console.log("changePage called");
    console.log("Current Page in changePage:", currentPage.value); // Debug

    if (direction === "prev" && currentPage.value > 1) {
        newPage -= 1;
    } else if (
        direction === "next" &&
        currentPage.value < pagination.value.last_page
    ) {
        newPage += 1;
    }

    console.log("New Page in changePage:", newPage); // Debug

    // Update currentPage
    currentPage.value = newPage;

    // Ambil teacherId dan pastikan attendanceDate ada sebelum memanggil fetchAttendanceData
    const teacherId = 1; // Gantikan dengan teacherId dinamis yang sesuai

    // Ambil tanggal hari ini
    const attendanceDate = new Date().toISOString().split("T")[0];
    console.log("Attendance Date in fetchAttendanceData:", attendanceDate); // Debug

    // Pastikan attendanceDate sudah terisi sebelum memanggil fetchAttendanceData
    console.log("Before calling fetchAttendanceData");

    // Cek apakah nilai yang dikirimkan ke fetchAttendanceData valid
    console.log("Teacher ID before fetchAttendanceData:", teacherId);
    console.log("Attendance Date before fetchAttendanceData:", attendanceDate);

    fetchAttendanceData(teacherId, attendanceDate, newPage); // Fetch data untuk halaman baru
};

// Fungsi untuk mengambil data absensi guru
const fetchAttendanceData = (teacherId, attendanceDate, page = 1) => {
    // Debug: Pastikan teacherId dan attendanceDate ada
    console.log("fetchAttendanceData called");
    console.log("Teacher ID in fetchAttendanceData:", teacherId);
    console.log("Attendance Date in fetchAttendanceData:", attendanceDate);

    // Cek apakah teacherId dan attendanceDate sudah diberikan
    if (!teacherId || !attendanceDate) {
        //console.error("Teacher ID and Attendance Date are required");
        return;
    }

    // Format tanggal untuk API request
    const formattedDate = new Date(attendanceDate).toISOString().split("T")[0];
    console.log("Formatted Date:", formattedDate); // Debug: Pastikan tanggal diformat dengan benar

    const url = `/api/attendance-teachers?teacher_id=${teacherId}&attendance_date=${formattedDate}&page=${page}`;
    console.log("URL in fetchAttendanceData:", url); // Debug: Lihat URL yang dipanggil untuk API

    // Melakukan permintaan ke API
    axios
        .get(url)
        .then((response) => {
            console.log("Attendance Data:", response.data); // Debug: Response dari server
            pagination.value = response.data.pagination || {
                current_page: 1,
                last_page: 1,
            };
            currentPage.value = pagination.value.current_page || 1;
        })
        .catch((error) => {
            console.error(
                "Error fetching attendance data:",
                error.response || error.message
            );
        });
};

const handleStatus = (status) => {
    console.log("Status received:", status);
    if (status === "Hadir") {
        // Lakukan sesuatu jika status hadir
    } else if (status === "Alpa") {
        // Lakukan sesuatu jika status alpa
    } else {
        console.log("Status is not recognized:", status);
    }
};

// Ambil data saat komponen dimuat pertama kali
onMounted(() => {
    //fetchClasses(); // Ambil kelas saat komponen pertama kali dimuat
    handleStatus();
    fetchAttendanceData(currentPage.value); // Ambil data untuk halaman pertama
});

// Fungsi untuk format tanggal
const formattedDate = (date) => {
    if (!(date instanceof Date)) {
        console.error("Invalid date object:", date);
        return null;
    }
    return date.toISOString().split("T")[0]; // Format: YYYY-MM-DD
};

// Fungsi untuk mendapatkan nama hari
const getDayName = (date) => {
    const days = [
        "Sunday",
        "Monday",
        "Tuesday",
        "Wednesday",
        "Thursday",
        "Friday",
        "Saturday",
    ];
    return days[new Date(date).getDay()];
};

// Fungsi untuk validasi tanggal (untuk memastikan validitas tanggal)
const getValidDate = (date) => {
    const validDate = new Date(currentYear, currentMonth, date);
    return validDate.getTime() === validDate.getTime() ? validDate : null;
};

// Fungsi untuk memeriksa apakah hari Minggu
const isSunday = (day) => {
    return new Date(currentYear, currentMonth, day).getDay() === 0;
};

const attendanceRecords = [
    { teacherId: 1, date: "2025-01-06", status: "Hadir" },
    { teacherId: 2, date: "2025-01-06", status: "Alpa" },
];

// Fungsi untuk mendapatkan status kehadiran guru
const getTeacherAttendanceStatus = (teacherId, date) => {
    // Cari data absensi untuk guru dan tanggal tertentu
    const record = attendanceRecords.find(
        (item) => item.teacherId === teacherId && item.date === date
    );

    // Jika record ditemukan, kembalikan status
    if (record) {
        return record.status;
    } else {
        // Jika tidak ditemukan, log hanya untuk kasus ini
        //console.log(
        //  `No attendance record found for Teacher ID: ${teacherId} and Date: ${date}`
        // );
        return "Belum diabsen"; // Default jika tidak ditemukan
    }
};

const handleTeacherStatusChange = (teacherId, date, classId) => {
    if (!teacherId || !date || !classId) {
        console.error("Teacher ID, Date, or Class ID is missing!");
        alert("Teacher ID, Date, and Class ID are required.");
        return;
    }

    // Validasi class_id
    if (!Number.isInteger(classId) || classId <= 0) {
        console.error("Invalid class_id:", classId);
        alert("Class ID is invalid. Please select a valid class.");
        return; // Hentikan proses jika class_id tidak valid
    }

    const formattedDate = new Date(date).toISOString().split("T")[0];
    console.log("Formatted Date:", formattedDate);

    // Tampilkan form untuk mengisi absensi
    const isPresent = confirm("Is the teacher present on this day?");
    const status = isPresent ? "Hadir" : "Tidak Hadir";

    // Kirim data absensi setelah konfirmasi
    const newAttendanceData = {
        teacher_id: teacherId,
        attendance_date: formattedDate,
        class_id: classId,
        is_present: isPresent, // pastikan isPresent adalah boolean (true/false)
        status: status,
    };

    // Log tambahan untuk debug
    console.log("Sending request to:", "/attendance-teacher-create");
    console.log("Sending request to:", url);
    if (!teacherId || !newAttendanceData || !classId) {
        console.error("Required data is missing:", {
            teacherId,
            newAttendanceData,
            classId,
        });
        return;
    }

    axios
        .post("/attendance-teacher-create", newAttendanceData)
        .then((response) => {
            console.log("Response:", response.data);
            alert("Attendance successfully created!");
        })
        .catch((error) => {
            console.error("Error:", error);
        });
};

// Fungsi untuk mendapatkan kelas berdasarkan absensi
const getAttendanceClass = (teacherId, date) => {
    //console.log(
    //  "Getting attendance class for Teacher ID:",
    //teacherId,
    //"Date:",
    // date
    // );

    //const status =
    //  getTeacherAttendanceStatus(teacherId, date) || "Belum diabsen"; // Default jika tidak ada status
    //console.log("Status for class:", status);

    const status = ref("");

    switch (status) {
        case "Hadir":
            //console.log("Status is 'Hadir'. Returning bg-info class.");
            return "bg-info text-white fw-bold"; // Hadir
        case "Alpa":
            //console.log("Status is 'Alpa'. Returning bg-danger class.");
            return "bg-danger text-white fw-bold"; // Absen
        case "Sakit":
            //console.log("Status is 'Sakit'. Returning bg-warning class.");
            return "bg-warning text-white fw-bold"; // Sakit
        case "Izin":
            //console.log("Status is 'Izin'. Returning bg-primary class.");
            return "bg-primary text-white fw-bold"; // Izin
        default:
            //console.log(
            //   "Status is unknown or default. Returning bg-gray-300 class."
            //);
            return "bg-light text-dark"; // Default
    }
};
</script>

<style scoped>
.bg-dark {
    background-color: #343a40;
}

.text-white {
    color: #fff;
}

.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
}

.modal-content {
    background-color: white;
    padding: 20px;
    border-radius: 8px;
    width: 300px;
}

.status-options button {
    margin-right: 10px;
}

.close-btn {
    background-color: red;
    color: white;
    padding: 5px 10px;
    margin-top: 10px;
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
                            >SMA BARUNAWATI SURABAYA</span
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
            <form @submit.prevent="submitTeacherAttendance">
                <div class="container py-5">
                    <div class="text-3xl d-flex justify-content-between mb-3">
                        <div class="sm:flex sm:items-center">
                            <div class="sm:flex-auto font-semibold">
                                <h1
                                    class="text-3xl font-semibold text-gray-900"
                                >
                                    Tabel Absensi Guru
                                </h1>
                                <p class="text-sm mb-3 fw-bold text-danger">
                                    Bulan {{ currentMonthYear }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Button untuk Tambah Absensi -->
                    <button
                        type="button"
                        class="btn btn-primary mb-4"
                        @click="isModalVisible = true"
                    >
                        Tambah Absensi
                    </button>

                    <!-- Tabel Absensi -->
                    <div class="overflow-x-auto max-w-full">
                        <table class="table table-bordered table-sm">
                            <thead style="background-color: aliceblue">
                                <tr class="custom-tr">
                                    <th>Tanggal</th>
                                    <th
                                        v-for="(
                                            date, index
                                        ) in totalDaysInMonth"
                                        :key="'date-' + index"
                                        class="text-center w-42"
                                    >
                                        {{ console.log(date) }} {{ date }}
                                    </th>
                                </tr>
                                <tr class="custom-tr">
                                    <th>Hari</th>
                                    <th
                                        v-for="(day, index) in totalDaysInMonth"
                                        :key="'day-name-' + index"
                                        :class="{
                                            'bg-danger text-white':
                                                isSunday(day),
                                        }"
                                    >
                                        {{ getDayName(getValidDate(day)) }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="teacher in paginatedTeachers"
                                    :key="teacher.id"
                                >
                                    <td>{{ teacher.name }}</td>
                                    <td
                                        v-for="(
                                            date, index
                                        ) in totalDaysInMonth"
                                        :key="
                                            'attendance-' +
                                            teacher.id +
                                            '-' +
                                            formattedDate(
                                                new Date(
                                                    currentYear,
                                                    currentMonth,
                                                    date
                                                )
                                            )
                                        "
                                        :class="
                                            getAttendanceClass(
                                                teacher.id,
                                                formattedDate(
                                                    new Date(
                                                        currentYear,
                                                        currentMonth,
                                                        date
                                                    )
                                                )
                                            )
                                        "
                                        @click="
                                            handleTeacherStatusChange(
                                                teacher.id,
                                                formatatedDate(
                                                    new Date(
                                                        currentYear,
                                                        currentMonth,
                                                        date
                                                    )
                                                )
                                            )
                                        "
                                    >
                                        <span>{{
                                            getTeacherAttendanceStatus(
                                                teacher.id,
                                                formattedDate(
                                                    new Date(
                                                        currentYear,
                                                        currentMonth,
                                                        date
                                                    )
                                                )
                                            )
                                        }}</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="pagination && pagination.current_page">
                        Current Page: {{ pagination.current_page }}
                    </div>
                    <div
                        v-if="pagination.last_page > 1"
                        class="flex items-center gap-2 mt-4"
                    >
                        <button
                            @click="changePage('prev')"
                            :disabled="pagination.current_page === 1"
                            class="px-4 py-2 border rounded disabled:bg-gray-300"
                        >
                            Previous
                        </button>
                        <button
                            v-for="page in pagination.last_page"
                            :key="page"
                            @click="fetchAttendanceData(page)"
                            :class="{
                                'bg-blue-500 text-white px-4 py-2 rounded': true,
                                'bg-gray-200': pagination.current_page !== page,
                            }"
                        >
                            {{ page }}
                        </button>
                        <button
                            @click="changePage('next')"
                            :disabled="
                                pagination.current_page === pagination.last_page
                            "
                            class="px-4 py-2 border rounded disabled:bg-gray-300"
                        >
                            Next
                        </button>
                    </div>

                    <!-- Keterangan Status Kehadiran -->
                    <div class="row mt-3 me-3">
                        <div class="col-12">
                            <p class="fw-bold">Status Kehadiran:</p>
                            <div class="d-flex">
                                <div class="me-3">
                                    <span
                                        class="badge bg-info text-black fw-bold"
                                        >Hadir (P)</span
                                    >
                                </div>
                                <div class="me-3">
                                    <span
                                        class="badge bg-danger text-black fw-bold"
                                        >Absen (A)</span
                                    >
                                </div>
                                <div class="me-3">
                                    <span
                                        class="badge bg-warning text-black fw-bold"
                                        >Sakit (S)</span
                                    >
                                </div>
                                <div class="me-3">
                                    <span
                                        class="badge bg-primary text-black fw-bold"
                                        >Izin (I)</span
                                    >
                                </div>
                                <div class="me-3">
                                    <span
                                        class="badge bg-light text-dark fw-bold"
                                        >Belum Diabsen</span
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                    <div
                        v-if="isModalVisible"
                        class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50"
                    >
                        <div class="bg-white p-6 rounded-lg w-96">
                            <h3 class="text-xl text-center font-semibold mb-4">
                                Masukkan Status Kehadiran
                            </h3>

                            <!-- Pilihan Status -->
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <button
                                    v-for="status in statuses"
                                    :key="status"
                                    :class="getButtonClass(status)"
                                    @click="selectStatus(status)"
                                    class="py-2 px-4 rounded-md text-white"
                                >
                                    {{ status }}
                                </button>
                            </div>

                            <!-- Input untuk Status Kustom -->
                            <div v-if="isCustomStatus" class="mb-4">
                                <input
                                    v-model="customStatus"
                                    type="text"
                                    placeholder="Masukkan status (P, A, S, I)"
                                    @keyup.enter="selectStatus(customStatus)"
                                />
                            </div>

                            <!-- Tombol Tutup -->
                            <button class="close-btn" @click="closeModal">
                                Tutup
                            </button>
                        </div>
                    </div>

                    <!-- Modal Tambah Absensi -->
                    <div
                        v-if="isModalVisible"
                        class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50"
                        @click.self="closeModal"
                    >
                        <!-- Modal Content -->
                        <div
                            class="bg-white p-6 rounded-lg shadow-lg max-w-md w-full"
                        >
                            <h3 class="text-xl font-semibold mb-4">
                                Masukkan Status Kehadiran
                            </h3>

                            <!-- Pilihan Status -->
                            <div class="status-options space-y-2 mb-4">
                                <button
                                    v-for="status in statuses"
                                    :key="status"
                                    :class="getButtonClass(status)"
                                    class="w-full py-2 px-4 rounded-lg text-white font-medium transition-colors duration-200"
                                    @click="selectStatus(status)"
                                >
                                    {{ status }}
                                </button>
                            </div>

                            <!-- Input untuk Status -->
                            <div v-if="isCustomStatus" class="mb-4">
                                <input
                                    v-model="customStatus"
                                    type="text"
                                    placeholder="Masukkan status (P, A, S, I)"
                                    class="w-full p-2 border border-gray-300 rounded-lg"
                                    @keyup.enter="selectStatus(customStatus)"
                                />
                            </div>

                            <!-- Modal Close Button -->
                            <button
                                class="w-full py-2 bg-gray-200 rounded-lg text-gray-700 hover:bg-gray-300 transition-colors"
                                @click="closeModal"
                            >
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </main>

        <!-- Sidebar -->
        <aside
            class="fixed top-0 left-0 z-40 w-60 h-screen pt-4 transition-transform -translate-x-full bg-white border-r border-gray-200 md:translate-x-0 dark:bg-gray-800 dark:border-gray-900"
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
                        <button
                            type="button"
                            class="flex items-center p-2 w-full text-base font-medium text-gray-900 rounded-lg transition duration-75 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
                            aria-controls="dropdown-pages"
                            data-collapse-toggle="dropdown-pages"
                            aria-expanded="true"
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

                            <span
                                class="flex-1 ml-3 text-left whitespace-nowrap"
                                >Siswa</span
                            >
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

                            <span
                                class="flex-1 ml-3 text-left whitespace-nowrap"
                                >Guru</span
                            >
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
                        <ul
                            id="dropdown-pages-guru"
                            class="hidden py-2 space-y-2"
                        >
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
                            <span
                                class="flex-1 ml-3 text-left whitespace-nowrap"
                                >Kelas</span
                            >
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

                            <span
                                class="flex-1 ml-3 text-left whitespace-nowrap"
                                >Mata Pelajaran</span
                            >
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

                        <ul
                            id="dropdown-authentication1"
                            class="hidden py-2 space-y-2"
                        >
                            <li>
                                <a
                                    href="mataPelajaran"
                                    class="flex items-center p-2 pl-11 w-full text-base font-medium text-gray-900 rounded-lg transition duration-75 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
                                    >Tambah Mata Pelajaran</a
                                >
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </aside>
    </div>
</template>
