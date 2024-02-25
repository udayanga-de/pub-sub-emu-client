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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('topic_id')->nullable();
            $table->unsignedBigInteger('subscription_id')->nullable();
            $table->boolean('sync')->default(0);
            $table->text('message')->nullable();
            $table->text('raw_message')->nullable();
            $table->text('raw_id')->nullable();
            $table->string('ack_id')->nullable();
            $table->boolean('ack')->default(0);
            $table->timestamp('ack_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
