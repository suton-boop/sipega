<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\TravelReport;
use App\Models\Letter;

class TravelReportSampleSeeder extends Seeder
{
    public function run(): void
    {
        // Cari user Sutono atau buat/ambil user pertama
        $user = User::where('name', 'like', '%sutono%')
            ->orWhere('nip', '198109282002121005')
            ->first();

        if (!$user) {
            $user = User::first();
        }

        if (!$user) {
            $user = User::create([
                'name' => 'Sutono,S.Kom',
                'email' => 'sutono@bpmpkaltim.kemdikbud.go.id',
                'password' => bcrypt('password'),
                'role' => 'Pegawai',
                'nip' => '198109282002121005',
                'position' => 'Widyaprada Ahli Pertama',
                'performance_score' => 95,
                'performance_color' => 'Biru',
            ]);
        }

        // Cek jika sample report sudah ada
        $existing = TravelReport::where('st_number', 'like', '%1188/C6.24/DM.00.02/2026%')->first();
        if ($existing) {
            return;
        }

        TravelReport::create([
            'user_id' => $user->id,
            'employee_name' => 'Sutono,S.Kom',
            'employee_nip' => '198109282002121005',
            'employee_position' => 'Widyaprada Ahli Pertama',
            'st_number' => '1188/C6.24/DM.00.02/2026 Tanggal 24 September 2026',
            'st_date' => '2026-09-24',
            'activity_name' => 'Verifikasi dan Validasi Data Pokok Pendidikan Tahun 2026',
            'start_date' => '2026-09-29',
            'end_date' => '2026-10-01',
            'date_range_text' => '29 September s.d 1 Oktober 2026',
            'location' => "SMAS Muhammadiyah Tanah Grogot\nSMA negeri 2 Unggulan Tanah Grogot",
            'purpose' => "Tujuan dari verifikasi dan validasi adalah untuk mendapatkan gambaran yang representatif terhadap kondisi kualitas data Dapodik satuan pendidikan. Pengecekan kualitas data dapodik berdasarkan pada 3 indikator utama yaitu:\n- Kelengkapan : Ukuran tingkat keterisian dan kelengkapan setiap entitas data pokok pendidikan\n- Validitas : Ukuran tingkat kesesuaian data dengan standar atau aturan yang telah ditetapkan.\n- Mutakhir : Ukuran kemutakhiran/kebaruan data pokok pendidikan untuk memastikan data yang dikumpulkan melalui data pokok pendidikan adalah data terkini.",
            'target_audience' => 'Responden kegiatan ini adalah Kepala Sekolah dan Operator Dapodik pada Satuan Pendidikan',
            'output' => "Terjaringnya data kondisi real di lapangan terkait 5 entitas Data Pokok Pendidikan yang meliputi :\n- Satuan Pendidikan\n- Sarana dan Prasarana\n- Peserta Didik\n- Pendidik dan Tenaga Kependidikan\n- Rombongan Belajar\nTeridentifikasinya kendala yang dialami oleh satuan Pendidikan dalam mengelola Dapodik;\nDapat menyampaikan analisis penyebab perbedaan data kondisi lapang dengan dapodik;\nMenyampaikan rekomendasi atas hasil verifikasi dan validasi data yang dilakukan.",
            'description' => "Berdasarkan Permendikbud RI No. 31 Tahun 2022 tentang Satu Data Pendidikan, Kebudayaan, Riset, dan Teknologi, Data Pendidikan, Kebudayaan, Riset dan, Teknologi yang selanjutnya disebut Data adalah data di bidang pendidikan, kebudayaan, ilmu pengetahuan, dan teknologi yang dikelola oleh Kementerian. Satu Data Indonesia adalah kebijakan tata kelola data pemerintah untuk menghasilkan data yang akurat, mutakhir, terpadu, dan dapat dipertanggungjawabkan, serta mudah diakses dan dibagipakaikan antar instansi pusat dan instansi daerah melalui pemenuhan Standar Data, Metadata, Interoperabilitas Data, dan menggunakan Kode Referensi dan Data Induk.

Dapodik merupakan sumber data utama pendidikan di Indonesia dan berfungsi sebagai sistem penghubung layanan pemerintah, baik di lingkungan internal Kemendikbudristek maupun lintas Kementerian/Lembaga. Namun, saat ini terdapat isu terkait kualitas Dapodik: ditemukannya anomali pada data satuan pendidikan, data yang diinputkan pada Dapodik tidak sesuai dengan kondisi lapangan. Hal tersebut tentu berdampak pada kurang akuratnya pemanfaatan Dapodik bagi para pemangku kepentingan dalam mengambil keputusan/kebijakan. Oleh karena itu, Balai Penjaminan Mutu Pendidikan Provinsi Kalimantan Timur bermaksud mengawal kualitas Dapodik.

Dalam mengawal kualitas Dapodik, dilakukan penetapan indikator kualitas Dapodik tahun 2026. Adapun indikator kualitas Dapodik tersebut terdiri dari Kelengkapan/Completeness, Validitas/Validity, dan Mutakhir/Up to Date. Dengan adanya penetapan indikator tersebut, diperlukan kolaborasi dengan berbagai pihak untuk mengawal kualitas Dapodik melalui penyusunan Instrumen Uji Petik ke satuan pendidikan.

=== Hasil Pelaksanaan Uji Petik Pada Satuan Pendidikan ===

1. Sekolah SMAS Muhammadiyah Tanah Grogot
- Gambaran Pemahaman terhadap Kebijakan Pendataan:
Berdasarkan hasil uji petik dan telaah terhadap data profil satuan pendidikan, SMAS Muhammadiyah Tanah Grogot telah memiliki dan melakukan pemutakhiran data satuan pendidikan pada Dapodik. Hal ini terlihat dari tersedianya data identitas sekolah, peserta didik, PTK, rombongan belajar, serta sarana dan prasarana pada profil satuan pendidikan.
Hasil pengukuran Indikator Kualitas Data (IKD) menunjukkan bahwa skor IKD sekolah mencapai 96,67%, rombongan belajar 97,14%, peserta didik 78,22%, PTK 89,86%, bangunan 37,57%, ruang 83,67%, dan tanah 76,12%. Fokus penguatan perlu diarahkan pada pemahaman bahwa pendataan Dapodik bukan hanya pemenuhan administrasi, tetapi dasar perencanaan dan pengambilan kebijakan.

- Entitas Satuan Pendidikan:
NPSN 30400178, SMA Swasta di bawah Yayasan, Akreditasi A. Beralamat di Jalan Pangeran Menteri Tanah Grogot No. 96, Kabupaten Paser, Kalimantan Timur. Daya listrik PLN 10.600 VA, internet fiber optic broadband, dan berpartisipasi dalam BOSP. Kualitas: Kelengkapan 100%, Validitas 91,67%, Kemutakhiran 100%, IKD 96,67%.

- Entitas Sarana dan Prasarana:
Data tanah kelengkapan 100%, validitas 66,67%, kemutakhiran 64,84%, IKD 76,12%. Diperlukan verifikasi dokumen kepemilikan. Data prasarana: 8 ruang kelas, 1 perpustakaan, ruang kepsek, ruang guru, ruang TU, ruang UKS, toilet siswa & guru, lab komputer, dan lab IPA. Entitas ruang IKD 83,67%, sedangkan entitas bangunan memperoleh IKD 37,57% (kelengkapan 33,33%, validitas 50%, kemutakhiran 25,24%) sehingga menjadi prioritas pemutakhiran.

- Entitas Peserta Didik:
Semester ganjil 2026/2027 tercatat 230 peserta didik (144 L, 86 P), turun dari semester sebelumnya 235 peserta didik. Skor IKD peserta didik 78,22% (kelengkapan 80,41%, validitas 82,44%, kemutakhiran 70,39%). Tindak lanjut: pemutakhiran berkala atas mutasi/perubahan status siswa.

- Entitas PTK:
Jumlah PTK sebanyak 19 orang (12 guru, 7 tendik), stabil dari semester lalu. IKD PTK 89,86% (kelengkapan 93,57%, validitas 93,98%, kemutakhiran 80,65%).

- Entitas Rombongan Belajar:
Tercatat 7 rombel, skor IKD mencapai 97,14% (kelengkapan 100%, validitas 92,86%, kemutakhiran 100%).

- Kondisi Data Penerima DAK Fisik:
Tidak tercantum status khusus penerima DAK Fisik pada profil Dapodik yang ditelaah sehingga perlu verifikasi sumber data pendukung lebih lanjut.

2. Sekolah SMAN 2 Unggulan Tanah Grogot
- Gambaran Pemahaman terhadap Kebijakan Pendataan:
SMA Negeri 2 Unggulan Tanah Grogot memiliki data yang lengkap berstatus negeri di bawah Pemerintah Daerah dengan Akreditasi A. Hasil pengukuran IKD: sekolah 96,67%, rombongan belajar 100%, peserta didik 75,35%, PTK 88,23%, bangunan 47,73%, ruang 80,09%, dan tanah 79,65%.

- Entitas Satuan Pendidikan:
NPSN 69899868, Akreditasi A, SK Pendirian 420/KEP-362/2015. Daya listrik PLN 33.000 VA, internet fiber optic shared PT Telkom 100 Mbps, berpartisipasi dalam BOSP. Kualitas: Kelengkapan 100%, Validitas 91,67%, Kemutakhiran 100%, IKD 96,67%.

- Entitas Sarana dan Prasarana:
Data tanah IKD 79,65% (kelengkapan 100%, validitas 66,67%, kemutakhiran 76,60%). Prasarana memiliki 22 ruang kelas, perpustakaan, kepsek, guru, 2 TU, UKS, 8 toilet siswa, lab komputer, lab Biologi, Fisika, Kimia, Multi, Bahasa, dan kantin. Entitas ruang IKD 80,09%, entitas bangunan IKD 47,73% (kelengkapan 21,43%, validitas 82,14%, kemutakhiran 28,16%).

- Entitas Peserta Didik:
Semester ganjil 2026/2027 tercatat 585 peserta didik (262 L, 323 P), naik dari 580 peserta didik pada semester genap sebelumnya. Skor IKD peserta didik 75,35% (kelengkapan 82,79%, validitas 84,40%, kemutakhiran 55,86%).

- Entitas PTK:
Tercatat 45 orang (34 guru, 11 tendik), sama dengan semester lalu. Skor IKD PTK 88,23% (kelengkapan 92,32%, validitas 93,56%, kemutakhiran 77,04%).

- Entitas Rombongan Belajar:
Tercatat 18 rombel. Entitas rombel memperoleh nilai sempurna IKD 100% (kelengkapan 100%, validitas 100%, kemutakhiran 100%).

- Kondisi Data Penerima DAK Fisik:
Diperlukan verifikasi terhadap dokumen atau sumber data pendukung yang secara khusus memuat informasi terkait penerima DAK Fisik.",
            'conclusion' => "Berdasarkan hasil uji petik dan telaah data Dapodik pada SMAS Muhammadiyah Tanah Grogot dan SMA Negeri 2 Unggulan Tanah Grogot, secara umum kedua satuan pendidikan telah memiliki data pokok pendidikan yang tersedia pada entitas sekolah, peserta didik, PTK, rombongan belajar, serta sarana dan prasarana. Kualitas data pada entitas sekolah dan rombongan belajar menunjukkan kondisi yang relatif baik pada kedua satuan pendidikan.

Namun demikian, hasil uji petik menunjukkan masih terdapat beberapa entitas yang perlu mendapat perhatian dalam hal kelengkapan, validitas, dan terutama kemutakhiran data. Pada SMAS Muhammadiyah Tanah Grogot, perhatian utama terdapat pada data bangunan, tanah, peserta didik, dan ruang. Sementara pada SMA Negeri 2 Unggulan Tanah Grogot, aspek yang perlu diprioritaskan meliputi bangunan, peserta didik, ruang, dan tanah.

Hasil uji petik juga menunjukkan bahwa data bangunan menjadi salah satu aspek yang paling perlu mendapatkan perhatian pada kedua sekolah. Oleh karena itu, diperlukan verifikasi antara data yang tercatat dalam Dapodik dengan kondisi fisik dan dokumen administrasi satuan pendidikan. Selain itu, pemutakhiran data peserta didik, ruang, tanah, dan PTK perlu dilakukan secara berkala agar data yang tersedia selalu menggambarkan kondisi faktual satuan pendidikan.

Dengan demikian, tindak lanjut hasil uji petik diarahkan pada penguatan pemahaman satuan pendidikan mengenai pentingnya kualitas data, pelaksanaan verifikasi dan validasi secara berkala, pemutakhiran data sesuai kondisi riil, serta penguatan dokumentasi pendukung. Untuk aspek penerima DAK Fisik, diperlukan verifikasi lebih lanjut melalui sumber atau dokumen pendukung karena informasi tersebut tidak tercantum secara khusus dalam profil Dapodik yang ditelaah.",
            'signed_place' => 'Samarinda',
            'signed_date' => '2026-10-03',
            'signature_image' => 'images/sample_report/image1.png',
            'photos' => [
                ['path' => 'images/sample_report/image2.jpeg', 'caption' => 'SMAS MUHAMMADIYAH TANAH GROGOT'],
                ['path' => 'images/sample_report/image3.jpeg', 'caption' => 'SMAN 2 UNGGULAN TANAH GROGOT'],
            ],
            'status' => 'Submitted',
        ]);
    }
}
