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
        // 1. Add role & is_owner to member_details table if not present
        if (Schema::hasTable('member_details')) {
            Schema::table('member_details', function (Blueprint $table) {
                if (!Schema::hasColumn('member_details', 'role')) {
                    $table->string('role', 32)->default('user')->after('name')->index();
                }
                if (!Schema::hasColumn('member_details', 'is_owner')) {
                    $table->boolean('is_owner')->default(false)->after('role')->index();
                }
            });
        }

        // 2. Add role & is_owner to users table if not present
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'role')) {
                    $table->string('role', 32)->default('user')->after('name')->index();
                }
                if (!Schema::hasColumn('users', 'is_owner')) {
                    $table->boolean('is_owner')->default(false)->after('role')->index();
                }
            });
        }

        // 3. Create isolated Owner Personalization Profile table
        if (!Schema::hasTable('ai_owner_profiles')) {
            Schema::create('ai_owner_profiles', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('member_id')->unique()->index();
                $table->string('owner_name')->default('Deepanshu Saini');
                $table->string('role')->default('owner');
                $table->json('languages')->nullable();
                $table->json('communication_preferences')->nullable();
                $table->json('technical_stack')->nullable();
                $table->json('active_projects')->nullable();
                $table->json('personal_preferences')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_owner_profiles');

        if (Schema::hasTable('member_details')) {
            Schema::table('member_details', function (Blueprint $table) {
                if (Schema::hasColumn('member_details', 'is_owner')) {
                    $table->dropColumn('is_owner');
                }
                if (Schema::hasColumn('member_details', 'role')) {
                    $table->dropColumn('role');
                }
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'is_owner')) {
                    $table->dropColumn('is_owner');
                }
                if (Schema::hasColumn('users', 'role')) {
                    $table->dropColumn('role');
                }
            });
        }
    }
};
