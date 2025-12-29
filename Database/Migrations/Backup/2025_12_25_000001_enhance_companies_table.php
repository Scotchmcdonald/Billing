<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->enum('pricing_tier', ['standard', 'non_profit', 'consumer'])->default('standard')->after('email');
            $table->string('tax_id', 50)->nullable()->after('pricing_tier');
            $table->text('billing_address')->nullable()->after('tax_id'); // JSON: street, city, state, zip, country
            $table->unsignedBigInteger('primary_contact_id')->nullable()->after('billing_address');
            $table->json('settings')->nullable()->after('primary_contact_id');
            $table->decimal('margin_floor_percent', 5, 2)->default(20.00)->after('settings');
            $table->boolean('is_active')->default(true)->after('margin_floor_percent');

            $table->index('pricing_tier');
            $table->index('is_active');
            
            // Assuming users table exists for FK, but to be safe and avoid circular dependency issues during fresh migrations if users table is created later (unlikely but possible in modular), we might want to add FK in a separate migration or check existence. 
            // However, standard practice is users table exists.
            // $table->foreign('primary_contact_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'pricing_tier',
                'tax_id',
                'billing_address',
                'primary_contact_id',
                'settings',
                'margin_floor_percent',
                'is_active'
            ]);
        });
    }
};
