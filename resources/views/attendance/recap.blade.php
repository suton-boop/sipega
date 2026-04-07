<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-navy-800 leading-tight">
            {{ __('Rekapitulasi Kehadiran Perorangan') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filter & Print Actions -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 p-6 flex flex-wrap justify-between items-center">
                <form method="GET" action="{{ route('attendance.recap', $user->id) }}" class="flex items-center space-x-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Pilih Bulan</label>
                        <input type="month" name="month" value="{{ $monthYear }}" onchange="this.form.submit()" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </form>
                
                <div class="space-x-2">
                    <button onclick="window.print()" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                        Cetak Laporan
                    </button>
                    <a href="{{ route('tukin.download_slip', $user->id) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                        Lihat Slip Tukin
                    </a>
                </div>
            </div>

            <!-- Employee Info Header -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 p-6 border-l-4 border-indigo-500">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div>
                        <p class="text-sm text-gray-500">Nama Pegawai</p>
                        <p class="text-lg font-bold text-gray-900">{{ $user->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">NIP</p>
                        <p class="text-lg font-bold text-gray-900">{{ $user->nip ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Kelas Jabatan</p>
                        <p class="text-lg font-bold text-gray-900">KJ {{ $user->jobClass->class_name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Unit Kerja</p>
                        <p class="text-lg font-bold text-gray-900">{{ $user->unit_kerja ?? 'BPMP Kaltim' }}</p>
                    </div>
                </div>
            </div>

            <!-- Summary Table (Excel Style) -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 border border-gray-300">
                    <thead style="background-color: #4A90E2;" class="text-white">
                        <tr>
                            <th class="px-3 py-3 border border-blue-400 text-center text-xs font-bold uppercase tracking-wider">No.</th>
                            <th class="px-6 py-3 border border-blue-400 text-left text-xs font-bold uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 border border-blue-400 text-center text-xs font-bold uppercase tracking-wider">Kedatangan</th>
                            <th class="px-6 py-3 border border-blue-400 text-center text-xs font-bold uppercase tracking-wider">Kepulangan</th>
                            <th class="px-6 py-3 border border-blue-400 text-center text-xs font-bold uppercase tracking-wider">Keterangan</th>
                            <th class="px-4 py-3 border border-blue-400 text-center text-xs font-bold uppercase tracking-wider bg-blue-700">TL (Menit)</th>
                            <th class="px-4 py-3 border border-blue-400 text-center text-xs font-bold uppercase tracking-wider bg-blue-700">PSW (Menit)</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($recapData as $index => $row)
                        <tr class="{{ $row['status'] == 'ALPA' ? 'bg-red-50' : ($row['status'] == 'Tugas Luar (ST)' ? 'bg-green-50' : '') }}">
                            <td class="px-3 py-2 border border-gray-300 text-center text-sm font-medium text-gray-900">{{ $index + 1 }}</td>
                            <td class="px-6 py-2 border border-gray-300 text-sm text-gray-900">{{ \Carbon\Carbon::parse($row['date'])->translatedFormat('d F Y') }}</td>
                            <td class="px-6 py-2 border border-gray-300 text-center text-sm text-gray-900">{{ $row['check_in'] ?? '-' }}</td>
                            <td class="px-6 py-2 border border-gray-300 text-center text-sm text-gray-900">{{ $row['check_out'] ?? '-' }}</td>
                            <td class="px-6 py-2 border border-gray-300 text-center text-sm">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $row['status'] == 'Hadir' ? 'bg-blue-100 text-blue-800' : 
                                       ($row['status'] == 'ALPA' ? 'bg-red-100 text-red-800' : 
                                       ($row['status'] == 'Tugas Luar (ST)' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800')) }}">
                                    {{ $row['status'] }}
                                </span>
                            </td>
                            <td class="px-4 py-2 border border-gray-300 text-center text-sm text-gray-900">{{ $row['tl_minutes'] > 0 ? $row['tl_minutes'] : '-' }}</td>
                            <td class="px-4 py-2 border border-gray-300 text-center text-sm text-gray-900">{{ $row['psw_minutes'] > 0 ? $row['psw_minutes'] : '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <!-- Footer Aggregation -->
                    <tfoot class="bg-gray-100 font-bold">
                        <tr>
                            <td colspan="4" class="px-6 py-3 border border-gray-300 text-right">JUMLAH KEHADIRAN:</td>
                            <td class="px-6 py-3 border border-gray-300 text-center text-indigo-600">{{ $summary['total_present'] }} Hari</td>
                            <td class="px-4 py-3 border border-gray-300 text-center text-red-600">{{ $summary['total_tl_minutes'] }}</td>
                            <td class="px-4 py-3 border border-gray-300 text-center text-red-600">{{ $summary['total_psw_minutes'] }}</td>
                        </tr>
                        <tr>
                            <td colspan="4" class="px-6 py-3 border border-gray-300 text-right">TOTAL ALPA & ST:</td>
                            <td class="px-6 py-3 border border-gray-300 text-center">
                                <span class="text-red-700">Alpa: {{ $summary['total_alpa'] }}</span> | 
                                <span class="text-green-700">ST: {{ $summary['total_st'] }}</span>
                            </td>
                            <td colspan="2" class="px-4 py-3 border border-gray-300"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Print Styles Override -->
    <style>
        @media print {
            .py-12 { padding-top: 0 !important; padding-bottom: 0 !important; }
            button, a, form { display: none !important; }
            .shadow-sm { box-shadow: none !important; }
            .max-w-7xl { max-width: 100% !important; margin: 0 !important; padding: 0 !important; }
            table { border-collapse: collapse !important; width: 100% !important; font-size: 10pt !important; }
            th, td { border: 1px solid #000 !important; padding: 4px !important; }
            .bg-gray-50 { background: white !important; }
            thead { background-color: #f3f4f6 !important; color: black !important; }
            thead th { border: 1px solid #000 !important; }
        }
    </style>
</x-app-layout>
