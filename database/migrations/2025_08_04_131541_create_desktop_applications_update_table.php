<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\DesktopApplication;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop old columns
        if (Schema::hasColumns('desktop_applications', ['mac_intel_file_path', 'mac_silicon_file_path'])) {
            Schema::dropColumns('desktop_applications', ['mac_intel_file_path', 'mac_silicon_file_path']);
        }

        if (!Schema::hasColumn('desktop_applications', 'mac_file_path')) {
            Schema::table('desktop_applications', function (Blueprint $table) {
                $table->string('mac_file_path')->nullable();
            });
        }

        // Update the desktop application
        $desktopApplication = DesktopApplication::first();

        if ($desktopApplication) {
            $desktopApplication->update([
                'windows_file_path' => DesktopApplication::WINDOWS_FILE_PATH,
                'mac_file_path' => DesktopApplication::MAC_FILE_PATH,
                'linux_file_path' => DesktopApplication::LINUX_FILE_PATH,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('desktop_applications');
    }
};
