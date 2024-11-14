@extends('backend.layout.main')
@section('content')
@if(session()->has('message'))
  <div class="alert alert-success alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{!! session()->get('message') !!}</div>
@endif
@if(session()->has('not_permitted'))
  <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}</div>
@endif
<section>
    <div class="container-fluid">
        <h3>Admin Setelment</h3>
    </div>
    <div class="d-flex justify-content-center">
        <form action="" method="get">
        @if (Auth::id()==1)
        <div>
            <select name="user" id="" class="form-control">
                @foreach ($users as $us)
                <option <?php if ($us->id==@$user) 
                  echo "selected" ;?> value="{{$us->id}}">{{$us->name}}</option>
                @endforeach
                
            </select>
        </div> 
        @endif
        
        <div>
            <input type="date" name="startdate" class="form-control" value="{{@$startdate}}">
        </div>
        <div>
            <input type="date" name="enddate" class="form-control" value="{{@$enddate}}">
        </div>
        <div>
            <input type="submit" value="submit" class="btn btn-sm btn-primary">
        </div>
    </form>
    </div>
    <div class="table-responsive">
        <table id="biller-table" class="table">
            <thead>
                <tr>
                   
                    <th>User</th>
                    <th>Total sale</th>
                    <th>Colective Amount</th>
                    
                    <th>Due Biller</th>
                    <th>Due Admin</th>
                    <th>Action</th>

                   
                </tr>
            </thead>
            <tbody>
                <tr>
                    @if($user)
                    <td><?php $username=App\Models\User::where('id',$user)->first(); ?>{{@$username->name}}</td>
                    @else
                    <td><?php $username=App\Models\User::where('id',Auth::id())->first(); ?>{{@$username->name}}</td>
                    @endif
                    
                
                    <td>{{@$invoicestotal}}</td>
                
                    <td>{{@$payments}}</td>
                    
                    <td>{{@$invoicestotaldue}}</td>
                
                    <td>{{@$allDuepayments-@$adminRecords}}</td>
                    <td>
                        <!-- Button trigger modal -->
<button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#{{$username->name}}">
    Setelment Admin
  </button>
  
  <!-- Modal -->
  <div class="modal fade" id="{{$username->name}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Pay Admin</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form action="{{url('biller/adminpayment')}}" method="post" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="user_id" value="{{@$username->id}}">
            <input type="number" name="amount" class="form-control" placeholder="pay amount">
            <input type="file" name="file" id="" class="form-control">
         
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Save changes</button>
        </div>
    </form>
      </div>
    </div>
  </div>

  {{-- list pay --}}
  <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#view{{$username->name}}">
    View
  </button>
  
  <!-- Modal -->
  <div class="modal fade" id="view{{$username->name}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Pay View</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <table width="100%">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Attach</th>
                </tr>
            </thead>
            <tbody>
                <?php $adminpayment=DB::table('setelment_admin')->where('user_id',@$username->id)->orderBy('id','DESC')->get() ?>
                @foreach ($adminpayment as $item)
                <tr>
                    <td>{{$item->date}}</td>
                    <td>{{$item->amount}}</td>
                    <td>
                        @if ($item->file)
                        <a href="{{$item->file}}">file</a>
                        @else
                       <a href="#">no file</a>
                       @endif
                    </td>
                </tr>
                @endforeach
                
            </tbody>
          </table>
         
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary">Save changes</button>
        </div>
   
      </div>
    </div>
  </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</section>




@endsection

@push('scripts')
<script type="text/javascript">

    $("ul#people").siblings('a').attr('aria-expanded','true');
    $("ul#people").addClass("show");
    $("ul#people #biller-list-adminsetelment").addClass("active");

    var biller_id = [];
    var user_verified = <?php echo json_encode(env('USER_VERIFIED')) ?>;
    var all_permission = <?php echo json_encode($all_permission) ?>;

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    function confirmDelete() {
        if (confirm("Are you sure want to delete?")) {
            return true;
        }
        return false;
    }
    var table = $('#biller-table').DataTable( {
        "order": [],
        'language': {
            'lengthMenu': '_MENU_ {{trans("file.records per page")}}',
             "info":      '<small>{{trans("file.Showing")}} _START_ - _END_ (_TOTAL_)</small>',
            "search":  '{{trans("file.Search")}}',
            'paginate': {
                    'previous': '<i class="dripicons-chevron-left"></i>',
                    'next': '<i class="dripicons-chevron-right"></i>'
            }
        },
        
        'select': { style: 'multi',  selector: 'td:first-child'},
        'lengthMenu': [[10, 25, 50, -1], [10, 25, 50, "All"]],
        dom: '<"row"lfB>rtip',
        buttons: [
            {
                extend: 'pdf',
                text: '<i title="export to pdf" class="fa fa-file-pdf-o"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible',
                    stripHtml: false
                },
                customize: function(doc) {
                    for (var i = 1; i < doc.content[1].table.body.length; i++) {
                        if (doc.content[1].table.body[i][0].text.indexOf('<img src=') !== -1) {
                            var imagehtml = doc.content[1].table.body[i][0].text;
                            var regex = /<img.*?src=['"](.*?)['"]/;
                            var src = regex.exec(imagehtml)[1];
                            var tempImage = new Image();
                            tempImage.src = src;
                            var canvas = document.createElement("canvas");
                            canvas.width = tempImage.width;
                            canvas.height = tempImage.height;
                            var ctx = canvas.getContext("2d");
                            ctx.drawImage(tempImage, 0, 0);
                            var imagedata = canvas.toDataURL("image/png");
                            delete doc.content[1].table.body[i][0].text;
                            doc.content[1].table.body[i][0].image = imagedata;
                            doc.content[1].table.body[i][0].fit = [30, 30];
                        }
                    }
                },
            },
            {
                extend: 'excel',
                text: '<i title="export to excel" class="dripicons-document-new"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible',
                    format: {
                        body: function ( data, row, column, node ) {
                            if (column === 0 && (data.indexOf('<img src=') != -1)) {
                                var regex = /<img.*?src=['"](.*?)['"]/;
                                data = regex.exec(data)[1];
                            }
                            return data;
                        }
                    }
                },
                footer:true
            },
            {
                extend: 'csv',
                text: '<i title="export to csv" class="fa fa-file-text-o"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible',
                    format: {
                        body: function ( data, row, column, node ) {
                            if (column === 0 && (data.indexOf('<img src=') != -1)) {
                                var regex = /<img.*?src=['"](.*?)['"]/;
                                data = regex.exec(data)[1];
                            }
                            return data;
                        }
                    }
                },
            },
            {
                extend: 'print',
                text: '<i title="print" class="fa fa-print"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible',
                    stripHtml: false
                },
            },
            {
                text: '<i title="delete" class="dripicons-cross"></i>',
                className: 'buttons-delete',
                action: function ( e, dt, node, config ) {
                    if(user_verified == '1') {
                        biller_id.length = 0;
                        $(':checkbox:checked').each(function(i){
                            if(i){
                                biller_id[i-1] = $(this).closest('tr').data('id');
                            }
                        });
                        if(biller_id.length && confirm("Are you sure want to delete?")) {
                            $.ajax({
                                type:'POST',
                                url:'biller/deletebyselection',
                                data:{
                                    billerIdArray: biller_id
                                },
                                success:function(data){
                                    alert(data);
                                }
                            });
                            dt.rows({ page: 'current', selected: true }).remove().draw(false);
                        }
                        else if(!biller_id.length)
                            alert('No biller is selected!');
                    }
                    else
                        alert('This feature is disable for demo!');
                }
            },
            {
                extend: 'colvis',
                text: '<i title="column visibility" class="fa fa-eye"></i>',
                columns: ':gt(0)'
            },
        ],
    } );

    if(all_permission.indexOf("billers-delete") == -1)
        $('.buttons-delete').addClass('d-none');

    $("#export").on("click", function(e){
        e.preventDefault();
        var biller = [];
        $(':checkbox:checked').each(function(i){
          biller[i] = $(this).val();
        });
        $.ajax({
           type:'POST',
           url:'/exportbiller',
           data:{

                billerArray: biller
            },
           success:function(data){
            alert('Exported to CSV file successfully! Click Ok to download file');
            window.location.href = data;
           }
        });
    });
</script>
@endpush
