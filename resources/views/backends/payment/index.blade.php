@extends('backends.master')
@section('title', __('Payments'))
@section('contents')
<style>
    .form-control {
        width: 100%;
    }
</style>
<div class="card">
    <h5 class=" ml-3 mt-2 mb-0 card-title">
        <a data-toggle="collapse" href="#collapse-example" aria-expanded="true" aria-controls="collapse-example"
            id="heading-example" class="d-block bg-success-header">
            <i class="fa fa-filter bg-success-header"></i>
            @lang('Filter')
        </a>
    </h5>
    <div id="collapse-example" class="collapse show" aria-labelledby="heading-example">
        <div class="mt-1 ml-2 mr-2 mb-4">
            <div class="row">
                <div class="col-sm-3">
                    <label for="user_id">@lang('User')</label>
                    <select id="filter-user" class="form-control select2">
                        <option value="">@lang('All')</option>
                        @foreach($users as $row)
                        <option value="{{ $row->id }}">{{ $row->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-3">
                    <label for="payment_status">@lang('Payment Status')</label>
                    <select id="filter-payment-status" class="form-control select2">
                        <option value="">@lang('All')</option>
                        <option value="partial">@lang('Partial')</option>
                        <option value="completed">@lang('Completed')</option>
                        <option value="pending">@lang('Pending')</option>
                    </select>
                </div>
                <div class="col-sm-3">
                    <label for="payment_type">@lang('Payment Type')</label>
                    <select id="filter-payment-type" class="form-control select2">
                        <option value="">@lang('All')</option>
                        <option value="all_paid">@lang('All Paid')</option>
                        <option value="rent">@lang('Rent')</option>
                        <option value="utility">@lang('Utility')</option>
                        <option value="advance">@lang('Advance')</option>
                    </select>
                </div>
                <div class="col-sm-3">
                    <label for="year_paid">@lang('Year')</label>
                    <select id="filter-payment-by-year" class="form-control select2">
                        <option value="">@lang('All')</option>
                        @for ($year = 2024; $year <= 2050; $year++)
                            <option value="{{ $year }}">@lang($year)</option>
                        @endfor
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-header">
        <label class="card-title font-weight-bold mb-1 text-uppercase">@lang('Payments Management')</label>
        @can('create payment')
        {{-- <a class="btn btn-primary float-right text-uppercase btn-sm btn-modal btn-add"
            data-href="{{ route('payments.create') }}" data-toggle="modal" data-container=".createPaymentModal">
            <i class="fas fa-plus"></i> {{ __('Add New') }}
        </a> --}}
        <a href="{{ route('payments.create') }}" class="btn btn-primary btn-sm text-uppercase float-right">Add New</a>
        @endcan
    </div>
    <div class="card-body">
        <table id="payment-datatables" class="table table-bordered text-nowrap table-hover table-responsive">
            <thead class="table-dark">
                <tr>
                    <th>@lang('Action')</th>
                    <th>@lang('Status')</th>
                    <th>@lang('Payment Date')</th>
                    <th>@lang('Invoice No.')</th>
                    <th>@lang('User')</th>
                    <th>@lang('Total Amount')</th>
                    <th>@lang('Paid Amount')</th>
                    <th>@lang('Due Amount')</th>
                    <th>@lang('Payment Type')</th>
                    <th>@lang('Month Paid')</th>
                    <th>@lang('Year Paid')</th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th></th>
                    <th></th>
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
<div class="modal fade createPaymentModal" id="createPaymentModal" data-bs-backdrop="static" data-bs-keyboard="false"
    tabindex="-1" aria-labelledby="createPaymentModalLabel" aria-hidden="true">
    {{-- Modal Create Payment --}}
</div>
<div class="modal fade editPaymentModal" id="editPaymentModal" data-bs-backdrop="static" data-bs-keyboard="false"
    tabindex="-1" aria-labelledby="editPaymentModalLabel" aria-hidden="true">
    {{-- Modal Edit Payment --}}
</div>
<div class="modal fade add_uitlity_payment" id="add_uitlity_payment" data-bs-backdrop="static" data-bs-keyboard="false"
    tabindex="-1" aria-labelledby="add_uitlity_paymentLabel" aria-hidden="true">
    {{-- Modal Utility Payment --}}
</div>
<div class="modal fade payment_details_modal" id="payment_details_modal" data-bs-backdrop="static"
    data-bs-keyboard="false" tabindex="-1" aria-labelledby="payment_details_modalLabel" aria-hidden="true">
    {{-- Modal Utility Payment --}}
</div>

@include('backends.payment.partial.add_payment_due')
@include('backends.payment.partial._list_utilities')
<script>
    $(document).on("click", ".btn-modal", function(e) {
        e.preventDefault();
        var container = $(this).data("container");
        var url = $(this).data("href");

        $.ajax({
            url: url,
            dataType: "html",
            success: function(result) {
                $(container).html(result);
                var bsModal = new bootstrap.Modal(document.querySelector(container));
                bsModal.show();
            },
            error: function(xhr, status, error) {
                console.error("Error loading modal content:", error);
                console.error("Response:", xhr.responseText);
                toastr.error("Failed to load content. Please try again.");
            }
        });
    });
        
    $(document).on('submit', '#editPaymentForm', function(e) {
        e.preventDefault();
        const form = $(this);
        const url = form.attr('action');
        const formData = form.serialize();
        
        $.ajax({
            type: 'POST',
            url: url,
            data: formData,
            beforeSend: function() {
                form.find(':submit').prop('disabled', true).text('Processing...');
            },
            success: function(response) {
                $('.editPaymentModal').modal('hide');
                
                let successMessage = 'Payment updated successfully';
                if (response.payment) {
                    const payment = response.payment;
                    const formattedAmount = parseFloat(payment.amount).toFixed(2);
                    const status = payment.payment_status.charAt(0).toUpperCase() + payment.payment_status.slice(1);
                    
                    successMessage = `Payment updated: ${status} - $${formattedAmount}`;
                    if (payment.total_due_amount > 0) {
                        successMessage += ` (Due: $${parseFloat(payment.total_due_amount).toFixed(2)})`;
                    }
                }
                
                toastr.success(successMessage);
                $('#payment-datatables').DataTable().ajax.reload();
            },
            error: function(xhr) {
                let errorMessage = 'Something went wrong. Please try again.';
                
                if(xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                toastr.error(errorMessage);
            },
            complete: function() {
                // Re-enable submit button after request completes
                form.find(':submit').prop('disabled', false).text('Update Payment');
            }
        });
    });
</script>
<script>
    function printInvoice(userId) {
            var url = "{{ route('invoice.print', ':userId') }}";
            url = url.replace(':userId', userId);
            window.open(url, '_blank');
        }
</script>
<script>
    $(document).ready(function() {
    var viewInvoiceRoute = "{{ route('payment-details.show', ['id' => '__USER_ID__']) }}";
    var downloadInvoiceRoute = "{{ route('invoice.download', '__USER_ID__') }}";
    var sendInvoiceRoute = "{{ route('send-invoice', ['userId' => '__USER_ID__']) }}";
    var deletePaymentRoute = "{{ route('payments.destroy', ['payment' => '__PAYMENT_ID__']) }}";
    const dataTable = $("#payment-datatables").DataTable({
            responsive: true,
            processing: true,
            serverSide: false,
            paging: true,
            searching: true,
            ordering: true,
        ajax: {
            url: "{{ route('payments.index') }}",
            data: function(d) {
                d.user_id = $('#filter-user').val();
                d.payment_status = $('#filter-payment-status').val();
                d.payment_type = $('#filter-payment-type').val();
                d.year_paid = $('#filter-payment-by-year').val();
            },
            dataSrc: function(json) {
                $('#total-payment').text(`$ ${json.total_payment.toFixed(2)}`);
                $('#amount-paid').text(`$ ${json.amount_paid.toFixed(2)}`);
                $('#total-due-amount').text(`$ ${json.total_due_amount.toFixed(2)}`);
                return json.data;
            }
        },
        columns: [
            {
            data: 'id',
            render: function(data, type, row) {
                const payment = row;
                const paymentId = payment.id;
                const userId = payment.user_contract.user.id;
                const viewInvoiceUrl = viewInvoiceRoute.replace('__USER_ID__', userId);
                const downloadInvoiceUrl = downloadInvoiceRoute.replace('__USER_ID__', userId);
                const sendInvoiceUrl = sendInvoiceRoute.replace('__USER_ID__', userId);
                const deletePaymentUrl = deletePaymentRoute.replace('__PAYMENT_ID__', paymentId);
                const editUrl = "{{ route('payments.edit', '__PAYMENT_ID__') }}".replace('__PAYMENT_ID__', paymentId);
                return `
                <div class="dropdown">
                            <button class="btn btn-primary btn-sm dropdown-toggle" type="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                Action
                            </button>
                            <ul class="dropdown-menu" style="font-size: 16px">
                                <li class="mb-1">
                                    <a class="btn float-right btn-sm dropdown-item btn-modal btn-add" href="#"
                                        data-href="${viewInvoiceUrl}"
                                        data-bs-toggle="modal" data-container=".createPaymentModal">
                                        <i class="fas fa-eye"></i> @lang('View Invoice')
                                    </a>
                                </li>
                                <li class="mb-1">
                                    <a class="btn float-right btn-sm dropdown-item" href="${editUrl}">
                                        <i class="fas fa-edit"></i> @lang('Edit Payment')
                                    </a>
                                </li>
                                ${payment.type === 'advance' ? `
                                <li class="mb-1">
                                    <a class="btn float-right btn-sm dropdown-item btn-modal btn-add" data-bs-toggle="modal"
                                        data-bs-target="#utility_list_modal-${payment.id}">
                                        <i class="fas fa-file-alt"></i> @lang('Utilities Payment')
                                    </a>
                                </li>` : ''}
                                <li class="mb-1">
                                    <a class="btn float-right btn-sm dropdown-item" href="#"
                                        onclick="printInvoice(${userId})">
                                        <i class="fas fa-file-alt"></i> @lang('Print Invoice')
                                    </a>
                                </li>
                                <li class="mb-1">
                                    <a class="btn float-right btn-sm dropdown-item" href="${downloadInvoiceUrl}">
                                        <i class="fas fa-download"></i> @lang('Download Invoice')
                                    </a>
                                </li>
                                ${payment.user_contract.user.telegram_id !== null ? `
                                <li class="mb-1">
                                    <form action="${sendInvoiceUrl}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="fas fa-paper-plane"></i> @lang('Send Invoice To Telegram')
                                        </button>
                                    </form>
                                </li>` : ''}
                                @can('delete payment')
                                <li>
                                    <form action="${deletePaymentUrl}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger btn delete-btn">
                                            <i class="fa fa-trash"></i> @lang('Delete')
                                        </button>
                                    </form>
                                </li>
                                @endcan
                            </ul>
                        </div>
                `;
                }
            },

            {
                data: 'payment_status',
                render: function(data, type, row) {
                    return data === 'completed'
                        ? '<a href="#" class="btn btn-success btn-sm">Completed</a>'
                        : data === 'partial'
                        ? `<a href="#" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#add_payment_due-${row.id}">Partial</a>`
                        : '<a href="#" class="btn btn-warning btn-sm">Pending</a>';
                }
            },
            { 
                data: 'payment_date',
                render: function(data, type, row) {
                    if (!data) return 'N/A';
                    const date = new Date(data);
                    return date.toLocaleDateString('en-US', {
                        year: 'numeric', 
                        month: 'short', 
                        day: 'numeric'
                    });
                }
            },
            { data: 'invoice_no' },
            {
                data: 'userContract.user.name',
                render: function(data, type, row) {
                    return `${row.user_contract.user.name || 'N/A'} (${row.user_contract.room.room_number || 'N/A'})`;
                }
            },
            { data: 'total_amount', render: data => data ? `$ ${parseFloat(data).toFixed(2)}` : '$ 0.00' },
            { data: 'amount', render: data => data ? `$ ${parseFloat(data).toFixed(2)}` : '$ 0.00' },
            { data: 'total_due_amount', render: data => data ? `$ ${parseFloat(data).toFixed(2)}` : '$ 0.00' },
            { 
                data: 'type', 
                className: 'text-capitalize',
                render: function(data) {
                    return data ? data.replace(/_/g, ' ') : '';
                }
            },
            {
                data: 'month_paid',
                render: function(data) {
                    const months = [
                        '', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'
                    ];
                    return data ? months[data] : '-';
                }
            },
            { data: 'year_paid', render: data => data || '-' },
        ],
        footerCallback: function(row, data, start, end, display) {
            const api = this.api();
            let totalPayment = 0;
            let amountPaid = 0;
            let totalDueAmount = 0;

            api.rows({ page: 'current' }).data().each(function(row) {
            totalPayment   += parseFloat(row.total_amount) || 0;
            amountPaid     += parseFloat(row.amount) || 0;
            totalDueAmount += parseFloat(row.total_due_amount) || 0;
            });

            $(api.column(5).footer()).html(`Total Amount: $ ${totalPayment.toFixed(2)}`);
            $(api.column(6).footer()).html(`Total Paid: $ ${amountPaid.toFixed(2)}`);
            $(api.column(7).footer()).html(`Total Due: $ ${totalDueAmount.toFixed(2)}`);
        }
    });

    $('#filter-user,#filter-payment-status,#filter-payment-type,#filter-payment-by-year').on('change', function() {
        dataTable.ajax.reload();
    });
});
</script>
<script>
    $(document).on('shown.bs.modal', '.editPaymentModal', function() {
        if ($.fn.select2) {
            $(this).find('.select2').select2({
                dropdownParent: $(this)
            });
        }
    });
    
    if (typeof toastr !== 'undefined') {
        toastr.options = {
            closeButton: true,
            progressBar: true,
            positionClass: 'toast-top-right',
            timeOut: 5000
        };
    }
</script>
@endsection
