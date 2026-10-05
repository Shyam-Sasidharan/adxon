<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integrations', function (Blueprint $table) {
            $table->id();
            $table->string('account_id', 100);
            $table->string('integration_type', 20);
            $table->text('head_code');
            $table->text('body_code')->nullable();
            $table->string('status', 10)->default('inactive');
            $table->string('created_by');
            $table->string('updated_by');
            $table->timestamps();
            $table->unique(['account_id', 'integration_type']);
        });
        Schema::create('integration_events', function (Blueprint $table) {
            $table->id();
            $table->string('account_id', 100);
            $table->string('integration_type', 20);
            $table->string('action', 30);
            $table->string('actor');
            $table->timestamp('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_events');
        Schema::dropIfExists('integrations');
    }
};
