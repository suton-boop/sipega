<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\User;
use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Google\Service\Drive\Permission;
use Illuminate\Support\Facades\Log;

class GoogleDriveService
{
    public static ?string $lastError = null;
    protected ?Client $client = null;
    protected ?Drive $service = null;
    protected string $credentialsPath;

    public function __construct(?string $customCredentialsPath = null)
    {
        $this->credentialsPath = $customCredentialsPath ?: storage_path('app/google/service-account.json');

        if (!self::isLibraryInstalled()) {
            Log::warning('GoogleDriveService: Library google/apiclient belum terpasang di vendor.');
            return;
        }

        // 1. Cek Mode OAuth 2.0 (Akun Google Pribadi / Google One 2 TB - Solusi Kuota Penuh)
        $oauthClientId = Setting::get('google_oauth_client_id');
        $oauthClientSecret = Setting::get('google_oauth_client_secret');
        $oauthRefreshToken = Setting::get('google_oauth_refresh_token');

        if ($oauthClientId && $oauthClientSecret && $oauthRefreshToken) {
            try {
                $this->client = new Client();
                $this->client->setClientId($oauthClientId);
                $this->client->setClientSecret($oauthClientSecret);
                $this->client->addScope(Drive::DRIVE);
                $this->client->refreshToken($oauthRefreshToken);
                $this->service = new Drive($this->client);
                return;
            } catch (\Throwable $e) {
                Log::error('GoogleDriveService OAuth Init Failed: ' . $e->getMessage());
                $this->client = null;
                $this->service = null;
            }
        }

        // 2. Fallback ke Mode Service Account (Kunci JSON)
        if (file_exists($this->credentialsPath)) {
            try {
                $this->client = new Client();
                $this->client->setAuthConfig($this->credentialsPath);
                $this->client->addScope(Drive::DRIVE);
                if ($impersonate = Setting::get('google_drive_impersonate_email')) {
                    $this->client->setSubject(trim($impersonate));
                }
                $this->service = new Drive($this->client);
            } catch (\Throwable $e) {
                Log::error('GoogleDriveService Init File Failed: ' . $e->getMessage());
                $this->client = null;
                $this->service = null;
            }
        } elseif ($dbJson = Setting::get('google_service_account_json')) {
            try {
                $decoded = json_decode($dbJson, true);
                if (is_array($decoded)) {
                    $dir = dirname($this->credentialsPath);
                    if (!file_exists($dir)) {
                        mkdir($dir, 0777, true);
                    }
                    file_put_contents($this->credentialsPath, $dbJson);

                    $this->client = new Client();
                    $this->client->setAuthConfig($decoded);
                    $this->client->addScope(Drive::DRIVE);
                    if ($impersonate = Setting::get('google_drive_impersonate_email')) {
                        $this->client->setSubject(trim($impersonate));
                    }
                    $this->service = new Drive($this->client);
                }
            } catch (\Throwable $e) {
                Log::error('GoogleDriveService Init DB Failed: ' . $e->getMessage());
                $this->client = null;
                $this->service = null;
            }
        }
    }

    /**
     * Cek apakah library google/apiclient terinstall
     */
    public static function isLibraryInstalled(): bool
    {
        return class_exists('Google\Client');
    }

    /**
     * Cek apakah Google Drive Service Account siap digunakan
     */
    public function isConfigured(): bool
    {
        return self::isLibraryInstalled() && $this->client !== null && $this->service !== null;
    }

    /**
     * Dapatkan mode autentikasi aktif ('oauth' atau 'service_account')
     */
    public function getAuthMode(): string
    {
        if (Setting::get('google_oauth_refresh_token')) {
            return 'oauth';
        }
        return 'service_account';
    }

    /**
     * Dapatkan email akun yang sedang terhubung (OAuth 2.0 atau Service Account)
     */
    public function getConnectedEmail(): ?string
    {
        if ($this->getAuthMode() === 'oauth') {
            return Setting::get('google_oauth_user_email') ?: 'Akun Google Pribadi (OAuth 2.0)';
        }
        return $this->getServiceAccountEmail();
    }

    /**
     * Dapatkan email Service Account yang digunakan
     */
    public function getServiceAccountEmail(): ?string
    {
        if (file_exists($this->credentialsPath)) {
            $json = json_decode(file_get_contents($this->credentialsPath), true);
            if (!empty($json['client_email'])) {
                return $json['client_email'];
            }
        }

        if ($dbJson = Setting::get('google_service_account_json')) {
            $json = json_decode($dbJson, true);
            if (!empty($json['client_email'])) {
                return $json['client_email'];
            }
        }

        return 'sipega-drive@sipega-bpmp-kaltim.iam.gserviceaccount.com';
    }

    /**
     * Ekstrak ID Folder Google Drive dari URL
     */
    public static function extractFolderId(?string $urlOrId): ?string
    {
        if (empty($urlOrId)) {
            return null;
        }

        $trimmed = trim($urlOrId);

        // Jika berbentuk URL Drive folder (dengan atau tanpa parameter ?usp=...)
        if (preg_match('/folders\/([a-zA-Z0-9_-]+)/', $trimmed, $matches)) {
            return $matches[1];
        }

        // Jika berbentuk URL dengan parameter query ?id=... atau &id=...
        if (preg_match('/[?&]id=([a-zA-Z0-9_-]+)/', $trimmed, $matches)) {
            return $matches[1];
        }

        // Jika berbentuk ID langsung (alfanumerik panjang tanpa slash)
        if (preg_match('/^[a-zA-Z0-9_-]{15,}$/', $trimmed)) {
            return $trimmed;
        }

        return null;
    }

    /**
     * Buat folder baru di Google Drive
     */
    public function createFolder(string $folderName, ?string $parentFolderUrlOrId = null, bool $makePublic = true): ?array
    {
        if (!$this->isConfigured()) {
            Log::warning('GoogleDriveService: Service account belum siap.');
            return null;
        }

        try {
            $parentId = self::extractFolderId($parentFolderUrlOrId);

            $folderMetadata = new DriveFile([
                'name' => $folderName,
                'mimeType' => 'application/vnd.google-apps.folder',
            ]);

            if ($parentId) {
                $folderMetadata->setParents([$parentId]);
            }

            $folder = $this->service->files->create($folderMetadata, [
                'fields' => 'id, name, webViewLink',
                'supportsAllDrives' => true,
            ]);

            // Set permission reader publik
            if ($makePublic && !empty($folder->id)) {
                try {
                    $permission = new Permission([
                        'type' => 'anyone',
                        'role' => 'reader',
                    ]);
                    $this->service->permissions->create($folder->id, $permission, [
                        'supportsAllDrives' => true,
                    ]);
                } catch (\Throwable $e) {
                    Log::warning('GoogleDriveService: Gagal set permission folder: ' . $e->getMessage());
                }
            }

            return [
                'id' => $folder->id,
                'name' => $folder->name,
                'web_view_link' => $folder->webViewLink,
            ];
        } catch (\Throwable $e) {
            Log::error('GoogleDriveService createFolder Error: ' . $e->getMessage(), [
                'folderName' => $folderName,
                'parent' => $parentFolderUrlOrId,
            ]);
            return null;
        }
    }

    /**
     * Cari folder berdasarkan nama di dalam parent folder tertentu
     */
    public function findFolderByName(string $folderName, ?string $parentFolderUrlOrId = null): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        $parentId = self::extractFolderId($parentFolderUrlOrId);

        try {
            $escapedName = str_replace("'", "\\'", $folderName);
            $q = "name = '{$escapedName}' and mimeType = 'application/vnd.google-apps.folder' and trashed = false";
            if ($parentId) {
                $q .= " and '{$parentId}' in parents";
            }

            $response = $this->service->files->listFiles([
                'q' => $q,
                'fields' => 'files(id, name, webViewLink)',
                'supportsAllDrives' => true,
                'includeItemsFromAllDrives' => true,
                'pageSize' => 5,
            ]);

            $files = $response->getFiles();
            if (!empty($files)) {
                return [
                    'id' => $files[0]->id,
                    'name' => $files[0]->name,
                    'web_view_link' => $files[0]->webViewLink,
                ];
            }
        } catch (\Throwable $e) {
            Log::warning('GoogleDriveService findFolderByName Error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Cari atau buat folder Google Drive untuk satu pegawai
     */
    public function findOrCreateUserFolder(User $user, ?string $masterFolderId = null): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        $master = $masterFolderId ?: Setting::get('google_drive_master_folder');
        if (empty($master)) {
            return null;
        }

        $masterId = self::extractFolderId($master);
        $exactName = ($user->nip ? trim($user->nip) . ' - ' : '') . trim($user->name);

        // 1. Coba cari dengan nama persis di Master Folder
        $found = $this->findFolderByName($exactName, $masterId);
        if ($found) {
            if ($user->drive_folder_link !== $found['web_view_link']) {
                $user->update(['drive_folder_link' => $found['web_view_link']]);
            }
            return $found;
        }

        // 2. Jika tidak ketemu dan user punya NIP, cari folder yang memuat NIP user di Master Folder
        if (!empty($user->nip)) {
            try {
                $cleanNip = trim($user->nip);
                $q = "name contains '{$cleanNip}' and mimeType = 'application/vnd.google-apps.folder' and trashed = false";
                if ($masterId) {
                    $q .= " and '{$masterId}' in parents";
                }
                $response = $this->service->files->listFiles([
                    'q' => $q,
                    'fields' => 'files(id, name, webViewLink)',
                    'supportsAllDrives' => true,
                    'includeItemsFromAllDrives' => true,
                    'pageSize' => 5,
                ]);
                $files = $response->getFiles();
                if (!empty($files)) {
                    $found = [
                        'id' => $files[0]->id,
                        'name' => $files[0]->name,
                        'web_view_link' => $files[0]->webViewLink,
                    ];
                    if ($user->drive_folder_link !== $found['web_view_link']) {
                        $user->update(['drive_folder_link' => $found['web_view_link']]);
                    }
                    return $found;
                }
            } catch (\Throwable $e) {
                Log::warning('GoogleDriveService findFolderByNip Error: ' . $e->getMessage());
            }
        }

        // 3. Jika belum ada di Google Drive, buat baru di Master Folder
        return $this->generateUserFolder($user, $masterId);
    }

    /**
     * Generate folder otomatis untuk satu pegawai
     */
    public function generateUserFolder(User $user, ?string $masterFolderId = null): ?array
    {
        $master = $masterFolderId ?: Setting::get('google_drive_master_folder');
        if (empty($master)) {
            Log::info('GoogleDriveService: Master folder belum diatur.');
            return null;
        }

        $folderName = ($user->nip ? trim($user->nip) . ' - ' : '') . trim($user->name);
        $folder = $this->createFolder($folderName, $master, true);

        if ($folder && !empty($folder['web_view_link'])) {
            $user->update(['drive_folder_link' => $folder['web_view_link']]);
            return $folder;
        }

        return null;
    }

    /**
     * Batch Generate Folder untuk seluruh pegawai (dengan limit per batch agar tidak terkena timeout)
     */
    public function batchGenerateFolders(?string $masterFolderId = null, int $limit = 12): array
    {
        @ini_set('max_execution_time', '300');

        $master = $masterFolderId ?: Setting::get('google_drive_master_folder');
        if (empty($master)) {
            return [
                'success' => false,
                'message' => 'Tautan Folder Induk Google Drive (Master Folder) belum diisi.'
            ];
        }

        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Google Drive API belum terkonfigurasi. Silakan unggah atau tempel file service-account.json terlebih dahulu.'
            ];
        }

        // Uji apakah folder induk bisa diakses oleh Service Account
        $cleanMasterId = self::extractFolderId($master);
        try {
            $this->service->files->get($cleanMasterId, [
                'fields' => 'id, name',
                'supportsAllDrives' => true
            ]);
        } catch (\Google\Service\Exception $e) {
            if ($e->getCode() === 404) {
                return [
                    'success' => false,
                    'message' => 'Folder Induk tidak ditemukan atau belum dibagikan! Pastikan Anda sudah membagikan (Share) folder tersebut ke email: ' . $this->getServiceAccountEmail() . ' dengan peran Editor.'
                ];
            }
            return [
                'success' => false,
                'message' => 'Gagal mengakses Folder Induk: ' . $e->getMessage()
            ];
        }

        $query = User::realPegawai()
            ->where(function ($q) {
                $q->whereNull('drive_folder_link')->orWhere('drive_folder_link', '');
            });

        $totalRemainingBefore = $query->count();
        $employees = $limit > 0 ? $query->limit($limit)->get() : $query->get();

        if ($employees->isEmpty()) {
            return [
                'success' => true,
                'total_candidates' => 0,
                'created_count' => 0,
                'remaining_count' => 0,
                'message' => 'Semua pegawai sudah memiliki folder Google Drive masing-masing!'
            ];
        }

        $createdCount = 0;
        $failedNames = [];

        foreach ($employees as $emp) {
            $result = $this->generateUserFolder($emp, $cleanMasterId);
            if ($result) {
                $createdCount++;
            } else {
                $failedNames[] = $emp->name;
            }
        }

        $remainingCount = User::realPegawai()
            ->where(function ($q) {
                $q->whereNull('drive_folder_link')->orWhere('drive_folder_link', '');
            })->count();

        $msg = "Berhasil membuat {$createdCount} folder baru.";
        if ($remainingCount > 0) {
            $msg .= " Masih tersisa {$remainingCount} pegawai. Klik tombol 'Generate' lagi untuk melanjutkan pembuatan sisa pegawai.";
        } else {
            $msg .= " Seluruh folder Google Drive pegawai kini telah lengkap 100%!";
        }

        return [
            'success' => true,
            'total_candidates' => $totalRemainingBefore,
            'created_count' => $createdCount,
            'remaining_count' => $remainingCount,
            'failed_count' => count($failedNames),
            'failed_names' => $failedNames,
            'message' => $msg
        ];
    }

    /**
     * Upload berkas bukti ke Google Drive folder pegawai
     */
    public function uploadEvidence(string $localFilePath, string $fileName, ?string $folderUrlOrId = null, bool $makePublic = true): ?array
    {
        if (!$this->isConfigured()) {
            self::$lastError = 'Kredensial Google Drive belum aktif atau file service-account.json belum diunggah.';
            Log::warning('GoogleDriveService: Credentials belum siap.');
            return null;
        }

        if (!file_exists($localFilePath)) {
            self::$lastError = 'Berkas lokal PDF tidak ditemukan di server: ' . $localFilePath;
            Log::error('GoogleDriveService: Berkas lokal tidak ditemukan: ' . $localFilePath);
            return null;
        }

        try {
            $folderId = self::extractFolderId($folderUrlOrId);

            $fileMetadata = new DriveFile([
                'name' => $fileName,
            ]);

            if ($folderId) {
                $fileMetadata->setParents([$folderId]);
            }

            $mimeType = mime_content_type($localFilePath) ?: 'application/octet-stream';
            $content = file_get_contents($localFilePath);

            $file = null;
            try {
                $file = $this->service->files->create($fileMetadata, [
                    'data' => $content,
                    'mimeType' => $mimeType,
                    'uploadType' => 'multipart',
                    'fields' => 'id, name, webViewLink, webContentLink',
                    'supportsAllDrives' => true,
                ]);
            } catch (\Throwable $primaryEx) {
                Log::warning("GoogleDriveService: Upload ke parent {$folderId} gagal: " . $primaryEx->getMessage() . ". Mencoba fallback parent...");

                // Fallback 1: Coba upload ke Master Folder jika parent awal gagal
                $master = Setting::get('google_drive_master_folder');
                $masterId = self::extractFolderId($master);

                if ($masterId && $masterId !== $folderId) {
                    try {
                        $fileMetadata->setParents([$masterId]);
                        $file = $this->service->files->create($fileMetadata, [
                            'data' => $content,
                            'mimeType' => $mimeType,
                            'uploadType' => 'multipart',
                            'fields' => 'id, name, webViewLink, webContentLink',
                            'supportsAllDrives' => true,
                        ]);
                    } catch (\Throwable $masterEx) {
                        Log::warning("GoogleDriveService: Upload ke Master Folder gagal: " . $masterEx->getMessage() . ". Fallback ke root...");
                    }
                }

                // Fallback 2: Jika masih belum berhasil, coba upload ke Service Account root (tanpa parent)
                if (!$file) {
                    try {
                        $fileMetadata->setParents([]);
                        $file = $this->service->files->create($fileMetadata, [
                            'data' => $content,
                            'mimeType' => $mimeType,
                            'uploadType' => 'multipart',
                            'fields' => 'id, name, webViewLink, webContentLink',
                            'supportsAllDrives' => true,
                        ]);
                    } catch (\Throwable $rootEx) {
                        $rawMsg = $primaryEx->getMessage() . ' ' . $rootEx->getMessage();
                        if (str_contains($rawMsg, 'storageQuotaExceeded') || str_contains($rawMsg, 'Service Accounts do not have storage quota')) {
                            $robotEmail = $this->getServiceAccountEmail() ?: 'sipega-drive@sipega-bpmp-kaltim.iam.gserviceaccount.com';
                            self::$lastError = "Google Drive menolak unggahan (Error Kuota Service Account): Akun robot Google tidak memiliki kuota pribadi di 'Drive Saya' biasa. Solusi: Gunakan 'Drive Bersama' (Shared Drive Google Workspace) dan undang email robot ({$robotEmail}) sebagai 'Pengelola Konten' (Content Manager).";
                        } else {
                            self::$lastError = $primaryEx->getMessage();
                        }
                        Log::error("GoogleDriveService: Seluruh jalur upload Drive gagal: " . $rootEx->getMessage());
                        throw $rootEx;
                    }
                }
            }

            if ($makePublic && !empty($file->id)) {
                try {
                    $permission = new Permission([
                        'type' => 'anyone',
                        'role' => 'reader',
                    ]);
                    $this->service->permissions->create($file->id, $permission, [
                        'supportsAllDrives' => true,
                    ]);
                } catch (\Throwable $e) {
                    Log::warning('GoogleDriveService: Gagal set permission publik: ' . $e->getMessage());
                }
            }

            return [
                'id' => $file->id,
                'name' => $file->name,
                'web_view_link' => $file->webViewLink,
                'web_content_link' => $file->webContentLink,
            ];

        } catch (\Throwable $e) {
            self::$lastError = $e->getMessage();
            Log::error('GoogleDriveService Upload Error: ' . $e->getMessage(), [
                'file' => $fileName,
                'folder' => $folderUrlOrId
            ]);
            return null;
        }
    }

    /**
     * Uji koneksi ke Google Drive API
     */
    public function testConnection(): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'File credentials service-account.json tidak ditemukan atau gagal dimuat.'
            ];
        }

        try {
            $about = $this->service->about->get(['fields' => 'user']);
            return [
                'success' => true,
                'email' => $about->getUser()->getEmailAddress(),
                'message' => 'Koneksi ke Google Drive API Berhasil!'
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Error koneksi Google Drive: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Diagnosis Menyeluruh Step-by-Step Google Drive (Koneksi, Izin Folder Induk, Subfolder Pegawai, & Uji Tulis)
     */
    public function diagnoseDrive(?User $user = null): array
    {
        $steps = [];

        // Step 1: Kredensial & Library
        if (!self::isLibraryInstalled()) {
            return [
                'success' => false,
                'steps' => [
                    ['name' => '1. Library Google API Client', 'status' => false, 'message' => 'Library google/apiclient belum terpasang di vendor.']
                ]
            ];
        }

        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'steps' => [
                    ['name' => '1. Kredensial Service Account', 'status' => false, 'message' => 'Kredensial service-account.json belum diunggah atau tidak valid.']
                ]
            ];
        }

        $steps[] = ['name' => '1. Kredensial Service Account', 'status' => true, 'message' => 'Kredensial aktif di server.'];

        // Step 2: Cek Koneksi API & Email Robot
        $robotEmail = $this->getServiceAccountEmail();
        try {
            $about = $this->service->about->get(['fields' => 'user']);
            if ($about->getUser()) {
                $robotEmail = $about->getUser()->getEmailAddress() ?: $robotEmail;
            }
            $steps[] = ['name' => '2. Koneksi Google Drive API', 'status' => true, 'message' => "Terhubung sebagai robot: {$robotEmail}"];
        } catch (\Throwable $e) {
            $steps[] = ['name' => '2. Koneksi Google Drive API', 'status' => false, 'message' => "Gagal terhubung ke Google: " . $e->getMessage()];
            return ['success' => false, 'steps' => $steps];
        }

        // Step 3: Cek Folder Induk (Master Folder)
        $masterUrl = Setting::get('google_drive_master_folder');
        $masterId = self::extractFolderId($masterUrl);

        if (empty($masterId)) {
            $steps[] = ['name' => '3. Folder Induk (Master Folder)', 'status' => false, 'message' => 'Tautan / ID Folder Induk belum diisi di Pengaturan Sistem.'];
            return ['success' => false, 'steps' => $steps];
        }

        try {
            $folder = $this->service->files->get($masterId, [
                'fields' => 'id, name, capabilities',
                'supportsAllDrives' => true
            ]);
            
            $canAddChildren = $folder->getCapabilities() ? $folder->getCapabilities()->getCanAddChildren() : true;
            if ($canAddChildren) {
                $steps[] = ['name' => '3. Akses Folder Induk (' . $folder->getName() . ')', 'status' => true, 'message' => "Berhasil diakses dengan izin EDITOR!"];
            } else {
                $steps[] = [
                    'name' => '3. Akses Folder Induk (' . $folder->getName() . ')',
                    'status' => false,
                    'message' => "Robot hanya memiliki izin Lihat (Viewer). Mohon buka Google Drive > klik kanan folder > Bagikan > ubah peran {$robotEmail} menjadi EDITOR!"
                ];
                return ['success' => false, 'steps' => $steps];
            }
        } catch (\Google\Service\Exception $e) {
            if ($e->getCode() === 404) {
                $steps[] = [
                    'name' => '3. Akses Folder Induk (ID: ' . $masterId . ')',
                    'status' => false,
                    'message' => "Folder Induk TIDAK DITEMUKAN / BELUM DIBAGIKAN! Mohon buka Google Drive, klik kanan folder 'BUKTI KINERJA 2026' > pilih Bagikan (Share) > tambahkan: {$robotEmail} sebagai EDITOR."
                ];
            } else {
                $steps[] = ['name' => '3. Akses Folder Induk (ID: ' . $masterId . ')', 'status' => false, 'message' => "Akses ditolak: " . $e->getMessage()];
            }
            return ['success' => false, 'steps' => $steps];
        }

        // Step 4: Uji Coba Tulis (Create File Test) di Folder Induk
        try {
            $testFileMetadata = new DriveFile([
                'name' => 'sipega_test_connection_' . time() . '.txt',
                'parents' => [$masterId]
            ]);
            $testContent = "Koneksi Google Drive SIPEGA Berhasil pada " . date('Y-m-d H:i:s');
            $createdTestFile = $this->service->files->create($testFileMetadata, [
                'data' => $testContent,
                'mimeType' => 'text/plain',
                'uploadType' => 'multipart',
                'fields' => 'id, name, webViewLink',
                'supportsAllDrives' => true,
            ]);

            // Hapus file uji coba agar tidak mengotori Drive
            if (!empty($createdTestFile->id)) {
                try {
                    $this->service->files->delete($createdTestFile->id, ['supportsAllDrives' => true]);
                } catch (\Throwable $delEx) {}
            }

            $steps[] = ['name' => '4. Uji Coba Tulis & Unggah File', 'status' => true, 'message' => "SUKSES! Robot dapat membuat dan mengunggah berkas ke Google Drive."];
        } catch (\Throwable $e) {
            $steps[] = [
                'name' => '4. Uji Coba Tulis & Unggah File',
                'status' => false,
                'message' => "Gagal membuat berkas uji coba: " . $e->getMessage() . ". Pastikan role email robot adalah EDITOR pada folder induk."
            ];
            return ['success' => false, 'steps' => $steps];
        }

        // Step 5: Jika ada user, cari / hubungkan folder pegawai
        if ($user) {
            try {
                $userFolder = $this->findOrCreateUserFolder($user, $masterId);
                if ($userFolder && !empty($userFolder['web_view_link'])) {
                    $steps[] = [
                        'name' => '5. Folder Pegawai (' . $user->name . ')',
                        'status' => true,
                        'message' => "Folder pegawai siap: <a href='{$userFolder['web_view_link']}' target='_blank' style='color:#2563eb;text-decoration:underline;'>Buka Folder Pegawai di Drive ↗</a>"
                    ];
                } else {
                    $steps[] = [
                        'name' => '5. Folder Pegawai (' . $user->name . ')',
                        'status' => false,
                        'message' => "Gagal mendeteksi / membuat subfolder pegawai di dalam Folder Induk."
                    ];
                }
            } catch (\Throwable $e) {
                $steps[] = ['name' => '5. Folder Pegawai', 'status' => false, 'message' => $e->getMessage()];
            }
        }

        return ['success' => true, 'steps' => $steps];
    }
}
