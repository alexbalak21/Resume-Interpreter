<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->string('status')->default('draft')->after('reference');
            $table->foreignId('parent_id')->nullable()->after('status')
                  ->constrained('documents')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['status', 'parent_id']);
        });
    }
};
