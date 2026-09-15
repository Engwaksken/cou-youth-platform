<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::create('organisation_units', function (Blueprint $table) { $table->id(); $table->foreignId('parent_id')->nullable()->constrained('organisation_units')->nullOnDelete(); $table->enum('type',['province','diocese','archdeaconry','parish','local_church','fellowship']); $table->string('name'); $table->string('code')->nullable()->unique(); $table->string('email')->nullable(); $table->string('phone')->nullable(); $table->string('address')->nullable(); $table->decimal('latitude',10,7)->nullable(); $table->decimal('longitude',10,7)->nullable(); $table->boolean('is_active')->default(true); $table->timestamps(); $table->index(['type','parent_id','is_active']); }); } public function down(): void { Schema::dropIfExists('organisation_units'); } };
