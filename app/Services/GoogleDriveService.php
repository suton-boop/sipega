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

        if (file_exists($this->credentialsPath)) {
            try {
                $this->client = new Client();
                $this->client->setAuthConfig($this->credentialsPath);
                $this->client->addScope(Drive::DRIVE);
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
            Log::warning('GoogleDriveService: Credentials belum siap.');
            return null;
        }

        if (!file_exists($localFilePath)) {
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
}
