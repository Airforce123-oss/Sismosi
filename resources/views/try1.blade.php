<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tabel Absensi Bulanan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <style>
        tr {
            border: 1px solid #606060;
        }

        td {
            height: 20px;
            width: 20px;
            font-size: 12px;
        }
    </style>
</head>

<body>
    @php
        # Ambil data hari pertama
        $hariPertamaBulanIni = date('1-m-Y');
        # Ambil total hari di bulan ini
        $totalHariBulanIni = date('t', strtotime($hariPertamaBulanIni));
        # Ambil data siswa
        $dataSiswa = DB::table('students')->get();
        # Jumlah data siswa
        $totalSiswa = $dataSiswa->count();
        # Variable nama dan id
        $namaSiswa = [];
        $idSiswa = [];
        $counter = 0;
        foreach ($dataSiswa as $key => $siswa) {
            $namaSiswa[] = $siswa->name;
            $idSiswa[] = $siswa->id;
        }
    @endphp

    <div class="container py-5">
        <div class="d-flex justify-content-between mb-3">
            <div>
                <h4>Tabel Absensi Siswa</h4>
                <p class="fw-bold text-danger">Bulan {{ date('M Y') }}</p>
            </div>
            <div>
                <button class="btn btn-primary" data-bs-target="#addModal" data-bs-toggle="modal">Tambah Absen</button>
            </div>
        </div>

  
        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                @for ($i = 1; $i <= $totalSiswa + 2; $i++)
                    @if ($i == 1)
                        <tr>
                            <td>Tanggal</td>
                            @for ($j = 1; $j <= $totalHariBulanIni; $j++)
                                <td>{{ $j }}
                                </td>
                            @endfor
                        </tr>
                    @elseif ($i == 2)
                        <tr>
                            <td>Hari</td>
                            @for ($j = 0; $j < $totalHariBulanIni; $j++)
                                <td
                                    class="{{ date('D', strtotime("+$j days", strtotime($hariPertamaBulanIni))) == 'Sun' ? 'bg-danger text-white' : '' }}">
                                    {{ date('D', strtotime("+$j days", strtotime($hariPertamaBulanIni))) }}
                                </td>
                            @endfor
                        </tr>
                    @else
                        <tr>
                            <td>{{ $namaSiswa[$counter] }}</td>
                            @for ($j = 1; $j <= $totalHariBulanIni; $j++)
                                @php
                                    $tanggalKehadiranSiswa = date("Y-m-$j");
                                    $dataKehadiranSiswa = DB::table('attendances')
                                        ->where([
                                            'id_siswa' => $idSiswa[$counter],
                                            'tanggal_kehadiran' => $tanggalKehadiranSiswa,
                                        ])
                                        ->get();

                                    $totalKehadiranSiswa = $dataKehadiranSiswa->count();
                                @endphp

                                @if ($totalKehadiranSiswa > 0)
                                    @foreach ($dataKehadiranSiswa as $item)
                                        @if ($item->status_kehadiran == 'P')
                                            <td class="bg-info text-black fw-bold"
                                                data-bs -target="#editModal{{ $item->id }}" data-bs-toggle="modal">
                                                {{ $item->status_kehadiran }} </td>
                                        @else
                                            <td class="bg-info text-black fw-bold"
                                                data-bs-target="#editModal{{ $item->id }}" data-bs-toggle="modal">
                                                {{ $item->status_kehadiran }}</td>
                                        @endif

                                        <!-- edit -->
                                        <div class="modal fade" id="editModal{{ $item->id }}" tabindex="-1"
                                            aria-labelledby="editModalLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    @php
                                                        $siswa = DB::table('students')
                                                            ->where('id', $item->id_siswa)
                                                            ->first();
                                                    @endphp
                                                    <div class="modal-header"> 
                                                        <h1 class="modal-title fs-5" id="editModalLabel">Edit Absensi
                                                            {{ $siswa->name }}</h1>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form
                                                            action="{{ route('update', ['id' => $item->id_siswa, 'tanggal_kehadiran' => $item->tanggal_kehadiran]) }}"
                                                            method="POST">
                                                            @csrf

                                                            <input type="hidden" name="tanggal_kehadiran"
                                                                value="{{ $item->tanggal_kehadiran }}">
                                                            <div class="form-group mb-3">
                                                                <label for="">Nama Siswa</label>
                                                                <input type="text" class="form-control bg-light"
                                                                    value="{{ $siswa->name }}" readonly>
                                                            </div>
                                                            <div class="form-group mb-3">
                                                                <label for="">Ubah Status Kehadiran</label>
                                                                <select name="status_kehadiran" class="form-select">
                                                                    <option value="P"
                                                                        {{ $item->status_kehadiran == 'P' ? 'selected' : '' }}>
                                                                        HADIR</option>
                                                                    <option value="A"
                                                                        {{ $item->status_kehadiran == 'A' ? 'selected' : '' }}>
                                                                        ALPHA</option>
                                                                    <option value="S"
                                                                        {{ $item->status_kehadiran == 'S' ? 'selected' : '' }}>
                                                                        SAKIT</option>
                                                                    <option value="I"
                                                                        {{ $item->status_kehadiran == 'I' ? 'selected' : '' }}>
                                                                        IZIN</option>
                                                                </select>
                                                            </div>

                                                            <button type="submit" class="btn btn-success w-100">Update
                                                                Absensi</button>
                                                        </form>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <td></td>
                                @endif
                            @endfor
                        </tr>

                        @php
                            $counter++;
                        @endphp
                    @endif
                @endfor
            </table>
        </div>
    </div>


    <!-- Add -->
    <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="addModalLabel">Tambah Absensi</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <form action="{{ route('store') }}" method="POST">
                        @csrf

                        <div class="form-group mb-3">
                            <label for="">Pilih Tanggal</label>
                            <input type="date" class="form-control" name="tanggal_kehadiran">
                        </div>
                        <table class="table table-bordered">
                            @foreach ($dataSiswa as $siswa)
                                <input type="hidden" name="id_siswa[]" value="{{ $siswa->id }}">
                                <div class="d-flex justify-content-between bg-light mb-1">
                                    <div>{{ $siswa->name }}</div>
                                    <div>
                                        <select name="status_kehadiran[]" class="form-select">
                                            <option value="P">HADIR</option>
                                            <option value="A">ALPHA</option>
                                            <option value="S">SAKIT</option>
                                            <option value="I">IZIN</option>
                                        </select>
                                    </div>
                                </div>
                            @endforeach
                        </table>
                        
                        <button type="submit" class="btn btn-primary w-100">Simpan Absensi</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous">
  </script>
</body>

</html>
