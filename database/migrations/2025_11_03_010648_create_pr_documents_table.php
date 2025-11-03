<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pr_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pr_request_id')->constrained('pr_requests')->onDelete('cascade');
            $table->string('document_type'); // Receipt, Quotation, Invoice, Others
            $table->string('file_name');
            $table->string('file_path');
            $table->string('original_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pr_documents');
    }
};