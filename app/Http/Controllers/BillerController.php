<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Biller;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\User;
use App\Models\MailSetting;
use Illuminate\Validation\Rule;
use Intervention\Image\Facades\Image;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Auth;
use App\Mail\BillerCreate;
use Mail;

class BillerController extends Controller
{
    use \App\Traits\CacheForget;
    use \App\Traits\TenantInfo;
    use \App\Traits\MailInfo;

    public function index()
    {
        $role = Role::find(Auth::user()->role_id);
        if($role->hasPermissionTo('billers-index')) {
            $permissions = Role::findByName($role->name)->permissions;
            foreach ($permissions as $permission)
                $all_permission[] = $permission->name;
            if(empty($all_permission))
                $all_permission[] = 'dummy text';
            $lims_biller_all = biller::where('is_active', true)->get();
            return view('backend.biller.index',compact('lims_biller_all', 'all_permission'));
        }
        else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    public function billerPaymentList(){
        $role = Role::find(Auth::user()->role_id);
        if($role->hasPermissionTo('billers-index')) {
            $permissions = Role::findByName($role->name)->permissions;
            foreach ($permissions as $permission)
                $all_permission[] = $permission->name;
            if(empty($all_permission))
                $all_permission[] = 'dummy text';
                if(Auth::id()==1){
                    $payments = DB::table('payments')
                    ->join('users as creater', 'creater.id', '=', 'payments.user_id')           // biller user
                    ->join('users as receiver', 'receiver.id', '=', 'payments.resive_user')   // Receiver user
                        ->select(
                            'creater.id as creater_id',
                            'creater.name as creater_name',
                            'receiver.id as receiver_id',
                            'receiver.name as receiver_name',
                            'payments.payment_reference',
                            'payments.amount',
                            'payments.status',
                            'payments.created_at',
                            'payments.id',
                        )
                    ->orderByDesc('id')
                    ->get();
        
                    
                }else{
                    $payments = DB::table('payments')->where('payments.user_id',Auth::user())
                    ->join('users as creater', 'creater.id', '=', 'payments.user_id')           // biller user
                    ->join('users as receiver', 'receiver.id', '=', 'payments.resive_user')   // Receiver user
                        ->select(
                            'creater.id as creater_id',
                            'creater.name as creater_name',
                            'receiver.id as receiver_id',
                            'receiver.name as receiver_name',
                            'payments.payment_reference',
                            'payments.amount',
                            'payments.status',
                            'payments.created_at',
                            'payments.id',
                        )
                    ->orderByDesc('id')
                    ->get();
                    
                }
        }
        else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
        
       // dd($payments);
    return view('backend.biller.setelment',compact('payments', 'all_permission'));
        
    }

    public function billerPaymentListApi(){
       
                if(Auth::id()==1){
                    $payments = DB::table('payments')
                    ->join('users as creater', 'creater.id', '=', 'payments.user_id')           // biller user
                    ->join('users as receiver', 'receiver.id', '=', 'payments.resive_user')   // Receiver user
                        ->select(
                            'creater.id as creater_id',
                            'creater.name as creater_name',
                            'receiver.id as receiver_id',
                            'receiver.name as receiver_name',
                            'payments.payment_reference',
                            'payments.amount',
                            'payments.status',
                            'payments.created_at',
                            'payments.id',
                        )
                    ->orderByDesc('id')
                    ->get();
        
                    
                }else{
                    $payments = DB::table('payments')->where('payments.user_id',Auth::id())
                    ->join('users as creater', 'creater.id', '=', 'payments.user_id')           // biller user
                    ->join('users as receiver', 'receiver.id', '=', 'payments.resive_user')   // Receiver user
                        ->select(
                            'creater.id as creater_id',
                            'creater.name as creater_name',
                            'receiver.id as receiver_id',
                            'receiver.name as receiver_name',
                            'payments.payment_reference',
                            'payments.amount',
                            'payments.status',
                            'payments.created_at',
                            'payments.id',
                        )
                    ->orderByDesc('id')
                    ->get();
                    
                }
    return response()->json([
        'payments'=>$payments
    ]);

        
    }
    public function biller_receive($id){
        $updated = DB::table('payments')
        ->where('id', $id)
        ->update(['status' => 1]);
        return back()->with('not_permitted','Setelment add successfully');

    }
    public function biller_receive_api($id){
        $updated = DB::table('payments')
        ->where('id', $id)
        ->update(['status' => 1]);
        return response()->json([
            'massege'=>'Setelment add successfully',
        ]);
       

    }
    public function create()
    {
        $role = Role::find(Auth::user()->role_id);
        if($role->hasPermissionTo('billers-add'))
            return view('backend.biller.create');
        else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'company_name' => [
                'max:255',
                    Rule::unique('billers')->where(function ($query) {
                    return $query->where('is_active', 1);
                }),
            ],
            'email' => [
                'email',
                'max:255',
                    Rule::unique('billers')->where(function ($query) {
                    return $query->where('is_active', 1);
                }),
            ],
            'image' => 'image|mimes:jpg,jpeg,png,gif|max:10000',
        ]);

        $lims_biller_data = $request->except('image');
        $lims_biller_data['is_active'] = true;
        $image = $request->image;
        if ($image) {
            $ext = pathinfo($image->getClientOriginalName(), PATHINFO_EXTENSION);
            $imageName = date("Ymdhis");
            if(!config('database.connections.saleprosaas_landlord')) {
                $imageName = $imageName . '.' . $ext;
                $image->move('public/images/biller', $imageName);
            }
            else {
                $imageName = $this->getTenantId() . '_' . $imageName . '.' . $ext;
                $image->move('public/images/biller', $imageName);
            }
            $lims_biller_data['image'] = $imageName;
        }
        Biller::create($lims_biller_data);
        $this->cacheForget('biller_list');

        $mailSetting = MailSetting::latest()->first();
        $message = $this->mailAction($lims_biller_data, $mailSetting);
        return redirect('biller')->with('message', $message);

    }

    public function edit($id)
    {
        $role = Role::find(Auth::user()->role_id);
        if($role->hasPermissionTo('billers-edit')) {
            $lims_biller_data = Biller::where('id',$id)->first();
            return view('backend.biller.edit',compact('lims_biller_data'));
        }
        else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'company_name' => [
                'max:255',
                    Rule::unique('billers')->ignore($id)->where(function ($query) {
                    return $query->where('is_active', 1);
                }),
            ],
            'email' => [
                'email',
                'max:255',
                    Rule::unique('billers')->ignore($id)->where(function ($query) {
                    return $query->where('is_active', 1);
                }),
            ],

            'image' => 'image|mimes:jpg,jpeg,png,gif|max:100000',
        ]);

        $lims_biller_data = Biller::findOrFail($id);
        $input = $request->except('image');
        $image = $request->image;
        if ($image) {
            $this->fileDelete('images/biller/', $lims_biller_data->image);

            $ext = pathinfo($image->getClientOriginalName(), PATHINFO_EXTENSION);
            $imageName = date("Ymdhis");
            if(!config('database.connections.saleprosaas_landlord')) {
                $imageName = $imageName . '.' . $ext;
                $image->move('public/images/biller', $imageName);
            }
            else {
                $imageName = $this->getTenantId() . '_' . $imageName . '.' . $ext;
                $image->move('public/images/biller', $imageName);
            }
            $input['image'] = $imageName;
        }

        $lims_biller_data->update($input);
        $this->cacheForget('biller_list');
        return redirect('biller')->with('message','Data updated successfully');
    }

    public function importBiller(Request $request)
    {
        $upload=$request->file('file');
        $ext = pathinfo($upload->getClientOriginalName(), PATHINFO_EXTENSION);
        if($ext != 'csv')
            return redirect()->back()->with('not_permitted', 'Please upload a CSV file');
        $filename =  $upload->getClientOriginalName();
        $filePath=$upload->getRealPath();
        //open and read
        $file=fopen($filePath, 'r');
        $header= fgetcsv($file);
        $escapedHeader=[];
        //validate
        foreach ($header as $key => $value) {
            $lheader=strtolower($value);
            $escapedItem=preg_replace('/[^a-z]/', '', $lheader);
            array_push($escapedHeader, $escapedItem);
        }

        $mailSetting = MailSetting::latest()->first();

        //looping through othe columns
        while($columns=fgetcsv($file))
        {
            if($columns[0]=="")
                continue;
            foreach ($columns as $key => $value) {
                $value=preg_replace('/\D/','',$value);
            }
            $data= array_combine($escapedHeader, $columns);

            $biller = Biller::firstOrNew(['company_name'=>$data['companyname']]);
            $biller->name = $data['name'];
            $biller->image = $data['image'];
            $biller->vat_number = $data['vatnumber'];
            $biller->email = $data['email'];
            $biller->phone_number = $data['phonenumber'];
            $biller->address = $data['address'];
            $biller->city = $data['city'];
            $biller->state = $data['state'];
            $biller->postal_code = $data['postalcode'];
            $biller->country = $data['country'];
            $biller->is_active = true;
            $biller->save();
            $message = $this->mailAction($data, $mailSetting);
        }
        $this->cacheForget('biller_list');
        return redirect('biller')->with('message', $message);
    }

    protected function mailAction($data, $mailSetting)
    {
        $message = 'Data inserted successfully';
        if(!$mailSetting) {
            $message = 'Data inserted successfully. Please setup your <a href="setting/mail_setting">mail setting</a> to send mail.';
        }
        else if($data['email'] && $mailSetting) {
            try{
                $this->setMailInfo($mailSetting);
                Mail::to($data['email'])->send(new BillerCreate($data));
            }
            catch(\Exception $e){
                $message = $e->getMessage();
            }
        }
        return $message;
    }

    public function deleteBySelection(Request $request)
    {
        $biller_id = $request['billerIdArray'];
        // Biller::whereIn($biller_id)->update(['is_active'=>false]);

        foreach ($biller_id as $id) {
            $lims_biller_data = Biller::find($id);
            $lims_biller_data->is_active = false;
            $lims_biller_data->save();

            $this->fileDelete('images/biller/', $lims_biller_data->image);
        }

        $this->cacheForget('biller_list');
        return 'Biller deleted successfully!';
    }

    public function destroy($id)
    {
        $lims_biller_data = Biller::find($id);
        $this->fileDelete('images/biller/', $lims_biller_data->image);

        $lims_biller_data->is_active = false;
        $lims_biller_data->save();
        $this->cacheForget('biller_list');
        return redirect('biller')->with('not_permitted','Data deleted successfully');
    }

    public function admin_setelment(Request $request){
        $users=User::where('id','!=',1)->select('id','name')->get();
        $role = Role::find(Auth::user()->role_id);
        if($role->hasPermissionTo('billers-index')) {
            $permissions = Role::findByName($role->name)->permissions;
            foreach ($permissions as $permission)
                $all_permission[] = $permission->name;
            if(empty($all_permission))
                $all_permission[] = 'dummy text';
        $user = $request->user;
        //dd($user);
        $startdate = $request->startdate;
        $enddate = $request->enddate;
        
        $queryInvoice = Sale::query();
        $queryPayment = Payment::query();
        $adminQuery = DB::table('setelment_admin');
        $allDuepayment=Payment::query();
        
        // Apply filters only if the authenticated user is an admin
        if (Auth::id() == 1) {
            // Apply filters based on the user ID, start date, and end date
            if ($user) {
                $queryInvoice->where('user_id', $user);
                $queryPayment->where('user_id', $user);
                $adminQuery->where('user_id', $user);
                $allDuepayment->where('user_id', $user);
            }
        
            elseif ($startdate && $enddate) {
                $queryInvoice->whereBetween('created_at', [$startdate, $enddate]);
                $queryPayment->whereBetween('created_at', [$startdate, $enddate]);
                $adminQuery;
                $allDuepayment;
            }
            else{
                $queryInvoice;
                $queryPayment;
                $adminQuery;
                $allDuepayment;
                //dd($queryPayment->sum('amount'));
            }
        }else{
            if ($startdate && $enddate) {
                $queryInvoice->where('user_id', Auth::id())->whereBetween('created_at', [$startdate, $enddate]);
                $queryPayment->where('user_id', Auth::id())->whereBetween('created_at', [$startdate, $enddate]);
                $adminQuery->where('user_id', Auth::id());
                $allDuepayment->where('user_id', Auth::id());
            }
            else{
                $queryInvoice->where('user_id', Auth::id());
                $queryPayment->where('user_id', Auth::id());
                $adminQuery->where('user_id', Auth::id());
                $allDuepayment->where('user_id', Auth::id());
            }
        }

        
        // Execute the queries
        $invoicestotal = $queryInvoice->sum('grand_total');
        $invoicestotalpaid = $queryInvoice->sum('paid_amount');
        $invoicestotaldue= $queryInvoice->sum('grand_total')- $queryPayment->sum('amount');
        $payments = $queryPayment->sum('amount');
        $allDuepayments=$allDuepayment->sum('amount');
        $adminRecords = $adminQuery->sum('amount');
        
       // dd($payments);
        return view('backend.biller.seteladmin',compact('invoicestotal','invoicestotalpaid','invoicestotaldue','payments','adminRecords','all_permission','users','user','startdate','enddate','allDuepayments'));
    }
    else
    return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }


    public function admin_setelment_api(Request $request){
        
       
        $startdate = $request->startdate;
        $enddate = $request->enddate;
        
        $queryInvoice = Sale::query();
        $queryPayment = Payment::query();
        $adminQuery = DB::table('setelment_admin');
        $allDuepayment=Payment::query();
        
        // Apply filters only if the authenticated user is an admin
       
            if ($startdate && $enddate) {
                $queryInvoice->where('user_id', Auth::id())->whereBetween('created_at', [$startdate, $enddate]);
                $queryPayment->where('user_id', Auth::id())->whereBetween('created_at', [$startdate, $enddate]);
                $adminQuery->where('user_id', Auth::id());
                $allDuepayment->where('user_id', Auth::id());
            }
            else{
                $queryInvoice->where('user_id', Auth::id());
                $queryPayment->where('user_id', Auth::id());
                $adminQuery->where('user_id', Auth::id());
                $allDuepayment->where('user_id', Auth::id());
            }
        

        
        // Execute the queries
        $invoicestotal = $queryInvoice->sum('grand_total');
        $invoicestotalpaid = $queryInvoice->sum('paid_amount');
        $invoicestotaldue= $queryInvoice->sum('grand_total')- $queryPayment->sum('amount');
        $payments = $queryPayment->sum('amount');
        $allDuepayments=$allDuepayment->sum('amount');
        $adminRecords = $adminQuery->sum('amount');
        $adminpayment=DB::table('setelment_admin')->where('user_id',Auth::id())->orderBy('id','DESC')->get();
        return response()->json([
            'totalsale'=>$invoicestotal,
            'colectiveAmount'=>$payments,
            'billerDue'=>$invoicestotaldue,
            'adminDue'=>$allDuepayments-$adminRecords,
            'AdminPayList'=>$adminpayment
        ]);
    }
    
    public function adminpayment(Request $request){
        if ($request->hasFile('file')) {
        $path = $request->file('file')->store('uploads', 'public');
        // Generate the URL
        $url = url(Storage::url($path));
        }
        DB::table('setelment_admin')->insert([
            'user_id'=>$request->user_id,
            'amount'=>$request->amount,
            'file'=>$url,
        ]);
        return back()->with('message','Admin Payment Setelment successfully');
    }
    public function adminpayment_api_pay(Request $request){

        if ($request->hasFile('file')) {
        $path = $request->file('file')->store('uploads', 'public');
        // Generate the URL
        $url = url(Storage::url($path));
        }
        DB::table('setelment_admin')->insert([
            'user_id'=>$request->user_id,
            'amount'=>$request->amount,
            'file'=>$url,
        ]);
        return response()->json([
        'message'=>'Admin Payment Setelment successfully'
        ]);
    }
}
