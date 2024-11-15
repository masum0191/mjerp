<?php

namespace App\Models;
use DB;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable =[
        "reference_no", "user_id", "cash_register_id", "table_id", "queue", "customer_id", "warehouse_id", "biller_id", "item", "total_qty", "total_discount", "total_tax", "total_price", "order_tax_rate", "order_tax", "order_discount_type", "order_discount_value", "order_discount", "coupon_id", "coupon_discount", "shipping_cost", "grand_total", "currency_id", "exchange_rate", "sale_status", "payment_status", "billing_name", "billing_phone", "billing_email", "billing_address", "billing_city", "billing_state", "billing_country", "billing_zip", "shipping_name", "shipping_phone", "shipping_email", "shipping_address", "shipping_city", "shipping_state","shipping_country","shipping_zip", "sale_type", "paid_amount", "document", "sale_note", "staff_note", "created_at", "woocommerce_order_id"
    ];
    public static function getSalesByDateAndBiller(string $startDate, string $endDate)
    {
        return self::select(
            DB::raw('DATE(created_at) as sale_date'),
            'biller_id',
            DB::raw('COUNT(*) as total_count'),
            DB::raw('SUM(total_price) as total_sales'),
            DB::raw('SUM(total_discount) as total_discount'),
            DB::raw('SUM(total_tax) as total_tax'),
            DB::raw('SUM(grand_total) as grand_total'),
            DB::raw('SUM(paid_amount) as paid_amount')
        )
        ->whereDate('sales.created_at', '>=' ,$startDate)->whereDate('sales.created_at', '<=' ,$endDate)
        //->whereBetween('created_at', [$startDate, $endDate])
        ->groupBy('sale_date', 'biller_id')
        ->orderBy('sale_date')
        ->orderBy('biller_id')
        ->get();
    }
    public function products()
    {
        return $this->belongsToMany('App\Models\Product', 'product_sales');
    }

    public function biller()
    {
        return $this->belongsTo('App\Models\Biller');
    }

    public function customer()
    {
        return $this->belongsTo('App\Models\Customer');
    }

    public function warehouse()
    {
        return $this->belongsTo('App\Models\Warehouse');
    }

    public function table()
    {
        return $this->belongsTo('App\Models\Table');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function currency()
    {
        return $this->belongsTo('App\Models\Currency');
    }
}
