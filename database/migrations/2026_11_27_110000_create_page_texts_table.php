<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Super-admin overrides for the wording on the public pages. A row only
     * exists when the text differs from the default in config/page_content.php.
     */
    public function up(): void
    {
        Schema::create('page_texts', function (Blueprint $table): void {
            $table->id();
            $table->string('page', 50);
            $table->string('key', 80);
            $table->text('value');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['page', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_texts');
    }
};
