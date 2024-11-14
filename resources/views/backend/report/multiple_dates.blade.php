@extends('backend.layout.main') @section('content')
@if(session()->has('message'))
  <div class="alert alert-success alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{!! session()->get('message') !!}</div>
@endif
@if(session()->has('not_permitted'))
  <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}</div>
@endif

<section>
    <div class="container-fluid">
       

        @if ($errors->any())
            <div class="alert alert-danger">
                {{ $errors->first() }}
            </div>
        @endif
        <section>
        <form action="{{ route('report.monthly_report') }}" method="GET">
            <div class="form-group">
                <label for="year">Start Date:</label>
                <input type="date" id="year" name="start_date" class="form-control" value="{{ @$startDate }}">
            </div>
            <div class="form-group">
                <label for="month">End Date:</label>
                <input type="date" id="month" name="end_date" class="form-control" value="{{ $endDate }}"
                    min="1" max="12">
            </div>
            <div class="d-flex justify-content-between">
            <button type="submit" class="btn btn-primary">Generate Report</button>
            <button onclick="printDiv('printableArea')"  class="btn btn-primary"><i
                class="dripicons-print"></i> {{ trans('file.Print') }}</button>
           
            </div> 
        </form>
    </section>
    <div id="printableArea">
        <h1>Date Rang Biller Report</h1>
        @if (isset($report) && !empty($report))
            @foreach ($report as $date => $data)
                <p class="p-2" style="background-color: #e9d7d7;">Date: {{ \Carbon\Carbon::parse($data['date'])->format('d M Y') }}</p>
                <table class="table mt-4">
                    <thead>
                        <tr>
                            <th>Biller</th>

                            {{-- <th>Total Sales</th> --}}
                            <th>Total Discount</th>
                            <th>Total Tax</th>
                            <th>Total Bill</th>
                            <th>Payment Amount</th>
                            <th>Due Amount</th>
                            <th>Total Invoice</th>
                            <th> Return Invoice</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $total_count = 0;
                            // $total_sales = 0;
                            $total_discount = 0;
                            $total_tax = 0;
                            $grand_total = 0;
                            $paid_amount = 0;
                            $total_return = 0;
                        @endphp

                        @foreach ($data['billers'] as $billerId => $billerData)
                            @php
                                $total_count += $billerData['total_count'];
                                // $total_sales += $billerData['total_sales'];
                                $total_discount += $billerData['total_discount'];
                                $total_tax += $billerData['total_tax'];
                                $grand_total += $billerData['grand_total'];
                                $paid_amount += $billerData['paid_amount'];

                                $returinnvoice = App\Models\Returns::where('created_at', $data['date'])
                                    ->where('biller_id', $billerId)
                                    ->count();

                                $total_return += $returinnvoice;
                            @endphp
                            <tr>
                                <td>{{ App\Models\Biller::find($billerId)->name ?? 'Unknown' }}</td>
                                <!-- Assuming you have biller names -->

                                {{-- <td>{{ number_format($billerData['total_sales'], 2) }}</td> --}}
                                <td>{{ number_format($billerData['total_discount'], 2) }}</td>
                                <td>{{ number_format($billerData['total_tax'], 2) }}</td>
                                <td>{{ number_format($billerData['grand_total'], 2) }}</td>
                                <td>{{ number_format($billerData['paid_amount'], 2) }}</td>
                                <td>{{ number_format($billerData['grand_total'] - $billerData['paid_amount'], 2) }}
                                </td>
                                <td>{{ $billerData['total_count'] }}</td>
                                <td>{{ $returinnvoice }}</td>
                            </tr>
                        @endforeach

                        <!-- Totals for the date -->
                        <tr>
                            <th>Total</th>

                            {{-- <th>{{ number_format($total_sales, 2) }}</th> --}}
                            <th>{{ number_format($total_discount, 2) }}</th>
                            <th>{{ number_format($total_tax, 2) }}</th>
                            <th>{{ number_format($grand_total, 2) }}</th>
                            <th>{{ number_format($paid_amount, 2) }}</th>
                            <th>{{ number_format($grand_total - $paid_amount, 2) }}</th>
                            <th>{{ $total_count }}</th>
                            <th>{{ $total_return }}</th>
                        </tr>
                    </tbody>
                </table>
            @endforeach
        @else
            <p>No data available for the selected date range.</p>
        @endif
        
    </div>
    </div>
</section>

@endsection

@push('scripts')
<script type="text/javascript">

    $("ul#report").siblings('a').attr('aria-expanded','true');
    $("ul#report").addClass("show");
    $("ul#report #billers-report-menu").addClass("active");

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
//     function printDiv(divId) {
//      var printContents = document.getElementById(divId).innerHTML;
//      var originalContents = document.body.innerHTML;

//      document.body.innerHTML = printContents;

//      window.print();

//      document.body.innerHTML = originalContents;
// }

function printDiv(divId) {
     var printContents = document.getElementById(divId).innerHTML;
     var originalContents = document.body.innerHTML;

     document.body.innerHTML = printContents;

     window.print();

     document.body.innerHTML = originalContents;
     
     location.reload();

}
  
</script>

<script type="text/javascript" src="https://js.stripe.com/v3/"></script>
@endpush
