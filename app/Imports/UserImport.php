<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UserImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // 1. Bersihkan NIP (hanya angka)
        $rawNip = $row['nip'] ?? null;
        if (empty($rawNip)) {
            return null;
        }

        $nip = preg_replace('/[^0-9]/', '', (string)$rawNip);
        if (empty($nip)) {
            return null;
        }

        // Cek apakah pegawai dengan NIP atau Email ini sudah terdaftar di database
        $user = User::where('nip', $nip)->first();
        $emailInput = trim((string)($row['email'] ?? ''));
        if (!$user && !empty($emailInput)) {
            $user = User::where('email', $emailInput)->first();
        }

        // 2. Ambil Nama
        $nameInput = trim((string)($row['nama'] ?? $row['name'] ?? ''));
        if (!$user && empty($nameInput)) {
            // Pegawai baru wajib punya Nama
            return null;
        }
        $name = !empty($nameInput) ? $nameInput : $user->name;

        // 3. Email: jika kosong dan user baru, auto-generate dari NIP
        $email = !empty($emailInput) ? $emailInput : ($user ? $user->email : ($nip . '@bpmpkaltim.id'));

        // 4. Role (Hak Akses): jika kosong pada pegawai lama, jangan ubah role yang sudah ada!
        $roleInput = trim((string)($row['role'] ?? ''));
        $validRoles = ['Admin', 'Pimpinan', 'Kasubag', 'Pegawai', 'Operator', 'Sekpri'];
        if (!empty($roleInput) && in_array($roleInput, $validRoles)) {
            $role = $roleInput;
        } else {
            $role = $user ? $user->role : 'Pegawai';
        }

        // 5. Jabatan, Golongan, Gugus Mutu
        $positionInput = trim((string)($row['jabatan'] ?? $row['position'] ?? ''));
        $position = !empty($positionInput) ? $positionInput : ($user ? $user->position : null);

        $gugusInput = trim((string)($row['gugus_mutu'] ?? $row['gugus'] ?? ''));
        $gugusMutu = !empty($gugusInput) ? $gugusInput : ($user ? $user->gugus_mutu : null);

        $rawGolongan = trim((string)($row['golongan'] ?? $row['pangkat'] ?? ''));
        if (!empty($rawGolongan)) {
            $golongan = User::formatPangkatGolongan($rawGolongan) ?: $rawGolongan;
        } else {
            $golongan = $user ? $user->golongan : null;
        }

        // 6. Kelas Jabatan / Grade Tukin & Sinkronisasi Tarif Resmi
        $gradeInput = trim((string)($row['kj'] ?? $row['grade'] ?? ''));
        if ($gradeInput !== '') {
            $cleanGrade = preg_replace('/[^0-9]/', '', $gradeInput) ?: $gradeInput;
            $grade = $cleanGrade;
            $baseTukin = \App\Services\TukinCalculatorService::OFFICIAL_RATES[$cleanGrade] 
                ?? ($user ? $user->base_tukin : null);
        } else {
            $grade = $user ? $user->grade : null;
            $baseTukin = $user ? $user->base_tukin : null;
        }

        // 7. Password: JANGAN PERNAH reset password user lama jika kolom password kosong di file!
        $passwordInput = trim((string)($row['password'] ?? ''));
        if (!empty($passwordInput)) {
            $passwordHashed = Hash::make($passwordInput);
        } elseif ($user) {
            $passwordHashed = $user->password; // Pertahankan password yang sudah aktif
        } else {
            $passwordHashed = Hash::make($nip); // Default untuk pegawai baru adalah NIP
        }

        // Update Pegawai yang Sudah Ada (Partial Update)
        if ($user) {
            $user->update([
                'name'        => $name,
                'email'       => $email,
                'password'    => $passwordHashed,
                'role'        => $role,
                'position'    => $position,
                'gugus_mutu'  => $gugusMutu,
                'golongan'    => $golongan,
                'grade'       => $grade,
                'base_tukin'  => $baseTukin,
                'is_active'   => true,
            ]);

            if (!empty($roleInput) && method_exists($user, 'syncRoles')) {
                try {
                    $user->syncRoles([$role]);
                } catch (\Throwable $e) {}
            }

            return null;
        }

        // Daftarkan Pegawai Baru
        $newUser = new User([
            'name'        => $name,
            'nip'         => $nip,
            'email'       => $email,
            'password'    => $passwordHashed,
            'role'        => $role,
            'position'    => $position,
            'gugus_mutu'  => $gugusMutu,
            'golongan'    => $golongan,
            'grade'       => $grade,
            'base_tukin'  => $baseTukin,
            'is_active'   => true,
        ]);

        return $newUser;
    }
}
