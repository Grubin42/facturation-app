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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('company_name');
            $table->string('company_address')->nullable();
            $table->string('company_city')->nullable();
            $table->string('company_postal_code')->nullable();
            $table->string('company_country')->nullable();
            $table->string('company_phone')->nullable();
            $table->string('company_email')->nullable();
            $table->string('company_website')->nullable();
            $table->string('company_siret')->nullable();
            $table->string('company_vat')->nullable();
            $table->string('invoice_prefix')->default('INV');
            $table->integer('invoice_next_number')->default(1);
            $table->string('quote_prefix')->default('QUO');
            $table->integer('quote_next_number')->default(1);
            $table->integer('payment_terms')->default(30);
            $table->decimal('default_tax_rate', 5, 2)->default(20);
            $table->string('currency')->default('EUR');
            $table->string('logo_path')->nullable();
            $table->string('invoice_footer')->nullable();
            $table->string('quote_footer')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
