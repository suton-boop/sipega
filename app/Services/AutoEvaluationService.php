<?php

namespace App\Services;

use App\Models\AutoEvaluationLog;
use App\Models\DailyAgenda;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AutoEvaluationService
{
    /**
     * Ambil Konfigurasi Penilaian Otomatis SIPEGA
     */
    public function getSettings(): array
    {
        return [
            'enabled' => Setting::get('auto_evaluate_enabled', '0') === '1',
            'time' => Setting::get('auto_evaluate_time', '24:00'),
            'score' => (int) Setting::get('auto_evaluate_score', 90),
            'feedback' => Setting::get('auto_evaluate_feedback', 'Penilaian otomatis oleh sistem SIPEGA atas pelimpahan wewenang pimpinan saat beban kerja/kesibukan tinggi.'),
            'scope' => Setting::get('auto_evaluate_scope', 'all_unrated'), // unrated_submitted, all_unrated, all_employees
            'auto_realization' => Setting::get('auto_evaluate_auto_realization', '1') === '1', // otomatis isi realisasi jika pegawai lupa submit
            'last_run' => Setting::get('auto_evaluate_last_run'),
            'last_count' => (int) Setting::get('auto_evaluate_last_count', 0),
            'last_actor' => Setting::get('auto_evaluate_last_actor'),
        ];
    }

    /**
     * Simpan / Perbarui Konfigurasi Penilaian Otomatis (Hanya Pimpinan)
     */
    public function updateSettings(array $data, ?User $actor = null): void
    {
        Setting::set('auto_evaluate_enabled', !empty($data['enabled']) ? '1' : '0');
        Setting::set('auto_evaluate_time', $data['time'] ?? '24:00');
        Setting::set('auto_evaluate_score', (string) ($data['score'] ?? 90));
        Setting::set('auto_evaluate_feedback', $data['feedback'] ?? 'Penilaian otomatis oleh sistem SIPEGA atas pelimpahan wewenang pimpinan.');
        Setting::set('auto_evaluate_scope', $data['scope'] ?? 'all_unrated');
        Setting::set('auto_evaluate_auto_realization', !empty($data['auto_realization']) ? '1' : '0');

        if ($actor) {
            Setting::set('auto_evaluate_last_actor', $actor->name . ' (' . $actor->role . ')');
        }
    }

    /**
     * Eksekusi Penilaian Otomatis Seluruh Pegawai
     */
    public function executeAutoEvaluation(?string $targetDate = null, string $triggerType = 'manual_instant', ?User $actor = null): array
    {
        $now = Carbon::now('Asia/Makassar');
        $targetDate = $targetDate ?: $now->toDateString();
        $settings = $this->getSettings();

        $score = (int) $settings['score'];
        $feedback = $settings['feedback'];
        $scope = $settings['scope'];
        $autoRealize = $settings['auto_realization'];

        // Tentukan user penilai resmi (default pimpinan tertinggi jika triggered by cron)
        $evaluatorId = $actor ? $actor->id : (User::where('role', 'Pimpinan')->value('id') ?: User::where('role', 'Kasubag')->value('id'));
        $evaluatorName = $actor ? $actor->name : 'Sistem Otomatis (Pimpinan)';

        // Ambil semua pegawai aktif non-pimpinan
        $realEmployees = User::realPegawai()->where('is_active', true)->get();
        $evaluatedCount = 0;
        $affectedNames = [];

        foreach ($realEmployees as $emp) {
            // Cari agenda pegawai pada targetDate
            $agenda = DailyAgenda::where('user_id', $emp->id)
                ->whereDate('date', $targetDate)
                ->first();

            // Skenario A: Jika agenda belum dibuat sama sekali dan scope 'all_employees'
            if (!$agenda && $scope === 'all_employees') {
                $agenda = DailyAgenda::create([
                    'user_id' => $emp->id,
                    'date' => $targetDate,
                    'activity_plan' => 'Tugas Kedinasan dan Pelayanan Pendidikan BPMP',
                    'activity_realization' => 'Tuntas dilaksanakan sesuai tugas pokok dan fungsi.',
                    'submitted_at' => $now,
                    'realization_submitted_at' => $now,
                    'status' => 'Verified',
                ]);
            }

            if (!$agenda) {
                continue;
            }

            // Jika sudah memiliki penilaian dari pimpinan/kasubag, lewati agar tidak menimpa penilaian manual
            if ($agenda->leader_rating !== null) {
                continue;
            }

            // Jika scope 'unrated_submitted', hanya nilai yang sudah kirim evaluasi/realisasi
            if ($scope === 'unrated_submitted' && empty($agenda->realization_submitted_at)) {
                continue;
            }

            // Jika belum kirim realisasi tapi autoRealize aktif, tandai realisasi selesai
            if (empty($agenda->realization_submitted_at) && $autoRealize) {
                $agenda->activity_realization = $agenda->activity_realization ?: 'Terverifikasi otomatis oleh sistem atas pelimpahan wewenang pimpinan.';
                $agenda->realization_submitted_at = $now;
            }

            // Berikan penilaian otomatis
            $agenda->leader_rating = $score;
            $agenda->leader_feedback = $feedback;
            $agenda->evaluated_at = $now;
            $agenda->evaluated_by = $evaluatorId;
            $agenda->status = 'Verified';
            $agenda->save();

            // Perbarui skor rata-rata performa pegawai
            $avgScore = DailyAgenda::where('user_id', $emp->id)
                ->whereNotNull('leader_rating')
                ->avg('leader_rating');

            if ($avgScore !== null) {
                $emp->update([
                    'performance_score' => round($avgScore, 2),
                    'performance_color' => $avgScore >= 80 ? 'Hijau' : ($avgScore >= 60 ? 'Kuning' : 'Merah'),
                    'performance_predicate' => $avgScore >= 90 ? 'Sangat Baik' : ($avgScore >= 75 ? 'Baik' : 'Butuh Perbaikan')
                ]);
            }

            $evaluatedCount++;
            $affectedNames[] = $emp->name;
        }

        // Catat riwayat ke AutoEvaluationLog
        $log = AutoEvaluationLog::create([
            'executed_at' => $now,
            'target_date' => $targetDate,
            'trigger_type' => $triggerType,
            'triggered_by' => $actor ? $actor->id : null,
            'triggered_by_name' => $evaluatorName,
            'score_given' => $score,
            'total_evaluated' => $evaluatedCount,
            'feedback_given' => $feedback,
            'scope' => $scope,
            'affected_summary' => implode(', ', array_slice($affectedNames, 0, 15)) . (count($affectedNames) > 15 ? ' dan ' . (count($affectedNames) - 15) . ' lainnya' : ''),
        ]);

        // Perbarui riwayat di Settings
        Setting::set('auto_evaluate_last_run', $now->toDateTimeString());
        Setting::set('auto_evaluate_last_count', (string) $evaluatedCount);
        Setting::set('auto_evaluate_last_actor', $evaluatorName);

        Log::info("SIPEGA Auto-Evaluation executed successfully on {$targetDate}: {$evaluatedCount} agendas evaluated with score {$score} by {$evaluatorName}.");

        return [
            'success' => true,
            'count' => $evaluatedCount,
            'score' => $score,
            'date' => $targetDate,
            'log' => $log,
        ];
    }
}
