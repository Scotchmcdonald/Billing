<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement("
            CREATE OR REPLACE VIEW cfo_margin_reports AS
            SELECT
                ili.invoice_id,
                ili.product_id,
                p.sku,
                p.name as product_name,
                ili.quantity,
                ili.unit_price as billed_unit_price,
                p.cost_price as current_unit_cost,
                (ili.unit_price - p.cost_price) as unit_margin,
                ((ili.unit_price - p.cost_price) * ili.quantity) as total_line_margin,
                ili.created_at as invoiced_at
            FROM
                invoice_line_items ili
            JOIN
                products p ON ili.product_id = p.id
        ");
    }

    public function down()
    {
        DB::statement("DROP VIEW IF EXISTS cfo_margin_reports");
    }
};
