<?php

namespace App\Traits;

use App\Models\Student;
use App\Models\Attendance;

trait AbsensiSiswaTrait
{
    public function getAbsensiSiswaKelas($classId, $year, $month, $mapel = null)
    {
        $students = Student::with('mapel')->where('class_id', $classId)->get();

        $attendances = Attendance::whereIn('student_id', $students->pluck('id'))
            ->whereYear('tanggal_kehadiran', $year)
            ->whereMonth('tanggal_kehadiran', $month);

        if ($mapel) {
            $attendances->where('mapel', $mapel);
        }

        $attendances = $attendances->get();

        return $students->map(function ($student) use ($attendances) {
            $studentAttendances = $attendances->where('student_id', $student->id)->values();
            return [
                'id' => $student->id,
                'name' => $student->name,
                'subject' => $student->mapel->mapel ?? '-',
                'attendances' => $studentAttendances->map(function ($a) {
                    return [
                        'tanggal' => $a->tanggal_kehadiran,
                        'status' => $a->status_kehadiran,
                    ];
                })->toArray(),
            ];
        });
    }
}