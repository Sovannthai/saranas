@extends('backends.master')
@section('title', 'Edit Payment')
@section('contents')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>@lang('Edit Payment')</h5>
                        <a href="{{ route('payments.index') }}" class="btn btn-primary btn-sm">@lang('Back to List')</a>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('payments.update', $payment->id) }}" method="POST" id="editPaymentForm">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="col-sm-6">
                                    <label for="user_contract_id">@lang('Contract')</label>
                                    <select name="user_contract_id" id="user_contract_id" class="form-control select2"
                                        required>
                                        <option value="" disabled>-- @lang('Select Contract') --</option>
                                        @foreach ($contracts as $contract)
                                            <option value="{{ $contract->id }}"
                                                {{ $contract->id == $payment->user_contract_id ? 'selected' : '' }}>
                                                {{ $contract->user->name }} -
                                                {{ $contract->room->room_number }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-sm-6">
                                    <label for="month_paid">@lang('Month Paid')</label>
                                    <select name="month_paid" id="month_paid" class="form-select select2" required>
                                        @for ($i = 1; $i <= 12; $i++)
                                            <option value="{{ $i }}"
                                                {{ $payment->month_paid == $i ? 'selected' : '' }}>
                                                {{ \Carbon\Carbon::create()->month($i)->format('F') }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-sm-6">
                                    <label for="year_paid">@lang('Year Paid')</label>
                                    <input type="number" class="form-control" id="year_paid" name="year_paid"
                                        value="{{ $payment->year_paid ?? date('Y') }}" required>
                                </div>
                                <div class="col-sm-6">
                                    <label for="room_price">@lang('Room Price')</label>
                                    <input type="number" name="room_price" id="room_price" class="form-control"
                                        step="0.01" min="0" value="{{ $payment->room_price }}" readonly>
                                </div>
                                <hr
                                    style="height: 1px;background-color: #000000;margin: 10px 0;width:-webkit-fill-available;">
                                <fieldset>
                                    <h6>@lang('Discount Details')</h6>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <label for="discount_value">@lang('Total Discount')</label>
                                            <input type="number" name="total_discount" id="discount_value"
                                                class="form-control" step="0.01" min="0"
                                                value="{{ $payment->total_discount }}" readonly>
                                        </div>
                                        <div class="col-sm-6">
                                            <label for="discount_type">@lang('Discount Type')</label>
                                            <select name="discount_type" id="discount_type" class="form-control" disabled>
                                                <option value="amount"
                                                    {{ $payment->discount_type == 'amount' ? 'selected' : '' }}>
                                                    @lang('Fixed Amount')</option>
                                                <option value="percentage"
                                                    {{ $payment->discount_type == 'percentage' ? 'selected' : '' }}>
                                                    @lang('Percentage')</option>
                                            </select>
                                            <input type="hidden" name="discount_type"
                                                value="{{ $payment->discount_type }}">
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
                                                    @foreach ($payment->paymentamenities as $amenity)
                                                        <tr>
                                                            <td>{{ @$amenity->amenity->name }}</td>
                                                            <td>
                                                                <input type="number"
                                                                    name="amenity_data[{{ $amenity->id }}][price]"
                                                                    class="form-control amenity-price"
                                                                    value="{{ $amenity->amenity_price }}" min="0"
                                                                    step="0.01" readonly>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="col-sm-4">
                                            <label for="total_amount_amenity">@lang('Total Amenity Price')</label>
                                            <input type="number" name="total_amount_amenity" id="total_amount_amenity"
                                                class="form-control" step="0.01" min="0"
                                                value="{{ $payment->total_amount_amenity }}" readonly>
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
                                                    @foreach ($payment->paymentutilities as $utility)
                                                        <tr>
                                                            <td>{{ @$utility->utility->type }}</td>
                                                            <td>
                                                                <input type="number"
                                                                    name="utility_data[{{ $utility->utility_id }}][usage]"
                                                                    class="form-control utility-usage"
                                                                    value="{{ $utility->usage }}" min="0"
                                                                    step="0.01" readonly>
                                                            </td>
                                                            <td>
                                                                <input type="number"
                                                                    name="utility_data[{{ $utility->utility_id }}][rate]"
                                                                    class="form-control utility-rate"
                                                                    value="{{ $utility->rate_per_unit }}" min="0"
                                                                    step="0.01" readonly>
                                                            </td>
                                                            <td>
                                                                <input type="number"
                                                                    name="utility_data[{{ $utility->utility_id }}][total]"
                                                                    class="form-control utility-total"
                                                                    value="{{ $utility->total_amount }}" min="0"
                                                                    step="0.01" readonly>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="col-sm-4">
                                            <label for="total_utility_amount">@lang('Total Utility Price')</label>
                                            <input type="number" name="total_utility_amount" id="total_utility_amount"
                                                class="form-control" step="0.01" min="0"
                                                value="{{ $payment->total_utility_amount }}" readonly>
                                        </div>
                                    </div>
                                </fieldset>
                                <div class="col-sm-6">
                                    <label for="total_amount">@lang('Total Amount')</label>
                                    <input type="number" name="total_amount" id="total_amount" class="form-control"
                                        step="0.01" min="0" required value="{{ $payment->total_amount }}"
                                        readonly>
                                </div>
                                <div class="col-sm-6">
                                    <label for="amount">@lang('Paid Amount')</label>
                                    <input type="number" name="amount" id="amount" class="form-control"
                                        step="0.01" min="0" required value="{{ $payment->amount }}">
                                </div>
                                <div class="col-sm-6">
                                    <label for="type">@lang('Payment Type')</label>
                                    <select name="type" id="type" class="form-control" required>
                                        <option value="all_paid" {{ $payment->type == 'all_paid' ? 'selected' : '' }}>
                                            @lang('Paid for All')</option>
                                        <option value="rent" {{ $payment->type == 'rent' ? 'selected' : '' }}>
                                            @lang('Rent')</option>
                                        <option value="utility" {{ $payment->type == 'utility' ? 'selected' : '' }}>
                                            @lang('Utility')</option>
                                        {{-- <option value="advance" {{ $payment->type == 'advance' ? 'selected' : '' }}>@lang('Advance')</option> --}}
                                    </select>
                                </div>
                                <div class="col-sm-6">
                                    <label for="payment_date">@lang('Payment Date')</label>
                                    <input type="date" name="payment_date" id="payment_date" class="form-control"
                                        required
                                        value="{{ $payment->payment_date ? $payment->payment_date->format('Y-m-d') : now()->format('Y-m-d') }}">
                                </div>
                                <div class="mt-2">
                                    <button type="submit"
                                        class="btn btn-outline-primary btn-sm text-uppercase float-right mb-2 ml-2">@lang('Update Payment')</button>
                                    <a href="{{ route('payments.index') }}"
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
                                    var usage = utility.usage !== undefined ? utility.usage :
                                        'N/A';
                                    var utilityRate = response.utilityRates ? response
                                        .utilityRates.find(rate =>
                                            rate.utility_type_id == utility.utility_type_id) :
                                        null;
                                    var ratePerUnit = utilityRate ? parseFloat(
                                        utilityRate.rate_per_unit) : 'N/A';
                                    var totalPrice = (usage !== 'N/A' && ratePerUnit !==
                                        'N/A') ?
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
                    $('#utility-details tbody').html(
                        '<tr><td colspan="4">Please select a contract and month.</td></tr>');
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
