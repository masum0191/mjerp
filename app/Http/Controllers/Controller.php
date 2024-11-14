<?php

namespace App\Http\Controllers;

use App\Traits\FileHandleTrait;
use App\Models\Customer;
use App\Models\Warehouse;
use App\Models\Biller;
use App\Models\Unit;
use App\Models\Sale;
use App\Models\Product;
use App\Models\Account;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

use Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests, FileHandleTrait;

    public function setSuccessMessage($message)
	{
    	session()->flash('message',$message);
    	session()->flash('type','success');
        return redirect()->back();
	}

	public function setErrorMessage($message)
	{
		session()->flash('not_permitted',$message);
		session()->flash('type','danger');
        return redirect()->back();
	}

	// customer api 
	public function customer(){
		$customer=Customer::get();
		return response()->json([
			'cstomer_list'=>$customer
		]);
	}

	// wherhouse api 
	public function wherehose(){
		$wherhouse=Warehouse::select('id','name')->get();
		return response()->json([
			'warhouse_list'=>$wherhouse
		]);
	}

	public function biller(){
		$biller=Biller::select('id','name')->get();
		return response()->json([
			'biller_list'=>$biller
		]);
	}

	public function sales_unit(){
		$unit=Unit::get();
		return response()->json([
			'unit_list'=>$unit
		]);
	}

	public function status(){
		$sale_status=[
			'1'=>'Completed',
			'2'=>'Pending'
		];
		$payment_status=[
			'1'=>'Pending',
			'2'=>'Due',
			'3'=>'Partial',
			'4'=>'Paid',
		];
		$paid_by=[
			'1'=>'Cash',
			'2'=>'Gift Card',
			'3'=>'Credit Card',
			'4'=>'Cheque',
			'5'=>'Paypal',
			'6'=>'Deposit',
			'7'=>'Points',
		];

		return response()->json([
			'sale_status'=>$sale_status,
			'payment_status'=>$payment_status,
			'paid_by_id'=>$paid_by
		]);
	}
	public function getProducts(Request $request)
{
    // Get the 'name' from the request
    $name = $request->input('name');

    // Start the query builder for the Product model
    $query = Product::query();

    // If 'name' is provided, check for it across multiple columns
    if ($name) {
        $query->where('name', 'like', '%' . $name . '%')
              ->orWhere('code', 'like', '%' . $name . '%')
              ->orWhere('barcode_symbology', 'like', '%' . $name . '%');
    }

    // Execute the query and get the results
    $products = $query->get();

    // Return the results as a JSON response
    return response()->json([
        'product_lists' => $products
    ]);
}


	// sale list 
	public function sale_list(){
		$sale=Sale::join('customers', 'sales.customer_id', '=', 'customers.id')
		->join('billers', 'sales.biller_id', '=', 'billers.id')
		->join('product_sales', 'sales.id', '=', 'product_sales.sale_id')
		->where('sales.user_id',Auth::id())->get();

		return response()->json([
			'sale_list'=>$sale
		]);
	}

	public function curency(){
		$curency=Currency::where('is_active',1)->get();
		return response()->json([
			'currency'=>$curency
		]);
	}

	public function biller_sale_report(Request $request){

		
		
		$customer_phone = $request->phone;
		$startdate = $request->startdate;
		$enddate = $request->enddate;
		
		// Build the base query with the necessary joins
		$query = Sale::join('customers', 'sales.customer_id', '=', 'customers.id')
			->join('billers', 'sales.biller_id', '=', 'billers.id')
			->join('product_sales', 'sales.id', '=', 'product_sales.sale_id')
			;
		
		// Apply filtering by customer phone if provided
		if ($customer_phone) {
			//$query->where('customers.phone_number', 'like','%',$customer_phone,'%','like');
			$query->where('customers.phone_number', 'like', '%' . $customer_phone . '%');
		}
		
		// Apply date filtering based on current date or provided startdate and enddate
		if ($startdate && $enddate) {
			// Filter by the provided date range
			$query->whereBetween('sales.created_at', [Carbon::parse($startdate), Carbon::parse($enddate)]);
		} else {
			// Filter by the current date if no dates are provided
			$query->where('sales.user_id', Auth::id())->whereDate('sales.created_at', Carbon::today());
		}
		
		// Execute the query
		$sales = $query->get();
		return response()->json([
			'sale_list'=>$sales,
			//'phone'=>$customer_phone
		]);
		
	}

	public function account_list(){
		$lims_account_list = Account::where('is_active', true)->get();
		return response()->json([
			'accounts'=>$lims_account_list,
			//'phone'=>$customer_phone
		]);
	}
}
