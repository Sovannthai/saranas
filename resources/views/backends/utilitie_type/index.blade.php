@extends('backends.master')
@section('title', __('Utilities Type'))
@section('contents')
    <div class="card">
        <div class="card-header">
            <label class="card-title font-weight-bold mb-1 text-uppercase">@lang('Utilities Type')</label>
            @if (auth()->user()->can('create utilitytype'))
                <a href="#" class="float-right btn btn-primary btn-sm" id="show-create-form">
                    <i class="fas fa-plus"></i> @lang('Add Utility Type')
                </a>
            @endif
        </div>
        <div class="card-body">
            <!-- Create Form (Initially Hidden) -->
            @if (auth()->user()->can('create utilitytype'))
                <div id="create-form-container" class="mb-4" style="display: none;">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">@lang('Create Utility Type')</h5>
                        </div>
                        <div class="card-body">
                            <form id="create-utility-form" action="{{ route('utilities.storeType') }}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-sm-12">
                                        <label for="type">@lang('Utility Type')</label>
                                        <input type="text" name="type" id="type" class="form-control"
                                            placeholder="@lang('Enter utility type')" required>
                                        <span class="text-danger error-text type_error"></span>
                                    </div>
                                    <div class="mt-3">
                                        <button type="submit"
                                            class="btn btn-outline-primary btn-sm text-uppercase me-2">
                                            <i class="fas fa-save"></i> @lang('Submit')
                                        </button>
                                        <button type="button" id="cancel-create" class="btn btn-dark btn-sm">
                                            @lang('Cancel')
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Edit Form (Initially Hidden) -->
            @if (auth()->user()->can('update utilitytype'))
                <div id="edit-form-container" class="mb-4" style="display: none;">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">@lang('Edit Utility Type')</h5>
                        </div>
                        <div class="card-body">
                            <form id="edit-utility-form" method="POST">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="id" id="edit_utility_id">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <label for="edit_type">@lang('Utility Type')</label>
                                        <input type="text" name="type" id="edit_type" class="form-control"
                                            placeholder="@lang('Enter utility type')" required>
                                        <span class="text-danger error-text type_error"></span>
                                    </div>
                                    <div class="mt-3">
                                        <button type="submit"
                                            class="btn btn-outline-primary btn-sm text-uppercase me-2">
                                            <i class="fas fa-save"></i> @lang('Update')
                                        </button>
                                        <button type="button" id="cancel-edit" class="btn btn-dark btn-sm">
                                            @lang('Cancel')
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Utility Types Table -->
            <div id="utility-type-list">
                @include('backends.utilitie_type.utility_type_list')
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Show/Hide Create Form
            $('#show-create-form').click(function(e) {
                e.preventDefault();
                $('#create-form-container').slideDown();
                $(this).hide();
                $('#type').focus();
            });

            $('#cancel-create').click(function() {
                $('#create-form-container').slideUp();
                $('#show-create-form').show();
                $('#create-utility-form')[0].reset();
                $('.error-text').text('');
            });

            // Cancel Edit
            $('#cancel-edit').click(function() {
                $('#edit-form-container').slideUp();
                $('#edit-utility-form')[0].reset();
                $('.error-text').text('');
            });

            // Delete Confirmation
            $(document).on('click', '.delete-btn', function(e) {
                e.preventDefault();
                let id = $(this).data('id');

                Swal.fire({
                    title: '@lang('Are you sure?')',
                    text: "@lang('You won\'t be able to revert this!')",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: '@lang('Yes, delete it!')',
                    cancelButtonText: '@lang('Cancel')'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $(this).closest('form').submit();
                    }
                });
            });

            // Create Form Submission
            $('#create-utility-form').on('submit', function(e) {
                e.preventDefault();
                let form = $(this);
                let url = form.attr('action');
                let formData = form.serialize();

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    beforeSend: function() {
                        form.find('.error-text').text('');
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#utility-type-list').html(response.html);
                            $('#create-utility-form')[0].reset();
                            $('#create-form-container').slideUp();
                            $('#show-create-form').show();

                            // Reinitialize DataTable
                            $('#basic-datatables').DataTable().destroy();
                            $('#basic-datatables').DataTable({
                                responsive: true,
                                language: {
                                    paginate: {
                                        next: '›',
                                        previous: '‹'
                                    }
                                }
                            });
                            toastr.success(response.message);
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            $.each(errors, function(key, value) {
                                form.find('.' + key + '_error').text(value[0]);
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: '@lang('Error')',
                                text: '@lang('Something went wrong!')',
                            });
                        }
                    }
                });
            });

            // Edit Button Click Event
            $(document).on('click', '.edit-btn', function() {
                let id = $(this).data('id');
                let type = $(this).data('type');

                $('#edit_utility_id').val(id);
                $('#edit_type').val(type);
                $('#edit-utility-form').attr('action', '{{ url('utilities/update-type') }}/' + id);

                $('#edit-form-container').slideDown();
                $('#edit_type').focus();
                $('html, body').animate({
                    scrollTop: $('#edit-form-container').offset().top - 100
                }, 500);
            });

            // Update Form Submission
            $('#edit-utility-form').on('submit', function(e) {
                e.preventDefault();
                let form = $(this);
                let url = form.attr('action');
                let formData = form.serialize();

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    beforeSend: function() {
                        form.find('.error-text').text('');
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#utility-type-list').html(response.html);
                            $('#edit-utility-form')[0].reset();
                            $('#edit-form-container').slideUp();

                            // Reinitialize DataTable
                            $('#basic-datatables').DataTable().destroy();
                            $('#basic-datatables').DataTable({
                                responsive: true,
                                language: {
                                    paginate: {
                                        next: '›',
                                        previous: '‹'
                                    }
                                }
                            });

                            toastr.success(response.message);
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            $.each(errors, function(key, value) {
                                form.find('.' + key + '_error').text(value[0]);
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: '@lang('Error')',
                                text: '@lang('Something went wrong!')',
                            });
                        }
                    }
                });
            });

            // Form validation
            $('#create-utility-form, #edit-utility-form').each(function() {
                $(this).validate({
                    rules: {
                        type: {
                            required: true,
                            maxlength: 50
                        }
                    },
                    messages: {
                        type: {
                            required: "@lang('Please enter utility type')",
                            maxlength: "@lang('Utility type cannot be more than 50 characters')"
                        }
                    },
                    errorElement: 'span',
                    errorPlacement: function(error, element) {
                        error.addClass('invalid-feedback');
                        element.closest('.col-sm-12').append(error);
                    },
                    highlight: function(element, errorClass, validClass) {
                        $(element).addClass('is-invalid');
                    },
                    unhighlight: function(element, errorClass, validClass) {
                        $(element).removeClass('is-invalid');
                    }
                });
            });
        });
    </script>
@endsection
