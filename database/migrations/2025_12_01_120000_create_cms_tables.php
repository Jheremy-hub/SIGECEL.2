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
        // 1. cms_users
        if (!Schema::hasTable('cms_users')) {
            Schema::create('cms_users', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('apellidos')->nullable();
                $table->string('cargo')->nullable();
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->string('photo')->nullable();
                $table->string('celular')->nullable();
                $table->integer('id_cms_privileges')->nullable();
                $table->integer('id_cargo')->nullable();
                $table->integer('id_sede')->nullable();
                $table->integer('id_estado')->default(1);
                $table->rememberToken();
                $table->timestamps();
            });
        }

        // 2. cms_user_roles
        if (!Schema::hasTable('cms_user_roles')) {
            Schema::create('cms_user_roles', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('role');
                $table->integer('hierarchy_level');
                $table->integer('parent_role_id')->nullable();
                $table->timestamp('assigned_at')->nullable();
                // No timestamps
            });
        }

        // 3. cms_auto_login_tokens
        if (!Schema::hasTable('cms_auto_login_tokens')) {
            Schema::create('cms_auto_login_tokens', function (Blueprint $table) {
                $table->id();
                $table->string('token');
                $table->unsignedBigInteger('user_id');
                $table->string('ip_address')->nullable();
                $table->string('user_agent')->nullable();
                $table->timestamp('expires_at');
                $table->timestamp('used_at')->nullable();
                $table->timestamps();
            });
        }

        // 4. cms_user_documents
        if (!Schema::hasTable('cms_user_documents')) {
            Schema::create('cms_user_documents', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('document_type');
                $table->string('sender');
                $table->string('institution')->nullable();
                $table->string('subject');
                $table->text('content');
                $table->string('file_path');
                $table->string('file_name');
                $table->string('file_type');
                $table->integer('file_size');
                $table->string('document_code')->nullable();
                $table->text('meta')->nullable();
                $table->timestamps();
            });
        }

        // 5. cms_user_messages
        if (!Schema::hasTable('cms_user_messages')) {
            Schema::create('cms_user_messages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('sender_id');
                $table->unsignedBigInteger('receiver_id');
                $table->unsignedBigInteger('intended_receiver_id')->nullable();
                $table->unsignedBigInteger('approver_id')->nullable();
                $table->string('subject');
                $table->text('message');
                $table->string('file_path')->nullable();
                $table->string('file_name')->nullable();
                $table->string('file_type')->nullable();
                $table->integer('file_size')->nullable();
                $table->boolean('is_read')->default(0);
                $table->string('status')->default('sent');
                $table->timestamps();
            });
        }

        // 6. cms_user_message_forwards
        if (!Schema::hasTable('cms_user_message_forwards')) {
            Schema::create('cms_user_message_forwards', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('original_message_id');
                $table->unsignedBigInteger('forwarded_message_id');
                $table->unsignedBigInteger('forwarded_by');
                $table->unsignedBigInteger('forwarded_to');
                $table->timestamps();
            });
        }

        // 7. cms_user_message_logs
        if (!Schema::hasTable('cms_user_message_logs')) {
            Schema::create('cms_user_message_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('message_id');
                $table->unsignedBigInteger('user_id');
                $table->string('action');
                $table->text('details')->nullable();
                $table->timestamps();
            });
        }

        // 8. cms_message_approvals
        if (!Schema::hasTable('cms_message_approvals')) {
            Schema::create('cms_message_approvals', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('message_id');
                $table->unsignedBigInteger('approver_id');
                $table->string('decision');
                $table->text('note')->nullable();
                $table->timestamp('decided_at')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_message_approvals');
        Schema::dropIfExists('cms_user_message_logs');
        Schema::dropIfExists('cms_user_message_forwards');
        Schema::dropIfExists('cms_user_messages');
        Schema::dropIfExists('cms_user_documents');
        Schema::dropIfExists('cms_auto_login_tokens');
        Schema::dropIfExists('cms_user_roles');
        Schema::dropIfExists('cms_users');
    }
};
