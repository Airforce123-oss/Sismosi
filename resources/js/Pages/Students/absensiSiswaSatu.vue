<script setup>
import { onMounted, ref, watch, watchEffect, computed, nextTick } from "vue";
import axios from "axios";
import { Link, Head, useForm, usePage, router } from "@inertiajs/vue3";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";

// Reactive Variables
let page = 1;
const newAttendance = ref([]);
const data = Object.values(newAttendance.value);
const selectedStudentStatuses = ref({});
const currentMonthYear = ref("");
const getCurrentMonthYear = () => {
    const today = new Date();
    return today.toLocaleDateString("id-ID", {
        month: "long",
        year: "numeric",
    });
};
const loading = ref(true);
const pageNumber = ref(1);
const studentId = ref([]);
const newStatus = ref("");
const newData = ref([]); // Hanya mendeklarasikan newData sekali di luar
const tanggal_kehadiran = ref("");
const pageChanged = ref(false);
const updateDate = (event) => {
    date.value = new Date(event.target.value); // Memperbarui objek Date
    console.log("Updated date:", date.value);
};

const isNavigating = ref(false);
const isSelectVisible = ref(false);
const selectedDate = ref(null);
const nextMonthYear = ref("");
const nextNextMonthYear = ref("");
const isAddModalVisible = ref(false);
function toggleModalSave() {
    console.log("toggleModalSave dipanggil");
    isAddModalVisible.value = !isAddModalVisible.value;
}

// Menampilkan modal
const showAddModal = () => {
    isAddModalVisible.value = true;
};

/*
    const updateAttendanceStatus = (studentIds, date, newStatus, newData, attendance) => {
    // Pastikan studentIds adalah array
    if (!Array.isArray(studentIds) || studentIds.length === 0) {
        console.error("studentIds bukan array atau kosong:", studentIds);
        return;
    }
    studentIds.forEach((studentId) => {
        if (!selectedStudentStatuses.value[studentId]) {
            selectedStudentStatuses.value[studentId] = {}; // Inisialisasi jika belum ada
        }
        selectedStudentStatuses.value[studentId][date] = newStatus;
    });
    console.log("Updated Attendance:", selectedStudentStatuses.value);
};

 */

// Fungsi updateAttendanceStatus (seharusnya menerima data absensi untuk update)
const statusChanged = ref(false);

function updateAttendanceStatus(studentId, date, newStatus, attendance) {
    const status = newStatus.value || newStatus; // Ambil nilai reaktif atau langsung objek
    attendance.status_kehadiran = status;
    //attendance.tanggal_kehadiran = new Date(); // Gunakan tanggal saat ini
    attendance.tanggal_kehadiran = date.value || new Date();
    // Logika untuk memperbarui absensi, bisa menyimpan ke server atau memodifikasi objek

    if (!attendance) {
        console.error(`Absensi tidak ditemukan untuk siswa ID: ${studentId}`);
        return; // Hentikan jika tidak ada data absensi
    }

    console.log("Updated Attendancesssss:", {
        studentId,
        //newStatus,
        newStatus: newStatus.value,
        date: date.value,
        attendance,
    });

    console.log("isi selectedStudentStatuses :", selectedStudentStatuses.value);

    // Kirim data ke API atau simpan sesuai kebutuhan
    // Contoh: axios.put('/api/attendance/update', attendance);
}

function updateStatusesFromServer(attendanceData) {
    // Loop melalui setiap data kehadiran dari server
    attendanceData.forEach((attendance) => {
        // Perbarui status siswa berdasarkan student_id
        const studentId = attendance.student_id;
        const status = attendance.status_kehadiran;

        // Jika ada siswa di selectedStudentStatuses yang ID-nya sama dengan studentId
        if (selectedStudentStatuses.value[studentId]) {
            selectedStudentStatuses.value[studentId] = status;
        }
    });
}

async function processAttendanceUpdates() {
    // Pastikan selectedStudentStatuses ada dan valid
    if (
        !selectedStudentStatuses.value ||
        Object.keys(selectedStudentStatuses.value).length === 0
    ) {
        console.error("selectedStudentStatuses masih kosong!");
        console.log(
            "selectedStudentStatuses.value:",
            selectedStudentStatuses.value
        ); // Log untuk debug
        return;
    }

    // Iterasi melalui setiap siswa dalam selectedStudentStatuses
    for (const studentId in selectedStudentStatuses.value) {
        const newStatus = selectedStudentStatuses.value[studentId];
        const date = new Date(); // Bisa menggunakan tanggal format lain sesuai kebutuhan

        // Validasi data
        if (!studentId || !newStatus) {
            console.error("Data tidak valid untuk siswa ID:", studentId);
            continue; // Lanjutkan ke siswa berikutnya
        }

        console.log("Processing Attendance Update:", {
            studentId,
            date,
            newStatus,
        });

        console.log("Data absensi:", newData.value);

        // Pastikan newData valid sebelum diproses
        if (!newData.value || newData.value.length === 0) {
            console.error("No attendance data found for update.");
            continue;
        }
        console.log("Mencari absensi untuk siswa dengan ID:", studentId);
        console.log(
            "Isi Data Absensi:",
            JSON.stringify(newData.value, null, 2)
        );

        // Cari absensi terkait siswa
        //const attendance = attendances.value.find(
        //  (item) => item.student_id === studentId
        //);

        const attendance = attendances.value.find(
            (item) => item.student_id === Number(studentId)
        );

        if (!attendance) {
            console.error(
                `Absensi tidak ditemukan untuk siswa ID: ${studentId}`
            );
            continue; // Lanjutkan ke siswa berikutnya
        }

        console.log("Data attendances:", attendances.value);

        console.log(
            "Isi selectedStudentStatuses sebelum mapping:",
            selectedStudentStatuses.value
        );

        // Panggil updateAttendanceStatus dengan data yang valid
        updateAttendanceStatus(studentId, date, newStatus, attendance);

        // Proses pembaruan absensi untuk setiap ID siswa yang dipilih
        Object.keys(selectedStudentStatuses.value).forEach((id) => {
            console.log("Processing student ID:", id);
            // Lakukan update absensi untuk setiap ID siswa di sini
            // Misalnya: updateAttendanceStatus(id, newStatus, newData);
        });
    }
}

const fetchAttendances = async () => {
    try {
        const response = await axios.get("/api/attendances", {});
        console.log("Fetched attendances:", response.data);
        console.log("Response Data:", response.data);

        newAttendance.value = Array.isArray(response.data.attendances)
            ? response.data.attendances
            : Object.values(response.data.attendances); // Convert object to array
        console.log("New Attendance Array:", newAttendance.value);

        newAttendance.value.forEach((attendance, index) => {
            console.log(`Attendance ${index}:`, attendance);
        });
    } catch (error) {
        console.error("Error fetching attendances:", error);
    }
};

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

//const formattedDate = new Date().toISOString();
/*
// Memastikan tanggal ada dan benar
if (!formattedDate) {
    console.error("Tanggal kehadiran tidak valid.");
    return;
}
*/

//const formattedDate = computed(() => date.value.toISOString().split("T")[0]);
//const formattedDate = date.value.toISOString().split("T")[0]; // Format ke YYYY-MM-DD

// Menutup modal
const hideAddModal = () => {
    isAddModalVisible.value = false;
};

const loadStudents = async () => {
    try {
        const response = await axios.get("/api/students");

        if (
            response.data &&
            response.data.data &&
            response.data.data.length > 0
        ) {
            // Menyimpan objek lengkap siswa (id, name, dll) ke dalam studentId.value
            studentId.value = response.data.data.map((student) => ({
                id: student.id,
                name: student.name, // Menyimpan nama siswa
                // Anda bisa menambahkan informasi lain yang diperlukan
            }));

            console.log("Siswa yang terload:", studentId.value);

            // Menyimpan informasi pagination
            pagination.value = {
                currentPage: response.data.current_page,
                totalPages: response.data.last_page,
            };
        } else {
            console.error("Data siswa kosong atau gagal dimuat.");
        }
    } catch (error) {
        console.error("Error loading students:", error);
    }
};

const loadNextPage = async (nextPageUrl) => {
    try {
        const response = await axios.get(nextPageUrl);

        if (
            response.data &&
            response.data.data &&
            response.data.data.length > 0
        ) {
            // Mengambil ID siswa saja dalam bentuk angka
            const nextPageIds = response.data.data.map((student) => student.id);

            // Menggabungkan ID siswa dari halaman berikutnya dengan ID siswa yang sudah ada
            studentId.value = [
                ...studentId.value.filter((id) => typeof id === "number"), // Pastikan hanya ID yang valid (angka)
                ...nextPageIds, // Data dari halaman berikutnya
            ];

            console.log("Siswa setelah memuat halaman berikutnya:", [
                ...studentId.value,
            ]);

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

// Menyusun data absensi hanya untuk siswa yang ada pada halaman ini

const attendancesData = Object.entries(selectedStudentStatuses.value)
    .map(([studentId, status, formattedDate]) => {
        return {
            student_id: parseInt(studentId, 10),
            status_kehadiran: status,
            tanggal_kehadiran: formattedDate,
        };
    })
    .filter(Boolean);

/*
            tanggal_kehadiran: date.value
                ? date.value.toISOString().split("T")[0]
                : "", // Format tanggal
            */
// Submit Attendance Form
const submitAttendance = async () => {
    try {
        const isValid = paginatedStudents.value.every((student) => {
            const studentStatus =
                selectedStudentStatuses.value[String(student.id)];
            return studentStatus !== "" && studentStatus !== undefined;
        });

        // Jika ada siswa yang belum dipilih status absensinya, hentikan proses
        if (!isValid) {
            console.warn(
                "Pastikan semua status absensi dipilih untuk setiap siswa pada halaman ini."
            );
            return;
        }

        // Validasi tanggal kehadiran
        if (!tanggal_kehadiran.value) {
            console.warn("Tanggal kehadiran belum dipilih.");
            return;
        }

        // Format tanggal ke format yang sesuai (YYYY-MM-DD)
        const formattedDate = new Date(tanggal_kehadiran.value)
            .toISOString()
            .split("T")[0];

        const attendancesData = Object.entries(selectedStudentStatuses.value)
            .map(([studentId, status]) => {
                return {
                    student_id: parseInt(studentId, 10),
                    status_kehadiran: status,
                    tanggal_kehadiran: formattedDate, // Menggunakan formattedDate yang sudah didefinisikan
                };
            })
            .filter(Boolean);

        // Jika data absensi tidak valid, hentikan proses
        if (attendancesData.length === 0) {
            console.warn("Data absensi tidak valid.");
            alert("Data absensi tidak valid.");
            return;
        }

        // Kirim data absensi ke server

        console.log("Mengirim data ke server...");
        const response = await axios.post("/api/attendance3", {
            tanggal_kehadiran: tanggal_kehadiran.value,
            attendances: attendancesData.map((attendance) => ({
                student_id: attendance.student_id, // pastikan ada ID siswa
                status_kehadiran: attendance.status_kehadiran || "", // pastikan status kehadiran ada
            })),
        });

        // Menampilkan respons dari server

        console.log("Response dari server:", response.data);
        // Update status kehadiran berdasarkan respons server
        updateStatusesFromServer(response.data.attendances);

        alert("Data berhasil disimpan.");

        resetAttendanceForm();

        // Sembunyikan modal setelah berhasil menyimpan data
        //hideAddModal(); // Menyembunyikan modal
        isAddModalVisible.value = false; // Menutup modal setelah sukses
    } catch (error) {
        console.error("Error saat submit absensi:", error.response?.data);
        alert("Terjadi kesalahan saat menyimpan data.");
    }
};

// Fungsi untuk mereset form input absensi setelah data berhasil disimpan
const resetAttendanceForm = () => {
    // Reset tanggal kehadiran
    tanggal_kehadiran.value = null; // Atur ini ke null atau sesuai dengan kebutuhan

    // Reset status absensi siswa
    selectedStudentStatuses.value = {}; // Kosongkan status siswa
};

console.log("Attending data:", attendancesData);

const getStatus = (studentId, date) => {
    const attendance = newAttendance.value.find(
        (item) => item.student_id === studentId.value && item.tanggal === date
    );
    return attendance ? attendance.status : ""; // Tampilkan status jika ada, kosong jika tidak
};

// Ambil tanggal pertama dari array
//const selectedStudentId = ref(null);
const attendances = computed(() =>
    Object.entries(selectedStudentStatuses.value).map(
        ([studentId, status]) => ({
            student_id: parseInt(studentId, 10),
            status_kehadiran: status,
        })
    )
);

const paginatedStudents = computed(() => {
    return studentId.value.slice(0, 5); // Ambil 10 siswa pertama
});

//const date = totalDaysInMonth.value[0];
const today = new Date();
const currentYear = new Date().getFullYear();
const currentMonth = new Date().getMonth();
const totalDaysInMonth = Array.from(
    { length: new Date(currentYear, currentMonth + 1, 0).getDate() },
    (_, i) => i + 1
);
console.log(totalDaysInMonth);

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

// Contoh penggunaan:
console.log(getFormattedDate(0)); // Hari ini
console.log(getFormattedDate(1)); // Besok
console.log(getFormattedDate(2)); // Lusa

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
    selectedStudentStatuses.value = {}; // Reset status absensi untuk halaman ini
    attendances.value.forEach((attendance) => {
        selectedStudentStatuses.value[attendance.student_id] =
            attendance.status_kehadiran;
    });
};

const fetchStudents = async (page = 1) => {
    isNavigating.value = true; // Menandai sedang berpindah halaman
    loading.value = true;
    try {
        let token = localStorage.getItem("auth_token");

        // Jika token tidak ada, buat token baru melalui API
        if (!token) {
            // const response = await axios.post("/api/auth/refresh-token");

            if (response.data && response.data.token) {
                token = response.data.token;
                localStorage.setItem("auth_token", token);
                console.log("Token yang baru dibuat:", token);
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
                    Authorization: `Bearer ${token}`,
                },
            }
        );

        // Verifikasi apakah data yang diterima adalah array
        if (
            Array.isArray(response.data.data) &&
            response.data.data.length > 0
        ) {
            studentId.value = response.data.data; // Pastikan format ini benar
            paginatedStudents.value = response.data.data;
            selectedStudentStatuses.value = {}; // Reset status absensi
            mapStudentStatuses(); // Panggil fungsi untuk memetakan status
        } else {
            console.error(
                "Data siswa tidak ditemukan atau kosong:",
                response.data
            );
            selectedStudentStatuses.value = {}; // Kosongkan status jika data tidak valid
        }

        console.log("Response Data Siswa:", response.data);
        if (response.data && response.data.data) {
            console.log("Data siswa:", response.data.data); // Log data siswa yang diterima
            studentId.value = response.data.data; // Simpan seluruh data siswa

            pagination.value = {
                current_page: response.data.current_page,
                last_page: response.data.last_page,
                total: response.data.total,
                per_page: pagination.value.per_page,
            };

            paginatedStudents.value = response.data.students || [];

            // Reset selectedStudentStatuses saat berpindah halaman
            selectedStudentStatuses.value = {}; // Reset status absensi saat berpindah halaman

            // Memanggil mapStudentStatuses untuk mengisi status absensi dari attendances
            mapStudentStatuses(); // Setelah reset, map kembali status absensi sesuai data siswa yang baru
        } else {
            console.error("Data siswa tidak ditemukan", response.data);
        }
    } catch (error) {
        console.error("Error saat mengirim data absensi:", error);

        if (error.response) {
            console.error("Error response:", error.response.data);
            console.error("Error status:", error.response.status);
        } else if (error.request) {
            console.error("No response received:", error.request);
        } else {
            console.error("Kesalahan lain:", error.message);
        }
    } finally {
        isNavigating.value = false; // Menandai selesai berpindah halaman
    }
};

console.log("Data siswa sebelum pemeriksaan:", studentId.value);

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
            /*
            console.error("Data siswa tidak ditemukan:", studentId.value);
            */
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

        console.log("Fetched Data:", response.data); // Debugging

        if (response.data && response.data.attendances) {
            newData.value = Array.isArray(response.data.attendances)
                ? response.data.attendances
                : Object.values(response.data.attendances); // Konversi objek menjadi array
            console.log("Fetched newData:", newData.value);
            console.log("Response Status:", response.status); // Menampilkan status respons

            // Pastikan data valid sebelum dioper ke updateAttendanceStatus
            const validNewStatus = newStatus.value || ""; // Gunakan nilai default jika newStatus belum terdefinisi

            // Pastikan date valid
            if (
                !date.value ||
                !(date.value instanceof Date) ||
                isNaN(new Date(date.value).getTime())
            ) {
                console.error("Tanggal tidak valid:", date.value);
                return;
            }

            // Panggil fungsi updateAttendanceStatus setelah validasi
            updateAttendanceStatus(
                studentIds,
                date.value,
                validNewStatus,
                newData.value
            );
        } else {
            console.error("Data absensi tidak ditemukan:", response.data);
        }
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

const updateAttendance = async (newData) => {
    try {
        const token = localStorage.getItem("auth_token");
        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute("content");

        // Validasi newData sebelum dikirimkan ke API
        if (!newData || !newData.tanggal_kehadiran) {
            //console.error("Data tidak lengkap:", newData);
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

//const date = new Date();
const date = ref(new Date());
console.log("date:", date.value);
console.log("Calling updateAttendanceStatus with:");
console.log("date:", date);
console.log("newStatus:", newAttendance.value);
console.log("studentId:", studentId.value);
console.log("newStatus:", newAttendance.value);
console.log("newData:", newData.value);

const updateSelectedStatuses = (newAttendance) => {
    console.log("Updating statuses with newData:", newAttendance);

    if (!newAttendance || newAttendance.value.length === 0) {
        /*
           console.error(
            "Data absensi tidak ditemukan atau kosong:",
            newAttendance
        );
         */
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

const getAttendanceStatus = (studentId, date) => {
    // Inisialisasi status untuk siswa jika belum ada
    if (!selectedStudentStatuses.value[studentId]) {
        selectedStudentStatuses.value[studentId] = {}; // Inisialisasi jika belum ada
    }

    // Cek apakah status untuk tanggal tertentu sudah ada
    const studentStatuses = selectedStudentStatuses.value[studentId];

    if (studentStatuses && studentStatuses[date]) {
        console.log("Status ditemukan:", studentStatuses[date]);
        return studentStatuses[date]; // Kembalikan status jika sudah ada
    }

    // Jika tidak ada di selectedStudentStatuses, cek apakah ada di selectedDate
    if (
        selectedDate &&
        selectedDate[studentId] &&
        selectedDate[studentId][date]
    ) {
        console.log(
            "Status ditemukan di selectedDate:",
            selectedDate[studentId][date]
        );
        return selectedDate[studentId][date]; // Kembalikan status dari selectedDate
    }

    // Jika tidak ada data, kembalikan "Belum diabsen"
    console.log("Belum diabsen");
    return "Belum diabsen"; // Default jika belum ada status
};

console.log("Nilai isAddModalVisible:", isAddModalVisible.value);

/*
         console.log(
        "Checking attendance for student:",
        studentId,
        "on date:",
        date
    );
     */

const getAttendanceClass = (studentId, tanggal_kehadiran) => {
    if (!tanggal_kehadiran.value) {
        return "bg-light text-dark"; // Jika tidak ada tanggal yang dipilih, tampilkan default
    }
    updateAttendance;
    // Jika tanggal valid (hari Senin - Jumat), cek status kehadiran
    //const status = getAttendanceStatus(studentId, tanggal_kehadiran);

    // Kelas berdasarkan status kehadiran
    switch (newStatus) {
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
        // 1. Ambil data siswa terlebih dahulu
        await fetchStudents(); // Ambil data siswa
        console.log("Data Siswa:", studentId.value);

        // Pastikan studentId.value sudah terisi data yang valid
        if (!studentId.value || studentId.value.length === 0) {
            console.error("Data siswa tidak ditemukan:", studentId.value);
            return;
        }

        // 2. Ekstrak ID siswa setelah fetchStudents
        const studentIds = studentId.value.map((student) => student.id);
        if (!studentIds.length) {
            console.error("Data siswa tidak ditemukan:", studentIds);
            return;
        }
        console.log("Student IDs:", studentIds);

        // 3. Panggil fetchData dengan studentIds
        await fetchData(studentIds);

        // 4. Ambil data absensi setelah siswa tersedia
        await fetchAttendances();
        console.log("Data Kehadiran:", attendances.value);

        if (!attendances.value || attendances.value.length === 0) {
            console.error(
                "Data absensi tidak ditemukan atau kosong:",
                attendances.value
            );
            return;
        }

        // 5. Proses status kehadiran untuk setiap siswa
        if (attendances.value && attendances.value.length > 0) {
            attendances.value.forEach((attendance) => {
                if (!(attendance.student_id in selectedStudentStatuses.value)) {
                    selectedStudentStatuses.value[attendance.student_id] =
                        attendance.status_kehadiran || "P"; // Default status
                }
            });
        }

        // 6. Verifikasi apakah selectedStudentStatuses sudah terupdate
        if (Object.keys(selectedStudentStatuses.value).length === 0) {
            console.error(
                "selectedStudentStatuses masih kosong setelah di-update!"
            );
            return;
        }

        // 7. Pastikan render selesai sebelum melanjutkan
        nextTick(() => {
            console.log(
                "selectedStudentStatuses setelah render:",
                selectedStudentStatuses.value
            );

            if (Object.keys(selectedStudentStatuses.value).length === 0) {
                console.error(
                    "selectedStudentStatuses masih kosong setelah render!"
                );
                return;
            }

            // 8. Panggil proses update jika data sudah ada
            processAttendanceUpdates();
        });

        // 9. Set tanggal kehadiran
        const today = new Date();
        tanggal_kehadiran.value = today.toISOString().split("T")[0];

        // 10. Perbarui data absensi
        await refreshAttendanceData();
        console.log(
            "Initialized selectedStudentStatuses:",
            selectedStudentStatuses.value
        );

        // 11. Proses pembaruan lainnya
        updateAttendanceStatus(
            studentIds,
            tanggal_kehadiran.value,
            newStatus,
            newData
        );
        updateSelectedStatuses();
        currentMonthYear.value = getCurrentMonthYear();
    } catch (error) {
        console.error("Error saat memuat data awal:", error);
    }

    // Inisialisasi CSRF Token dan data lainnya
    console.log("Token CSRF:", csrfToken);
    console.log("Data siswa setelah fetchStudents:", studentId.value);

    // Pastikan data sudah ada sebelum memanggil updateAttendanceStatus
    if (!attendances.value || !Array.isArray(attendances.value)) {
        console.error("attendances.value tidak valid:", attendances.value);
        return;
    }
    if (!newAttendance.value) {
        console.error("newAttendance.value tidak terdefinisi");
        return;
    }
});
// Update selected statuses (gunakan hasil fetchAttendances)
//updateSelectedStatuses(newData); // Pastikan response.data.attendances adalah array yang valid

/*

// Fungsi untuk menghandle perubahan status kehadiran
const handleAttendanceChange = async (studentId, date, newAttendance) => {
    // Validasi data
    if (!studentId || !date || !newAttendance.value) {
        console.error("Data tidak valid:", { studentId, date, newStatus });
        return;
    }
    // Memastikan newAttendance memiliki struktur yang diharapkan
    if (!newAttendance.value) {
        console.error(
            "newAttendance belum diinisialisasi:",
            newAttendance.value
        );
        return;
    }

    if (!newAttendance.value[studentId]) {
        newAttendance.value[studentId] = {};
    }

    if (!newAttendance.value || newAttendance.value.length === 0) {
        console.error(
            "Data absensi tidak ditemukan atau kosong:",
            newAttendance.value
        );
        return;
    }

    // Menambahkan atau memperbarui status absensi untuk siswa
    newAttendance.value[studentId][date] = newStatus;
    console.log("Updated newAttendance:", newAttendance.value);
};
 */

//isSelectVisible.value = false; // Sembunyikan dropdown setelah perubahan
//selectedDate.value = null; // Reset tanggal yang dipilih
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
                                    >
                                        <span>{{
                                            selectedStudentStatuses[
                                                String(student.id)
                                            ] || "Belum diabsen"
                                        }}</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!--


                    <div>
                        <button @click="loadStudents">Load Students</button>
                        <div
                            v-for="student in paginatedStudents"
                            :key="student"
                        >
                            {{ student.name }}
                        </div>
                    </div>

                                       {{
                                            getAttendanceStatus(
                                                student.id,
                                                date
                                            )
                                        }}

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
                    <div v-if="loading">Loading...</div>

                    <!-- Jika selectedStudentStatuses sudah terisi data, tampilkan bagian terkait -->
                    <div
                        v-else-if="
                            selectedStudentStatuses &&
                            Object.keys(selectedStudentStatuses).length > 0
                        "
                    >
                        <!-- Kode yang menggunakan selectedStudentStatuses -->
                        <!-- Misalnya tampilkan daftar status siswa -->
                    </div>

                    <!-- Jika tidak ada data siswa, tampilkan pesan -->
                    <div v-else>No students found.</div>

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
                                                                    String(
                                                                        student.id
                                                                    )
                                                                ] ||
                                                                !tanggal_kehadiran,
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
