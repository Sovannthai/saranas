@extends('backends.master')
@section('title', 'Create Payment')
@section('contents')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>@lang('Create Payment')</h5>
                        <a href="{{ route('payments.index') }}" class="btn btn-primary btn-sm"><i class="fas fa-list"></i>
                            @lang('Payment List')</a>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('payments.store') }}" method="POST" id="createPaymentForm">
                            @csrf
                            <input type="hidden" name="total_room_price_before_discount"
                                id="total_room_price_before_discount">
                            <div class="row">
                                <div class="col-sm-6">
                                    <label for="user_contract_id">@lang('Contract')</label>
                                    <select name="user_contract_id" id="user_contract_id" class="form-control select2"
                                        required>
                                        <option value="" selected>-- @lang('Select Contract') --</option>
                                        @foreach ($contracts as $contract)
                                            <option value="{{ $contract->id }}">{{ $contract->user->name }} -
                                                {{ $contract->room->room_number }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-sm-6">
                                    <label for="month_paid">@lang('Month Paid')</label>
                                    <select name="month_paid" class="form-select select2" required>
                                        <option value="" selected>-- @lang('Select Month') --</option>
                                        <option value="1">@lang('January')</option>
                                        <option value="2">@lang('February')</option>
                                        <option value="3">@lang('March')</option>
                                        <option value="4">@lang('April')</option>
                                        <option value="5">@lang('May')</option>
                                        <option value="6">@lang('June')</option>
                                        <option value="7">@lang('July')</option>
                                        <option value="8">@lang('August')</option>
                                        <option value="9">@lang('September')</option>
                                        <option value="10">@lang('October')</option>
                                        <option value="11">@lang('November')</option>
                                        <option value="12">@lang('December')</option>
                                    </select>
                                </div>
                                <div class="col-sm-6">
                                    <label for="year_paid">@lang('Year Paid')</label>
                                    <input type="number" class="form-control" id="year" name="year_paid"
                                        value="{{ date('Y') }}" required>
                                </div>
                                <div class="col-sm-6">
                                    <label for="room_price">@lang('Room Price')</label>
                                    <input type="number" name="room_price" id="room_price" class="form-control"
                                        step="0.01" min="0" readonly style="color: black;">
                                </div>
                                <hr
                                    style="height: 1px;background-color: #000000;margin: 10px 0;width:-webkit-fill-available;">
                                <fieldset>
                                    <h6 data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="Discount value will be auto completed when you choose on another contract">
                                        @lang('Discount Details') <i class="fa fa-info-circle"></i></h6>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <label for="discount_value">@lang('Total Discount')</label>
                                            <input type="number" name="discount_value" id="discount_value"
                                                class="form-control" step="0.01" min="0" required readonly
                                                style="color: black;">
                                        </div>
                                        <div class="col-sm-6">
                                            <label for="discount_type">@lang('Discount Type')</label>
                                            <input type="text" name="discount_type" id="discount_type"
                                                class="form-control text-uppercase" required readonly style="color: black;">
                                        </div>
                                    </div>
                                </fieldset>
                                <hr
                                    style="height: 1px;background-color: #000000;margin: 10px 0;width:-webkit-fill-available;">
                                <fieldset>
                                    <h6>@lang('Amenity Details')</h6>
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <table class="table table-bordered" id="amenity-details">
                                                <thead>
                                                    <tr>
                                                        <th>@lang('Amenity Name')</th>
                                                        <th>@lang('Additional Price')</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <!-- Amenity details -->
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="col-sm-4">
                                            <label for="total_amount_amenity">@lang('Total Amenity Price')</label>
                                            <input type="number" name="total_amount_amenity" id="total_amount_amenity"
                                                class="form-control" step="0.01" min="0" readonly
                                                style="color: black;">
                                        </div>
                                    </div>
                                </fieldset>
                                <hr
                                    style="height: 1px;background-color: #000000;margin: 10px 0;width:-webkit-fill-available;">
                                <fieldset>
                                    <h6>@lang('Utility Details')</h6>
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <table class="table table-bordered" id="utility-details">
                                                <thead>
                                                    <tr>
                                                        <th>@lang('Name')</th>
                                                        <th>@lang('Usage')</th>
                                                        <th>@lang('Rate')</th>
                                                        <th>@lang('Subtotal')</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <!-- Utility details -->
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="col-sm-4">
                                            <label for="total_utility_amount">@lang('Total Utility Price')</label>
                                            <input type="number" name="total_utility_amount" id="total_utility_amount"
                                                class="form-control" step="0.01" min="0" readonly
                                                style="color: black;">
                                        </div>
                                    </div>
                                </fieldset>
                                <div class="col-sm-12">
                                    <label for="total_amount">@lang('Total Amount')</label>
                                    <input type="number" name="total_amount" id="total_amount" class="form-control"
                                        step="0.01" min="0" required readonly style="color: black;">
                                </div>
                                <div class="col-sm-6">
                                    <label for="type">@lang('Payment Type')</label>
                                    <select name="type" id="type" class="form-control" required>
                                        <option value="" selected>-- @lang('Select Type') --</option>
                                        <option value="all_paid">@lang('Paid for All')</option>
                                        <option value="rent">@lang('Rent')</option>
                                        <option value="utility">@lang('Utility')</option>
                                        {{-- <option value="advance">@lang('Advance')</option> --}}
                                    </select>
                                </div>
                                <div class="col-sm-6">
                                    <label for="amount">@lang('Paid Amount')</label>
                                    <input type="number" name="amount" id="amount" class="form-control"
                                        step="0.01" min="0" required readonly style="color: black;"
                                        value="0">
                                </div>
                                <div class="col-sm-6">
                                    <label for="advance_payment_amount">@lang('Advance Payment Amount')</label>
                                    <input type="number" name="advance_payment_amount" id="advance_payment_amount"
                                        class="form-control" step="0.01" min="0" value="0"
                                        style="color: black" required readonly>
                                </div>
                                <div class="col-sm-6" id="from-date-field" style="display: none;">
                                    <label for="form_date">@lang('From Date')</label>
                                    <input type="date" name="form_date" id="form_date" class="form-control">
                                </div>
                                <div class="col-sm-6" id="to-date-field" style="display: none;">
                                    <label for="to_date">@lang('To Date')</label>
                                    <input type="date" name="to_date" id="to_date" class="form-control">
                                </div>
                                <div class="col-sm-6">
                                    <label for="payment_date">@lang('Payment Date')</label>
                                    <input type="date" name="payment_date" id="payment_date" class="form-control"
                                        required>
                                </div>
                                <div class="mt-2">
                                    <button type="submit"
                                        class="btn btn-outline-primary btn-sm text-uppercase float-right mb-2 ml-2">@lang('Submit')</button>
                                    <a href="#" type="button" data-bs-dismiss="modal"
                                        class="float-right btn btn-dark btn-sm">@lang('Cancel')</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            var roomPrice = 0;

            function fetchRoomPrice(contractId) {
                return $.ajax({
                    url: "{{ route('payments.getRoomPrice', ':id') }}".replace(':id', contractId),
                    type: "GET",
                    dataType: "json",
                    success: function(response) {
                        console.log("Room price:", response.price);
                        if (response.price) {
                            roomPrice = parseFloat(response.price);
                        } else {
                            roomPrice = 0;
                        }
                    },
                    error: function(error) {
                        console.error("Error fetching room price:", error);
                        alert("Failed to retrieve room price.");
                        roomPrice = 0;
                    }
                });
            }

            $('#user_contract_id').on('change', function() {
                var contractId = $(this).val();
                if (contractId) {
                    fetchRoomPrice(contractId).done(function() {
                        handleDateChange();
                    });
                }
            });

            $('#form_date, #to_date').on('change', function() {
                handleDateChange();
            });

            $('#type').on('change', function() {
                toggleAmountField();
                if ($(this).val() === 'advance') {
                    handleDateChange();
                }
            });

            function handleDateChange() {
                var fromDate = $('#form_date').val();
                var toDate = $('#to_date').val();

                if (fromDate && toDate) {
                    var start = new Date(fromDate);
                    var end = new Date(toDate);

                    if (start <= end) {
                        var totalMonths = getMonthDifference(start, end);
                        var totalPrice = totalMonths * roomPrice;
                        console.log("Calculated advance payment:", totalPrice.toFixed(2),
                            "months:", totalMonths, "roomPrice:", roomPrice);

                        $('#advance_payment_amount').val(totalPrice.toFixed(2)).trigger('change');

                        $('#amount').val(totalPrice.toFixed(2));
                    } else {
                        alert('From Date cannot be after To Date.');
                        $('#advance_payment_amount').val('0');
                        $('#amount').val('0');
                    }
                } else {
                    $('#advance_payment_amount').val('0');
                    $('#amount').val('0');
                }
            }

            function getMonthDifference(start, end) {
                var yearDiff = end.getFullYear() - start.getFullYear();
                var monthDiff = end.getMonth() - start.getMonth();
                var dayDiff = end.getDate() - start.getDate();

                var totalMonths = yearDiff * 12 + monthDiff;
                if (dayDiff > 0) {
                    totalMonths += 1;
                }

                return totalMonths > 0 ? totalMonths : 0;
            }

            function toggleAmountField() {
                var selectedType = $('#type').val();

                if (selectedType === 'advance') {
                    $('#advance_payment_amount').closest('.col-sm-6').show();
                    $('#from-date-field').show();
                    $('#to-date-field').show();
                    $('#advance_payment_amount').prop('readonly', true).css('color', 'black');
                    $('#amount').closest('.col-sm-6').hide();
                } else {
                    $('#amount').closest('.col-sm-6').show();
                    $('#advance_payment_amount').closest('.col-sm-6').hide();
                    $('#from-date-field').hide();
                    $('#to-date-field').hide();
                }
            }

            // Initialize fields state
            toggleAmountField();
        });

        $(document).ready(function() {
            $('#createPaymentModal').on('show.bs.modal', function() {
                const today = new Date();
                const formattedDate = today.toISOString().split('T')[0];
                const currentMonth = today.getMonth() + 1;
                const currentYear = today.getFullYear();

                $('#payment_date').val(formattedDate);
                $('select[name="month_paid"]').val(currentMonth);
                $('input[name="year_paid"]').val(currentYear);
            });

            $('#amount').prop('disabled', true);
            
            // Handle payment type change
            $('#type').on('change', function() {
                updatePaymentAmount();
            });

            // Handle contract change
            $('#user_contract_id').on('change', function() {
                updatePaymentAmount();
            });
            
            // Handle month change
            $('select[name="month_paid"]').on('change', function() {
                updatePaymentAmount();
            });
            
            // Handle year change
            $('input[name="year_paid"]').on('change', function() {
                updatePaymentAmount();
            });
            
            function updatePaymentAmount() {
                var paymentType = $('#type').val();
                var contractId = $('#user_contract_id').val();
                var monthPaid = $('select[name="month_paid"]').val();
                var yearPaid = $('input[name="year_paid"]').val();
                
                if (!contractId || !paymentType) {
                    $('#amount').val('').prop('disabled', true);
                    return;
                }

                $('#amount').prop('disabled', false);
                
                if (paymentType === 'rent') {
                    fetchPrice("{{ route('payments.getRoomPrice', ':id') }}".replace(':id', contractId),
                        paymentType, monthPaid, yearPaid);
                } else if (paymentType === 'all_paid' || paymentType === 'utility') {
                    fetchPrice("{{ route('payments.getTotalRoomPrice', ':id') }}".replace(':id', contractId),
                        paymentType, monthPaid, yearPaid);
                } else {
                    $('#amount').val('').prop('disabled', true);
                }
            }

            function fetchPrice(url, paymentType, monthPaid, yearPaid) {
                $.ajax({
                    url: url,
                    method: 'GET',
                    data: {
                        month_paid: monthPaid,
                        year_paid: yearPaid
                    },
                    success: function(response) {
                        console.log("Response:", response);
                        if (paymentType === 'utility') {
                            if (response.totalCost) {
                                $('#amount').val(response.totalCost).prop('disabled', false);
                            } else {
                                alert('Error: Total cost not found.');
                                $('#amount').val('').prop('disabled', true);
                            }
                        } else if (paymentType === 'all_paid') {
                            if (response.price) {
                                $('#amount').val(response.price).prop('disabled', false);
                            } else {
                                $('#amount').val('0').prop('disabled', true);
                            }
                        } else { 
                            if (response.price) {
                                $('#amount').val(response.price).prop('disabled', false);
                            } else {
                                $('#amount').val('0').prop('disabled', true);
                            }
                        }
                    },
                    error: function(xhr) {
                        console.error("AJAX Error:", xhr.responseText);
                        alert('Failed to fetch price. Please try again.');
                    }
                });
            }
        });

        //Get Total Room Price
        $(document).ready(function() {
            function fetchContractData() {
            var contractId = $('#user_contract_id').val();
            var monthPaid = $('select[name="month_paid"]').val();
            var yearPaid = $('input[name="year_paid"]').val();

            if (contractId && monthPaid) {
                $.ajax({
                url: "{{ route('payments.getTotalRoomPrice', ':id') }}".replace(':id', contractId),
                type: 'GET',
                data: {
                    month_paid: monthPaid,
                    year_paid: yearPaid
                },
                dataType: 'json',
                success: function(response) {
                    if (response.price && response.discount) {
                    $('#total_amount').val(response.price);
                    $('#discount_value').val(response.discount.discount_value);
                    $('#discount_type').val(response.discount.discount_type);
                    $('#total_amount_amenity').val(response.amenity_prices);
                    $('#total_utility_amount').val(response.totalCost);
                    $('#room_price').val(response.room_price);
                    $('#total_room_price_before_discount').val(response
                        .total_room_price_before_discount);
                    } else {
                    // alert('Discount data is incomplete.');
                    $('#total_amount').val(response.price);
                    $('#discount_value').val('');
                    $('#discount_type').val('');
                    $('#total_amount_amenity').val(response.amenity_prices);
                    $('#total_utility_amount').val(response.totalCost || '');
                    $('#room_price').val(response.room_price);
                    $('#total_room_price_before_discount').val(response
                        .total_room_price_before_discount);
                    }
                    //Amenity
                    var amenityHtml = '';
                    if (response.amenities.length > 0) {
                    response.amenities.forEach(function(amenity) {
                        amenityHtml += `
                        <tr data-id="${amenity.id}">
                        <td data-name="${amenity.name}">${amenity.name}</td>
                        <td data-price="${amenity.additional_price}">${amenity.additional_price}</td>
                        </tr>`;
                    });
                    } else {
                    amenityHtml =
                        '<tr><td colspan="2">No amenities found.</td></tr>';
                    }
                    $('#amenity-details tbody').html(amenityHtml);
                    
                    //utility
                    var utilityHtml = '';
                    if (response.utilityUsage && response.utilityUsage.length > 0) {
                    response.utilityUsage.forEach(function(utility) {
                        var utilityType = utility.utility_type ? utility
                        .utility_type : 'N/A';
                        var usage = utility.usage !== undefined ? utility.usage : 'N/A';
                        var utilityRate = response.utilityRates ? response.utilityRates.find(rate =>
                        rate.utility_type_id == utility.utility_type_id) : null;
                        var ratePerUnit = utilityRate ? parseFloat(
                        utilityRate.rate_per_unit) : 'N/A';
                        var totalPrice = (usage !== 'N/A' && ratePerUnit !== 'N/A') ?
                        parseFloat(usage) * ratePerUnit : 'N/A';
                        var formattedRate = (ratePerUnit !== 'N/A') ?
                        `$ ${ratePerUnit.toFixed(2)}` : 'N/A';
                        var formattedTotal = (totalPrice !== 'N/A') ?
                        `$ ${totalPrice.toFixed(2)}` : 'N/A';
                        utilityHtml += `
                        <tr data-id="${utility.utility_type_id || ''}"
                        data-type="${utilityType}"
                        data-usage="${usage}"
                        data-rate="${ratePerUnit !== 'N/A' ? ratePerUnit : ''}"
                        data-total="${totalPrice !== 'N/A' ? totalPrice : ''}">
                        <td>${utilityType}</td>
                        <td>${usage}</td>
                        <td>${formattedRate}</td>
                        <td>${formattedTotal}</td>
                        </tr>`;
                    });
                    } else {
                    utilityHtml =
                        '<tr><td colspan="4">No utilities found for this month.</td></tr>';
                    }

                    $('#utility-details tbody').html(utilityHtml);
                    $('#total_utility_amount').val(response.totalCost || '0');
                },
                error: function(error) {
                    console.error('Error fetching data:', error);
                    alert('Unable to fetch data. Please try again.');
                }
                });
            } else {
                $('#total_amount').val('');
                $('#discount_value').val('');
                $('#discount_type').val('');
                $('#utility-details tbody').html('<tr><td colspan="4">Please select a contract and month.</td></tr>');
            }
            }

            // Attach event handlers to both contract ID and month selection
            $('#user_contract_id').on('change', fetchContractData);
            $('select[name="month_paid"]').on('change', fetchContractData);
            $('input[name="year_paid"]').on('change', fetchContractData);
        });

        // get amenity_id and utility_id
        $(document).ready(function() {
            $('#createPaymentForm').on('submit', function(e) {
                e.preventDefault();
                $('.dynamic-input').remove();

                var amenityInputs = '';
                $(this).find('#amenity-details tbody tr').each(function() {
                    var amenityId = $(this).data('id');
                    var amenityName = $(this).find('td[data-name]').data('name');
                    var amenityPrice = $(this).find('td[data-price]').data('price');

                    if (amenityId && amenityName && amenityPrice) {
                        amenityInputs += `
                        <input type="hidden" name="amenity_ids[]" value="${amenityId}" class="dynamic-input">
                        <input type="hidden" name="amenity_names[]" value="${amenityName}" class="dynamic-input">
                        <input type="hidden" name="amenity_prices[]" value="${amenityPrice}" class="dynamic-input">
                    `;
                    }
                });

                var utilityInputs = '';
                $(this).find('#utility-details tbody tr').each(function() {
                    var utilityId = $(this).data('id');
                    var utilityType = $(this).data('type');
                    var usage = $(this).data('usage');
                    var rate = $(this).data('rate');
                    var total = $(this).data('total');

                    if (utilityId && utilityType && usage && rate && total) {
                        utilityInputs += `
                        <input type="hidden" name="utility_ids[]" value="${utilityId}" class="dynamic-input">
                        <input type="hidden" name="utility_types[]" value="${utilityType}" class="dynamic-input">
                        <input type="hidden" name="utility_usages[]" value="${usage}" class="dynamic-input">
                        <input type="hidden" name="utility_rates[]" value="${rate}" class="dynamic-input">
                        <input type="hidden" name="utility_totals[]" value="${total}" class="dynamic-input">
                    `;
                    }
                });
                $(this).append(amenityInputs + utilityInputs);
                this.submit();
            });
        });
    </script>
@endsection
