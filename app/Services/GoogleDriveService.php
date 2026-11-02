<?php

namespace App\Services;

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
     * Upload berkas bukti ke Google Drive folder pegawai
     *
     * @param string $localFilePath Path fisik berkas di server
     * @param string $fileName Nama berkas yang diinginkan di Google Drive
     * @param string|null $folderUrlOrId URL atau ID folder Google Drive tujuan
     * @param bool $makePublic Apakah file langsung dibuat publik (bisa dibuka oleh penilai e-Kinerja)
     * @return array|null Berisi file id, webViewLink, webContentLink, atau null jika gagal
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
