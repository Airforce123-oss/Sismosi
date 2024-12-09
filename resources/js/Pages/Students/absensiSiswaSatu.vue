<script setup>
import { onMounted, ref, watch, watchEffect, computed, reactive } from "vue";
import axios from "axios";
import { Link, Head, useForm, usePage, router } from "@inertiajs/vue3";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";
//import { status } from "vendor/livewire/livewire/dist/livewire";

// Reactive Variables
const studentsPerPage = 5;
const currentPage = ref(1);
const isSubmitClicked = ref(false);
const newAttendanceDate = new Date().toISOString().split("T")[0];
const editableAttendancestatus = ref({});
const isNavigating = ref(false);
const isSelectVisible = ref(false);
const selectedDate = ref(null);
const newStatus = ref("");
const nextMonthYear = ref("");
const nextNextMonthYear = ref("");
const isAddModalVisible = ref(false);
const tanggal_kehadiran = ref("");
function toggleModalSave() {
    console.log("toggleModalSave dipanggil");
    isAddModalVisible.value = !isAddModalVisible.value;
}

// Menampilkan modal
const showAddModal = () => {
    isAddModalVisible.value = true;
};

// Menutup modal
const hideAddModal = () => {
    isAddModalVisible.value = false;
};

const fetchData = async () => {
    try {
        const response = await axios.get("/api/attendance"); // Ganti dengan endpoint yang sesuai
        const newData = response.data.attendances || []; // Ambil data dari field 'attendances'
        //let newData = response.data; // Ambil data absensi
        console.log("Fetched newData:", newData);
        console.log("Response Status:", response.status); // Menampilkan status respons

        if (newData.length === 0) {
            console.warn("Data absensi tidak ditemukan atau kosong:", newData);
            return; // Jangan lanjut jika data kosong
        }
        updateSelectedStatuses(newData);
        // Lanjutkan dengan pemrosesan data jika newData valid
        console.log("Data absensi ditemukan dan valid:", newData);
    } catch (error) {
        console.error("Error fetching data:", error);
    }
};
fetchData();

//const selectedStudentStatuses = ref({});
const selectedStudentStatuses = ref({});
const newAttendance = ref([]);

console.log(
    "Isi selectedStudentStatuses sebelum mapping:",
    selectedStudentStatuses.value
);

// Submit Attendance Form
const submitAttendance = async () => {
    console.log(
        "Selected Student Statuses Before Submit:",
        selectedStudentStatuses.value
    );

    // Validasi jika semua status absensi telah dipilih
    const isValid = Object.values(selectedStudentStatuses.value).every(
        (status) => status !== "" && status !== undefined
    );

    if (!isValid) {
        alert("Pastikan semua status absensi dipilih untuk setiap siswa.");
        return;
    }

    if (!validateAttendances()) {
        return;
    }

    try {
        // Siapkan data attendancesData
        const attendancesData = Object.entries(
            selectedStudentStatuses.value
        ).map(([studentId, status]) => ({
            student_id: parseInt(studentId), // pastikan ini integer
            status_kehadiran: status,
        }));

        // Definisikan requestData di sini
        const requestData = {
            tanggal_kehadiran: tanggal_kehadiran.value, // Tanggal absensi yang dipilih
            attendances: attendancesData, // Data absensi yang telah dipilih
        };

        // Ambil token autentikasi dan CSRF token
        const token = localStorage.getItem("auth_token");
        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute("content");

        // Mengirimkan request data ke server
        const response = await axios.post("/api/attendances", requestData, {
            headers: {
                Authorization: `Bearer ${token}`,
                "X-CSRF-TOKEN": csrfToken,
            },
        });

        console.log("Response dari server:", response.data);

        // Fetch data ulang dari server untuk memperbarui tampilan
        await fetchAttendances(); // Pastikan ini memperbarui data absensi

        mapStudentStatuses(); // Sinkronkan status absensi dengan data terbaru

        alert("Data berhasil disimpan.");
        isAddModalVisible.value = false; // Tutup modal setelah sukses
    } catch (error) {
        console.error("Error saving attendance:", error);
        alert("Terjadi kesalahan saat menyimpan data.");
    }
};

const attendancesData = Object.entries(selectedStudentStatuses.value).map(
    ([studentId, status]) => ({
        student_id: parseInt(studentId),
        status_kehadiran: status,
    })
);

console.log("Attending data:", attendancesData);

const getStatus = (studentId, date) => {
    const attendance = newAttendance.value.find(
        (item) => item.student_id === studentId && item.tanggal === date
    );
    return attendance ? attendance.status : ""; // Tampilkan status jika ada, kosong jika tidak
};

// Ambil tanggal pertama dari array
const selectedStudentId = ref(null);
const students = ref([]);
const attendances = computed(() =>
    Object.entries(selectedStudentStatuses.value).map(
        ([studentId, status]) => ({
            student_id: parseInt(studentId, 10),
            status_kehadiran: status,
        })
    )
);

/*
// Mengonversi selectedStudentStatuses menjadi array attendances
attendances = Object.entries(selectedStudentStatuses.value).map(
    ([studentId, statusKehadiran]) => ({
        student_id: parseInt(studentId, 10),
        status_kehadiran: statusKehadiran,
    })
);

*/

const paginatedStudents = computed(() => {
    return students.value.slice(0, 10); // Ambil 10 siswa pertama
});

//paginatedStudents.forEach((student) => {
//  selectedStudentStatuses[student.id] =
//    selectedStudentStatuses[student.id] || {};
//});

function validateAttendances() {
    if (attendances.value.length === 0) {
        //console.error("Attendances kosong!");
        return false;
    }

    for (const attendance of attendances.value) {
        if (!attendance.student_id || !attendance.status_kehadiran) {
            console.error("Data tidak lengkap:", attendance);
            return false;
        }
    }
    return true;
}
//const date = totalDaysInMonth.value[0];
const today = new Date();
const currentYear = new Date().getFullYear();
const currentMonth = new Date().getMonth();
const totalDaysInMonth = Array.from(
    { length: new Date(currentYear, currentMonth + 1, 0).getDate() },
    (_, i) => i + 1
);
const currentMonthYear = ref("");

const isSunday = (day) => {
    const dateObj = new Date(currentYear, currentMonth, day);
    return dateObj.getDay() === 0;
};

// Inisialisasi array totalDaysInMonth
const initializeDaysInMonth = () => {
    const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
    totalDaysInMonth.value = Array.from(
        { length: daysInMonth },
        (_, index) => new Date(currentYear, currentMonth, index + 1)
    );
};

// Fungsi untuk membuat objek Date yang valid

/*
const createDate = (dateString) => {
    let date = new Date(dateString);
    if (isNaN(date.getTime())) {
        // Memeriksa apakah dateString adalah tanggal yang valid
        console.error("Tanggal tidak valid:", dateString);
        return null; // Atau nilai default lainnya
    }
    return date;
};
 */

initializeDaysInMonth();

const createDate = (dateString) => {
    const parsedDate = new Date(dateString);
    if (isNaN(parsedDate.getTime())) {
        // Check if the parsed date is valid
        console.error("Tanggal tidak valid:", dateString);
        return null; // Or a default value
    }
    return parsedDate;
};

/*
const getDayName = (dateString) => {
    const date = createDate(dateString);
    if (!date) {
        console.error("Parameter dateString tidak valid:", dateString);
        return "Hari tidak valid"; // Return default if invalid date
    }

    const daysInIndonesian = [
        "Minggu",
        "Senin",
        "Selasa",
        "Rabu",
        "Kamis",
        "Jumat",
        "Sabtu",
    ];
    const dayIndex = date.getDay();
    return daysInIndonesian[dayIndex];
};
*/

const getDayName = (date) => {
    const dateObj = new Date(date);
    const daysOfWeek = [
        "Minggu",
        "Senin",
        "Selasa",
        "Rabu",
        "Kamis",
        "Jumat",
        "Sabtu",
    ];
    return daysOfWeek[dateObj.getDay()];
};

const getValidDate = (day) => {
    const dateObj = new Date(currentYear, currentMonth, day);
    if (isNaN(dateObj.getTime())) {
        // Tangani kasus tanggal tidak valid
        return "";
    }
    return dateObj.toISOString().split("T")[0];
};

const getFormattedDate = (dayIndex) => {
    const currentDate = new Date();
    currentDate.setDate(currentDate.getDate() + dayIndex); // Menambahkan dayIndex ke tanggal saat ini

    // Validasi jika tanggal valid
    if (isNaN(currentDate.getTime())) {
        console.error("Tanggal tidak valid untuk dayIndex:", dayIndex);
        return "Tanggal tidak valid";
    }

    // Format tanggal dalam bentuk DD-MM-YYYY
    const day = currentDate.getDate().toString().padStart(2, "0"); // Format 2 digit
    const month = (currentDate.getMonth() + 1).toString().padStart(2, "0"); // Bulan dalam format 2 digit
    const year = currentDate.getFullYear();

    // Mengembalikan nama hari dan tanggal dalam format DD-MM-YYYY
    const dayName = getDayName(dayIndex); // Mendapatkan nama hari
    return `${dayName}, ${day}-${month}-${year}`; // Format: "Senin, 07-12-2024"
};

const getCurrentMonthYear = () => {
    const today = new Date();
    return today.toLocaleDateString("id-ID", {
        month: "long",
        year: "numeric",
    });
};

// Contoh penggunaan:
console.log(getFormattedDate(0)); // Hari ini
console.log(getFormattedDate(1)); // Besok
console.log(getFormattedDate(2)); // Lusa

// Inisialisasi newAttendance untuk setiap siswa
students.value.forEach((student) => {
    if (!(student.id in newAttendance.value)) {
        newAttendance.value[student.id] =
            selectedStudentStatuses.value[student.id] || "P";
    }
});

const toggleSelectVisibility = (date) => {
    isSelectVisible.value = true; // Menampilkan dropdown
    selectedDate.value = date; // Menyimpan tanggal yang dipilih
};

const isWeekend = (date) => {
    //const day = new Date(date).getDay();
    //return day === 0 || day === 6; // 0 = Minggu, 6 = Sabtu
    const dayOfWeek = new Date(date).getDay();
    return dayOfWeek === 0 || dayOfWeek === 6;
};

if (!students.value || students.value.length === 0) {
    //console.warn("Tidak ada data siswa untuk di-reset");
}

const fetchAttendances = async () => {
    const token = localStorage.getItem("token"); // atau sessionStorage.getItem('token')
    console.log("Token retrieved:", token); // Pastikan token berhasil diambil
    if (!token) {
        axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;
        return;
    } else {
        console.error("Auth token tidak ditemukan.");
    }

    if (!data || data.length === 0) {
        console.error("Absensi tidak ditemukan:", data);
        return;
    }

    const csrfTokenElement = document.querySelector('meta[name="csrf-token"]');
    if (!csrfTokenElement) {
        console.error("CSRF token tidak ditemukan di halaman.");
        alert("Token CSRF tidak valid. Silakan refresh halaman.");
        return;
    }
    const csrfToken = csrfTokenElement.getAttribute("content");

    try {
        axios.defaults.withCredentials = true;
        const response = await axios.get("/api/attendance", {
            headers: {
                Authorization: `Bearer ${your_token}`, // Ganti dengan token yang benar
                Accept: "application/json",
            },
        });
        console.log("Full response:", response);

        // Cek struktur respons secara mendalam
        if (response && response.data) {
            console.log("Fetched attendance data:", response.data);

            if (response.data.attendances) {
                newAttendance.value = response.data.attendances;
                console.log("Updated newAttendance:", newAttendance.value);
            } else {
                console.error(
                    "Attendance data is missing or invalid:",
                    response.data
                );
            }
        } else {
            console.error("Unexpected response format:", response);
        }
    } catch (error) {
        console.error("Error fetching attendance data:", error);
    }
};

/*
        try {
        const response = await axios.get("/api/attendance");
        console.log("Fetched attendance data:", response.data);
        //selectedStudentStatuses.value = response.data; // Pastikan data yang diterima adalah objek

        // Pastikan response.data adalah objek dan update selectedStudentStatuses
        if (typeof response.data === "object" && response.data !== null) {
            selectedStudentStatuses.value = response.data;
        } else {
            console.error("Invalid data format:", response.data);
            selectedStudentStatuses.value = {};
        }
    } catch (error) {
        console.error("Error fetching attendance data:", error);
    }
    */
/*
    try {
        const formattedDate = formatDate(tanggal_kehadiran.value);
        const response = await axios.get("/api/attendances", {
            params: { date: formattedDate },
        });
        console.log("Data attendances fetched:", response.data.attendances);
        attendances.value = response.data.attendances;
        mapStudentStatuses();
    } catch (error) {
        console.error("Error fetching attendances:", error);
    }
*/

const refreshAttendanceData = async () => {
    // Ambil data absensi berdasarkan tanggal yang dipilih
    await fetchAttendances({ tanggal_kehadiran: selectedDate.value });

    // Log data terbaru (opsional)
    console.log("Data attendances setelah refresh:", attendances.value);
};

const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 5,
    total: 0,
});

console.log(students.value);

// Ambil CSRF token dari meta tag di halaman Blade
const csrfToken = document.head.querySelector(
    'meta[name="csrf-token"]'
)?.content;

// Set CSRF token di header Axios untuk setiap request
axios.defaults.headers.common["X-CSRF-TOKEN"] = csrfToken;

// memverifikasi apakah token disetel dengan benar sebelum mengirimkan permintaan
console.log("CSRF Token:", axios.defaults.headers.common["X-CSRF-TOKEN"]);

// Fetch Data / ambil data siswa dan absensi
const fetchStudents = async (page) => {
    isNavigating.value = true; // Menandai sedang berpindah halaman
    try {
        let token = localStorage.getItem("auth_token");

        // Jika token tidak ada, buat token baru melalui API
        if (!token) {
            const response = await axios.post("/api/auth/refresh-token"); // API untuk menghasilkan token baru

            if (response.data && response.data.token) {
                token = response.data.token;
                localStorage.setItem("auth_token", token); // Simpan token baru di localStorage
                console.log("Token yang baru dibuat:", token); // Menampilkan token yang baru dibuat
            } else {
                throw new Error("Gagal mendapatkan token baru");
            }
        } else {
            console.log("Token yang digunakan (dari localStorage):", token);
        }
        const response = await axios.get(
            `/api/students?page=${page}&per_page=5`,
            {
                headers: {
                    Authorization: `Bearer ${token}`, // Menyertakan header Authorization
                },
            }
        );
        students.value = response.data.data; // Memperbarui data siswa
        pagination.current_page = response.data.current_page;
        //pagination.value.current_page = response.data.current_page;
        pagination.last_page = response.data.last_page;
        pagination.value = {
            current_page: response.data.current_page,
            last_page: response.data.last_page,
            total: response.data.total,
        };

        if (!response.data || !Array.isArray(response.data)) {
            //console.error("Data absensi tidak valid:", response.data);
            return;
        }

        console.log("Data siswa berhasil diambil:", students.value);
    } catch (error) {
        console.error("Error saat mengirim data absensi:", error);

        // Periksa apakah ada response pada error
        if (error.response) {
            console.error("Error response:", error.response.data.attendances);
            console.error("Error status:", error.response.status);
        } else if (error.request) {
            // Jika tidak ada respons, periksa jika ada permintaan yang dikirim
            console.error("No response received:", error.request);
        } else {
            // Kesalahan lain selain response dan request
            console.error("Kesalahan lain:", error.message);
        }
    }
};

const formatDate = (date) => {
    const d = new Date(date);
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(
        2,
        "0"
    )}-${String(d.getDate()).padStart(2, "0")}`;
};

const mapStudentStatuses = () => {
    selectedStudentStatuses.value = {};
    attendances.value.forEach((attendance) => {
        selectedStudentStatuses.value[attendance.student_id] =
            attendance.status_kehadiran;
    });
};

onMounted(async () => {
    try {
        // Pastikan students sudah tersedia sebelum menginisialisasi
        if (students && students.length > 0) {
            students.forEach((student) => {
                selectedStudentStatuses.value[student.id] = ""; // Inisialisasi setiap siswa dengan nilai kosong
            });

            console.log(
                "Inisialisasi selectedStudentStatuses:",
                selectedStudentStatuses.value
            );
        } else {
            console.warn("Data students belum tersedia saat mounted");
        }

        // Fetch attendance data
        await fetchAttendances();
        console.log("Fetched attendances in mounted:", newAttendance.value);

        // Fetch students jika diperlukan (pastikan ini sesuai logika)
        await fetchStudents();

        // Refresh attendance data jika perlu
        await refreshAttendanceData();

        // Submit attendance hanya jika diperlukan pada awal load
        // (Mungkin tidak perlu jika tujuannya hanya mengambil data)
        // await submitAttendance();

        // Set tanggal kehadiran
        const today = new Date();
        tanggal_kehadiran.value = today.toISOString().split("T")[0]; // Format date as YYYY-MM-DD
    } catch (error) {
        console.error("Error saat memuat data awal:", error);
    }
});

/*

onMounted(async () => {
    students.forEach((student) => {
        selectedStudentStatuses.value[student.id] = ""; // Inisialisasi setiap siswa dengan nilai kosong
    });

    console.log(
        "Inisialisasi selectedStudentStatuses:",
        selectedStudentStatuses.value
    );

    try {
        await fetchAttendances();
        console.log("Fetched attendances in mounted:", newAttendance.value);
        await submitAttendance();
        fetchStudents();
        await refreshAttendanceData();
        updateSelectedStatuses(); // Pastikan fungsi ini memproses status yang baru
    } catch (error) {
        console.error("Error saat memuat data awal:", error);
    }

    // Mengatur tanggal dan bulan
    const today = new Date();
    // Set tanggalKehadiran if needed, e.g.:
    tanggal_kehadiran.value = today.toISOString().split("T")[0]; // Format date as YYYY-MM-DD
    //status.value = "HADIR";
});
*/

//const newData = [];

const updateAttendance = async (newData) => {
    try {
        const token = localStorage.getItem("auth_token");
        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute("content");

        // Validasi newData sebelum dikirimkan ke API
        if (!newData || !newData.tanggal_kehadiran) {
            console.error("Data tidak lengkap:", newData);
            alert("Data tidak lengkap, silakan coba lagi.");
            return;
        }

        const response = await axios.put(
            `/api/attendances`,
            {
                tanggal_kehadiran: newData.tanggal_kehadiran, // Pastikan tanggal_kehadiran ada
            },
            {
                headers: {
                    Authorization: `Bearer ${token}`,
                    "X-CSRF-TOKEN": csrfToken,
                },
            }
        );

        console.log("Absensi berhasil diperbarui:", response.data);
        alert("Absensi berhasil diperbarui!");
        fetchAttendances(); // Ambil data absensi terbaru
    } catch (error) {
        console.error("Error memperbarui absensi:", error);
        alert("Gagal memperbarui absensi. Silakan coba lagi.");
    }
};

//updateAttendanceStatus(students.id, date, newStatus, newData);
// Pastikan mendeklarasikan fungsi terlebih dahulu
const updateAttendanceStatus = (studentId, date, newStatus, newData) => {
    console.log("Tanggal yang dipilih:", date);
    console.log("Menerima newData:", newData);

    // Pastikan newData valid
    if (newData === undefined || newData === null) {
        console.error("newData is undefined or null:", newData);
        // Inisialisasi dengan array kosong atau nilai default yang diinginkan
        newAttendance.value = [];
        return;
    }

    // Jika studentId belum ada di newAttendance, inisialisasi
    if (!newAttendance.value[studentId]) {
        newAttendance.value[studentId] = {};
    }

    // Memastikan newData adalah array yang valid
    if (Array.isArray(newData)) {
        // Menambahkan data yang valid ke dalam newAttendance.value
        newAttendance.value[studentId][date] = newStatus;
    } else {
        console.error("Data baru tidak valid:", newData);
        return;
    }

    // Format attendance untuk selectedStudentStatuses
    const formattedAttendance = Object.keys(newAttendance.value).reduce(
        (acc, id) => {
            const studentAttendance = newAttendance.value[id];
            acc[id] = studentAttendance; // Menyimpan status kehadiran per siswa
            return acc;
        },
        {}
    );

    selectedStudentStatuses.value = formattedAttendance; // Perbarui status siswa

    // Perbarui data absensi di selectedStudentStatuses
    newData.forEach((attendance) => {
        const { student_id, day, status } = attendance;

        if (!selectedStudentStatuses.value[student_id]) {
            selectedStudentStatuses.value[student_id] = {}; // Inisialisasi objek jika belum ada
        }

        // Ganti status pada hari tertentu
        selectedStudentStatuses.value[student_id][day] = status;
    });
};

const date = new Date();
console.log("Tanggal yang dipilih:", date);

console.log("Calling updateAttendanceStatus with:");
console.log("studentId:", students);
console.log("date:", date);
console.log("newStatus:", newStatus);
console.log("newData:", newData);


const updateSelectedStatuses = (newData) => {
    console.log("Updating statuses with newData:", newData);

    // Validasi newData
    if (!newData || newData.length === 0) {
        console.error("Data absensi tidak ditemukan atau kosong:", newData);
        return;
    }

    // Proses pembaruan status siswa
    newData.forEach((attendance) => {
        if (attendance.student_id && attendance.status_kehadiran) {
            selectedStudentStatuses.value[attendance.student_id] =
                attendance.status_kehadiran;
        } else {
            console.warn("Data absensi tidak lengkap:", attendance);
        }
    });

    // Masukkan status yang sudah diperbarui ke dalam newData
    newData = newData.map((attendance) => {
        if (
            attendance.student_id &&
            selectedStudentStatuses.value[attendance.student_id]
        ) {
            // Memperbarui status pada newData
            attendance.updated_status_kehadiran =
                selectedStudentStatuses.value[attendance.student_id];
        }
        return attendance;
    });

    console.log(
        "Updated selectedStudentStatuses:",
        selectedStudentStatuses.value
    );
};

// Update selected statuses (gunakan hasil fetchAttendances)
updateSelectedStatuses();
//updateSelectedStatuses(newData); // Pastikan response.data.attendances adalah array yang valid

// Fungsi untuk menghandle perubahan status kehadiran
const handleAttendanceChange = async (studentId, date, newStatus) => {
    // Memastikan data baru disimpan dengan benar
    if (!newAttendance.value[studentId]) {
        newAttendance.value[studentId] = {};
    }
    newAttendance.value[studentId][date] = newStatus;

    console.log("Updated newAttendance:", newAttendance.value);
};

//isSelectVisible.value = false; // Sembunyikan dropdown setelah perubahan
//selectedDate.value = null; // Reset tanggal yang dipilih

const getAttendanceStatus = (studentId, day) => {
    if (
        selectedStudentStatuses.value &&
        selectedStudentStatuses.value[studentId]
    ) {
        return selectedStudentStatuses.value[studentId][day] || "Belum diabsen";
    }
    return "Belum diabsen";
};

console.log("Nilai isAddModalVisible:", isAddModalVisible.value);

const getAttendanceClass = (studentId, tanggal_kehadiran) => {
    if (!tanggal_kehadiran.value) {
        return "bg-light text-dark"; // Jika tidak ada tanggal yang dipilih, tampilkan default
    }
    updateAttendance;
    // Jika tanggal valid (hari Senin - Jumat), cek status kehadiran
    const status = getAttendanceStatus(
        studentId,
        status_kehadiran,
        tanggal_kehadiran
    );

    console.log("Status kehadiran:", status);

    // Kelas berdasarkan status kehadiran
    switch (status) {
        case "P":
            return "bg-info text-white fw-bold"; // Hadir
        case "A":
            return "bg-danger text-white fw-bold"; // Absen
        case "S":
            return "bg-warning text-white fw-bold"; // Sakit
        case "I":
            return "bg-primary text-white fw-bold"; // Izin
        default:
            return "bg-light text-dark"; // Status tidak ditemukan atau belum diabsen
    }
};

{
    // Lifecycle Hooks
    onMounted(async () => {
        const csrfToken = document.head.querySelector(
            'meta[name="csrf-token"]'
        )?.content;
        console.log("Token CSRF:", csrfToken);
        axios.defaults.headers.common["X-CSRF-TOKEN"] = csrfToken;

        console.log("Tanggal Kehadiran yang dikirim:", tanggal_kehadiran.value);

        try {
            // Pastikan kedua fungsi dijalankan dengan benar
            await fetchStudents();
            await fetchAttendances();
            updateAttendanceStatus();
            handleAttendanceChange();
        } catch (error) {
            console.error("Error saat memuat data awal:", error);
        }
        // Fungsi untuk memproses data absensi
        if (!attendances.value || !Array.isArray(attendances.value)) {
            console.error("attendances.value tidak valid:", attendances.value);
            return;
        }
        console.log("Data Attendance untuk siswa:", newAttendance.value);

        // Mengatur tanggal dan bulan
        const today = new Date();
        totalDaysInMonth.value = new Date(
            today.getFullYear(),
            today.getMonth() + 1,
            0
        ).getDate();
        currentMonthYear.value = today.toLocaleDateString("id-ID", {
            month: "long",
            year: "numeric",
        });

        // Menghitung bulan berikutnya
        const nextMonth = new Date(today.getFullYear(), today.getMonth() + 1);
        nextMonthYear.value = nextMonth.toLocaleDateString("id-ID", {
            month: "long",
            year: "numeric",
        });

        // Bulan setelah bulan berikutnya (2 bulan ke depan)
        const nextNextMonth = new Date(
            today.getFullYear(),
            today.getMonth() + 2
        );
        nextNextMonthYear.value = nextNextMonth.toLocaleDateString("id-ID", {
            month: "long",
            year: "numeric",
        });
    });

    // Inertia Page Props and Form Handling
    const { props } = usePage();
    const form = useForm({
        name: props.auth.user.name,
        email: props.auth.user.email,
        role_type: props.auth.user.role_type,
    });

    const pageNumber = ref(1);
    const searchTerm = ref(props.search ?? "");

    const studentsUrl = computed(() => {
        const url = new URL(route("students.index"));
        url.searchParams.set("page", pageNumber.value);
        if (searchTerm.value) {
            url.searchParams.set("search", searchTerm.value);
        }
        return url;
    });

    fetchStudents(1);

    watchEffect(() => {
        if (!students.value || students.value.length === 0) {
            // Proses data siswa
            console.log("Data siswa ditemukan:", students.value);
        }
    });
    // Watch for page number changes
    watch(pageNumber, async (newPage) => {
        console.log("Halaman baru:", newPage);
        fetchStudents(newPage);
    });

    watch(
        () => studentsUrl,
        (updatedStudentsUrl) => {
            if (updatedStudentsUrl) {
                router.visit(updatedStudentsUrl.toString(), {
                    preserveState: true,
                    preserveScroll: true,
                    replace: true,
                });
            } else {
                console.error("URL tidak valid:", studentsUrl.value);
            }
        }
    );

    watch(
        () => newAttendance.value,
        (newValue) => {
            console.log("Add newAttendance:", newValue); // Pastikan data ada di sini
        },
        { deep: true }
    );

    watch(students, (newStudents) => {
        newStudents.forEach((student) => {
            if (!(student.id in newAttendance.value)) {
                newAttendance.value[student.id] = student.defaultStatus || "P";
            } else {
                console.log(
                    `Student ID ${student.id} already has an attendance status.`
                );
            }
        });
        fetchAttendances();
        return true;
    });
    /*
        watch(
        selectedStudentStatuses,
        (newVal) => {
            attendances.value = Object.entries(newVal).map(
                ([studentId, status]) => ({
                    student_id: parseInt(studentId, 10),
                    status_kehadiran: status,
                })
            );
        },
        { deep: true }
    );
    */

    watch(
        selectedStudentStatuses,
        (newStatus) => {
            console.log("Updated selectedStudentStatuses:", newStatus);
            Object.keys(newStatus).forEach((studentId) => {
                console.log(
                    `Student ID: ${studentId}, Status: ${newStatus[studentId]}`
                );
            });
        },
        { deep: true }
    );
}
</script>

<style scoped>
.g-bordered {
    border: 1px solid #606060;
}
.table-bordered td {
    height: 20px;
    width: 20px;
    font-size: 12px;
}
.modal-content {
    background-color: white;
    margin: 15% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
}
.close {
    color: #aaa;
    font-size: 28px;
    font-weight: bold;
    position: absolute;
    top: 0;
    right: 20px;
}
.close:hover,
.close:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}
.large-td {
    font-size: 28px;
    font-style: normal;
    padding: 15px;
    /* Sesuaikan dengan lebar yang diinginkan */
}
.large-p {
    font-size: 12px;
    font-style: normal;
    text-align: center;
    padding: 15px;
}

.custom-thead {
    background-color: #4caf50; /* Ganti dengan warna yang Anda inginkan */
    color: yellow; /* Warna teks */
}

.custom-thead th {
    padding: 10px; /* Menambahkan padding agar teks tidak terlalu rapat */
    text-align: center; /* Menjaga agar teks berada di tengah */
}
</style>

<style>
.modal.fade.show {
    display: block;
    background-color: rgba(15, 13, 14, 0.889);
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
            <form @submit.prevent="submitAttendance">
                <div class="container py-5">
                    <div class="text-3xl d-flex justify-content-between mb-3">
                        <div class="sm:flex sm:items-center">
                            <div class="sm:flex-auto font-semibold">
                                <h1
                                    class="text-3xl font-semibold text-gray-900"
                                >
                                    Tabel Absensi Siswa
                                </h1>
                                <p class="text-sm mb-3 fw-bold text-danger">
                                    Bulan {{ currentMonthYear }}
                                </p>
                                <!--
                                {{ nextMonthYear }} and {{ nextNextMonthYear }}
                                -->
                            </div>
                        </div>
                        <div>
                            <button
                                class="btn btn-primary modal-title fs-5"
                                @click="showAddModal"
                            >
                                Tambah Absen
                            </button>
                        </div>
                    </div>

                    <div
                        class="g-responsive overflow-x-auto max-w-full"
                        style="background-color: aliceblue"
                    >
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr class="custom-tr">
                                    <th>Tanggal</th>
                                    <th
                                        v-for="(
                                            date, index
                                        ) in totalDaysInMonth"
                                        :key="'date-' + index"
                                        class="text-center w-42"
                                    >
                                        {{ date }}
                                    </th>
                                </tr>
                                <tr class="custom-tr">
                                    <th>Hari</th>
                                    <th
                                        v-for="(day, index) in totalDaysInMonth"
                                        :key="'day-name-' + index"
                                        :class="{
                                            'bg-danger text-white':
                                                isSunday(day), // Cek hari Minggu
                                        }"
                                    >
                                        {{ getDayName(getValidDate(day)) }}
                                    </th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr
                                    v-for="student in paginatedStudents"
                                    :key="student.id"
                                >
                                    <td>{{ student.name }}</td>
                                    <td
                                        v-for="(
                                            date, index
                                        ) in totalDaysInMonth"
                                        :key="
                                            'attendance-' +
                                            student.id +
                                            '-' +
                                            date
                                        "
                                    >
                                        ngentot
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Menampilkan absensi status tambahan -->
                    <!--
                                                                        <div
                                        v-if="
                                            attendances &&
                                            Object.keys(attendances).length > 0
                                        "
                                    >
                                        <div
                                            v-for="student in students"
                                            :key="student.id"
                                        >
                                            <p>{{ student.name }}:</p>
                                            {{
                                                getAttendanceStatus(
                                                    student.id
                                                ) || "Belum mengisi status"
                                            }}
                                        </div>
                                    </div>
                                     -->

                    <!-- flex justify-center mt-4-->
                    <div class="flex flex-col items-center mt-4">
                        <!-- Help text -->
                        <span class="text-sm text-gray-700 dark:text-gray-400">
                            Page
                            <span
                                class="font-semibold text-gray-900 dark:text-white"
                                >{{ pagination.current_page }}</span
                            >
                            of
                            <span
                                class="font-semibold text-gray-900 dark:text-white"
                                >{{ pagination.last_page }}</span
                            >
                        </span>

                        <!--
                                            <div
                            v-for="(
                                newStatus, studentId
                            ) in newAttendance.value"
                            :key="studentId"
                        >
                            <label :for="`student-${studentId}`"
                                >Status Kehadiran Siswa {{ studentId }}</label
                            >
                            <input
                                type="text"
                                :id="`student-${studentId}`"
                                v-model="attendances.value[studentId]"
                                placeholder="Isi status kehadiran"
                            />
                        </div>
                        -->

                        <div class="inline-flex mt-2 xs:mt-0">
                            <!-- Tombol Previous -->
                            <button
                                @click="
                                    fetchStudents(pagination.current_page - 1)
                                "
                                :disabled="pagination.current_page <= 1"
                                class="flex items-center justify-center px-4 h-10 text-base font-medium text-white bg-gray-800 rounded-s hover:bg-gray-900 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <svg
                                    class="w-3.5 h-3.5 me-2 rtl:rotate-180"
                                    aria-hidden="true"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 14 10"
                                >
                                    <path
                                        stroke="currentColor"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M13 5H1m0 0 4 4M1 5l4-4"
                                    />
                                </svg>
                                Previous
                            </button>

                            <!-- Tombol Next -->
                            <button
                                @click="
                                    fetchStudents(pagination.current_page + 1)
                                "
                                :disabled="
                                    pagination.current_page >=
                                    pagination.last_page
                                "
                                class="flex items-center justify-center px-4 h-10 text-base font-medium text-white bg-blue-500 border-0 border-s border-gray-700 rounded-e hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <!-- bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none -->
                                Next
                                <svg
                                    class="w-3.5 h-3.5 ms-2 rtl:rotate-180"
                                    aria-hidden="true"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 14 10"
                                >
                                    <path
                                        stroke="currentColor"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M1 5h12m0 0L9 1m4 4L9 9"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <p class="fw-bold">Keterangan Status Kehadiran:</p>
                            <div class="d-flex">
                                <div class="me-3">
                                    <span
                                        class="badge bg-info text-white fw-bold"
                                        >Hadir (P)</span
                                    >
                                </div>
                                <div class="me-3">
                                    <span
                                        class="badge bg-danger text-white fw-bold"
                                        >Absen (A)</span
                                    >
                                </div>
                                <div class="me-3">
                                    <span
                                        class="badge bg-warning text-white fw-bold"
                                        >Sakit (S)</span
                                    >
                                </div>
                                <div class="me-3">
                                    <span
                                        class="badge bg-primary text-white fw-bold"
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

                    <!--
                                     <div>
                        <p>{{ getFormattedDate(0) }}</p>
                        <!-- Hari ini -->
                    <!--  <p>{{ getFormattedDate(1) }}</p>-->
                    <!-- Besok -->
                    <!--<p>{{ getFormattedDate(2) }}</p>-->
                    <!-- Lusa -->
                    <!-- </div>-->

                    <!-- Modal Tambah Absen -->
                    <div
                        v-if="isAddModalVisible"
                        class="modal fade show"
                        tabindex="-1"
                        aria-hidden="true"
                    >
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Tambah Absensi</h5>
                                    <button
                                        type="button"
                                        class="btn-close"
                                        @click="hideAddModal"
                                        aria-label="Close"
                                    ></button>
                                </div>
                                <div class="modal-body">
                                    <form @submit.prevent="submitAttendance">
                                        <div class="form-group mb-3">
                                            <label for="tanggal-kehadiran"
                                                >Pilih Tanggal</label
                                            >
                                            <input
                                                id="tanggal-kehadiran"
                                                type="date"
                                                class="form-control"
                                                v-model="tanggal_kehadiran"
                                                required
                                            />
                                        </div>
                                        <table class="table table-bordered">
                                            <tr
                                                v-for="student in paginatedStudents"
                                                :key="student.id"
                                            >
                                                <td>{{ student.name }}</td>
                                                <td>
                                                    <select
                                                        v-model="
                                                            selectedStudentStatuses[
                                                                student.id
                                                            ]
                                                        "
                                                        class="form-select"
                                                        :class="{
                                                            'is-invalid':
                                                                !selectedStudentStatuses[
                                                                    student.id
                                                                ],
                                                        }"
                                                    >
                                                        <option
                                                            value=""
                                                            disabled
                                                        >
                                                            Pilih Status
                                                        </option>
                                                        <option value="P">
                                                            HADIR
                                                        </option>
                                                        <option value="A">
                                                            ALPHA
                                                        </option>
                                                        <option value="S">
                                                            SAKIT
                                                        </option>
                                                        <option value="I">
                                                            IZIN
                                                        </option>
                                                    </select>
                                                </td>
                                            </tr>
                                        </table>

                                        <button
                                            type="submit"
                                            class="btn btn-primary w-100"
                                        >
                                            Simpan Absensi
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!--
                    -->
                </div>
            </form>
        </main>

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
                <form action="#" method="GET" class="md:hidden mb-2">
                    <label for="sidebar-search" class="sr-only">Search</label>
                    <div class="relative">
                        <div
                            class="flex absolute inset-y-0 left-0 items-center pl-3 pointer-events-none"
                        >
                            <svg
                                class="w-5 h-5 text-gray-500 dark:text-gray-400"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    fill-rule="evenodd"
                                    clip-rule="evenodd"
                                    d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                ></path>
                            </svg>
                        </div>
                        <input
                            type="text"
                            name="search"
                            id="sidebar-search"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full pl-10 p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                            placeholder="Search"
                        />
                    </div>
                </form>
                <ul class="space-y-2">
                    <li>
                        <a
                            href="teachersDashboard"
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
                            href="mataPelajaran"
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
                            <span class="ml-3">Mata Pelajaran</span>
                        </a>
                    </li>

                    <li>
                        <a
                            href="tugas"
                            class="flex items-center p-2 text-base font-medium text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group"
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
                            <span class="ml-3">Upload Tugas</span>
                        </a>
                    </li>
                </ul>
            </div>
        </aside>
    </div>
</template>
