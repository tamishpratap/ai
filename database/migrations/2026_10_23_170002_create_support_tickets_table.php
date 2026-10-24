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
        if (!Schema::hasTable('support_tickets')) {
            Schema::create('support_tickets', function (Blueprint $table) {
                $table->increments('id');
                $table->string('ticket_id', 50);
                $table->string('memberid', 50);
                $table->string('masterid', 255)->nullable();
                $table->string('subject', 255);
                $table->enum('support_status', ['New', 'Replied'])->default('New');
                $table->enum('status', ['Open', 'Closed'])->default('Open');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('ticket_texts')) {
            Schema::create('ticket_texts', function (Blueprint $table) {
                $table->increments('id');
                $table->string('ticket_id', 50);
                $table->enum('written_by', ['Admin', 'Member'])->nullable();
                $table->string('text', 1000);
                $table->enum('status', ['Unread', 'Read'])->default('Unread');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_texts');
        Schema::dropIfExists('support_tickets');
    }
};
