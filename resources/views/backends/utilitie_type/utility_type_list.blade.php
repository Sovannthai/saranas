<table id="basic-datatables" class="table table-bordered text-nowrap table-hover table-responsive-lg">
    <thead class="table-dark">
        <tr>
            <th>@lang('Name')</th>
            <th>@lang('Actions')</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($utilityTypes as $utility_type)
            <tr>
                <td>{{ $utility_type->type }}</td>
                <td>
                    @if(auth()->user()->can('update utilitytype'))
                    <button class="btn btn-sm btn-outline-primary edit-btn" 
                            data-id="{{ $utility_type->id }}"
                            data-type="{{ $utility_type->type }}">
                        <i class="fa fa-edit ambitious-padding-btn text-uppercase"></i> @lang('Edit')
                    </button>
                    &nbsp;&nbsp;
                    @endif
                    @if(auth()->user()->can('delete utilitytype'))
                    <form action="{{ route('utilities.destroyType', ['id' => $utility_type->id]) }}"
                        method="POST" class="d-inline-block delete-form">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-outline-danger btn-sm delete-btn"
                            data-id="{{ $utility_type->id }}"
                            title="@lang('Delete Rate')">
                            <i class="fa fa-trash"></i> @lang('Delete')
                        </button>
                    </form>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>