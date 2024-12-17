<script setup>
import {
    onMounted,
    ref,
    watch,
    watchEffect,
    computed,
    reactive,
    toRaw,
    isProxy,
} from "vue";
import axios from "axios";
import { Link, Head, useForm, usePage, router } from "@inertiajs/vue3";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";

// State management
const newAttendance = ref([]);
const loading = ref(true);
const studentId = ref([1]);
const students = ref([]);
const props = defineProps(["student"]);
const student = props.student;
const selectedStudentStatuses = ref({});
//const currentMonthYear = ref("");
const pageNumber = ref(1);
const newStatus = ref("");
//const newStatus = selectedStudentStatuses.value[studentId]?.status_kehadiran[0]; // Mengambil status dari array

const today = new Date();
const currentYear = new Date().getFullYear();
const currentMonth = new Date().getMonth();
const currentMonthYear = computed(() => {
    const months = [
        "Januari",
        "Februari",
        "Maret",
        "April",
        "Mei",
        "Juni",
        "Juli",
        "Agustus",
        "September",
        "Oktober",
        "November",
        "Desember",
    ];
    const monthName = months[currentMonth]; // Mendapatkan nama bulan berdasarkan currentMonth
    return `${monthName} ${currentYear}`; // Format "Bulan YYYY"
});
// Mendapatkan tanggal pertama bulan ini
const defaultDay = new Date(currentYear, currentMonth, 1).getDate();
if (defaultDay === 0) {
    console.error("Default Day tidak valid:", defaultDay);
}

// Mendapatkan tanggal minimum dan maksimum dalam bulan ini
const minDate = `${currentYear}-${(currentMonth + 1)
    .toString()
    .padStart(2, "0")}-01`;
const maxDate = `${currentYear}-${(currentMonth + 1)
    .toString()
    .padStart(2, "0")}-${new Date(currentYear, currentMonth + 1, 0).getDate()}`;

// Format tanggal dengan dua digit untuk hari dan bulan
const formatDate = (date) => {
    const day = date.getDate().toString().padStart(2, "0");
    const month = (date.getMonth() + 1).toString().padStart(2, "0"); // Bulan +1 karena bulan dimulai dari 0
    const year = date.getFullYear();
    return `${year}-${month}-${day}`; // Format YYYY-MM-DD
};

const getDayName = (date) => {
    if (isNaN(new Date(date))) {
        console.log("Tanggal tidak valid:", date);
        return;
    }
    const dateObj = new Date(date);
    //console.log("Date Object:", dateObj);
    const daysOfWeek = [
        "Minggu",
        "Senin",
        "Selasa",
        "Rabu",
        "Kamis",
        "Jumat",
        "Sabtu",
    ];
    const dayName = daysOfWeek[dateObj.getDay()];
    return dayName;
};

const dayIndex = 0;
const getFormattedDate = (dayIndex) => {
    if (dayIndex === undefined || isNaN(dayIndex)) {
        console.error("dayIndex tidak valid:", dayIndex);
        return "Tanggal tidak valid";
    }

    const currentDate = new Date();
    currentDate.setDate(currentDate.getDate() + dayIndex); // Menambahkan dayIndex ke tanggal saat ini

    // Validasi jika tanggal valid
    if (isNaN(currentDate.getTime())) {
        console.error("Tanggal tidak valid untuk dayIndex:", dayIndex);
        return "Tanggal tidak valid";
    }

    const day = currentDate.getDate().toString().padStart(2, "0"); // Format 2 digit
    const month = (currentDate.getMonth() + 1).toString().padStart(2, "0"); // Bulan dalam format 2 digit
    const year = currentDate.getFullYear();
    const formattedDate = `${year}-${month}-${day}`;
    const dayName = getDayName(dayIndex); // Mendapatkan nama hari
    return `${dayName}, ${day}-${month}-${year}`; // Format: "Senin, 07-12-2024"
};

const defaultTanggal = computed(() => {
    const today = new Date();
    const day = today.getDate().toString().padStart(2, "0");
    const month = (today.getMonth() + 1).toString().padStart(2, "0");
    const year = today.getFullYear();
    return `${year}-${month}-${day}`; // Pastikan format YYYY-MM-DD
});

const totalDaysInMonth = Array.from(
    { length: new Date(currentYear, currentMonth + 1, 0).getDate() },
    (_, i) => i + 1
);

totalDaysInMonth.forEach((dayIndex) => {
    console.log(getFormattedDate(dayIndex)); // Memanggil fungsi dengan dayIndex yang valid
});

console.log(totalDaysInMonth);

const isSunday = (day) => {
    const dateObj = new Date(currentYear, currentMonth, day);
    return dateObj.getDay() === 0; // 0 berarti Minggu
};

console.log(defaultTanggal.value);

const tanggal_kehadiran = ref(
    `${currentYear}-${(currentMonth + 1)
        .toString()
        .padStart(2, "0")}-${defaultDay}`
);

console.log(tanggal_kehadiran.value);
const pageChanged = ref(false);
const isNavigating = ref(false);
const isSelectVisible = ref(false);
const selectedDate = ref(null);
const nextMonthYear = ref("");
const nextNextMonthYear = ref("");
const isAddModalVisible = ref(false);

// Helper functions
const getCurrentMonthYear = () => {
    const today = new Date();
    return today.toLocaleDateString("id-ID", {
        month: "long",
        year: "numeric",
    });
};

// Date related updates
const updateDate = (event) => {
    date.value = new Date(event.target.value); // Memperbarui objek Date
    console.log("Updated date:", date.value);
};

// Modal handling functions
function toggleModalSave() {
    console.log("toggleModalSave dipanggil");
    isAddModalVisible.value = !isAddModalVisible.value;
}

// Logs for debugging
//console.log(studentId.value);
console.log("Data siswa:", toRaw(studentId.value));

// Example of page navigation state
watch(pageNumber, (newPageNumber) => {
    console.log("Page changed to:", newPageNumber);
});

const data = {
    tanggal_kehadiran: tanggal_kehadiran.value,
    attendances: Object.values(selectedStudentStatuses.value), // Contoh pengiriman status absensi
};
console.log("Tanggal Kehadiran:", tanggal_kehadiran.value);

console.log("Data yang dikirim ke API ||const data: ", data);

// Data form yang akan dikirim ke backend
const form = ref({
    tanggal_kehadiran: "",
    attendances: [], // Array untuk menyimpan status kehadiran tiap siswa
});

const attendances = ref([]);

let isFetchingData = false;

const fetchData = async () => {
    if (isFetchingData) return; // Menghentikan jika masih dalam proses pengambilan data
    isFetchingData = true;
    loading.value = false;

    try {
        // Debugging - cek nilai studentId sebelum melanjutkan
        console.log("Student ID sebelum fetchData:", studentId.value);

        // Pastikan studentId terisi dengan valid sebelum melanjutkan
        if (!studentId.value || studentId.value.length === 0) {
            return;
        }

        const studentIds = studentId.value;

        // Pastikan studentIds adalah array yang valid
        if (!Array.isArray(studentIds) || studentIds.length === 0) {
            console.error(
                "Data siswa tidak ditemukan atau studentIds bukan array:",
                studentIds
            );
            return;
        }

        console.log("Student IDs:", studentIds); // Debugging untuk memastikan studentIds adalah array

        // Ambil data absensi
        const response = await axios.get("/api/attendance1"); // Ganti dengan endpoint yang sesuai

        console.log(
            "Data absensi yang diterima:",
            JSON.stringify(response.data.data, null, 2)
        );

        console.log(
            "Respons dari API fetchData:",
            JSON.stringify(response, null, 2)
        );

        if (response.data && response.data.attendances) {
            const absensiArray = Object.entries(response.data.attendances).map(
                ([studentId, attendance]) => ({
                    studentId, // ID Siswa
                    status: Object.entries(attendance).map(
                        ([date, status]) => ({
                            date, // Tanggal
                            status, // Status kehadiran
                        })
                    ),
                })
            );

            // Proses pembaruan status absensi
            absensiArray.forEach(({ studentId, status }) => {
                status.forEach((attendance) => {
                    if (!attendance.date || !attendance.status) {
                        console.warn(
                            "Status atau tanggal absensi tidak lengkap:",
                            attendance
                        );
                        return;
                    }

                    // Memperbarui status absensi siswa
                    selectedStudentStatuses.value[studentId] =
                        selectedStudentStatuses.value[studentId] || {};

                    selectedStudentStatuses.value[studentId][attendance.date] =
                        attendance.status || "Belum diabsen"; // Status default jika tidak ada status
                });
            });

            // Assign absensi array ke attendances setelah validasi
            attendances.value = absensiArray;
        } else {
            console.warn("Data absensi kosong, memberikan status default.");
        }

        // Cek tipe dan struktur data attendances
        console.log("Tipe attendances:", typeof attendances.value);
        console.log("Struktur attendances:", attendances.value);

        // Jika attendances adalah objek, konversi ke array
        if (!Array.isArray(attendances.value)) {
            // Mengonversi objek menjadi array
            attendances.value = Object.values(attendances.value);
        }

        // Jika data absensi kosong, memberikan status default untuk setiap siswa
        console.log("Data siswa yang diterima:", students);
        paginatedStudents.value = attendances.value.filter((student) => {
            // Cek apakah student adalah objek yang valid
            if (typeof student !== "object" || student === null) {
                console.warn("Data siswa tidak valid:", student);
                return false; // Lewatkan data yang tidak valid
            }

            // Validasi struktur objek siswa
            if (!student.studentId || !Array.isArray(student.status)) {
                console.warn(
                    "Data siswa tidak valid. Student ID atau Status tidak ditemukan:",
                    student
                );
                return false; // Lewatkan data yang tidak valid
            }

            // Periksa apakah status adalah array dengan elemen yang valid
            if (
                student.status.length === 0 ||
                !student.status.every((status) =>
                    ["P", "A", "S", "I"].includes(status)
                )
            ) {
                console.warn(
                    "Status absensi tidak valid untuk siswa ID:",
                    student.studentId
                );
                return false; // Lewatkan data yang tidak valid
            }

            // Jika semua validasi lulus, data siswa valid dan bisa diproses
            return true;
        });

        // Menangani data yang tidak valid secara lebih jelas
        if (paginatedStudents.value.length === 0) {
            console.warn("Tidak ada siswa yang valid untuk diproses.");
        }

        console.log(
            "Selected Student Statuses:",
            selectedStudentStatuses.value
        );
        console.log("Paginated Students:", paginatedStudents.value);
    } catch (error) {
        console.error("Error fetching data:", error);
    } finally {
        // Pastikan flag di-reset setelah proses selesai
        isFetchingData = false;
    }

    console.log("Fetched newData:", newData.value); // Gunakan newData.value setelah fetch
};

// Panggil fungsi fetchData untuk memulai pemanggilan data
fetchData();

const statusChanged = ref(false);

// Fungsi untuk menyimpan status ke localStorage
const saveStatusToLocalStorage = () => {
    // Ambil data absensi dari selectedStudentStatuses dan simpan ke localStorage
    const storedStatuses = toRaw(selectedStudentStatuses.value); // Pastikan data dalam bentuk biasa
    // Cek apakah ada perubahan sebelum menyimpan
    if (
        JSON.stringify(storedStatuses) !== localStorage.getItem("attendances")
    ) {
        localStorage.setItem("attendances", JSON.stringify(storedStatuses));
        console.log("Status kehadiran disimpan ke localStorage.");
    }
};

const attendanceData = Object.entries(selectedStudentStatuses.value).map(
    ([studentId, status]) => ({
        student_id: studentId,
        status_kehadiran: status.status_kehadiran,
    })
);
console.log("Data absensi yang siap dikirim:", attendanceData);

function updateStatusesFromServer(attendanceData) {
    attendanceData.forEach((attendance) => {
        const studentId = attendance.student_id;
        const newStatus = attendance.status_kehadiran;

        // Validasi data absensi
        if (!studentId || !newStatus) {
            console.warn(
                `Data absensi tidak lengkap untuk siswa ID: ${studentId}`
            );
            return; // Skip jika data tidak valid
        }

        // Periksa apakah status siswa sudah diubah oleh pengguna
        const currentStatus =
            selectedStudentStatuses.value[studentId]?.status_kehadiran;

        // Jika status sudah diubah oleh pengguna, jangan diperbarui oleh server
        if (currentStatus && currentStatus !== newStatus) {
            console.log(
                `Status siswa ID ${studentId} tidak diperbarui karena sudah diubah oleh pengguna`
            );
            return;
        }

        // Jika status belum diubah oleh pengguna atau siswa belum ada di selectedStudentStatuses
        if (selectedStudentStatuses.value[studentId]) {
            // Pastikan status selalu disimpan dalam bentuk objek dengan properti 'status_kehadiran'
            if (typeof selectedStudentStatuses.value[studentId] === "string") {
                selectedStudentStatuses.value[studentId] = {
                    status_kehadiran: selectedStudentStatuses.value[studentId],
                };
            }

            // Jika status siswa berbeda dengan status baru dari server, perbarui status
            if (
                selectedStudentStatuses.value[studentId].status_kehadiran !==
                newStatus
            ) {
                selectedStudentStatuses.value[studentId].status_kehadiran =
                    newStatus;
                console.log(
                    `Status siswa ID ${studentId} diperbarui ke: ${newStatus}`
                );
            }
        } else {
            // Jika siswa belum ada, tambahkan status siswa baru
            selectedStudentStatuses.value[studentId] = {
                student_id: studentId,
                status_kehadiran: newStatus,
            };
            console.log(
                `Status siswa ID ${studentId} ditambahkan: ${newStatus}`
            );
        }
    });

    console.log(
        "Selected Student Statuses setelah update:",
        selectedStudentStatuses.value
    );
}

function normalizeAttendanceData(attendances) {
    const normalizedAttendances = {};

    Object.keys(attendances).forEach((studentId) => {
        let attendance = attendances[studentId];

        // Jika attendance memiliki key "P", itu berarti format data yang salah
        if (attendance.hasOwnProperty("P")) {
            // Menormalisasi format absensi untuk siswa dengan ID 1 atau format serupa
            attendance = {
                status_kehadiran: attendance.P.status_kehadiran,
                tanggal_kehadiran: attendance.P.tanggal_kehadiran,
            };
        }

        // Pastikan bahwa setiap siswa memiliki "status_kehadiran" dan "tanggal_kehadiran"
        if (!attendance.hasOwnProperty("tanggal_kehadiran")) {
            // Tentukan tanggal default jika belum ada
            attendance.tanggal_kehadiran = "2024-12-24"; // Ganti dengan tanggal yang sesuai
        }

        normalizedAttendances[studentId] = attendance;
    });

    return normalizedAttendances;
}

async function processAttendanceUpdates() {
    if (
        !selectedStudentStatuses.value ||
        Object.keys(selectedStudentStatuses.value).length === 0
    ) {
        console.error("selectedStudentStatuses masih kosong!");
        return;
    }

    // Iterasi setiap siswa di selectedStudentStatuses
    for (const studentId in selectedStudentStatuses.value) {
        const status =
            selectedStudentStatuses.value[studentId]?.status_kehadiran;

        // Hanya proses siswa yang memiliki status absensi yang valid
        if (!status || !["P", "A", "S", "I"].includes(status)) {
            console.warn(`Data tidak valid untuk siswa ID: ${studentId}`, {
                status,
            });
            continue; // Lewati siswa ini jika status tidak valid
        }

        // Ambil data absensi dari localStorage
        const storedAttendances =
            JSON.parse(localStorage.getItem("attendances")) || {};

        // Normalisasi data absensi
        const normalizedAttendances =
            normalizeAttendanceData(storedAttendances);

        // Simpan kembali data yang sudah dinormalisasi ke localStorage
        localStorage.setItem(
            "attendances",
            JSON.stringify(normalizedAttendances)
        );

        // Pastikan bahwa storedAttendances adalah objek yang valid
        if (
            typeof storedAttendances !== "object" ||
            storedAttendances === null
        ) {
            console.error("Data absensi tidak dalam format objek yang valid!");
            return;
        }

        const normalizedStudentId = Number(studentId);

        // Temukan data absensi terkait siswa
        const attendance = normalizedAttendances[normalizedStudentId];

        console.log(
            "Absensi ditemukan untuk siswa ID:",
            normalizedStudentId,
            attendance
        );

        if (!attendance) {
            console.warn(
                `Absensi tidak ditemukan untuk siswa ID: ${normalizedStudentId}`
            );
            continue;
        }

        // Proses pembaruan absensi
        console.log("Memperbarui absensi:", { studentId, status });

        // Update status absensi berdasarkan ID siswa dan tanggal yang valid
        const date = attendance.tanggal_kehadiran;
        updateAttendanceStatus(studentId, date, status); // Pastikan fungsi ini menerima ID, tanggal, dan status
    }

    console.log("Pembaruan absensi selesai!");
}

const fetchAttendances = async () => {
    try {
        const response = await axios.get("/api/attendances");
        console.log("Response Data:", response.data);

        // Pastikan response.data.attendances adalah array
        if (Array.isArray(response.data.attendances)) {
            attendanceData.value = response.data.attendances;
            console.log("Absensi Data Valid:", attendanceData.value);

            // Memproses data absensi
            processAttendances();

            // Pastikan ada data yang valid untuk absensi
            if (attendanceData.value.length === 0) {
                console.error(
                    "Data absensi kosong, pastikan semua siswa memiliki status absensi yang valid."
                );
                alert(
                    "Data absensi kosong, pastikan semua siswa memiliki status absensi yang valid."
                );
                return;
            }
        } else {
            console.error(
                "Data absensi tidak valid: response.data.attendances bukan array."
            );
            attendanceData.value = [];
        }

        // Pastikan newAttendance diisi dengan data yang benar
        newAttendance.value = Array.isArray(response.data.attendances)
            ? response.data.attendances
            : Object.values(response.data.attendances);

        console.log("New Attendance Array:", newAttendance.value);
    } catch (error) {
        console.error("Error fetching attendances:", error);
    }
};

const combineStudentAndAttendance = () => {
    const studentAttendanceMap = studentId.value.map((student) => {
        const attendance = newAttendance.value.find(
            (att) => att.student_id === student.id
        );
        return {
            ...student,
            attendance_status: attendance ? attendance.status_kehadiran : "P", // Default "P" if no attendance found
        };
    });
    console.log("Combined Student and Attendance:", studentAttendanceMap);
    return studentAttendanceMap;
};

const refreshAttendanceData = async () => {
    // Ambil data absensi berdasarkan tanggal yang dipilih
    await fetchAttendances({ tanggal_kehadiran: selectedDate.value });

    // Log data terbaru (opsional)
    console.log("Data attendances setelah refresh:", attendances.value);
};

const refreshAttendanceDataAndCombine = async () => {
    await refreshAttendanceData(); // Ambil data absensi terbaru
    const combinedData = combineStudentAndAttendance(); // Gabungkan data siswa dan absensi
    studentAttendanceMap.value = combinedData; // Simpan data gabungan ke variabel yang akan digunakan di template
};

const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 5,
    total: 0,
});

const loadNextPage = async (nextPageUrl) => {
    try {
        const response = await axios.get(nextPageUrl);

        if (
            response.data &&
            response.data.data &&
            response.data.data.length > 0
        ) {
            // Mengambil ID dan nama siswa
            const nextPageData = response.data.data.map((student) => ({
                id: student.id,
                name: student.name,
            }));

            // Menggabungkan ID siswa dari halaman berikutnya dengan ID siswa yang sudah ada
            studentId.value = [
                ...studentId.value.filter(
                    (student) => typeof student === "number"
                ), // Pastikan hanya ID yang valid (angka)
                ...nextPageData, // Data dari halaman berikutnya
            ];

            console.log(
                "Siswa setelah memuat halaman berikutnya:",
                studentId.value
            );
            // Jika ada halaman berikutnya, lanjutkan memuat
            if (response.data.next_page_url) {
                console.log(
                    "Melanjutkan ke halaman berikutnya:",
                    response.data.next_page_url
                );
                await loadNextPage(response.data.next_page_url);
            }
        }
    } catch (error) {
        console.error("Error loading next page:", error);
    }
};

//const date = totalDaysInMonth.value[0];

// Inisialisasi array totalDaysInMonth
const initializeDaysInMonth = () => {
    const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
    totalDaysInMonth.value = Array.from(
        { length: daysInMonth },
        (_, index) => new Date(currentYear, currentMonth, index + 1)
    );
};

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
// Menyusun data absensi hanya untuk siswa yang ada pada halaman ini

//const attendancesData = Object.entries(selectedStudentStatuses.value).filter(
([studentId, status]) => {
    // Kirim data jika status tidak 'P', 'A', 'S', atau 'I'
    //return (
    ////status.status_kehadiran &&
    //!["P", "A", "S", "I"].includes(status.status_kehadiran)
    //);
};
//);
/*
    .map(([studentId, status]) => ({
        student_id: parseInt(studentId, 10),
        status_kehadiran: status.status_kehadiran || "P",
        tanggal_kehadiran: tanggal_kehadiran.value,
    }))
    .filter(Boolean);
   */

const paginatedStudents = computed(() => {
    return studentId.value.slice(0, 5);
});

console.log("Tanggal kehadiran sebelum pengiriman:", tanggal_kehadiran.value);

console.log(
    "Selected Student Statuses setelah inisialisasiii:",
    selectedStudentStatuses.value
);

const processAttendances = () => {
    if (Array.isArray(attendanceData.value)) {
        attendances.value = attendanceData.value
            .map((attendance) => {
                if (!attendance.student_id || !attendance.status_kehadiran) {
                    console.warn(
                        `Data tidak lengkap untuk siswa ID: ${attendance.student_id}`
                    );
                    return null; // Kembalikan null jika data tidak lengkap
                }
                return {
                    student_id: attendance.student_id,
                    status_kehadiran:
                        attendance.status_kehadiran || "Belum diabsen", // Gunakan status default
                };
            })
            .filter(Boolean); // Hapus nilai null atau data yang tidak lengkap
    } else {
        console.error("attendancesData bukan array:", attendanceData.value);
    }
};

const resetInvalidStatuses = () => {
    Object.entries(selectedStudentStatuses.value).forEach(
        ([studentId, status]) => {
            // Cek apakah status adalah Proxy, dan jika iya, ambil objek mentahnya
            if (isProxy(status)) {
                status = toRaw(status);
            }

            // Jika status adalah objek, pastikan kita memeriksa properti status_kehadiran
            if (
                typeof status === "object" &&
                status !== null &&
                status.hasOwnProperty("status_kehadiran")
            ) {
                status = status.status_kehadiran;
            }

            // Periksa status, jika status kosong atau "Belum diabsen", atau tidak valid, baru hapus
            if (
                !status || // Jika status kosong
                status === "Belum diabsen" || // Jika status "Belum diabsen"
                !["P", "A", "S", "I"].includes(status) // Jika status bukan P, A, S, I
            ) {
                console.warn(`Menghapus data siswa yang tidak valid:`, {
                    studentId,
                    status,
                });
                delete selectedStudentStatuses.value[studentId];
            }
        }
    );
};

console.log("Selected Student Statuses ||rIS:", selectedStudentStatuses.value);

// Status untuk menentukan apakah data sudah terkirim
const isAttendanceDataSent = ref(false);

const isAlertVisible = ref(false);
const alertMessage = ref("");

// Logika ketika halaman berubah
const handlePageChange = (page) => {
    console.log("Tombol pagination ditekan, halaman sekarang:", page);
    pagination.current_page = page;

    if (isAttendanceDataSent.value) {
        isAttendanceDataSent.value = false;
    }
    fetchStudents(page);
    isAlertVisible.value = false;
};

// Variabel untuk mengontrol modal
const isModalOpen = ref(false);

const isUpdatingAttendance = ref(false);

const showAddModal = () => {
    isAddModalVisible.value = true;
};
//const hideAddModal = () => {
//  isAddModalVisible.value = false;
//};

// Fungsi untuk menutup modal (setelah data terkirim)
const handleModalClose = () => {
    //isModalOpen.value = false; // Tutup modal
    isAddModalVisible.value = false;

    // Pastikan pengiriman data hanya terjadi setelah modal ditutup atau tombol submit ditekan
    if (!isAttendanceDataSent.value && !isUpdatingAttendance.value) {
        submitAttendance(); // Kirim data absensi jika belum terkirim
    }
};

const handleStatusChange = (studentId, date) => {
    // Ambil status yang baru dipilih
    console.log("Menangani status perubahan untuk siswa:", studentId);
    const currentStatus =
        selectedStudentStatuses.value[studentId]?.status_kehadiran;

    console.log(
        `handleStatusChange dipanggil untuk siswa ID: ${studentId} dan tanggal: ${date}`
    );

    // Jika status sudah dipilih, langsung perbarui tanpa prompt
    if (currentStatus) {
        updateAttendanceStatus(studentId, date, currentStatus);
    } else {
        // Prompt hanya muncul jika status belum dipilih
        console.log(
            "Prompt akan dipicu untuk siswa ID:",
            studentId,
            "dan tanggal:",
            date
        );
        const newStatus = prompt("Masukkan status kehadiran: P, A, S, I", "P");
        if (newStatus && ["P", "A", "S", "I"].includes(newStatus)) {
            // Update status jika valid
            updateAttendanceStatus(studentId, date, newStatus);
        }
    }
};

const updateAttendanceStatus = (studentId, date) => {
    // Pastikan tanggal valid
    if (!date || !/^\d{4}-\d{2}-\d{2}$/.test(date)) {
        console.error(`Tanggal tidak valid untuk Student ID: ${studentId}`);
        return;
    }

    console.log(
        `Memperbarui status kehadiran untuk siswa ID: ${studentId} pada tanggal: ${date}`
    );

    // Ambil status baru dari UI atau modal
    const newStatus = prompt("Masukkan status kehadiran: P, A, S, I");
    console.log(`Status yang dimasukkan: ${newStatus}`);

    // Validasi status kehadiran
    if (newStatus && ["P", "A", "S", "I"].includes(newStatus)) {
        let student = selectedStudentStatuses.value[studentId];

        // Jika status tidak ada, buat objek baru untuk siswa
        if (!student) {
            student = {};
        }

        // Memperbarui status kehadiran siswa pada tanggal tertentu
        student[date] = {
            status_kehadiran: newStatus, // Memperbarui status siswa
            tanggal_kehadiran: date, // Set tanggal yang valid
        };

        // Update status kehadiran siswa pada selectedStudentStatuses
        selectedStudentStatuses.value[studentId] = student;

        console.log(
            `Status kehadiran siswa ${studentId} pada ${date} diperbarui menjadi: ${newStatus}`
        );
    } else {
        // Jika status tidak valid, berikan status default "P"
        console.log(
            `Status tidak valid untuk Student ID: ${studentId}. Memberikan status default "P".`
        );

        // Tetap memperbarui status untuk satu siswa (bukan semua siswa)
        selectedStudentStatuses.value[studentId] = {
            [date]: {
                status_kehadiran: "P", // Status default
                tanggal_kehadiran: date, // Set tanggal default
            },
        };
    }

    // Log status yang sudah diperbarui
    console.log(
        "Updated Attendance:",
        JSON.stringify(selectedStudentStatuses.value, null, 2)
    );

    // Simpan status yang diperbarui ke localStorage
    saveStatusToLocalStorage(); // Pastikan fungsi ini menyimpan perubahan ke localStorage
};

// Fungsi untuk menampilkan modal dan menangani form submit
const submitAttendance = async () => {
    try {
        if (!tanggal_kehadiran.value) {
            alert("Tanggal kehadiran harus dipilih!");
            return;
        }

        const studentsPerPage = 5;
        const currentPage = pagination.current_page || 1; // Menggunakan nilai halaman aktif
        const startIndex = (currentPage - 1) * studentsPerPage;
        const endIndex = startIndex + studentsPerPage;

        const currentPageStudents = paginatedStudents.value.slice(
            startIndex,
            endIndex
        );

        const isValidData = currentPageStudents.every((student) => {
            let studentStatus = selectedStudentStatuses.value[student.id] || {
                status_kehadiran: "P",
            };
            studentStatus[tanggal_kehadiran.value] =
                studentStatus.status_kehadiran;

            const statusIsValid = ["P", "A", "S", "I"].includes(
                studentStatus.status_kehadiran
            );
            const dateIsValid = tanggal_kehadiran.value !== "";

            return statusIsValid && dateIsValid;
        });

        if (!isValidData) {
            alert("Terdapat siswa dengan status absensi yang tidak valid.");
            return;
        }

        const attendancesData = currentPageStudents.map((student) => {
            const status =
                selectedStudentStatuses.value[student.id]?.status_kehadiran ||
                "P";
            return {
                student_id: student.id,
                status_kehadiran: status,
                tanggal_kehadiran: tanggal_kehadiran.value,
            };
        });

        if (attendancesData.length === 0) {
            alert("Tidak ada data absensi untuk siswa.");
            return;
        }

        localStorage.setItem("attendances", JSON.stringify(attendancesData));

        const response = await axios.post("/api/attendance3", {
            tanggal_kehadiran: tanggal_kehadiran.value,
            attendances: attendancesData,
        });

        console.log("Response dari server:", response.data);
        alert("Data absensi berhasil disimpan.");

        isAttendanceDataSent.value = true;
        resetAttendanceForm();
        updateStatusesFromServer(response.data.attendances);
        processAttendanceUpdates();
    } catch (error) {
        console.error("Terjadi kesalahan saat mengirim data absensi:", error);
        alert("Terjadi kesalahan saat mengirim data absensi. Mohon coba lagi.");
    }

    isModalOpen.value = true;
};

// Fungsi untuk memuat status dari localStorage
function loadStatusFromLocalStorage() {
    const savedStatuses = localStorage.getItem("selectedStudentStatuses");
    if (savedStatuses) {
        const parsedStatuses = JSON.parse(savedStatuses);
        // Gabungkan data yang dimuat dengan data yang ada
        Object.keys(parsedStatuses).forEach((studentId) => {
            if (!selectedStudentStatuses.value[studentId]) {
                selectedStudentStatuses.value[studentId] =
                    parsedStatuses[studentId];
            }
        });
        console.log("Status absensi dimuat dari localStorage.");
        console.log(localStorage.getItem("attendances"));
    }
}

// Fungsi untuk mereset form input absensi setelah data berhasil disimpan
const resetAttendanceForm = () => {
    // Reset tanggal kehadiran
    //tanggal_kehadiran.value = null; // Atur ini ke null atau sesuai dengan kebutuhan
    // Reset status absensi siswa
};

console.log("Attending data:", attendanceData);

const updateStudentStatus = (studentId, status) => {
    if (selectedStudentStatuses.value[studentId]) {
        selectedStudentStatuses.value[studentId].status_kehadiran = status;
    }
};

const getValidDate = (day) => {
    const dateObj = new Date(currentYear, currentMonth, day);
    if (isNaN(dateObj.getTime())) {
        // Tangani kasus tanggal tidak valid
        return "";
    }
    return dateObj.toISOString().split("T")[0];
};

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

// Ambil CSRF token dari meta tag di halaman Blade
const csrfToken = document.head.querySelector(
    'meta[name="csrf-token"]'
)?.content;

// Set CSRF token di header Axios untuk setiap request
axios.defaults.headers.common["X-CSRF-TOKEN"] = csrfToken;

// memverifikasi apakah token disetel dengan benar sebelum mengirimkan permintaan
console.log("CSRF Token:", axios.defaults.headers.common["X-CSRF-TOKEN"]);

const mapStudentStatuses = () => {
    const savedStatuses = localStorage.getItem("studentStatuses");

    if (savedStatuses) {
        try {
            const parsedStatuses = JSON.parse(savedStatuses);
            if (parsedStatuses && typeof parsedStatuses === "object") {
                selectedStudentStatuses.value = parsedStatuses;
            } else {
                console.warn("Data status absensi yang disimpan tidak valid.");
                selectedStudentStatuses.value = {}; // Inisialisasi jika data tidak valid
            }
        } catch (error) {
            console.error("Error parsing saved statuses:", error);
            selectedStudentStatuses.value = {}; // Fallback jika parsing gagal
        }
    } else {
        selectedStudentStatuses.value = {}; // Inisialisasi jika tidak ada data
    }
};
const fetchStudents = async (page = 1) => {
    isNavigating.value = true;
    loading.value = true;

    try {
        let token = sessionStorage.getItem("auth_token"); // Mengambil token dari sessionStorage

        if (!token) {
            const response = await axios.post("/api/auth/refresh-token");
            console.log("Response dari /api/auth/refresh-token:", response);

            if (response.data && response.data.data) {
                token = response.data.data;
                sessionStorage.setItem("auth_token", token);
            } else {
                console.error(
                    JSON.stringify(
                        {
                            message: "Data token tidak ditemukan",
                            responseData: response.data || null,
                            status: response.status || "Unknown status",
                            timestamp: new Date().toISOString(),
                        },
                        null,
                        2
                    ) // Indentasi 2 spasi untuk keterbacaan
                );

                throw new Error("Gagal mendapatkan token baru");
            }
        }

        const response = await axios.get(
            `/api/students?page=${page}&per_page=5`,
            {
                headers: {
                    Authorization: `Bearer ${token}`,
                },
            }
        );

        if (
            Array.isArray(response.data.data) &&
            response.data.data.length > 0
        ) {
            // Menyaring data siswa
            studentId.value = response.data.data.map((student) => ({
                id: student.id,
                name: student.name,
            }));

            // Inisialisasi status absensi untuk semua siswa yang terambil
            //selectedStudentStatuses.value = {}; // Reset status absensi
            response.data.data.forEach((student) => {
                if (student.id) {
                    // Mengatur status default absensi untuk setiap siswa
                    selectedStudentStatuses.value[student.id] = {};

                    // Mengatur status default absensi berdasarkan tanggal yang dipilih
                    if (
                        !selectedStudentStatuses.value[student.id][
                            tanggal_kehadiran.value
                        ]
                    ) {
                        selectedStudentStatuses.value[student.id][
                            tanggal_kehadiran.value
                        ] = {
                            status_kehadiran: "Belum diabsen", // Status default
                        };
                    }
                }
            });

            // Update pagination
            pagination.value = {
                current_page: response.data.current_page,
                last_page: response.data.last_page,
                total: response.data.total,
                per_page: response.data.per_page,
            };

            // Panggil inisialisasi status absensi setelah data siswa terambil
            initializeStatuses();
        } else {
            console.error("Data siswa tidak ditemukan atau kosong");
            //selectedStudentStatuses.value = {}; // Kosongkan status jika data tidak valid
        }
    } catch (error) {
        console.error("Error saat mengirim data absensi:", error);
    } finally {
        isNavigating.value = false;
        loading.value = false;
    }
};

console.log("Data siswa sebelum pemeriksaan:", studentId.value);

// Inisialisasi selectedStudentStatuses dengan status default jika belum ada
// Inisialisasi selectedStudentStatuses dengan status default jika belum ada
const initializeStatuses = () => {
    if (Array.isArray(paginatedStudents.value)) {
        paginatedStudents.value.forEach((student) => {
            if (typeof student !== "object" || student === null) {
                return;
            }

            // Validasi student.id
            if (student.id !== undefined && student.id !== null) {
                // Jika status absensi belum ada, set status default berdasarkan tanggal
                if (!selectedStudentStatuses.value[student.id]) {
                    selectedStudentStatuses.value[student.id] = {};
                }

                // Jika status untuk tanggal tertentu belum ada, set status default
                if (
                    !selectedStudentStatuses.value[student.id][
                        tanggal_kehadiran.value
                    ]
                ) {
                    selectedStudentStatuses.value[student.id][
                        tanggal_kehadiran.value
                    ] = {
                        status_kehadiran: "Belum diabsen", // Status default
                    };
                }
            } else {
                console.warn(
                    `ID siswa tidak valid: ${JSON.stringify(student)}`
                );
            }
        });
    } else {
        console.warn("Data siswa bukan array:", paginatedStudents.value);
    }
};

// Panggil fungsi inisialisasi saat data siswa pertama kali dimuat
if (!Object.keys(selectedStudentStatuses.value).length) {
    initializeStatuses();
}

const newData = ref([]); // Hanya mendeklarasikan newData sekali di luar

const updateAttendance = async (newData) => {
    try {
        const token = localStorage.getItem("auth_token");
        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute("content");

        // Validasi newData sebelum dikirimkan ke API
        if (!newData || !newData.tanggal_kehadiran) {
            //console.error("Data tidak lengkap:", newData);
            //alert("Data tidak lengkap, silakan coba lagi.");
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

updateAttendance(newData);

// Ambil semua data siswa
const getAllStudents = async () => {
    let currentPageUrl = "http://127.0.0.1:8000/api/students?page=1";
    let allStudents = [];

    while (currentPageUrl) {
        try {
            const response = await axios.get(currentPageUrl);
            allStudents = [...allStudents, ...response.data.data];
            currentPageUrl = response.data.next_page_url;
        } catch (error) {
            console.error("Error mengambil data siswa:", error);
            break;
        }
    }
    students.value = allStudents;

    // Inisialisasi status absensi dengan status default
    allStudents.forEach((student) => {
        selectedStudentStatuses.value[student.id] = selectedStudentStatuses
            .value[student.id] || { status_kehadiran: "Belum diabsen" };
    });
};

getAllStudents(); // Memanggil fungsi untuk mengambil semua siswa

//const date = new Date();
const date = ref(new Date());
console.log("date:", date.value);
console.log("Calling updateAttendanceStatus with:");
console.log("date:", date.value);
console.log("newStatus:", newAttendance.value);
console.log("studentId:", studentId.value);
console.log("newStatus:", newAttendance.value);
console.log("newData:", newData.value);

const updateSelectedStatuses = (newAttendance) => {
    // Membersihkan selectedStudentStatuses.value
    selectedStudentStatuses.value = Object.entries(
        selectedStudentStatuses.value
    )
        .filter(([key, value]) => value.student_id && value.status_kehadiran) // Hanya ambil yang valid
        .map(([key, value]) => {
            value.status_kehadiran = value.status_kehadiran || "Belum diabsen"; // Default status
            return value;
        });

    console.log("Updating statuses with newAttendance:", newAttendance);

    if (newAttendance && Array.isArray(newAttendance)) {
        newAttendance.forEach((attendance) => {
            const studentId = attendance.student_id;

            // Validasi: pastikan student_id ada dan valid
            if (!studentId) {
                console.error(
                    "ID siswa tidak valid atau tidak ditemukan:",
                    attendance
                );
                return; // Tidak lanjutkan pemrosesan untuk item ini
            }

            // Ambil status siswa saat ini dari selectedStudentStatuses
            const currentStatus = selectedStudentStatuses.value[studentId];

            // Jika status siswa sudah ada
            if (currentStatus) {
                // Update status jika belum ada atau status default
                if (currentStatus.status_kehadiran === "Belum diabsen") {
                    // Update status kehadiran dengan status yang diterima atau default "P"
                    currentStatus.status_kehadiran =
                        attendance.status_kehadiran || "P";
                }
            } else {
                // Jika status siswa belum ada, tambahkan status baru
                selectedStudentStatuses.value[studentId] = {
                    student_id: studentId,
                    status_kehadiran:
                        attendance.status_kehadiran || "Belum diabsen",
                };
            }
        });
    } else {
        console.error("newAttendance kosong atau bukan array:", newAttendance);
    }

    // Debug hasil akhir
    console.log(
        "Updated selectedStudentStatuses:",
        selectedStudentStatuses.value
    );
};

const getAttendanceData = async () => {
    try {
        const response = await axios.get("/api/attendance");
        console.log("Data absensi setelah diambil:", response.data);

        // Periksa apakah data absensi kosong
        if (
            Array.isArray(response.data.attendances) &&
            response.data.attendances.length > 0
        ) {
            attendances.value = response.data.attendances;
            updateSelectedStatuses(response.data.attendances);
        } else {
            console.warn("Data absensi kosong.");
            // Berikan penanganan atau feedback untuk kasus kosong
            attendances.value = []; // Kosongkan array absensi
        }
    } catch (error) {
        console.error("Gagal mengambil data absensi:", error);
    }
};

const getAttendanceStatus = (studentId, date) => {
    const rawStatuses = toRaw(selectedStudentStatuses.value); // Mengambil data yang tidak reaktif
    const studentStatuses = rawStatuses[studentId];

    // Cek jika ada status untuk studentId dan tanggal tertentu
    if (studentStatuses) {
        // Pastikan ada status absensi untuk tanggal tertentu
        const statusObj = studentStatuses[date];
        return statusObj ? statusObj.status_kehadiran : "Belum diabsen"; // Mengembalikan status atau 'Belum diabsen'
    }

    return "Belum diabsen"; // Jika tidak ada status absensi untuk studentId
};

const getAttendanceStatusOnClick = (studentId, date) => {
    const student = selectedStudentStatuses.value[studentId];
    if (student && student[date]) {
        return student[date].status_kehadiran;
    }
    return "Belum diabsen"; // Default jika tidak ada status
};

console.log("Nilai isAddModalVisible:", isAddModalVisible.value);

const getAttendanceClass = (studentId, tanggal_kehadiran) => {
    if (!tanggal_kehadiran) {
        return "bg-light text-dark"; // Jika tidak ada tanggal yang dipilih, tampilkan default
    }

    // Panggil fungsi untuk mendapatkan status absensi berdasarkan studentId dan tanggal_kehadiran
    const status = getAttendanceStatus(studentId, tanggal_kehadiran);

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

axios.defaults.headers.common["X-CSRF-TOKEN"] = csrfToken;
onMounted(async () => {
    try {
        loading.value = true; // Menandakan bahwa data sedang diambil

        // Ambil data siswa terlebih dahulu
        await fetchStudents(); // Menunggu hasil dari fetchStudents

        await getAttendanceData();

        mapStudentStatuses();

        loadStatusFromLocalStorage();

        // Mengonversi Proxy menjadi objek biasa
        const studentsData = JSON.parse(
            JSON.stringify(paginatedStudents.value)
        );
        console.log("Data siswa tanpa Proxy:", studentsData);

        // Pastikan selectedStudentStatuses adalah objek yang valid dan kosong
        console.log(
            "Paginated Students || onMounted:",
            paginatedStudents.value
        );
        paginatedStudents.value.forEach((student) => {
            if (student && student.id !== undefined && student.id !== null) {
                if (!selectedStudentStatuses.value[student.id]) {
                    selectedStudentStatuses.value[student.id] = {
                        status_kehadiran: "Belum diabsen", // Status default
                    };
                }
            } else {
                console.warn(`ID siswa tidak valid: ${student.id}`);
            }
        });

        console.log(
            "Selected Student Statuses setelah inisialisasi:",
            JSON.stringify(selectedStudentStatuses.value, null, 2)
        );

        // Cek data absensi dan update status absensi untuk setiap siswa
        if (attendances.value && attendances.value.length > 0) {
            attendances.value.forEach((attendance) => {
                const studentId = attendance.student_id;
                const status = attendance.status_kehadiran || "Belum diabsen"; // Default status
                const attendanceDate = attendance.tanggal_kehadiran; // Pastikan tanggal_kehadiran ada

                // Cek apakah studentId sudah ada di selectedStudentStatuses
                if (!selectedStudentStatuses.value[studentId]) {
                    selectedStudentStatuses.value[studentId] = {};
                }

                // Set status kehadiran untuk tanggal yang relevan
                selectedStudentStatuses.value[studentId][attendanceDate] =
                    status;
            });
        } else {
            console.warn("Data absensi kosong, status default diterapkan.");
        }

        console.log(
            "Selected Student Statuses setelah update:",
            selectedStudentStatuses.value
        );
    } catch (error) {
        console.error("Error saat memuat data awal:", error);
    } finally {
        loading.value = false; // Menandakan bahwa loading selesai
    }
});
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
                                Tambah Absensi
                            </button>
                        </div>
                    </div>

                    <div class="g-responsive overflow-x-auto max-w-full">
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
                                        @click="
                                            handleStatusChange(student.id, date)
                                        "
                                        :class="
                                            getAttendanceClass(student.id, date)
                                        "
                                    >
                                        <!--                   <span>{{
                                            getAttendanceStatus(
                                                student.id,
                                                date
                                            )
                                        }}</span>-->
                                        <span>
                                            {{
                                                getAttendanceStatusOnClick(
                                                    student.id,
                                                    date
                                                )
                                            }}
                                            <!-- Menampilkan status -->
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

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

                        <div class="inline-flex mt-2 xs:mt-0">
                            <!-- Tombol Previous -->
                            <button
                                @click="
                                    handlePageChange(
                                        pagination.current_page - 1
                                    )
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
                                    handlePageChange(
                                        pagination.current_page + 1
                                    )
                                "
                                :disabled="
                                    pagination.current_page >=
                                    pagination.last_page
                                "
                                class="flex items-center justify-center px-4 h-10 text-base font-medium text-white bg-blue-500 border-0 border-s border-gray-700 rounded-e hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                            >
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

                    <div class="row mt-3 me-3">
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
                    <!-- Tampilkan "Loading..." jika sedang memuat data -->
                    <div v-if="loading">
                        <p>Loading data siswa...</p>
                    </div>
                    <div
                        v-else-if="
                            paginatedStudents && paginatedStudents.length > 0
                        "
                    >
                        <!-- Tabel siswa -->
                    </div>
                    <div v-else>
                        <p>Data siswa tidak ditemukan.</p>
                    </div>

                    <!-- Jika tidak ada data siswa, tampilkan pesan -->
                    <div v-else>No students found.</div>
                    <p>Tanggal Kehadiran: {{ tanggal_kehadiran }}</p>

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
                        v-if="
                            isAddModalVisible &&
                            Array.isArray(paginatedStudents) &&
                            paginatedStudents.length > 0
                        "
                        class="modal fade show"
                        tabindex="-1"
                    >
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title text-3xl">
                                        Tambah Absensi
                                    </h5>
                                    <button
                                        type="button"
                                        class="btn-close"
                                        @click="handleModalClose"
                                        aria-label="Close"
                                    ></button>
                                </div>
                                <div class="modal-body">
                                    <form @submit.prevent="submitAttendance">
                                        <div>
                                            <label for="tanggal-kehadiran"
                                                >Pilih Tanggal</label
                                            >
                                            <input
                                                id="tanggal-kehadiran"
                                                type="date"
                                                class="form-control"
                                                v-model="tanggal_kehadiran"
                                                :min="minDate"
                                                :max="maxDate"
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
                                                            ].status_kehadiran
                                                        "
                                                        class="form-select"
                                                        :class="{
                                                            'is-invalid':
                                                                !selectedStudentStatuses[
                                                                    student.id
                                                                ]
                                                                    .status_kehadiran ||
                                                                !tanggal_kehadiran,
                                                        }"
                                                        required
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
                                        <!-- @click="isAlertVisible"-->
                                        <button
                                            type="submit"
                                            class="btn btn-primary w-100"
                                        >
                                            Simpan Absensi
                                        </button>

                                        <!-- Alert notifikasi -->
                                        <div
                                            v-if="isAlertVisible"
                                            class="alert alert-success"
                                        >
                                            {{ alertMessage }}
                                            <!-- Menampilkan pesan alert -->
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <!--
                        <ul>
                <li v-for="student in studentId" :key="student.id">
                    {{ student.name }} - {{ student.no_induk_id }}
                </li>
            </ul>

            <div v-if="studentId.length === 0">Data siswa tidak ditemukan</div>
            <ul v-else>
                <li v-for="student in studentId" :key="student.id">
                    {{ student.name }} - {{ student.no_induk_id }}
                </li>
            </ul>
            -->
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
