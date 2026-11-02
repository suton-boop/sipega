<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use App\Services\TukinCalculatorService;

class SettingController extends Controller
{
    /**
     * Tampilkan Halaman Pengaturan Aturan & Sistem
     */
    public function index(Request $request)
    {
        if (!TukinController::isPrivilegedUser(auth()->user())) {
            abort(403, 'Akses terbatas untuk Administrator & Pengelola Kepegawaian.');
        }

        $tukinRules = TukinCalculatorService::getRules();
        $rates = TukinCalculatorService::getRates();
        $officialRates = TukinCalculatorService::OFFICIAL_RATES;

        $generalSettings = [
            'is_reward_active' => Setting::get('is_reward_active', '1'),
            'is_tukin_active' => Setting::get('is_tukin_active', '1'),
            'show_slip_tukin' => Setting::get('show_slip_tukin', '1'),
            'is_realization_open_anytime' => Setting::get('is_realization_open_anytime', '0'),
            'institution_name' => Setting::get('institution_name', 'Balai Penjaminan Mutu Pendidikan (BPMP) Provinsi Kalimantan Timur'),
            'google_drive_master_folder' => Setting::get('google_drive_master_folder', ''),
        ];

        $driveService = new \App\Services\GoogleDriveService();
        $driveConfigured = $driveService->isConfigured();
        $driveServiceEmail = $driveService->getServiceAccountEmail();

        return view('settings.index', compact('tukinRules', 'rates', 'officialRates', 'generalSettings', 'driveConfigured', 'driveServiceEmail'));
    }

    /**
     * Simpan Kumpulan Pengaturan Aturan Pemotongan Tukin & Tarif
     */
    public function updateTukinRules(Request $request)
    {
        if (!TukinController::isPrivilegedUser(auth()->user())) {
            abort(403, 'Akses terbatas untuk Administrator & Pengelola Kepegawaian.');
        }

        // 1. Text Regulasi
        if ($request->has('tukin_regulation_name')) {
            Setting::set('tukin_regulation_name', trim($request->input('tukin_regulation_name')));
        }
        if ($request->has('tukin_regulation_desc')) {
            Setting::set('tukin_regulation_desc', trim($request->input('tukin_regulation_desc')));
        }
        if ($request->has('tukin_rate_regulation')) {
            Setting::set('tukin_rate_regulation', trim($request->input('tukin_rate_regulation')));
        }

        // 2. Persentase Pengurangan Jam Kerja (TL / PSW)
        $pctKeys = [
            'tukin_tl1_pct', 'tukin_tl2_pct', 'tukin_tl3_pct', 'tukin_tl4_pct', 'tukin_tl5_pct',
            'tukin_tkd_pct', 'tukin_tkp_pct', 'tukin_alpa_pct', 'tukin_accum_penalty_pct',
            'tukin_perf_baik_pct', 'tukin_perf_cukup_pct', 'tukin_perf_kurang_pct', 'tukin_perf_sangat_kurang_pct',
            'tukin_tax_gol_4', 'tukin_tax_gol_3', 'tukin_tax_gol_1_2',
        ];

        foreach ($pctKeys as $k) {
            if ($request->has($k)) {
                $rawVal = str_replace(',', '.', trim((string)$request->input($k)));
                if (is_numeric($rawVal)) {
                    Setting::set($k, (float)$rawVal);
                }
            }
        }

        // 3. Batas Jam & Hari
        if ($request->has('tukin_accum_hours_threshold')) {
            $rawVal = str_replace(',', '.', trim((string)$request->input('tukin_accum_hours_threshold')));
            if (is_numeric($rawVal) && (float)$rawVal > 0) {
                Setting::set('tukin_accum_hours_threshold', (float)$rawVal);
            }
        }
        if ($request->has('tukin_alpa_max_days')) {
            $rawVal = (int)$request->input('tukin_alpa_max_days');
            if ($rawVal > 0) {
                Setting::set('tukin_alpa_max_days', $rawVal);
            }
        }

        // 4. Tarif Nominal Plafon Grade (KJ 1 s.d 17)
        for ($grade = 1; $grade <= 17; $grade++) {
            $k = 'tukin_grade_' . $grade;
            if ($request->has($k)) {
                $cleanRate = preg_replace('/[^0-9]/', '', (string)$request->input($k));
                if (is_numeric($cleanRate) && (int)$cleanRate > 0) {
                    Setting::set($k, (int)$cleanRate);
                }
            }
        }

        // 5. Pengaturan Umum (Saklar Toggle)
        $isReward = $request->input('is_reward_active');
        Setting::set('is_reward_active', ($isReward === '1' || $isReward === 1 || $isReward === true || $isReward === 'on') ? '1' : '0');

        $isTukin = $request->input('is_tukin_active');
        Setting::set('is_tukin_active', ($isTukin === '1' || $isTukin === 1 || $isTukin === true || $isTukin === 'on') ? '1' : '0');

        $showSlip = $request->input('show_slip_tukin');
        Setting::set('show_slip_tukin', ($showSlip === '1' || $showSlip === 1 || $showSlip === true || $showSlip === 'on') ? '1' : '0');

        $isRealization = $request->input('is_realization_open_anytime');
        Setting::set('is_realization_open_anytime', ($isRealization === '1' || $isRealization === 1 || $isRealization === true || $isRealization === 'on') ? '1' : '0');

        if ($request->has('institution_name')) {
            Setting::set('institution_name', trim($request->input('institution_name')));
        }

        if ($request->has('google_drive_master_folder')) {
            Setting::set('google_drive_master_folder', trim($request->input('google_drive_master_folder')));
        }

        return redirect()->route('settings.index')->with('success', 'Pengaturan sistem dan regulasi berhasil disimpan!');
    }

    /**
     * Generate Folder Google Drive untuk Seluruh Pegawai yang Belum Memiliki Folder
     */
    public function generateAllEmployeeFolders(Request $request)
    {
        if (!TukinController::isPrivilegedUser(auth()->user())) {
            abort(403, 'Akses terbatas untuk Administrator & Pengelola Kepegawaian.');
        }

        $masterFolder = $request->input('google_drive_master_folder');
        if (!empty($masterFolder)) {
            Setting::set('google_drive_master_folder', trim($masterFolder));
        }

        $driveService = new \App\Services\GoogleDriveService();
        $result = $driveService->batchGenerateFolders();

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }

    /**
     * Reset Pengaturan Aturan Tukin ke Standar Resmi Permendikdasmen No. 14 Tahun 2025 & Perpres No. 18 Tahun 2025
     */
    public function resetTukinRules(Request $request)
    {
        if (!TukinController::isPrivilegedUser(auth()->user())) {
            abort(403, 'Akses terbatas untuk Administrator & Pengelola Kepegawaian.');
        }

        $keysToDelete = [
            'tukin_regulation_name', 'tukin_regulation_desc', 'tukin_rate_regulation',
            'tukin_tl1_pct', 'tukin_tl2_pct', 'tukin_tl3_pct', 'tukin_tl4_pct', 'tukin_tl5_pct',
            'tukin_tkd_pct', 'tukin_tkp_pct', 'tukin_alpa_pct', 'tukin_alpa_max_days',
            'tukin_accum_hours_threshold', 'tukin_accum_penalty_pct',
            'tukin_perf_baik_pct', 'tukin_perf_cukup_pct', 'tukin_perf_kurang_pct', 'tukin_perf_sangat_kurang_pct',
            'tukin_tax_gol_4', 'tukin_tax_gol_3', 'tukin_tax_gol_1_2',
            'show_slip_tukin',
        ];

        for ($grade = 1; $grade <= 17; $grade++) {
            $keysToDelete[] = 'tukin_grade_' . $grade;
        }

        Setting::whereIn('key', $keysToDelete)->delete();

        return redirect()->route('settings.index')->with('success', 'Pengaturan aturan telah berhasil direset ke standar resmi Permendikdasmen Nomor 14 Tahun 2025 & Perpres Nomor 18 Tahun 2025.');
    }

    /**
     * Update a single system setting via POST (Fast Update)
     */
    public function update(Request $request)
    {
        $request->validate([
            'key' => 'required|string',
            'value' => 'nullable|string'
        ]);

        Setting::set($request->key, $request->value);

        return back()->with('success', "Pengaturan '{$request->key}' berhasil diperbarui.");
    }
}
