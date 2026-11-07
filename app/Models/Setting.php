<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['key', 'value'])]
class Setting extends Model
{
    /**
     * Helper Static untuk ambil setting cepat
     */
    public static function get($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Helper Static untuk update setting cepat
     */
    public static function set($key, $value)
    {
        return self::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    /**
     * Memeriksa apakah suatu menu aktif
     */
    public static function isMenuEnabled(string $key, bool $default = true): bool
    {
        return self::get($key, $default ? '1' : '0') === '1';
    }

    /**
     * Memeriksa apakah sub-menu aktif (sekaligus memastikan parent menu aktif)
     */
    public static function isSubmenuEnabled(string $submenuKey, ?string $parentKey = null, bool $default = true): bool
    {
        if ($parentKey && !self::isMenuEnabled($parentKey, $default)) {
            return false;
        }
        return self::isMenuEnabled($submenuKey, $default);
    }

    /**
     * Definisi Lengkap Seluruh Menu dan Sub-Menu SIPEGA
     */
    public static function getMenuDefinitions(): array
    {
        return [
            'menu_home' => [
                'label' => 'Home / Beranda',
                'icon' => '🏠',
                'desc' => 'Halaman utama dashboard pegawai & pimpinan',
                'submenus' => []
            ],
            'menu_mik' => [
                'label' => 'MiK (Monitoring Izin Karyawan)',
                'icon' => '🏢',
                'desc' => 'Modul perizinan keluar kantor dan keluar ruangan',
                'submenus' => [
                    'submenu_mik_dashboard' => ['label' => 'Dashboard MiK (Statistik Izin Keluar)', 'route' => 'mik.index', 'tag' => 'UTAMA'],
                    'submenu_mik_form_kantor' => ['label' => 'Form_Kantor (Izin Keluar Kantor)', 'route' => 'mik.form_kantor', 'tag' => 'Kantor'],
                    'submenu_mik_form_ruangan' => ['label' => 'Form_Ruangan (Izin Keluar Ruangan)', 'route' => 'mik.form_ruangan', 'tag' => 'Ruangan'],
                    'submenu_mik_approvals' => ['label' => 'Persetujuan Izin MiK (Ketua GM / Kasubag / Kepala)', 'route' => 'mik.approvals', 'tag' => 'APPROVAL'],
                ]
            ],
            'menu_arsip' => [
                'label' => 'Arsip (Dokumen Fisik)',
                'icon' => '📁',
                'desc' => 'Pusat dokumen, slip tukin, dan berkas fisik kepegawaian',
                'submenus' => []
            ],
            'menu_kinerja' => [
                'label' => 'Kinerja (SKP & RHK)',
                'icon' => '🎯',
                'desc' => 'Manajemen Output SKP, kelola RHK, bukti fisik, dan agenda kegiatan',
                'submenus' => [
                    'submenu_kinerja_rhk' => ['label' => 'Kelola RHK', 'route' => 'agenda.index', 'tag' => 'RHK'],
                    'submenu_kinerja_agenda' => ['label' => 'Agenda & Realisasi', 'route' => 'agenda.index', 'tag' => 'SKP'],
                    'submenu_kinerja_evidence' => ['label' => 'Bukti Fisik (SKP)', 'route' => 'evidence.index', 'tag' => 'BUKTI'],
                    'submenu_kinerja_individu' => ['label' => 'Agenda Individu', 'route' => 'schedules.index', 'tag' => 'AGENDA'],
                ]
            ],
            'menu_cuti' => [
                'label' => 'Cuti (Administrasi Cuti ASN)',
                'icon' => '🏖️',
                'desc' => 'Kelola data cuti ASN, pengajuan cuti baru, persetujuan dan saldo cuti',
                'submenus' => [
                    'submenu_cuti_index' => ['label' => 'Kelola Data Cuti ASN', 'route' => 'leaves.index', 'tag' => 'CUTI'],
                    'submenu_cuti_create' => ['label' => '+ Ajukan Cuti Baru', 'route' => 'leaves.create', 'tag' => 'FORM'],
                    'submenu_cuti_approvals' => ['label' => 'Persetujuan Cuti (Approval)', 'route' => 'leaves.approvals', 'tag' => 'APPROVAL'],
                    'submenu_cuti_balances' => ['label' => 'Saldo Cuti Satker', 'route' => 'leaves.balances', 'tag' => 'KUOTA'],
                ]
            ],
            'menu_piket' => [
                'label' => 'Piket (Pelayanan ULT & Perpustakaan)',
                'icon' => '🛡️',
                'desc' => 'Jadwal piket tahunan, kalender, deteksi konflik, penggantian dan laporan',
                'submenus' => [
                    'submenu_piket_dashboard' => ['label' => 'Dashboard Piket', 'route' => 'piket.dashboard', 'tag' => 'UTAMA'],
                    'submenu_piket_calendar' => ['label' => 'Kalender Piket', 'route' => 'piket.schedules.calendar', 'tag' => 'KALENDER'],
                    'submenu_piket_schedules' => ['label' => 'Pengaturan Jadwal Tahunan', 'route' => 'piket.schedules.index', 'tag' => 'JADWAL'],
                    'submenu_piket_conflicts' => ['label' => 'Deteksi Konflik Jadwal', 'route' => 'piket.conflicts.index', 'tag' => 'OTOMATIS'],
                    'submenu_piket_replacements' => ['label' => 'Penggantian Petugas (3 Prioritas)', 'route' => 'piket.replacements.index', 'tag' => '3 LEVEL'],
                    'submenu_piket_swaps' => ['label' => 'Pengajuan Tukar Jadwal', 'route' => 'piket.swaps.index', 'tag' => 'TUKAR'],
                    'submenu_piket_monitoring' => ['label' => 'Monitoring & Presensi Layanan', 'route' => 'piket.monitoring.index', 'tag' => 'PRESENSI'],
                    'submenu_piket_officers' => ['label' => 'Data Petugas Piket', 'route' => 'piket.officers.index', 'tag' => 'PETUGAS'],
                    'submenu_piket_reports' => ['label' => 'Laporan & Rekapitulasi', 'route' => 'piket.reports.index', 'tag' => 'REKAP'],
                ]
            ],
            'menu_presensi' => [
                'label' => 'Presensi & Remunerasi',
                'icon' => '⏱️',
                'desc' => 'Laporan presensi harian, rapat, SK/Surat tugas, perjalanan dinas, QR code, dan tukin',
                'submenus' => [
                    'submenu_presensi_recap' => ['label' => 'Laporan Harian (Log Presensi)', 'route' => 'attendance.recap', 'tag' => 'LOG'],
                    'submenu_presensi_meeting' => ['label' => 'Presensi Rapat / Kegiatan', 'route' => 'attendance.index', 'tag' => 'KEGIATAN'],
                    'submenu_presensi_letters' => ['label' => 'SK & Surat Tugas', 'route' => 'letters.index', 'tag' => 'SURAT'],
                    'submenu_presensi_travel' => ['label' => 'Laporan Perjalanan Dinas', 'route' => 'travel-reports.index', 'tag' => 'LAPORAN'],
                    'submenu_presensi_qrcode' => ['label' => 'Pembuat QR Code', 'route' => 'qrcode.index', 'tag' => 'QR'],
                    'submenu_presensi_tukin' => ['label' => 'Hitung Tukin (Permendikdasmen 14/2025) / Slip Tukin', 'route' => 'tukin.index', 'tag' => 'TUKIN'],
                ]
            ],
            'menu_admin' => [
                'label' => 'Admin (Peralatan & Pengaturan)',
                'icon' => '⚙️',
                'desc' => 'Fitur administratif pimpinan, import presensi, penilaian pegawai, dan smart pengaturan',
                'submenus' => [
                    'submenu_admin_auto_eval' => ['label' => 'Otomatisasi Penilaian Pegawai (Jam 24:00)', 'route' => 'pimpinan.auto_evaluation.index', 'tag' => 'AUTO'],
                    'submenu_admin_attendance_manage' => ['label' => 'Kelola Kehadiran Pegawai', 'route' => 'admin.attendance.manage', 'tag' => 'ABSEN'],
                    'submenu_admin_import_presence' => ['label' => 'Import Log Kehadiran (SIPEGA-Check)', 'route' => 'attendance.import.index', 'tag' => 'IMPORT'],
                    'submenu_admin_performance_recap' => ['label' => 'Raport Capaian Pegawai', 'route' => 'performance.recap', 'tag' => 'RAPORT'],
                    'submenu_admin_simulation' => ['label' => 'Simulasi Surat Tugas', 'route' => 'duty-simulation.index', 'tag' => 'SIMULASI'],
                    'submenu_admin_users' => ['label' => 'Data Pegawai (NIP & Satker)', 'route' => 'users.index', 'tag' => 'PEGAWAI'],
                    'submenu_admin_monitoring' => ['label' => 'Monitoring & Penilaian (Pimpinan)', 'route' => 'leader.agenda.index', 'tag' => 'MONITORING'],
                    'submenu_admin_travel_recap' => ['label' => 'Rekap Dinas Luar', 'route' => 'travel.recap', 'tag' => 'REKAP'],
                    'submenu_admin_calendar' => ['label' => 'Kalender Kerja Dinas', 'route' => 'admin.calendar.index', 'tag' => 'KALENDER'],
                    'submenu_admin_settings' => ['label' => 'Smart Pengaturan', 'route' => 'settings.index', 'tag' => 'SETTINGS'],
                ]
            ],
        ];
    }
}
