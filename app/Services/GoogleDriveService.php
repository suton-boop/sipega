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
                Log::error('GoogleDriveService Init Failed: ' . $e->getMessage());
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
        if (!file_exists($this->credentialsPath)) {
            return null;
        }

        $json = json_decode(file_get_contents($this->credentialsPath), true);
        return $json['client_email'] ?? null;
    }

    /**
     * Ekstrak ID Folder Google Drive dari URL
     * Mendukung format:
     * - https://drive.google.com/drive/folders/1A2B3C...
     * - https://drive.google.com/drive/u/0/folders/1A2B3C...
     * - ID langsung: 1A2B3C...
     */
    public static function extractFolderId(?string $urlOrId): ?string
    {
        if (empty($urlOrId)) {
            return null;
        }

        $trimmed = trim($urlOrId);

        // Jika berbentuk URL Drive folder
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
     * Buat folder baru di Google Drive (misalnya subfolder untuk pegawai)
     *
     * @param string $folderName Nama folder
     * @param string|null $parentFolderUrlOrId ID atau URL folder induk (Master Folder)
     * @param bool $makePublic Apakah folder dapat diakses siapa saja dengan link
     * @return array|null Berisi ['id', 'name', 'web_view_link']
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
            ]);

            // Set permission: Siapa saja yang punya link dapat membuka folder bukti (reader)
            if ($makePublic && !empty($folder->id)) {
                try {
                    $permission = new Permission([
                        'type' => 'anyone',
                        'role' => 'reader',
                    ]);
                    $this->service->permissions->create($folder->id, $permission);
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
     * Batch Generate Folder untuk semua pegawai yang belum memiliki folder
     */
    public function batchGenerateFolders(?string $masterFolderId = null): array
    {
        $master = $masterFolderId ?: Setting::get('google_drive_master_folder');
        if (empty($master)) {
            return [
                'success' => false,
                'message' => 'Folder Induk Google Drive (Master Folder) belum diatur di Pengaturan Sistem.'
            ];
        }

        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Google Drive API belum terkonfigurasi (file service-account.json tidak ditemukan).'
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
            $result = $this->generateUserFolder($emp, $master);
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
            'message' => "Berhasil membuat {$createdCount} folder Google Drive baru untuk pegawai."
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
            ]);

            // Set permission: Siapa saja yang memiliki link bisa melihat bukti tanpa login
            if ($makePublic && !empty($file->id)) {
                try {
                    $permission = new Permission([
                        'type' => 'anyone',
                        'role' => 'reader',
                    ]);
                    $this->service->permissions->create($file->id, $permission);
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
