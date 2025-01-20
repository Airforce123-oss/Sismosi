<script setup>
import { Link, Head, useForm, usePage, router } from "@inertiajs/vue3";
import {
    ref,
    watch,
    computed,
    onMounted,
    toRaw,
    nextTick,
    reactive,
} from "vue";
import axios from "axios";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";

const selectedClassId = ref(null);
const isModalVisible = ref(false);
const showAbsensiModal = (teacherId, date) => {
    // Tambahkan logic untuk membuka modal sesuai dengan teacherId dan date
    selectedTeacherId.value = teacherId;
    selectedDate.value = date;
    isModalVisible.value = true;
};
const selectedTeacherId = ref(null);
const selectedDate = ref(null);
const statuses = ref(["P", "A", "S", "I"]);
const customStatus = ref("");
//const customStatus = "";

const isCustomStatus = computed(
    () => !statuses.value.includes(customStatus.value)
);

const { props } = usePage();

defineProps({
    attendanceRecords: {
        type: Array,
        default: () => [],
    },
});

/*
const attendanceRecords = computed(() => {
    try {
        const data = localStorage.getItem("attendanceRecords");
        if (data) {
            const parsedData = JSON.parse(data);
            return parsedData
                .map((record) => {
                    if (
                        record.teacher_id &&
                        record.attendance_date &&
                        record.status
                    ) {
                        return record;
                    } else {
                        console.warn("Invalid record found, skipping:", record);
                        return null;
                    }
                })
                .filter((record) => record !== null); // Filter out invalid records
        }
        return [];
    } catch (error) {
        console.error(
            "Error parsing attendanceRecords from localStorage",
            error
        );
        return [];
    }
});
*/

const attendanceRecords = ref([]); // Mengonversi ke ref
try {
    const data = localStorage.getItem("attendanceRecords");
    if (data) {
        const parsedData = JSON.parse(data);
        attendanceRecords.value = parsedData
            .map((record) => {
                if (
                    record.teacher_id &&
                    record.attendance_date &&
                    record.status
                ) {
                    return record;
                } else {
                    console.warn("Invalid record found, skipping:", record);
                    return null;
                }
            })
            .filter((record) => record !== null); // Filter out invalid records
    }
} catch (error) {
    console.error("Error parsing attendanceRecords from localStorage", error);
}

const filteredStatuses = computed(() => {
    if (!Array.isArray(statuses.value)) {
        console.warn("Statuses is not an array:", statuses.value);
        return [];
    }
    return statuses.value.filter((status) => status !== "Belum diabsen");
});
console.log("Filtered Statuses:", filteredStatuses.value);

const processedRecords = computed(() => {
    if (!filteredStatuses || !filteredStatuses.value) {
        console.warn("filteredStatuses belum siap!");
        return [];
    }

    return attendanceRecords.value.map((record) => {
        if (filteredStatuses.value.includes(record.status)) {
            return {
                teacher_id: record.teacher_id,
                attendance_date: record.attendance_date,
                status: record.status,
            };
        } else {
            return {
                teacher_id: record.teacher_id,
                attendance_date: record.attendance_date,
                status: "Unknown",
            };
        }
    });
});

watch(
    processedRecords,
    (newRecords) => {
        localStorage.setItem("attendanceRecords", JSON.stringify(newRecords));
    },
    { deep: true }
);

const { teachers } = usePage().props;
if (!teachers) {
    console.error("Teacher data is missing in props.");
} else if (Array.isArray(teachers)) {
    const rawTeachers = teachers; // Jangan reactive
    if (rawTeachers.length > 0) {
        console.log("Teacher found:", rawTeachers);
        rawTeachers.forEach((teacher) => {
            if (teacher && teacher.id !== undefined && !isNaN(teacher.id)) {
                console.log(`Teacher ID: ${teacher.id}, Name: ${teacher.name}`);
            } else {
                console.warn("Invalid teacher.id:", teacher.id);
            }
        });
    } else {
        console.error("Teacher data is empty.");
    }
} else {
    console.error("Teacher data is not an array:", teachers);
}

// Computed property untuk memfilter atau memproses data absensi
const attendanceSummary = computed(() => {
    if (props.attendance && props.attendance.length > 0) {
        console.log("Attendance data is available.");
        return props.attendance.map((record) => ({
            teacherId: record.teacher_id,
            date: record.attendance_date,
            status: record.status,
        }));
    } else {
        //console.warn("Attendance data is not yet available.");
        return [];
    }
});

const emit = defineEmits(["update:attendance"]);

const updateAttendance = (attendance) => {
    console.log("Updating attendance records:", attendance);

    // Emit perubahan ke parent (opsional)
    emit("update:attendance", attendance);
};

const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 5,
    total: 0,
});

const handlePageChange = (newPage) => {
    if (newPage >= 1 && newPage <= pagination.value.last_page) {
        pagination.value.current_page = newPage; // Perbarui halaman aktif
        fetchPageData(newPage); // Panggil data sesuai halaman
    }
};

// Fungsi untuk mengambil data berdasarkan halaman
const fetchPageData = async (page) => {
    try {
        console.log(`Fetching data for page ${page}...`);
        // Simulasikan API atau sumber data lain
        const response = await mockFetchData(page, pagination.value.per_page);
        const { current_page, last_page, per_page, total } =
            response.pagination;

        // Perbarui data pagination
        pagination.value = {
            current_page,
            last_page,
            per_page,
            total,
        };

        console.log("Updated pagination:", pagination.value);
    } catch (error) {
        console.error("Error fetching page data:", error);
    }
};

const mockFetchData = async (page, perPage) => {
    return new Promise((resolve) => {
        setTimeout(() => {
            const totalItems = 50; // Total data
            const lastPage = Math.ceil(totalItems / perPage);
            const data = Array.from(
                { length: perPage },
                (_, i) => `Item ${(page - 1) * perPage + i + 1}`
            );

            resolve({
                data,
                pagination: {
                    current_page: page,
                    last_page: lastPage,
                    per_page: perPage,
                    total: totalItems,
                },
            });
        }, 500);
    });
};

watch(
    () => props.attendance,
    (newAttendance) => {
        if (Array.isArray(newAttendance)) {
            if (
                JSON.stringify(newAttendance) !==
                JSON.stringify(attendanceRecords.value)
            ) {
                attendanceRecords.value = newAttendance;
                console.log(
                    "Updated attendanceRecords from props:",
                    attendanceRecords.value
                );
            }
        } else {
            attendanceRecords.value = [];
        }
    },
    { immediate: true }
);

watch(
    () => props.attendanceRecords,
    (newRecords) => {
        // Validasi awal untuk memastikan props.attendanceRecords adalah array
        if (!Array.isArray(newRecords)) {
            console.error(
                "props.attendanceRecords is not an array or is undefined. Skipping watch handler."
            );
            return;
        }

        // Cek apakah data baru sama dengan data lokal
        if (
            JSON.stringify(newRecords) ===
            JSON.stringify(localAttendanceRecords.value)
        ) {
            console.log("No changes detected in attendanceRecords.");
            return;
        } else {
            if (
                JSON.stringify(localAttendanceRecords.value) ===
                JSON.stringify(newRecords)
            ) {
                console.log("No changes detected.");
            } else {
                console.log("Changes detected.");
            }
        }

        // Logika penambahan data baru
        const newAttendance = {
            teacher_id: 1,
            attendance_date: "2025-01-15",
        };

        const isDuplicate = newRecords.some(
            (record) =>
                record.teacher_id === newAttendance.teacher_id &&
                record.attendance_date === newAttendance.attendance_date
        );

        if (!isDuplicate) {
            localAttendanceRecords.value = [...newRecords, newAttendance];
            saveToLocalStorage(); // Simpan otomatis
            console.log(
                "Attendance records updated:",
                localAttendanceRecords.value
            );
        } else {
            console.log("Duplicate attendance detected. Skipping add.");
        }
    },
    { deep: true }
);

// Debug computed data
console.log("Computed Attendance Summary:", attendanceSummary.value);

/*
const attendanceRecords = [
    { teacherId: 1, date: "2025-01-06", status: "Hadir" },
    { teacherId: 2, date: "2025-01-06", status: "Alpa" },
];
*/

console.log("Attendance records from props:", toRaw(attendanceRecords.value));

const loadAttendanceRecords = async () => {
    if (attendanceRecords.value && attendanceRecords.value.length > 0) {
        console.log(
            "Attendance records are already loaded:",
            attendanceRecords.value
        );

        // Validasi dan inisialisasi props.attendanceRecords sebagai array reaktif
        props.attendanceRecords = props.attendanceRecords || reactive([]);

        // Pastikan semua status dalam props.attendance memiliki nilai yang valid
        props.attendance = props.attendance.map((record) => ({
            ...record,
            status: record.status || "Unknown", // Beri default jika kosong
        }));

        console.log(
            "Type of attendanceRecords:",
            typeof attendanceRecords.value
        );
        console.log("Attendance Records:", attendanceRecords.value);

        attendanceRecords.value.forEach((record) => {
            console.log(
                "attendanceRecords.find():",
                attendanceRecords.find(
                    (r) =>
                        formattedDate(new Date(r.attendance_date)) ===
                            formattedDate(
                                new Date(currentYear, currentMonth, date)
                            ) && r.teacher_id === record.teacher_id
                )
            );
            console.log("attendanceRecords:", attendanceRecords);

            try {
                const formatted = formattedDate(
                    new Date(record.attendance_date)
                );
                if (formatted) {
                    console.log("Record Date:", formatted);
                } else {
                    console.warn(
                        "Invalid Date Detected:",
                        record.attendance_date
                    );
                }
            } catch (error) {
                console.error("Error during formattedDate:", error);
            }

            console.log("Teacher ID:", record.teacher_id);
            console.log("Status:", record.status);
        });

        console.log("Final attendanceRecords:", props.attendanceRecords);
    } else {
        console.error("No attendance records found in props.");
        props.attendanceRecords = reactive([]);
    }
};

const handleTeacherStatusChange = async (teacherId, date) => {
    console.log("Menangani status perubahan untuk guru:", teacherId);
    const formattedDateValue = formattedDate(new Date(date));
    console.log("Formatted Date:", formattedDateValue);

    if (!props.attendanceRecords) {
        console.warn("Data absensi belum tersedia. Menunggu data...");
        console.log(
            "Props Attendance Records before loading:",
            props.attendanceRecords
        );
        await loadAttendanceRecords(); // Fungsi untuk memuat data absensi
        console.log(
            "Props Attendance Records after loading:",
            props.attendanceRecords
        );
        if (!props.attendanceRecords || props.attendanceRecords.length === 0) {
            console.error("attendanceRecords is empty or not loaded yet");
            alert("Data absensi tidak ditemukan. Silakan coba lagi.");
            return; // exit early jika tidak ada data
        }
    }

    console.log(
        `Mencari data untuk teacher_id: ${teacherId} dan attendance_date: ${formattedDateValue}`
    );

    console.log(
        "Inside handleTeacherStatusChange, Filtered Statuses:",
        filteredStatuses.value
    );

    // Filter dan update atau hapus rekaman "Belum diabsen"
    const recordToUpdate = props.attendanceRecords.find(
        (record) =>
            record.teacher_id === teacherId &&
            formattedDate(new Date(record.attendance_date)) ===
                formattedDateValue &&
            record.status === "Belum diabsen"
    );

    if (recordToUpdate) {
        recordToUpdate.status = customStatus.value;
        console.log("Updated existing attendance record:", recordToUpdate);
    } else {
        const existingRecordIndex = props.attendanceRecords.findIndex(
            (record) =>
                record.teacher_id === teacherId &&
                formattedDate(new Date(record.attendance_date)) ===
                    formattedDateValue
        );

        if (existingRecordIndex !== -1) {
            props.attendanceRecords[existingRecordIndex].status =
                customStatus.value;
            console.log(
                "Updated existing attendance record:",
                props.attendanceRecords[existingRecordIndex]
            );
        } else {
            const newRecord = {
                teacher_id: teacherId,
                attendance_date: formattedDateValue,
                status: customStatus.value,
            };
            props.attendanceRecords.push(newRecord);
            console.log("Added new attendance record:", newRecord);
        }
    }

    // Simpan data absensi terbaru ke penyimpanan lokal (optional)
    saveToLocalStorage();
    console.log("AttendanceRecords updated:", props.attendanceRecords);

    // Ambil data absensi berdasarkan teacherId dan formattedDate
    const attendanceData = props.attendanceRecords.find(
        (item) =>
            item.teacher_id === teacherId &&
            item.attendance_date === formattedDateValue
    );

    if (attendanceData) {
        const currentStatus = attendanceData.status;

        // Logika status tidak berubah pada "Belum diabsen"
        if (currentStatus && currentStatus !== "Belum diabsen") {
            handleStatus(currentStatus);
        }

        // Selalu buka modal jika data absensi ditemukan
        selectedTeacherId.value = teacherId;
        selectedDate.value = date;
        isModalVisible.value = true;
        customStatus.value = currentStatus || "";
    } else {
        console.error(
            `Data absensi tidak ditemukan untuk guru ID: ${teacherId} dan tanggal: ${formattedDateValue}`
        );
        alert("Data absensi tidak ditemukan. Silakan coba lagi.");
    }
};

/*
const selectStatus = (status) => {
    console.log(
        `Status selected: ${status}, for Teacher: ${selectedTeacherId.value}, Date: ${selectedDate.value}`
    );

    if (!Array.isArray(props.attendance)) {
        console.error("Attendance data is not an array or is undefined");
        return;
    }

    if (!selectedTeacherId.value || !selectedDate.value) {
        console.error(
            "Invalid teacher ID or date:",
            selectedTeacherId.value,
            selectedDate.value
        );
        return;
    }

    const attendanceRecord = props.attendance.find(
        (item) =>
            item.teacher_id === selectedTeacherId.value &&
            item.attendance_date === selectedDate.value
    );

    if (attendanceRecord) {
        attendanceRecord.status = status;
        console.log("Attendance updated:", attendanceRecord);
    } else {
        props.attendance.push({
            teacher_id: selectedTeacherId.value,
            attendance_date: selectedDate.value,
            status,
        });
        console.log("New attendance record added:", {
            teacher_id: selectedTeacherId.value,
            attendance_date: selectedDate.value,
            status,
        });
    }

    // Panggil updateAttendance untuk menyimpan perubahan
    updateAttendance(props.attendance);

    // Tutup modal dan reset status
    isModalVisible.value = false;
    customStatus.value = "";
};

*/
const closeModal = () => {
    isModalVisible.value = false;
    selectedTeacherId.value = null;
    selectedDate.value = null;
    customStatus.value = "";
};

const { classes, attendance } = usePage().props;

const selectedClass = classes[0];

const getButtonClass = (status) => {
    switch (status) {
        case "P":
            return "bg-info text-black fw-bold status-btn info-btn"; // Kelas untuk hadir
        case "A":
            return "bg-danger text-black fw-bold status-btn danger-btn"; // Kelas untuk alpa
        case "S":
            return "bg-warning text-black fw-bold status-btn warning-btn"; // Kelas untuk sakit
        case "I":
            return "bg-primary text-black fw-bold status-btn primary-btn"; // Kelas untuk izin
        default:
            return "bg-light text-dark status-btn light-btn"; // Default class
    }
};

// Ambil data dari props yang diterima dari Inertia
//const { teachers } = usePage().props; // Mengakses data teachers dari Inertia
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
const date = new Date();
// Ekstrak nilai tanggal (1-31) dari objek Date
const currentYear = new Date().getFullYear();
const currentMonth = new Date().getMonth() + 1;
console.log("currentMonth:", currentMonth);
new Date(currentYear, currentMonth - 1, date);
console.log("Pre-correction currentMonth:", currentMonth);

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

    // Ambil teacherId dan pastikan attendanceDate ada sebelum memanggil fetchAttendanceData
    const teacherId = 1;
    const classId = selectedClass ? selectedClass.id : 1; // Ambil ID dari selectedClass, atau nilai default 1 jika null

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

const attendanceMessage = ref("");

//const localAttendanceRecords = ref([]);
//const localAttendanceRecords = ref([...props.attendanceRecords]);
const localAttendanceRecords = ref(
    Array.isArray(props.attendanceRecords) ? [...props.attendanceRecords] : []
);
const rawLocalAttendanceRecords = toRaw(localAttendanceRecords.value); // Mengubah menjadi objek biasa
console.log("Raw Local Attendance Records:", rawLocalAttendanceRecords);

watch(
    localAttendanceRecords,
    (newRecords) => {
        saveToLocalStorage();
        console.log("Data saved to localStorage:", newRecords);
    },
    { deep: true }
);

const addAttendanceRecord = (newRecord) => {
    if (
        !localAttendanceRecords.value.some(
            (record) =>
                record.teacher_id === newRecord.teacher_id &&
                record.attendance_date === newRecord.attendance_date
        )
    ) {
        localAttendanceRecords.value.push(newRecord);
        saveToLocalStorage(); // Simpan data baru ke localStorage
    }
};

const handleAttendance = (status) => {
    console.log(
        `Status selected: ${status}, for Teacher: ${selectedTeacherId.value}, Date: ${selectedDate.value}`
    );

    if (!Array.isArray(props.attendance)) {
        console.error("Attendance data is not an array or is undefined");
        return;
    }

    if (!selectedTeacherId.value || !selectedDate.value) {
        console.error(
            "Invalid teacher ID or date:",
            selectedTeacherId.value,
            selectedDate.value
        );
        return;
    }

    // Hapus semua record dengan status "Unknown" di database utama
    props.attendance = props.attendance.filter(
        (record) => record.status !== "Unknown"
    );

    // Cari record yang cocok berdasarkan teacher_id dan attendance_date
    const attendanceRecord = props.attendance.find(
        (item) =>
            item.teacher_id === selectedTeacherId.value &&
            item.attendance_date === selectedDate.value
    );

    if (attendanceRecord) {
        // Jika record sudah ada, update status
        attendanceRecord.status = status;
        console.log("Attendance updated:", attendanceRecord);
    } else {
        // Jika record tidak ada, tambahkan ke array attendance
        props.attendance.push({
            teacher_id: selectedTeacherId.value,
            attendance_date: selectedDate.value,
            status,
        });
        console.log("New attendance record added:", {
            teacher_id: selectedTeacherId.value,
            attendance_date: selectedDate.value,
            status,
        });
    }

    // Sinkronisasi ke localAttendanceRecords (hapus "Unknown" juga)
    localAttendanceRecords.value = localAttendanceRecords.value.filter(
        (record) => record.status !== "Unknown"
    );

    const newRecord = {
        teacher_id: selectedTeacherId.value,
        attendance_date: selectedDate.value,
        status,
    };

    if (!newRecord.status) {
        newRecord.status = "Unknown";
    }

    const localAttendanceExists = localAttendanceRecords.value.some(
        (record) =>
            record.teacher_id === newRecord.teacher_id &&
            record.attendance_date === newRecord.attendance_date
    );

    if (!localAttendanceExists) {
        localAttendanceRecords.value.push(newRecord);
    }

    console.log(
        "Updated attendanceRecords in local ref:",
        localAttendanceRecords.value
    );

    // Panggil updateAttendance untuk menyimpan perubahan
    updateAttendance(props.attendance);

    // Simpan ke localStorage jika diperlukan
    saveToLocalStorage();

    // Log after saving to localStorage
    console.log("Data saved to localStorage:", localAttendanceRecords.value);

    // Tutup modal dan reset status
    isModalVisible.value = false;
    customStatus.value = "";
};

const displayAttendanceStatus = (date) => {
    if (!Array.isArray(attendanceRecords)) {
        console.warn("attendanceRecords is not an array.");
        return "Belum diabsen";
    }

    if (!toRaw(attendanceRecords)) {
        console.error("attendanceRecords data is missing in props.");
        return "Belum diabsen";
    } else if (Array.isArray(toRaw(attendanceRecords))) {
        console.log("Attendance records found:", toRaw(attendanceRecords));
    } else {
        console.error(
            "attendanceRecords data is not an array:",
            toRaw(attendanceRecords)
        );
    }

    const formattedDateValue = formattedDate(new Date(date));
    const attendanceRecord = attendanceRecords.find(
        (record) =>
            formattedDate(new Date(record.attendance_date)) ===
            formattedDateValue
    );

    if (!attendanceRecord) {
        console.log(
            `No attendance record found for date: ${formattedDateValue}`
        );
        return "Belum diabsen";
    }

    if (attendanceRecord) {
        return attendanceRecord.status;
    } else {
        return "Belum diabsen";
    }
};

// Ambil data saat komponen dimuat pertama kali
onMounted(() => {
    try {
        console.log("Component mounted, data loading process started.");

        // Load data awal dari localStorage
        loadFromLocalStorage();
        console.log(
            "Data loaded from localStorage:",
            toRaw(localAttendanceRecords.value)
        );

        // Validasi format data dan log
        if (Array.isArray(localAttendanceRecords.value)) {
            localAttendanceRecords.value.forEach((record, index) => {
                if (!record.status) {
                    console.warn(
                        `Record at index ${index} is missing status:`,
                        record
                    );
                }
            });
        } else {
            console.error(
                "localAttendanceRecords is not an array:",
                toRaw(localAttendanceRecords)
            );
        }

        console.log("Local Attendance Records:", toRaw(localAttendanceRecords));

        // Update props.attendanceRecords setelah data berhasil dimuat
        if (localAttendanceRecords.value.length > 0) {
            props.attendanceRecords = localAttendanceRecords.value;
        }

        // Validasi props.attendanceRecords
        if (!Array.isArray(props.attendanceRecords)) {
            console.warn(
                "Initializing props.attendanceRecords as an empty array because it is undefined or not an array."
            );
            props.attendanceRecords = [];
        }

        nextTick(() => {
            if (props.attendanceRecords.length > 0) {
                console.log(
                    "Attendance records are now available:",
                    props.attendanceRecords
                );
            } else {
                console.log("Attendance records are still empty.");
                attendanceMessage.value =
                    "Data absensi masih dalam proses pemuatan.";
            }
        });

        // Validasi dan log untuk wali_kelas
        const { wali_kelas } = usePage().props;
        if (!Array.isArray(wali_kelas) || wali_kelas.length === 0) {
            attendanceMessage.value =
                "Data wali kelas tidak ditemukan atau tidak valid.";
            return;
        }

        console.log("Validated wali_kelas data:", wali_kelas);

        // Tambahkan data baru ke attendanceRecords
        props.attendanceRecords = [
            ...props.attendanceRecords,
            ...attendanceRecords.filter(
                (record) =>
                    !props.attendanceRecords.some(
                        (item) =>
                            item.teacher_id === record.teacher_id &&
                            item.attendance_date === record.attendance_date
                    )
            ),
        ];

        console.log("Final attendanceRecords:", props.attendanceRecords);

        // Tambahkan record baru
        const newRecord = {
            teacher_id: 1,
            attendance_date: "2025-01-15",
            status: "Present",
        };
        addAttendanceRecord(newRecord);

        // Simpan data ke localStorage setelah diperbarui
        saveToLocalStorage();

        // Logika tambahan
        handleStatus();
        fetchPageData(1);
        fetchAttendanceData(currentPage.value);

        // Debugging tanggal
        console.log("Formatted Date Function Test:", formattedDate(new Date()));
    } catch (error) {
        console.error("An error occurred during onMounted:", error);
        attendanceMessage.value = "Terjadi kesalahan saat memuat data.";
    }
});

console.log("Pre-validation currentMonth:", currentMonth);
console.log("Pre-validation date:", date);

// Fungsi untuk validasi tanggal
const isValidDate = (year, month, day) => {
    // Validasi tahun, bulan, dan tanggal
    return (
        typeof year === "number" &&
        typeof month === "number" &&
        typeof day === "number" &&
        month >= 0 &&
        month <= 11 &&
        day > 0 &&
        day <= new Date(year, month + 1, 0).getDate()
    );
};

// Pastikan `date` adalah angka
let extractedDate = date instanceof Date ? date.getDate() : date;

// Validasi input dengan menggunakan `isValidDate`
const formattedDate = (attendance_date) => {
    let date = new Date(attendance_date);
    if (!(date instanceof Date) || isNaN(date.getTime())) {
        console.error("Invalid date object:", attendance_date);
        return null; // Mengembalikan null jika tidak valid
    }
    return date.toISOString().split("T")[0]; // Format: YYYY-MM-DD
};

if (!isValidDate(currentYear, currentMonth, extractedDate)) {
    console.error("Invalid date input:", {
        currentYear,
        currentMonth,
        extractedDate,
    });
} else {
    // Format tanggal menjadi "YYYY-MM-DD"
    const dateToFormat = new Date(currentYear, currentMonth - 1, extractedDate);
    const formattedDateString = formattedDate(dateToFormat);

    // Pastikan `attendanceRecords` adalah array
    if (!Array.isArray(attendanceRecords.value)) {
        console.error("attendanceRecords is not an array:", attendanceRecords);
    } else {
        // Mencari data absensi
        const recordForCurrentDate = attendanceRecords.value.find(
            (record) =>
                formattedDate(new Date(record.attendance_date)) ===
                    formattedDateString && record.teacherId === teacher.id
        );

        // Pengecekan data absensi
        if (recordForCurrentDate) {
            console.log("Attendance status:", recordForCurrentDate.status);
        } else {
            console.log("No attendance found for this date and teacher.");
        }
    }
}

console.log(
    "Debug formattedDate:",
    currentYear,
    currentMonth,
    date,
    new Date(currentYear, currentMonth, date)
);

console.log("Debug formattedDate:", formattedDate);
console.log("attendanceRecords:", attendanceRecords.value);

/*
attendanceRecords.value.forEach((record) => {
    console.log("Checking record:", record);

    try {
        const formatted = formattedDate(new Date(record.attendance_date));
        if (formatted) {
            console.log("Record Date:", formatted);
        } else {
            console.warn("Invalid Date Detected:", record.attendance_date);
        }
    } catch (error) {
        console.error("Error during formattedDate:", error);
    }

    console.log("Teacher ID:", record.teacher_id);
    console.log("Status:", record.status);
});
*/

// Fungsi untuk memuat data dari localStorage
function loadFromLocalStorage() {
    try {
        console.log(
            "Attempting to load attendanceRecords from localStorage..."
        );
        const storedData = localStorage.getItem("attendanceRecords");

        if (storedData) {
            console.log("Data found in localStorage:", storedData);
            const parsedData = JSON.parse(storedData);

            // Pastikan data memiliki struktur yang sesuai
            if (
                Array.isArray(parsedData) &&
                parsedData.every(
                    (record) =>
                        typeof record.teacher_id === "number" &&
                        typeof record.attendance_date === "string" &&
                        typeof record.status === "string"
                )
            ) {
                localAttendanceRecords.value = parsedData; // Sinkronkan ke local ref
                console.log(
                    "Successfully loaded attendanceRecords from localStorage:",
                    parsedData
                );
            } else {
                console.error(
                    "Data from localStorage is not in the expected format."
                );
                localAttendanceRecords.value = [];
            }
        } else {
            console.log("No attendance data found in localStorage.");
            localAttendanceRecords.value = [];
        }
    } catch (error) {
        console.error("Error loading from localStorage:", error);
        localAttendanceRecords.value = [];
    }
}
// Fungsi untuk menyimpan data ke localStorage
const saveToLocalStorage = () => {
    try {
        window.addEventListener("beforeunload", () => {
            localAttendanceRecords.value = localAttendanceRecords.value.map(
                (record) => ({
                    ...record,
                    status: record.status || "Unknown", // Beri default jika kosong
                })
            );

            const dataToSave = localAttendanceRecords.value.map((record) => {
                if (record.status !== "Unknown") {
                    return { ...record, status: record.status }; // Jangan ubah jika bukan 'Unknown'
                }
                return { ...record, status: record.status || "Unknown" }; // Menambahkan default jika status kosong
            });

            console.log(
                "Data to save in localStorage:",
                localAttendanceRecords.value
            ); // Tambahan ini
            localStorage.setItem(
                "attendanceRecords",
                JSON.stringify(dataToSave)
            );
            console.log("Attendance data saved to localStorage.");
        });

        // Atau Anda bisa menambahkan trigger tambahan jika diperlukan
        setInterval(() => {
            localAttendanceRecords.value = localAttendanceRecords.value.map(
                (record) => ({
                    ...record,
                    status: record.status || "Unknown", // Beri default jika kosong
                })
            );

            const dataToSave = localAttendanceRecords.value.map((record) => {
                if (record.status !== "Unknown") {
                    return { ...record, status: record.status }; // Jangan ubah jika bukan 'Unknown'
                }
                return { ...record, status: record.status || "Unknown" }; // Menambahkan default jika status kosong
            });

            console.log(
                "Data to save in localStorage:",
                localAttendanceRecords.value
            ); // Tambahan ini
            localStorage.setItem(
                "attendanceRecords",
                JSON.stringify(dataToSave)
            );
            console.log("Attendance data saved to localStorage.");
        }, 300000); // Save every 5 minutes
    } catch (error) {
        console.error("Error saving to localStorage:", error);
    }
};

const handleStatus = (status) => {
    if (status == null || status === undefined) {
        console.log("Status received is undefined or null");
        return;
    }
    console.log("Status received:", status);
    if (status === "P") {
        // Lakukan sesuatu jika status hadir
    } else if (status === "A") {
        // Lakukan sesuatu jika status alpa
    } else {
        console.log("Status is not recognized:", status);
    }
};

const fetchAttendanceData = (teacherId, attendanceDate, page = 1) => {
    if (!attendanceDate) {
        console.error("Attendance Date is required");
        return;
    }
    console.log("Checking teacherId:", teacherId);
    console.log("Checking teacher_id:", teacherId.id);

    const formattedAttendanceDate = new Date(attendanceDate)
        .toISOString()
        .split("T")[0];
    console.log("Checking attendance_date:", attendanceDate); // Tambahan untuk cek nilai attendance_date

    const url = `/api/attendance-teachers?teacher_id=${teacherId}&attendance_date=${formattedAttendanceDate}&page=${page}`;
    console.log("Fetching URL:", url); // Debug untuk memastikan URL yang dipanggil

    axios
        .get(url)
        .then((response) => {
            console.log("Raw API Response:", response.data);

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

watch(
    attendanceRecords,
    (newRecords) => {
        const fullDate = new Date(currentYear, currentMonth, date.getDate());

        const recordForCurrentDate = newRecords.find(
            (record) =>
                formattedDate(new Date(record.attendance_date)) ===
                    formattedDate(fullDate) && record.teacher_id === teachers.id
        );

        if (recordForCurrentDate) {
            console.log("Attendance status:", recordForCurrentDate.status);
        } else {
            console.log("No attendance found for this date and teacher.");
        }
    },
    { deep: true }
);

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

// Fungsi untuk mendapatkan status kehadiran guru
const getTeacherAttendanceStatus = (teacherId, date) => {
    const record = attendanceRecords.value.find(
        (r) =>
            r.teacher_id === teacherId &&
            formattedDate(new Date(r.attendance_date)) === date
    );
    return record ? record.status : "Belum diabsen";
};

const isValidAttendanceDate = (date) => {
    return date && !isNaN(Date.parse(date)); // Pastikan nilai bisa dikonversi ke Date
};

const getAttendanceInfo = async (teacher, date) => {
    console.log("Teacher object received:", teacher);
    console.log("Type of teacher:", typeof teacher);

    if (!teacher) {
        console.warn("Teacher data is null or undefined.");
        return "Belum diabsen";
    }

    const rawTeacher = toRaw(teacher);

    if (!rawTeacher || (Array.isArray(rawTeacher) && rawTeacher.length === 0)) {
        console.warn(
            "Invalid teacher.id: Teacher object is null, undefined, or empty array."
        );
        return "Belum diabsen";
    }

    console.log("Raw teacher object:", rawTeacher);
    console.log("Type of rawTeacher:", typeof rawTeacher);

    const validTeachers = Array.isArray(rawTeacher)
        ? rawTeacher.filter((t) => t && t.id !== undefined && t.id !== null)
        : [];

    console.log("Valid teachers:", validTeachers);

    if (validTeachers.length === 0) {
        console.warn("No valid teachers found in data");
        return "Belum diabsen";
    }

    const teacherId =
        validTeachers.length > 0 ? Number(validTeachers[0]?.id) : NaN; // Konversi ke Number untuk validasi
    console.log("Converted teacher.id:", teacherId);

    if (isNaN(teacherId)) {
        console.warn("Invalid teacher.id after conversion:", rawTeacher);
        return "Belum diabsen";
    }

    if (!date) {
        console.warn("Date provided is null or undefined:", date);
        return "Belum diabsen";
    }

    await nextTick(); // Tunggu Vue merender

    const recordsArray = Array.isArray(ref(attendanceRecords.value))
        ? ref(attendanceRecords.value).slice()
        : [];

    console.log("Attendance records:", attendanceRecords.value);

    if (recordsArray.length === 0) {
        console.warn("No valid attendance records found.");
        return "Belum diabsen";
    }

    console.log("Valid teacher ID:", teacherId);

    const validRecords = toRaw(attendanceRecords.value).filter((record) => {
        console.log("Checking record:", record);

        if (!isValidDate(record.attendance_date)) {
            console.warn(`Invalid attendance_date: ${record.attendance_date}`);
            return false;
        }

        if (!isValidAttendanceDate(record.attendance_date)) {
            console.warn(
                `Attendance date not in expected format: ${record.attendance_date}`
            );
            return false;
        }

        return true;
    });
    console.log("Valid records:", validRecords);

    if (validRecords.length === 0) {
        console.warn("No valid attendance records found.");
        return "Belum diabsen";
    }

    console.log("Filtered valid records:", validRecords); // Tambahkan ini untuk melihat data valid setelah proses filtering

    const formattedDateInput = formattedDate(
        new Date(currentYear, currentMonth, date)
    );

    console.log("Formatted date input:", formattedDateInput);

    if (isNaN(new Date(formattedDateInput).getTime())) {
        console.error("Invalid formattedDateInput:", formattedDateInput);
        return "Belum diabsen";
    }

    // Tambahkan logging untuk memeriksa apakah record.attendance_date dan formattedDateInput cocok

    validRecords.forEach((record) => {
        console.log(
            "Record attendance date:",
            new Date(record.attendance_date)
        );
    });

    console.log("Filtered valid records:", validRecords);
    console.log("Formatted Date Input:", formattedDateInput);
    console.log("Attendance Records:", toRaw(attendanceRecords.value));
    console.log("Matched Record:", matchedRecord);

    const matchedRecord = validRecords.find(
        (record) =>
            formattedDate(new Date(record.attendance_date)) ===
                formattedDateInput && record.teacher_id === teacherId
    );

    if (!matchedRecord) {
        console.warn(
            `No match found for teacher_id: ${teacherId}, date: ${formattedDateInput}`
        );
        return "Belum diabsen";
    } else {
        console.log("Matched Record:", matchedRecord);
        console.log("Status:", matchedRecord.status);
        return getAttendanceClass(matchedRecord);
    }
};

const attendanceInfo = getAttendanceInfo(teachers, new Date());
console.log(attendanceInfo);

const getAttendanceClass = (teacherId, date) => {
    const status = getTeacherAttendanceStatus(teacherId, date);
    switch (status) {
        case "P":
            return "bg-info text-black fw-bold"; // Hadir
        case "A":
            return "bg-danger text-black fw-bold"; // Absen
        case "S":
            return "bg-warning text-black fw-bold"; // Sakit
        case "I":
            return "bg-primary text-black fw-bold"; // Izin
        default:
            return "bg-light text-dark"; // Status tidak ditemukan atau belum diabsen
    }
};

console.log("Attendance Records:", toRaw(attendanceRecords.value));

nextTick(() => {
    console.log("Attendance Records:", props.attendanceRecords);
});
//console.log("usePage().propss:", usePage().props);
//const rawProps = toRaw(usePage().props);
//console.log("rawProps:", rawProps);

const propsData = usePage().props;
if (propsData && propsData.then) {
    // Jika propsData adalah Promise
    propsData
        .then((data) => {
            if (data && data.errors) {
                console.log("Error in propsData:", data.errors);
            } else {
                const rawProps = toRaw(data); // Ambil data asli dari Proxy
                console.log("rawProps:", rawProps);

                if (rawProps.attendance && Array.isArray(rawProps.attendance)) {
                    const filteredRecord = rawProps.attendance.find(
                        (record) =>
                            formattedDate(new Date(record.attendance_date)) ===
                                formattedDate(
                                    new Date(currentYear, currentMonth, date)
                                ) && record.teacher_id === teacher.id
                    );

                    if (filteredRecord) {
                        console.log("Filtered Record:", filteredRecord);
                    } else {
                        console.log("Attendance record not found");
                    }
                } else {
                    console.log("Attendance records is not an array or empty");
                }
            }
        })
        .catch((error) => {
            console.log("Promise rejected:", error);
        });
} else if (propsData && propsData.errors) {
    console.log("Error in propsData:", propsData.errors);
} else {
    const rawProps = toRaw(propsData); // Ambil data asli dari Proxy
    console.log("rawProps:", rawProps);

    if (rawProps.attendance && Array.isArray(rawProps.attendance)) {
        const filteredRecord = rawProps.attendance.find(
            (record) =>
                formattedDate(new Date(record.attendance_date)) ===
                    formattedDate(new Date(currentYear, currentMonth, date)) &&
                record.teacher_id === teacher.id
        );

        if (filteredRecord) {
            console.log("Filtered Record:", filteredRecord);
        } else {
            console.log("Attendance record not found");
        }
    } else {
        console.log("Attendance records is not an array or empty");
    }
}

nextTick(() => {
    const attendanceRecords = toRaw(usePage().props.attendance);
    console.log("Attendance records:", attendanceRecords);
});

if (
    attendanceRecords &&
    Array.isArray(attendanceRecords) &&
    attendanceRecords.length > 0
) {
    const filteredRecord = attendanceRecords.find(
        (record) =>
            formattedDate(new Date(record.attendance_date)) ===
                formattedDate(new Date(currentYear, currentMonth, date)) &&
            record.teacher_id === teacher.id
    );

    if (filteredRecord) {
        console.log("Filtered Record:", filteredRecord);
    } else {
        console.log("No matching record found for today.");
    }
} else {
    console.log("attendanceRecords is not an array or is empty");
}

watch(
    localAttendanceRecords,
    (newRecords) => {
        try {
            console.log("Preparing to save to localStorage:", newRecords);
            localStorage.setItem(
                "attendanceRecords",
                JSON.stringify(newRecords)
            );
            console.log("Attendance data saved to localStorage:", newRecords);
        } catch (error) {
            console.error("Error saving to localStorage:", error);
        }
    },
    { deep: true }
);

watch(
    () => attendanceRecords.value,
    (newValue, oldValue) => {
        console.log("AttendanceRecords updated:", oldValue, "->", newValue);
    },
    { immediate: true } // Jalankan segera setelah watch diaktifkan
);

watch(customStatus, (newValue) => {
    console.log("customStatus.value updated:", newValue);
});
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

/* Media query untuk layar kecil */
@media (max-width: 576px) {
    .badge {
        font-size: 0.9rem; /* Ukuran font badge lebih kecil di layar kecil */
        padding: 0.5rem 1rem; /* Padding proporsional untuk layar kecil */
    }
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
                    <!--
                                   <button
                        type="button"
                        class="btn btn-primary mb-4"
                        @click="isModalVisible = true"
                    >
                        Tambah Absensi
                    </button>
                     -->

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
                                        :key="`attendance-${
                                            teacher.id
                                        }-${formattedDate(
                                            new Date(
                                                currentYear,
                                                currentMonth,
                                                date
                                            )
                                        )}`"
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
                                                formattedDate(
                                                    new Date(
                                                        currentYear,
                                                        currentMonth,
                                                        date
                                                    )
                                                )
                                            )
                                        "
                                    >
                                        <span
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
                                                ) + ' block text-center'
                                            "
                                        >
                                            {{
                                                processedRecords.find(
                                                    (record) =>
                                                        formattedDate(
                                                            new Date(
                                                                record.attendance_date
                                                            )
                                                        ) ===
                                                            formattedDate(
                                                                new Date(
                                                                    currentYear,
                                                                    currentMonth,
                                                                    date
                                                                )
                                                            ) &&
                                                        record.teacher_id ===
                                                            teacher.id
                                                )?.status || "Belum Absen"
                                            }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="pagination && pagination.current_page">
                        Current Page: {{ pagination.current_page }}
                    </div>

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

                    <!-- Keterangan Status Kehadiran -->
                    <div class="row mt-3 me-3">
                        <div class="col-12">
                            <p class="fw-bold fs-5">Status Kehadiran:</p>
                            <div class="d-flex flex-wrap align-items-center">
                                <div class="me-3 mb-2">
                                    <span
                                        class="badge bg-info text-black fw-bold"
                                        >Hadir (P)</span
                                    >
                                </div>
                                <div class="me-3 mb-2">
                                    <span
                                        class="badge bg-danger text-black fw-bold"
                                        >Absen (A)</span
                                    >
                                </div>
                                <div class="me-3 mb-2">
                                    <span
                                        class="badge bg-warning text-black fw-bold"
                                        >Sakit (S)</span
                                    >
                                </div>
                                <div class="me-3 mb-2">
                                    <span
                                        class="badge bg-primary text-black fw-bold"
                                        >Izin (I)</span
                                    >
                                </div>
                                <div class="me-3 mb-2">
                                    <span
                                        class="badge bg-light text-dark fw-bold"
                                        >Belum Diabsen</span
                                    >
                                </div>
                            </div>
                        </div>
                    </div>

                    <tr
                        v-for="record in processedRecords"
                        :key="record.attendance_date"
                    >
                        <td>{{ record.teacher_id }}</td>
                        <td>{{ record.attendance_date }}</td>
                        <td>{{ record.status }}</td>
                    </tr>

                    <!--           <tr
                        v-for="record in formattedRecords"
                        :key="record.attendance_date"
                    >
                        <td>{{ record.teacher_id }}</td>
                        <td>{{ record.attendance_date }}</td>
                        <td
                            :class="
                                getAttendanceClass(
                                    record.teacher_id,
                                    record.attendance_date
                                )
                            "
                        >
                            {{ record.status }}
                        </td>
                    </tr>-->

                    <!-- Modal Tambah Absensi -->
                    <div
                        v-if="isModalVisible"
                        class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50"
                        @click.self="closeModal"
                    >
                        <div
                            class="bg-white p-6 rounded-xl shadow-2xl max-w-md w-full relative overflow-hidden transform transition-all duration-300 scale-95 hover:scale-100"
                        >
                            <!-- Close Icon -->
                            <button
                                class="absolute top-4 right-4 text-gray-500 hover:text-red-500 transition"
                                @click="closeModal"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="2"
                                    stroke="currentColor"
                                    class="w-6 h-6"
                                >
                                    <circle
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        class="stroke-current"
                                        stroke-opacity="0.2"
                                        stroke-width="1.5"
                                    />
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M9 9l6 6m0-6l-6 6"
                                    />
                                </svg>
                            </button>

                            <!-- Modal Header -->
                            <div class="text-center mb-6">
                                <div
                                    class="w-14 h-14 mx-auto flex items-center justify-center bg-blue-100 rounded-full mb-4"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke-width="2"
                                        stroke="currentColor"
                                        class="w-8 h-8 text-blue-600"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M9 12h6m-3-3v6m9-6a9 9 0 11-18 0 9 9 0 0118 0z"
                                        />
                                    </svg>
                                </div>
                                <h3 class="text-2xl font-bold text-gray-800">
                                    Pilih Status Kehadiran
                                </h3>
                                <p class="text-gray-500 text-sm">
                                    Silakan pilih salah satu status di bawah
                                    ini.
                                </p>
                            </div>

                            <!-- Pilihan Status -->
                            <div class="space-y-4">
                                <button
                                    v-for="status in statuses"
                                    :key="status"
                                    :class="getButtonClass(status)"
                                    class="w-full py-3 px-5 rounded-lg font-semibold text-black transition-all duration-300"
                                    @click="handleAttendance(status)"
                                >
                                    {{ status }}
                                </button>
                            </div>

                            <!-- Modal Footer -->
                            <div class="mt-6 text-center">
                                <button
                                    class="w-full py-3 bg-gray-200 rounded-lg text-gray-700 font-medium hover:bg-gray-300 transition-colors"
                                    @click="closeModal"
                                >
                                    Tutup
                                </button>
                            </div>
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
