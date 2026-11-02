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
     * Cek apakah Google Drive Service Account siap digunakan
     */
    public function isConfigured(): bool
    {
        return $this->client !== null && $this->service !== null;
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
     * Batch Generate Folder untuk seluruh pegawai
     */
    public function batchGenerateFolders(?string $masterFolderId = null): array
    {
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

        $employees = User::realPegawai()
            ->where(function ($q) {
                $q->whereNull('drive_folder_link')->orWhere('drive_folder_link', '');
            })
            ->get();

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

        return [
            'success' => true,
            'total_candidates' => $employees->count(),
            'created_count' => $createdCount,
            'failed_count' => count($failedNames),
            'failed_names' => $failedNames,
            'message' => "Berhasil men-generate {$createdCount} folder Google Drive baru untuk pegawai di dalam folder induk!"
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

            $file = $this->service->files->create($fileMetadata, [
                'data' => $content,
                'mimeType' => $mimeType,
                'uploadType' => 'multipart',
                'fields' => 'id, name, webViewLink, webContentLink',
                'supportsAllDrives' => true,
            ]);

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
