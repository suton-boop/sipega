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

        // 2. Ambil Nama
        $name = trim($row['nama'] ?? $row['name'] ?? '');
        if (empty($name)) {
            return null;
        }

        // 3. Email: jika kosong, auto-generate dari NIP agar tetap valid unique di database
        $email = !empty($row['email']) ? trim($row['email']) : ($nip . '@sipega.local');

        // 4. Password: DEFAULT ADALAH NIP PEGAWAI ITU SENDIRI!
        $passwordPlain = !empty($row['password']) ? trim((string)$row['password']) : $nip;
        $passwordHashed = Hash::make($passwordPlain);

        // 5. Jabatan, Golongan, KJ, Role
        $role = !empty($row['role']) ? trim($row['role']) : 'Pegawai';
        $validRoles = ['Admin', 'Pimpinan', 'Kasubag', 'Pegawai', 'Operator', 'Sekpri'];
        if (!in_array($role, $validRoles)) {
            $role = 'Pegawai';
        }

        $position = trim($row['jabatan'] ?? $row['position'] ?? '');
        $gugusMutu = trim($row['gugus_mutu'] ?? $row['gugus'] ?? '');
        $golongan = trim($row['golongan'] ?? $row['pangkat'] ?? '');
        $grade = trim($row['kj'] ?? $row['grade'] ?? '');

        // 6. Cek apakah pegawai dengan NIP ini sudah ada (hindari duplicate key error)
        $user = User::where('nip', $nip)->first();
        if (!$user && !empty($email)) {
            $user = User::where('email', $email)->first();
        }

        if ($user) {
            $user->update([
                'name'        => $name,
                'email'       => $email,
                'password'    => $passwordHashed,
                'role'        => $role,
                'position'    => $position ?: $user->position,
                'gugus_mutu'  => $gugusMutu ?: $user->gugus_mutu,
                'golongan'    => $golongan ?: $user->golongan,
                'grade'       => $grade ?: $user->grade,
                'is_active'   => true,
            ]);

            if (method_exists($user, 'syncRoles')) {
                try {
                    $user->syncRoles([$role]);
                } catch (\Throwable $e) {}
            }

            return null;
        }

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
            'is_active'   => true,
        ]);

        return $newUser;
    }
}
