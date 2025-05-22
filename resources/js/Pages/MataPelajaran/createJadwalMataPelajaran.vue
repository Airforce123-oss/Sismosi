<script setup>
import { initFlowbite } from 'flowbite';
import Pagination from '../../Components/Pagination.vue';
import MagnifyingGlass from '../../Components/Icons/MagnifyingGlass.vue';
import { Link, Head, useForm, usePage, router } from '@inertiajs/vue3';
import { onMounted, ref, watch, computed } from 'vue';
import Swal from 'sweetalert2';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
const props = defineProps({
  auth: { type: Object },
  master_mapel: {
    type: Object,
    required: true,
  },
  classes_for_student: {
    type: Object,
    required: true,
  },
  wali_kelas: {
    type: Object,
    default: () => ({ data: [] }),
  },
  teachers: Array,
  mapelList: Array,
});

const teachers = ref([]);
const mapelList = ref([]);

console.log('Data Kelas:', props.classes_for_student);
console.log('📦 Props diterima:', props);
console.log('📦 Data Mapel:', props.master_mapel);
console.log('📦 Data Guru:', props.teachers);

const fetchTeachers = () => {
  try {
    // Menggunakan data yang diterima langsung dari props
    const teachersData = props.teachers; // Asumsi bahwa data guru diteruskan sebagai props

    // Memastikan data guru valid
    if (teachersData && Array.isArray(teachersData)) {
      // Proses data guru (seperti penambahan nama atau atribut lain)
      teachersData.forEach((teacher) => {
        //console.log('Nama Guru:', teacher.name); // Menampilkan nama guru
      });

      // Perbarui data teachers tanpa menghapus data yang ada
      teachersData.forEach((newTeacher) => {
        const index = teachers.value.findIndex(
          (teacher) => teacher.id === newTeacher.id
        );

        if (index === -1) {
          teachers.value.push(newTeacher); // Jika data baru, tambahkan
        } else {
          teachers.value[index] = newTeacher; // Jika ada data lama, update
        }
      });
    } else {
      console.error('Invalid or empty data for teachers:', teachersData);
    }
  } catch (error) {
    console.error('Error fetching teachers:', error);
  }
};

const selectedWaliKelas = ref(null);
function handleWaliKelasChange() {
  if (selectedWaliKelas.value) {
    router.get(
      route('settingJadwalMataPelajaran'),
      {
        ...props.filter, // jika ada filter jurusan/tingkat/kelas
        wali_kelas_id: selectedWaliKelas.value,
      },
      {
        preserveState: true,
        replace: true,
      }
    );
  }
}

const getKelasForMapel = (mapelId) => {
  if (!mapelId) return '-';
  const m = props.master_mapel.data.find((m) => m.id === mapelId);
  return m?.kelas || '-';
};

const classesArray = props.classes_for_student.data; // Ambil array dari properti data
console.log('Classes for Student:', classesArray);

const form = useForm({
  name: props.auth?.user?.name || '',
  email: props.auth?.user?.email || '',
  role_type: props.auth?.user?.role_type || '',
});
const selectedTeacher = ref('');
//console.log('Selected Teacher:', this.selectedTeacher.value);
const selectedDay = ref('');
const currentPage = ref(1); // Gunakan ini sebagai pengganti pageNumber
const searchTerm = ref('');

const waliKelas = ref(props.wali_kelas || { data: [] });
console.log('Wali Kelas:', waliKelas.value);

const kelasUrl = computed(() => {
  const url = new URL(route('matapelajaran.index'));
  console.log('URL manual:', url);
  url.searchParams.set('page', currentPage.value); // Gunakan currentPage
  if (searchTerm.value) {
    url.searchParams.set('search', searchTerm.value);
  }
  return url;
});

const allowedDays = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];

// Fungsi validasi jadwal
const validateEntry = (entry) => {
  const errors = [];

  if (!allowedDays.includes(entry.hari)) {
    errors.push(`Hari tidak valid: ${entry.hari}`);
  }

  if (!entry.mapel_id) {
    errors.push('mapel_id kosong');
  }

  // Tambahkan validasi tambahan jika perlu, misalnya:
  // if (!entry.guru_id) errors.push('guru_id kosong');

  return errors;
};

const laporanJadwal = () => {
  try {
    entries.value = [];
    const invalidEntries = [];

    if (!Array.isArray(schedule.value)) {
      console.warn('schedule.value bukan array.');
      return {
        valid: [],
        invalid: [],
        isValid: false,
      };
    }

    schedule.value.forEach((slot) => {
      if (!slot || typeof slot !== 'object') return;

      days.forEach((day) => {
        if (!slot.jadwal || typeof slot.jadwal !== 'object') return;

        const jadwalItem = slot.jadwal[day];
        if (!jadwalItem) return;

        const entry = {
          hari: day,
          jam_ke: slot.jam_ke,
          jam: slot.jam ?? null,
          mapel_id: jadwalItem.mapel_id ?? null,
          guru_id: jadwalItem.guru_id ?? null,
          kelas: jadwalItem.kelas ?? null,
          wali_kelas: jadwalItem.wali_kelas ?? null,
          tahun: jadwalItem.tahun ?? null,
        };

        const errors = validateEntry(entry) || [];

        if (errors.length === 0) {
          entries.value.push(entry);
        } else {
          invalidEntries.push({ ...entry, alasan: errors.join(', ') });
        }
      });
    });

    return {
      valid: entries.value,
      invalid: invalidEntries,
      isValid: invalidEntries.length === 0,
    };
  } catch (err) {
    console.error('Terjadi error saat menjalankan laporanJadwal:', err);
    return {
      valid: [],
      invalid: [],
      isValid: false,
    };
  }
};

watch(
  () => kelasUrl.value,
  (updatedKelasUrl) => {
    console.log('Navigating to URL:', updatedKelasUrl.toString());
    router.visit(updatedKelasUrl.toString(), {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    });
  }
);

const jamOptions = [];
const startTime = new Date(0, 0, 0, 7, 0); // mulai dari 07:00
const endBoundary = new Date(0, 0, 0, 17, 0); // sampai 17:00

while (startTime < endBoundary) {
  const endTime = new Date(startTime.getTime() + 45 * 60000); // tambah 45 menit

  const startHour = startTime.getHours().toString().padStart(2, '0');
  const startMinute = startTime.getMinutes().toString().padStart(2, '0');
  const endHour = endTime.getHours().toString().padStart(2, '0');
  const endMinute = endTime.getMinutes().toString().padStart(2, '0');

  jamOptions.push(`${startHour}:${startMinute} - ${endHour}:${endMinute}`);

  // Geser startTime ke slot berikutnya
  startTime.setTime(endTime.getTime());
}

const currentYear = new Date().getFullYear();
const selectedJam = ref('');
const tahunOptions = [];
const selectedTahun = ref('');
for (let year = currentYear - 5; year <= currentYear + 5; year++) {
  tahunOptions.push(year);
}

const waliKelasForKelas = computed(() => {
  const classes = props.classes_for_student;

  if (Array.isArray(classes)) {
    const selectedClass = classes.find(
      (kelas) => kelas.id === selectedKelas.value
    );
    return selectedClass || null;
  }

  return null;
});

const selectedKelas = ref(null);
// HARI
const days = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'];
const schedule = ref([]);

function getTeacherNameById(id) {
  console.log('📌 Mencari nama guru untuk ID:', id);

  if (typeof id === 'string' && id.includes(',')) {
    const ids = id.split(',').map((s) => Number(s.trim()));
    console.log('🔍 Beberapa ID yang akan dicari:', ids);

    const foundTeachers = teachers.value.filter((t) => ids.includes(t.id));
    console.log(
      '✅ Guru ditemukan:',
      foundTeachers.map((t) => t.name)
    );

    return foundTeachers.map((t) => t.name).join(', ');
  } else {
    const teacher = teachers.value.find((t) => t.id === Number(id));
    console.log('✅ Guru ditemukan:', teacher?.name ?? 'Tidak ditemukan');

    return teacher ? teacher.name : null;
  }
}

const loadSchedule = async () => {
  const id = Number(selectedKelas.value);

  if (isNaN(id) || id <= 0) {
    console.warn('❗ kelas_id tidak valid. Jadwal tidak dimuat.');
    schedule.value = Array.from({ length: 8 }, (_, i) => ({
      jam_ke: i + 1,
      jam: `${String(7 + i).padStart(2, '0')}:00 - ${String(7 + i)}:45`,
      jadwal: {
        senin: {},
        selasa: {},
        rabu: {},
        kamis: {},
        jumat: {},
        sabtu: {},
        minggu: {},
      },
      wali_kelas: 'Tidak ada wali guru',
    }));
    return;
  }

  try {
    const response = await axios.get(route('jadwal.get'), {
      params: { kelas_id: id },
    });

    const rawData = response.data;

    const waliKelasData = Array.isArray(waliKelas.value)
      ? waliKelas.value
      : waliKelas.value?.data || [];

    if (!Array.isArray(waliKelasData)) {
      console.error('❌ waliKelas bukan array');
      return;
    }

    const transformed = Array.from({ length: 8 }, (_, i) => {
      const jamKe = i + 1; // gunakan number

      const entryWithJam = rawData.find(
        (item) => Number(item.jam_ke) === jamKe
      );

      const jamLabel =
        entryWithJam?.jam ||
        `${String(7 + i).padStart(2, '0')}:00 - ${String(7 + i)}:45`;

      const jadwalPerHari = {};
      days.forEach((day) => {
        const entry = rawData.find(
          (item) => Number(item.jam_ke) === jamKe && item.jadwal?.[day]
        );

        jadwalPerHari[day] = entry?.jadwal?.[day]
          ? {
              mapel:
                entry.jadwal[day].mapel ||
                `Mapel ID: ${entry.jadwal[day].mapel_id}`,
              mapel_id: entry.jadwal[day].mapel_id,
              kelas: entry.jadwal[day].kelas || '-',
              guru: getTeacherNameById(entry.jadwal[day].guru_id) ?? '-',
              guru_id: entry.jadwal[day].guru_id ?? '-',
              tahun: entry.jadwal[day].tahun ?? null,
              wali_kelas: entry.jadwal[day].wali_kelas ?? null,
            }
          : {};
      });

      const waliKelasForKelas = waliKelasData.find(
        (wali) => wali.class_id === id
      );

      return {
        jam_ke: jamKe, // tetap number
        jam: jamLabel,
        jadwal: jadwalPerHari,
        wali_kelas: waliKelasForKelas?.name || 'Tidak ada wali guru',
      };
    });

    schedule.value = transformed;

    // Jika sedang edit slot, perbarui selectedMapelModal
    if (editingSlot.value?.jamKe != null && editingSlot.value?.hari) {
      const matchedSlot = transformed.find(
        (s) => s.jam_ke === Number(editingSlot.value.jamKe)
      );
      const mapelDiSlot = matchedSlot?.jadwal?.[editingSlot.value.hari];
      selectedMapelModal.value = mapelDiSlot?.mapel_id || null;
    }

    console.log('✅ Jadwal berhasil dimuat:', schedule.value);
  } catch (error) {
    console.error('❌ Gagal mengambil jadwal:', error.response?.data || error);
  }
};

console.log('DEBUG — schedule:', schedule.value);
console.log('DEBUG — days:', days);

const entries = ref([]);

const generateEntriesFromSchedule = () => {
  entries.value = schedule.value.flatMap((slot) =>
    days
      .map((day) => {
        const item = slot.jadwal[day];
        if (!item) return null;

        const mapelId =
          typeof item === 'string'
            ? Number(item)
            : typeof item === 'number'
            ? item
            : item?.mapel_id || item?.id;

        if (!mapelId) return null;

        return {
          hari: day,
          jam_ke: String(slot.jam_ke),
          jam: slot.jam ? null : '',
          mapel_id: Number(mapelId),
          wali_kelas: waliKelasForKelas.value
            ? waliKelasForKelas.value.name
            : 'Tidak ada guru',
        };
      })
      .filter(Boolean)
  );
};

watch(
  schedule,
  () => {
    generateEntriesFromSchedule(); // Memperbarui entries setiap schedule berubah
  },
  { immediate: true }
);

// MODAL STATE
const showModal = ref(false);
const selectedMapel = ref('');
const selectedMapelModal = ref('');
console.log(
  'Selected Mapel:',
  selectedMapelModal.value,
  typeof selectedMapelModal.value
);

console.log('Selected Mapel ID:', selectedMapelModal.value);
console.log('Master Mapel Data:', props.master_mapel.data);

const selectedMapelObject = computed(() => {
  return props.master_mapel.data.find(
    (m) => m.id === Number(selectedMapelModal.value)
  );
});

const selectedMapelTeachers = computed(() => {
  const mapel = selectedMapelObject.value;
  if (mapel) {
    console.log('Guru untuk mapel:', mapel.teachers);
  }
  return selectedMapelObject.value?.teachers || [];
});

console.log('👨‍🏫 selectedMapelTeachers:', selectedMapelTeachers.value);

const editingSlot = ref({
  jamKe: null,
  hari: '',
  selectedKelas: null,
  selectedMapelModal: null,
});

const openEditModal = (jamLabel, hari) => {
  console.log('🔍 Jam yang dicari:', jamLabel);
  console.log(
    '🕒 Jam dalam schedule:',
    schedule.value.map((s) => s.jam)
  );

  const currentSlot = schedule.value.find((slot) => slot.jam === jamLabel);

  if (!currentSlot) {
    console.warn('❌ Tidak ditemukan jam:', jamLabel);
    alert('Jam tidak ditemukan!\nPastikan slot jam tersedia di jadwal.');
    return;
  }

  editingSlot.value = {
    jamKe: currentSlot.jam_ke,
    hari,
    jam: currentSlot.jam,
  };

  console.log('✅ Ditemukan slot:', currentSlot);

  selectedMapel.value = '';
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  editingSlot.value = { jamKe: null, hari: '' };
};

/*
const fetchSchedule = async () => {
  try {
    const response = await axios.get(
      route('jadwal.get', {
        kelas_id: selectedKelas.value,
      })
    );
    schedule.value = response.data;
    console.log('Fetched schedule:', schedule.value);
  } catch (error) {
    console.error('Gagal mengambil jadwal:', error);
  }
};
*/

const mapelLabelById = (id) => {
  const mapel = props.master_mapel.data.find((m) => m.id === id);
  return mapel?.name || 'Tidak diketahui';
};

const updateLocalSchedule = () => {
  entries.value.forEach((entry) => {
    const slot = schedule.value.find((s) => s.jam_ke === entry.jam_ke);
    if (slot) {
      slot.jadwal = {
        ...slot.jadwal,
        [entry.hari]: {
          mapel: mapelLabelById(entry.mapel_id),
        },
      };
    }
  });
};

const formatJam = (jamString) => {
  if (!jamString) return '';

  const [start, end] = jamString.split(' - ');

  const padTime = (t) => {
    const [h, m] = t.split(':');
    return `${h.padStart(2, '0')}:${m.padStart(2, '0')}`;
  };

  return `${padTime(start)} - ${padTime(end)}`;
};

const isSubmitting = ref(false);

const saveJadwal = async () => {
  laporanJadwal();
  if (isSubmitting.value) return;
  isSubmitting.value = true;

  const jamKeValue = String(editingSlot.value.jamKe);
  const hari = editingSlot.value.hari?.toLowerCase();

  // --- VALIDASI FORM ---
  const missingFields = [];
  if (!selectedMapelModal.value) missingFields.push('Mata Pelajaran');
  if (!editingSlot.value.jamKe) missingFields.push('Jam ke');
  if (!editingSlot.value.hari) missingFields.push('Hari');
  if (!selectedKelas.value) missingFields.push('Kelas');
  if (!selectedTeacher.value) missingFields.push('Guru');
  if (!selectedTahun.value) missingFields.push('Tahun Ajaran');

  if (missingFields.length > 0) {
    Swal.fire({
      icon: 'warning',
      title: 'Form belum lengkap!',
      text: `Harap lengkapi: ${missingFields.join(', ')}`,
    });
    isSubmitting.value = false;
    return;
  }

  const jamEntry = schedule.value.find((s) => String(s.jam_ke) === jamKeValue);
  if (!jamEntry) {
    showWarning(
      'Jam tidak ditemukan!',
      'Pastikan slot jam tersedia di jadwal.'
    );
    return resetSubmit();
  }

  const hariEntry = jamEntry.jadwal?.[hari];
  if (!hariEntry || typeof hariEntry !== 'object') {
    showWarning('Hari tidak ditemukan!', 'Pastikan hari tersedia di jadwal.');
    return resetSubmit();
  }

  const jam = jamEntry?.jam ?? getJamLabelByJamKe(jamKeValue);
  if (!jam || jam === 'Jam belum ditentukan') {
    showWarning('Jam kosong!', 'Pastikan waktu jam pelajaran tersedia.');
    return resetSubmit();
  }

  const isDuplicate = entries.value.some(
    (entry) =>
      entry.hari === editingSlot.value.hari &&
      entry.jam_ke === jamKeValue &&
      entry.mapel_id === selectedMapelModal.value
  );

  if (!isDuplicate) {
    entries.value.push({
      hari: editingSlot.value.hari,
      jam_ke: jamKeValue,
      jam,
      mapel_id: selectedMapelModal.value,
      guru_id: selectedTeacher.value,
      wali_kelas: selectedWaliKelas.value,
      tahun: selectedTahun.value,
    });
  } else {
    console.warn('⛔ Entri duplikat tidak ditambahkan ulang');
  }

  showModal.value = false;

  // --- KIRIM DATA KE SERVER ---
  try {
    const payload = {
      kelas_id: selectedKelas.value,
      guru_id: selectedTeacher.value,
      tahun_ajaran: String(selectedTahun.value),
      entries: entries.value.map((entry) => ({
        ...entry,
        guru_id: selectedTeacher.value,
        wali_kelas: selectedWaliKelas.value,
        tahun: selectedTahun.value,
      })),
    };

    console.log('Payload dikirim ke server:', payload);

    const response = await axios.post('/api/jadwal', payload);

    console.log('✅ Jadwal berhasil dikirim:', response.data);

    updateLocalSchedule();
    entries.value = [];

    Swal.fire({
      icon: 'success',
      title: 'Berhasil!',
      text: 'Jadwal berhasil disimpan.',
      timer: 2000,
      showConfirmButton: false,
    });

    router.visit(
      route('matapelajaran.JadwalMataPelajaran', {
        kelas_id: selectedKelas.value,
      })
    );
  } catch (error) {
    const response = error.response?.data;
    let showConsoleError = true;

    if (response?.message?.includes('Jadwal bentrok')) {
      const conflict = response.conflict_with ?? {};
      const hariJam = response.message.replace('❌ ', '');

      const guruName =
        conflict.guru_name ?? `Guru ID ${conflict.guru_id ?? '-'}`;
      const mapelName =
        conflict.mapel_name ?? `Mapel ID ${conflict.mapel_id ?? '-'}`;

      Swal.fire({
        icon: 'warning',
        title: '⛔ Jadwal Bentrok!',
        html: `
        <p>${hariJam}</p>
        <p><strong>${guruName}</strong> sudah mengajar <strong>${mapelName}</strong> di jam tersebut.</p>
        <p>Silakan pilih waktu lain agar tidak bentrok.</p>
      `,
      });

      showConsoleError = false;
    } else if (response?.message) {
      Swal.fire({
        icon: 'error',
        title: 'Gagal menyimpan jadwal',
        text: response.message,
      });
    }

    if (showConsoleError) {
      console.error('❌ Gagal mengirim jadwal:', response || error);
    }
  } finally {
    isSubmitting.value = false;
  }
};

// --- 🔧 UTILITIES ---
const showWarning = (title, text) => {
  Swal.fire({
    icon: 'warning',
    title,
    text,
  });
};

const resetSubmit = () => {
  isSubmitting.value = false;
};

function getJamLabelByJamKe(jamKe) {
  const jamStart = 7 + (parseInt(jamKe) - 1);
  const start = String(jamStart).padStart(2, '0') + ':00';
  const end = String(jamStart).padStart(2, '0') + ':45';
  return `${start} - ${end}`;
}

const hasJadwalForDay = (day) => {
  return schedule.value.some((slot) => {
    const entry = slot.jadwal?.[day];
    if (!entry) return false;
    if (!selectedMapelModal.value) return true;
    return entry.mapel_id === selectedMapelModal.value;
  });
};

const getMapelForSlot = (slot, day) => {
  // Cek apakah slot ada
  if (!slot || typeof slot !== 'object') return null;

  // Cek apakah slot memiliki property jadwal dan jadwal berbentuk objek
  if (!slot.jadwal || typeof slot.jadwal !== 'object') return null;

  // Cek apakah hari (day) ada dalam jadwal
  if (!slot.jadwal.hasOwnProperty(day)) return null;

  const entry = slot.jadwal[day];

  // Pastikan entry tidak null dan berbentuk object
  if (!entry || typeof entry !== 'object') return null;

  // Jika tidak ada filter mapel, tampilkan semua
  if (!selectedMapelModal.value) return entry.mapel || null;

  // Jika mapel cocok dengan filter yang dipilih
  return entry.mapel_id === selectedMapelModal.value
    ? entry.mapel || null
    : null;
};

const fetchTahunOptions = async () => {
  try {
    const response = await axios.get('/api/tahun_ajaran');
    tahunOptions.value = response.data; // Mengisi tahunOptions dengan data dari API
  } catch (error) {
    console.error('❌ Gagal mengambil data tahun ajaran:', error);
  }
};

onMounted(async () => {
  const savedSchedule = localStorage.getItem('jadwal_mingguan');
  if (savedSchedule) {
    try {
      schedule.value = JSON.parse(savedSchedule);
    } catch (e) {
      console.error('Jadwal tidak valid:', e);
    }
  }

  // Pastikan teachers dan tahunOptions didefinisikan terlebih dahulu sebagai array kosong
  if (!Array.isArray(teachers.value)) {
    teachers.value = []; // Inisialisasi teachers jika belum ada
  }

  if (!Array.isArray(tahunOptions.value)) {
    tahunOptions.value = []; // Inisialisasi tahunOptions jika belum ada
  }

  // Menambahkan pengecekan apakah teachers dan tahunOptions sudah ada
  if (teachers.value.length === 0 || tahunOptions.value.length === 0) {
    console.log('❌ Data teachers atau tahunOptions belum dimuat!');
    try {
      // Memuat teachers dan tahun ajaran
      fetchTeachers();
      await fetchTahunOptions(); // Pastikan ada fungsi untuk mengambil tahun ajaran

      if (teachers.value.length === 0) {
        console.warn('❌ Tidak ada data guru!');
        Swal.fire({
          icon: 'warning',
          title: 'Tidak ada data guru',
          text: 'Pastikan data guru sudah tersedia.',
        });
      }

      if (tahunOptions.value.length === 0) {
        console.warn('❌ Tidak ada data tahun ajaran!');
        Swal.fire({
          icon: 'warning',
          title: 'Tidak ada data tahun ajaran',
          text: 'Pastikan data tahun ajaran sudah tersedia.',
        });
      }
    } catch (error) {
      console.error(
        '❌ Gagal mengambil data teachers atau tahun ajaran:',
        error
      );
      Swal.fire({
        icon: 'error',
        title: 'Gagal memuat data',
        text: 'Terjadi kesalahan saat memuat data guru atau tahun ajaran.',
      });
    }
  }

  // Lanjutkan proses lainnya
  getMapelForSlot();
  loadSchedule();
  initFlowbite();
});

watch(selectedKelas, (newVal) => {
  console.log('Selected Kelas updated:', newVal, typeof newVal);
  if (newVal) loadSchedule();
});

watch(
  () => editingSlot.value.jamKe,
  (newVal) => {
    if (newVal) {
      console.log('✅ User telah memilih jam ke:', newVal);
    } else {
      console.log('❌ Jam belum dipilih');
    }
  }
);

watch(
  () => props.schedule,
  (newVal) => {
    if (newVal) {
      console.log('🎯 Jadwal masuk lewat watch:', newVal);
    } else {
      console.warn('⚠️ Jadwal masih kosong atau belum dimuat');
    }
  }
);

watch(selectedMapelModal, (newMapelId) => {
  const selectedMapel = props.master_mapel.data.find(
    (mapel) => mapel.id === parseInt(newMapelId)
  );
  console.log('Selected Mapel:', selectedMapel);

  if (selectedMapel && selectedMapel.teachers) {
    teachers.value = selectedMapel.teachers;
  } else {
    teachers.value = [];
  }
});

console.log(route('settingjadwalmataPelajarans.settingJadwalMataPelajaran'));
</script>

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
            <img src="/images/barunawati.jpeg" class="mr-3 h-8" alt="" />
            <span
              class="self-center text-base md:text-lg lg:text-xl xl:text-2xl font-semibold whitespace-nowrap dark:text-white"
              >SMA BARUNAWATI SURABAYA</span
            >
          </a>
        </div>
        <div class="flex items-center lg:order-2">
          <!-- Apps -->
          <button
            type="button"
            class="p-2 text-gray-500 rounded-lg hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-700 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-600"
          ></button>

          <button
            type="button"
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
            class="hidden w-full sm:w-1/2 lg:w-1/4 text-base list-none bg-white rounded divide-y divide-gray-100 shadow dark:bg-gray-700 dark:divide-gray-600 rounded-xl"
            id="dropdown"
          >
            <div class="py-3 px-3">
              <div
                class="'block w-full ps-3 pe-4 py-2 border-l-4 border-indigo-400 text-start text-base text-indigo-700 focus:outline-none focus:text-indigo-800 focus:bg-indigo-100 focus:border-indigo-700 transition duration-150 ease-in-out text-[12px]'"
              >
                <span
                  class="block text-sm font-semibold text-gray-900 dark:text-white"
                  >{{ $page.props.auth.user.email }}
                </span>
                <span
                  class="block text-sm text-gray-900 truncate dark:text-white"
                >
                  {{ $page.props.auth.user.name }}
                </span>
                <span
                  class="block text-sm text-gray-900 truncate dark:text-white"
                  >{{ form.role_type }}</span
                >
              </div>
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
    <!-- start1 -->

    <main class="md:ml-64 pt-20 min-h-screen bg-gray-100">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="p-6 space-y-6">
          <!-- FILTER -->
          <div class="bg-white shadow-md rounded-xl p-6 w-full mb-6">
            <h2 class="text-lg text-center font-semibold mb-4 text-gray-700">
              PENGATURAN JADWAL MATA PELAJARAN
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
              <!-- Mata Pelajaran -->
              <div>
                <label class="block text-sm font-semibold text-gray-600 mb-1">
                  Mata Pelajaran
                </label>
                <select
                  v-model.number="selectedMapelModal"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                >
                  <option value="">Pilih Mata Pelajaran</option>
                  <option
                    v-for="mapel in props.master_mapel.data"
                    :key="mapel.id"
                    :value="mapel.id"
                  >
                    {{ mapel.mapel }}
                    <!-- - {{ mapel.teachers }}-->
                  </option>
                </select>
              </div>

              <!-- Guru -->
              <div>
                <label class="block text-sm font-semibold text-gray-600 mb-1">
                  Guru
                </label>
                <select
                  v-model="selectedTeacher"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                >
                  <option value="">Pilih Guru</option>
                  <option
                    v-for="teacher in selectedMapelTeachers"
                    :key="teacher.id"
                    :value="teacher.id"
                  >
                    {{ teacher.name }}
                  </option>
                </select>
              </div>

              <!-- Kelas -->
              <div>
                <label class="block text-sm font-semibold text-gray-600 mb-1">
                  Kelas
                </label>
                <select
                  v-model.number="selectedKelas"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                >
                  <option value="">Pilih Kelas</option>
                  <option
                    v-for="c in props.classes_for_student.data"
                    :key="c.id"
                    :value="c.id"
                  >
                    {{ c.name }}
                  </option>
                </select>
              </div>

              <!-- Hari -->
              <div>
                <label class="block text-sm font-semibold text-gray-600 mb-1">
                  Hari
                </label>
                <select
                  v-model="editingSlot.hari"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                >
                  <option value="">Pilih Hari</option>
                  <option
                    v-for="(day, index) in days"
                    :key="index"
                    :value="day"
                  >
                    {{ day }}
                  </option>
                </select>
              </div>

              <!-- Jam -->
              <div>
                <label class="block text-sm font-semibold text-gray-600 mb-1">
                  Jam
                </label>
                <select
                  v-model="editingSlot.jamKe"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                >
                  <option value="">Pilih Jam</option>
                  <option
                    v-for="(slot, index) in schedule"
                    :key="index"
                    :value="slot.jam_ke"
                  >
                    {{ formatJam(slot.jam) }}
                  </option>
                </select>
              </div>

              <!-- Tahun -->
              <div>
                <label class="block text-sm font-semibold text-gray-600 mb-1">
                  Tahun
                </label>
                <select
                  v-model="selectedTahun"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                >
                  <option value="">Pilih Tahun</option>
                  <option
                    v-for="tahun in tahunOptions"
                    :key="tahun"
                    :value="tahun"
                  >
                    {{ tahun }}
                  </option>
                </select>
              </div>
            </div>

            <!-- Button Section -->
            <div class="px-4 py-3 text-right sm:px-6 flex justify-end mt-5">
              <div class="flex items-center space-x-4">
                <Link
                  :href="route('matapelajaran.JadwalMataPelajaran')"
                  class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                  Batal
                </Link>

                <button
                  type="button"
                  @click="saveJadwal"
                  class="bg-blue-600 hover:bg-blue-700 text-white border border-transparent rounded-md shadow-sm py-2 px-4 inline-flex justify-center text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                  Simpan
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- end1-->

    <!-- Sidebar -->
    <aside
      class="fixed top-0 left-0 z-40 w-60 h-screen pt-4 transition-transform -translate-x-full bg-white border-r border-gray-200 md:translate-x-0 dark:bg-gray-800 dark:border-gray-900"
      aria-label="Sidenav"
      id="drawer-navigation"
      style=""
    >
      <div class="overflow-y-auto py-5 px-3 h-full bg-white dark:bg-gray-800">
        <ul class="space-y-2">
          <li>
            <a
              href="admin-dashboard"
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

              <span class="flex-1 ml-3 text-left whitespace-nowrap">Siswa</span>
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

              <span class="flex-1 ml-3 text-left whitespace-nowrap">Guru</span>
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
            <ul id="dropdown-pages-guru" class="hidden py-2 space-y-2">
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
              <!-- Dropdown Daftar Absensi Guru -->
              <li>
                <a
                  href="dataAbsensiGuru"
                  class="flex items-center p-2 pl-11 w-full text-base font-medium text-gray-900 rounded-lg transition duration-75 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
                  >Data Absensi Guru</a
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
              <span class="flex-1 ml-3 text-left whitespace-nowrap">Kelas</span>
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
                  >Data Kelas</a
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

              <span class="flex-1 ml-3 text-left whitespace-nowrap"
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

            <ul id="dropdown-authentication1" class="hidden py-2 space-y-2">
              <li>
                <a
                  href="mataPelajaran"
                  class="flex items-center p-2 pl-11 w-full text-base font-medium text-gray-900 rounded-lg transition duration-75 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
                  >Data Mata Pelajaran</a
                >
              </li>
              <li>
                <a
                  href="settingJadwalMataPelajaran"
                  class="flex items-center p-2 pl-11 w-full text-base font-medium text-gray-900 rounded-lg transition duration-75 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
                  >Jadwal Mata Pelajaran</a
                >
              </li>
              <li>
                <a
                  href="laporanJadwalMataPelajaran"
                  class="flex items-center p-2 pl-11 w-full text-base font-medium text-gray-900 rounded-lg transition duration-75 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
                  >Laporan Jadwal Mata Pelajaran</a
                >
              </li>
            </ul>
          </li>
              <li>
            <button
              type="button"
              class="flex items-center p-2 w-full text-base font-medium text-gray-900 rounded-lg transition duration-75 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
              aria-controls="dropdown-authentication"
              data-collapse-toggle="dropdown-authentication11"
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

              <span class="flex-1 ml-3 text-left whitespace-nowrap"
                >Master Jabatan</span
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

            <ul id="dropdown-authentication11" class="hidden py-2 space-y-2">
              <li>
                <a
                  href="indexMasterJabatan"
                  class="flex items-center p-2 pl-11 w-full text-base font-medium text-gray-900 rounded-lg transition duration-75 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
                  >Data Master Jabatan</a
                >
              </li>
            </ul>
          </li>
        </ul>
      </div>
    </aside>
  </div>
</template>
