<!-- Edit Payment Modal -->
<style>
    input {
        color: black;
    }
</style>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="editPaymentModalLabel">@lang('Edit Payment #:id', ['id' => $payment->id])</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <form action="{{ route('payments.update', $payment->id) }}" method="POST" id="editPaymentForm">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-sm-6">
                        <label for="user_contract_id">@lang('Contract')</label>
                        <select name="user_contract_id" id="user_contract_id" class="form-control select2" required>
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
                    <hr style="height: 1px;background-color: #000000;margin: 10px 0;width:-webkit-fill-available;">
                    <fieldset>
                        <h6>@lang('Discount Details')</h6>
                        <div class="row">
                            <div class="col-sm-6">
                                <label for="discount_value">@lang('Total Discount')</label>
                                <input type="number" name="total_discount" id="discount_value"
                                    class="form-control" step="0.01" min="0" value="{{ $payment->total_discount }}" readonly>
                            </div>
                            <div class="col-sm-6">
                                <label for="discount_type">@lang('Discount Type')</label>
                                <select name="discount_type" id="discount_type" class="form-control" disabled>
                                    <option value="amount" {{ $payment->discount_type == 'amount' ? 'selected' : '' }}>@lang('Fixed Amount')</option>
                                    <option value="percentage" {{ $payment->discount_type == 'percentage' ? 'selected' : '' }}>@lang('Percentage')</option>
                                </select>
                                <input type="hidden" name="discount_type" value="{{ $payment->discount_type }}">
                            </div>
                        </div>
                    </fieldset>
                    <hr style="height: 1px;background-color: #000000;margin: 10px 0;width:-webkit-fill-available;">
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
                                                    <input type="number" name="amenity_data[{{ $amenity->id }}][price]" 
                                                        class="form-control amenity-price" 
                                                        value="{{ $amenity->amenity_price }}" min="0" step="0.01" readonly>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-sm-4">
                                <label for="total_amount_amenity">@lang('Total Amenity Price')</label>
                                <input type="number" name="total_amount_amenity" id="total_amount_amenity"
                                    class="form-control" step="0.01" min="0" value="{{ $payment->total_amount_amenity }}" readonly>
                            </div>
                        </div>
                    </fieldset>
                    <hr style="height: 1px;background-color: #000000;margin: 10px 0;width:-webkit-fill-available;">
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
                                                    <input type="number" name="utility_data[{{ $utility->utility_id }}][usage]" 
                                                        class="form-control utility-usage" 
                                                        value="{{ $utility->usage }}" min="0" step="0.01" readonly>
                                                </td>
                                                <td>
                                                    <input type="number" name="utility_data[{{ $utility->utility_id }}][rate]" 
                                                        class="form-control utility-rate" 
                                                        value="{{ $utility->rate_per_unit }}" min="0" step="0.01" readonly>
                                                </td>
                                                <td>
                                                    <input type="number" name="utility_data[{{ $utility->utility_id }}][total]" 
                                                        class="form-control utility-total" 
                                                        value="{{ $utility->total_amount }}" min="0" step="0.01" readonly>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-sm-4">
                                <label for="total_utility_amount">@lang('Total Utility Price')</label>
                                <input type="number" name="total_utility_amount" id="total_utility_amount"
                                    class="form-control" step="0.01" min="0" value="{{ $payment->total_utility_amount }}" readonly>
                            </div>
                        </div>
                    </fieldset>
                    <div class="col-sm-6">
                        <label for="total_amount">@lang('Total Amount')</label>
                        <input type="number" name="total_amount" id="total_amount" class="form-control"
                            step="0.01" min="0" required value="{{ $payment->total_amount }}" readonly>
                    </div>
                    <div class="col-sm-6">
                        <label for="amount">@lang('Paid Amount')</label>
                        <input type="number" name="amount" id="amount" class="form-control"
                            step="0.01" min="0" required value="{{ $payment->amount }}">
                    </div>
                    <div class="col-sm-6">
                        <label for="type">@lang('Payment Type')</label>
                        <select name="type" id="type" class="form-control" required>
                            <option value="all_paid" {{ $payment->type == 'all_paid' ? 'selected' : '' }}>@lang('Paid for All')</option>
                            <option value="rent" {{ $payment->type == 'rent' ? 'selected' : '' }}>@lang('Rent')</option>
                            <option value="utility" {{ $payment->type == 'utility' ? 'selected' : '' }}>@lang('Utility')</option>
                            {{-- <option value="advance" {{ $payment->type == 'advance' ? 'selected' : '' }}>@lang('Advance')</option> --}}
                        </select>
                    </div>
                    <div class="col-sm-6">
                        <label for="payment_date">@lang('Payment Date')</label>
                        <input type="date" name="payment_date" id="payment_date" class="form-control"
                            required value="{{ $payment->payment_date ? $payment->payment_date->format('Y-m-d') : now()->format('Y-m-d') }}">
                    </div>
                    <div class="mt-2">
                        <button type="submit"
                            class="btn btn-outline-primary btn-sm text-uppercase float-right mb-2 ml-2">@lang('Update Payment')</button>
                        <a href="#" type="button" data-bs-dismiss="modal"
                            class="float-right btn btn-dark btn-sm">@lang('Cancel')</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        if ($.fn.select2) {
            $('.select2').select2({
                dropdownParent: $(".editPaymentModal")
            });
        }

        // Store original values
        const originalAmount = parseFloat($("#amount").val()) || 0;
        const totalAmount = parseFloat($("#total_amount").val()) || 0;
        const roomPrice = parseFloat($("#room_price").val()) || 0;
        const totalUtilityAmount = parseFloat($("#total_utility_amount").val()) || 0;
        const totalAmenityAmount = parseFloat($("#total_amount_amenity").val()) || 0;
        
        // Function to update amount based on payment type
        function updateAmountBasedOnType(paymentType) {
            let newAmount = originalAmount; // Default to original amount
            
            switch(paymentType) {
                case 'all_paid':
                    newAmount = totalAmount;
                    break;
                case 'rent':
                    newAmount = roomPrice + totalAmenityAmount;
                    break;
                case 'utility':
                    newAmount = totalUtilityAmount;
                    break;
                case 'advance':
                    // Keep the original amount
                    break;
            }
            
            $("#amount").val(newAmount.toFixed(2));
        }
        
        // Handle payment type change
        $("#type").on('change', function() {
            const selectedType = $(this).val();
            updateAmountBasedOnType(selectedType);
        });

        $(document).on('change', '.utility-usage, .utility-rate', function() {
            const row = $(this).closest('tr');
            const usage = parseFloat(row.find('.utility-usage').val()) || 0;
            const rate = parseFloat(row.find('.utility-rate').val()) || 0;
            const total = usage * rate;
            
            row.find('.utility-total').val(total.toFixed(2));
            recalculateTotals();
        });
        
        $(document).on('change', '.amenity-price', function() {
            recalculateTotals();
        });
        
        function recalculateTotals() {
            let amenityTotal = 0;
            $('.amenity-price').each(function() {
                amenityTotal += parseFloat($(this).val()) || 0;
            });
            $('#total_amount_amenity').val(amenityTotal.toFixed(2));
            
            let utilityTotal = 0;
            $('.utility-total').each(function() {
                utilityTotal += parseFloat($(this).val()) || 0;
            });
            $('#total_utility_amount').val(utilityTotal.toFixed(2));
            
            const roomPrice = parseFloat($('#room_price').val()) || 0;
            const totalAmount = roomPrice + amenityTotal + utilityTotal;
            $('#total_amount').val(totalAmount.toFixed(2));
            
            // Update amount based on payment type if the type is selected
            const selectedType = $('#type').val();
            if (selectedType) {
                updateAmountBasedOnType(selectedType);
            }
        }
        
        recalculateTotals();
    });
</script> 