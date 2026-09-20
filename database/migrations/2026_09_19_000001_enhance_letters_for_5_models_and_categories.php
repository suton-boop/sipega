<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('letters', function (Blueprint $table) {
            if (!Schema::hasColumn('letters', 'st_model')) {
                $table->string('st_model')->default('model_1')->after('type'); // model_1, model_2, model_3, model_4, model_5
            }
            if (!Schema::hasColumn('letters', 'category')) {
                $table->enum('category', ['DLK', 'DLP', 'DLN'])->default('DLK')->after('st_model'); // DLK: Kantor, DLP: Pusat, DLN: Kemitraan
            }
            if (!Schema::hasColumn('letters', 'invitation_from')) {
                $table->text('invitation_from')->nullable()->after('basis');
            }
            if (!Schema::hasColumn('letters', 'invitation_number')) {
                $table->string('invitation_number')->nullable()->after('invitation_from');
            }
            if (!Schema::hasColumn('letters', 'invitation_date')) {
                $table->date('invitation_date')->nullable()->after('invitation_number');
            }
            if (!Schema::hasColumn('letters', 'invitation_subject')) {
                $table->text('invitation_subject')->nullable()->after('invitation_date');
            }
            if (!Schema::hasColumn('letters', 'dipa_source')) {
                $table->text('dipa_source')->nullable()->after('invitation_subject');
            }
            if (!Schema::hasColumn('letters', 'show_keterangan')) {
                $table->boolean('show_keterangan')->default(false)->after('dipa_source');
            }
        });

        Schema::table('letter_user', function (Blueprint $table) {
            if (!Schema::hasColumn('letter_user', 'custom_role')) {
                $table->string('custom_role')->nullable()->after('user_id'); // Narasumber, Panitia, etc.
            }
            if (!Schema::hasColumn('letter_user', 'keterangan')) {
                $table->string('keterangan')->nullable()->after('custom_role'); // SPMI, LITNUM, DIGITALISASI, etc.
            }
            if (!Schema::hasColumn('letter_user', 'city_destination')) {
                $table->string('city_destination')->nullable()->after('keterangan'); // Kab/Kota (khusus Model 4)
            }
            if (!Schema::hasColumn('letter_user', 'venue')) {
                $table->string('venue')->nullable()->after('city_destination'); // Tempat Kegiatan per orang
            }
            if (!Schema::hasColumn('letter_user', 'execution_dates')) {
                $table->string('execution_dates')->nullable()->after('venue'); // Tanggal pelaksanaan per orang
            }
            if (!Schema::hasColumn('letter_user', 'person_in_charge')) {
                $table->string('person_in_charge')->nullable()->after('execution_dates'); // Penanggung Jawab
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('letters', function (Blueprint $table) {
            $table->dropColumn([
                'st_model',
                'category',
                'invitation_from',
                'invitation_number',
                'invitation_date',
                'invitation_subject',
                'dipa_source',
                'show_keterangan'
            ]);
        });

        Schema::table('letter_user', function (Blueprint $table) {
            $table->dropColumn([
                'custom_role',
                'keterangan',
                'city_destination',
                'venue',
                'execution_dates',
                'person_in_charge'
            ]);
        });
    }
};
