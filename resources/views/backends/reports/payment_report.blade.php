@extends('backends.master')

@section('title', __('Payment Report'))
@section('contents')
    <div class="card">
        <h5 class="ml-3 mt-2 mb-0 card-title">
            <a data-toggle="collapse" href="#collapse-filters" aria-expanded="true" aria-controls="collapse-filters"
                id="heading-filters" class="d-block bg-success-header">
                <i class="fa fa-filter bg-success-header"></i>
                @lang('Filter')
            </a>
        </h5>
        <div id="collapse-filters" class="collapse show" aria-labelledby="heading-filters">
            <div class="mt-1 ml-2 mr-2 mb-4">
                <div class="row">
                    <!-- Month Filter -->
                    <div class="col-md-3">
                        <label for="month" class="form-label">@lang('Month')</label>
                        <select name="month" id="month" class="form-select select2">
                            <option value="">@lang('All')</option>
                            @foreach (range(1, 12) as $m)
                                <option value="{{ $m }}">{{ date('F', mktime(0, 0, 0, $m, 10)) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Year Filter -->
                    <div class="col-md-3">
                        <label for="year" class="form-label">@lang('Year')</label>
                        <select name="year" id="year" class="form-select select2">
                            <option value="">@lang('All')</option>
                            @foreach (range(date('Y'), date('Y') + 50) as $y)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Payment Type Filter -->
                    <div class="col-md-3">
                        <label for="type" class="form-label">@lang('Payment Type')</label>
                        <select name="type" id="type" class="form-select select2">
                            <option value="">@lang('All')</option>
                            <option value="all_paid">@lang('All Paid')</option>
                            <option value="rent">@lang('Rent')</option>
                            <option value="utility">@lang('Utility')</option>
                            <option value="advance">@lang('Advance')</option>
                        </select>
                    </div>

                    <!-- Payment Status Filter -->
                    <div class="col-md-3">
                        <label for="status" class="form-label">@lang('Payment Status')</label>
                        <select name="status" id="status" class="form-select select2">
                            <option value="">@lang('All')</option>
                            <option value="completed">@lang('Completed')</option>
                            <option value="pending">@lang('Pending')</option>
                            <option value="partial">@lang('Partial')</option>
                        </select>
                    </div>
                </div>
                <div>
                    <a href="{{ route('reports.payment') }}"
                        class="btn btn-outline-danger float-right text-capitalize mb-3 mt-3">@lang('Reset Filter')</a>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header">@lang('Payment Report')</div>
        <div class="card-body">
            <!-- DataTable -->
            <table id="payment-report-datatables" class="table table-bordered table-striped nowrap table-responsive">
                <thead class="table-dark">
                    <tr>
                        <th>@lang('Invoice No')</th>
                        <th>@lang('User Name')</th>
                        <th>@lang('Room No')</th>
                        <th>@lang('Room Price')</th>
                        <th>@lang('Total Amount')</th>
                        <th>@lang('Paid Amount')</th>
                        <th>@lang('Due Amount')</th>
                        <th>@lang('Payment Status')</th>
                        <th>@lang('Payment Date')</th>
                        <th>@lang('Type')</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th class="text-center">@lang('Total Amount'): <span id="total-payment"></span></th>
                        <th class="text-center">@lang('Amount Paid'): <span id="amount-paid"></span></th>
                        <th class="text-center">@lang('Due Amount'): <span id="total-due-amount"></span></th>
                        <th></th>
                        <th></th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- DataTable Script -->
    <script>
        $(document).ready(function() {
            let table = $("#payment-report-datatables").DataTable({
                responsive: true,
                processing: true,
                serverSide: false,
                paging: true,
                searching: true,
                ordering: true,
                info: true,
                autoWidth: false,
                lengthChange: true,
                ajax: {
                    url: "{{ route('reports.payment') }}",
                    data: function(d) {
                        d.month = $('#month').val();
                        d.year = $('#year').val();
                        d.type = $('#type').val();
                        d.status = $('#status').val();
                    },
                    dataSrc: function(json) {
                        $('#total-payment').text(`$ ${json.total_payment.toFixed(2)}`);
                        $('#amount-paid').text(`$ ${json.amount_paid.toFixed(2)}`);
                        $('#total-due-amount').text(`$ ${json.total_due_amount.toFixed(2)}`);
                        return json.data;
                    }
                },
                columns: [{
                        data: 'invoice_no',
                        name: 'invoice_no'
                    },
                    {
                        data: 'user_name',
                        name: 'user_name'
                    },
                    {
                        data: 'room_number',
                        name: 'room_number'
                    },
                    {
                        data: 'room_price',
                        name: 'room_price',
                        render: function(data) {
                            return '$ ' + parseFloat(data).toFixed(2);
                        }
                    },
                    {
                        data: 'total_amount',
                        name: 'total_amount',
                        render: function(data) {
                            return '$ ' + parseFloat(data).toFixed(2);
                        }
                    },
                    {
                        data: 'amount_paid',
                        name: 'amount_paid',
                        render: function(data) {
                            return '$ ' + parseFloat(data).toFixed(2);
                        }
                    },
                    {
                        data: 'total_due_amount',
                        name: 'total_due_amount',
                        render: function(data) {
                            return '$ ' + parseFloat(data).toFixed(2);
                        }
                    },
                    {
                        data: 'payment_status',
                        name: 'payment_status',
                        render: function(data) {
                            return data == "completed" ?
                                '<span class="badge badge-success">Completed</span>' :
                                data == "pending" ?
                                '<span class="badge badge-warning">Pending</span>' :
                                '<span class="badge badge-info">Partial</span>';
                        },
                    },
                    {
                        data: 'payment_date',
                        name: 'payment_date',
                        render: function(data) {
                            if (!data) return '';
                            let date = new Date(data);
                            return ('0' + (date.getMonth() + 1)).slice(-2) + '-' +
                                ('0' + date.getDate()).slice(-2) + '-' +
                                date.getFullYear().toString().substr(-2) + ' ' +
                                ('0' + date.getHours()).slice(-2) + ':' +
                                ('0' + date.getMinutes()).slice(-2);
                        }
                    },
                    {
                        data: 'type',
                        name: 'type',
                        render: function(data) {
                            return data.split('_')
                                .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                                .join(' ');
                        }
                    }
                ],
                footerCallback: function(row, data, start, end, display) {
                    const api = this.api();
                    let totalPayment = 0;
                    let amountPaid = 0;
                    let totalDueAmount = 0;

                    api.column(4, {page: 'current'}).data().each(function(value) {
                        totalPayment += parseFloat(value) || 0;
                    });

                    api.column(5, {page: 'current'}).data().each(function(value) {
                        amountPaid += parseFloat(value) || 0;
                    });

                    api.column(6, {page: 'current'}).data().each(function(value) {
                        totalDueAmount += parseFloat(value) || 0;
                    });

                    $('#total-payment').text('$ ' + totalPayment.toFixed(2));
                    $('#amount-paid').text('$ ' + amountPaid.toFixed(2));
                    $('#total-due-amount').text('$ ' + totalDueAmount.toFixed(2));
                },
                pageLength: 10,
                lengthMenu: [5, 10, 20, 50, 100],
                language: {
                    search: "@lang('Search'):",
                    lengthMenu: "@lang('Show _MENU_ entries')",
                    info: "@lang('Showing _START_ to _END_ of _TOTAL_ entries')",
                    infoEmpty: "@lang('No entries available')",
                    paginate: {
                        next: "@lang('Next')",
                        previous: "@lang('Previous')"
                    }
                },
            });
            $('#month, #year, #type, #status').on('change', function() {
                table.ajax.reload();
            });

        });
    </script>
@endsection
