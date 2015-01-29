@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>Edit Symptom</h1>
</div>

<form class="form-horizontal" role="form" method="post" action="{{ route('admin/health/symptom/edit', $symptom->id) }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

    <div class="form-group">
        <label for="cp-symptom-id" class="col-sm-2 control-label">ID</label>
        <div class="col-sm-10">
            <p class="form-control-static">
                {{ $symptom->id }}
            </p>
        </div>
    </div>

    <div class="form-group">
        <label for="cp-symptom-name" class="col-sm-2 control-label">Name</label>
        <div class="col-sm-10">
            <input type="text" name="name" class="form-control" id="cp-symptom-name" value="{{{ Input::old('name', $symptom->name) }}}" required />
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-10 col-sm-offset-2 text-right">
            <a href="{{ route('admin/health/symptom/delete', $symptom->id) }}" name="delete_symptom" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this symptom?');">Delete</a>
            <button type="submit" name="edit_symptom" class="btn btn-primary">Save</button>
        </div>
    </div>
</form>

<h2>Assigned to Characteristics</h2>

<table class="table table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Characteristic</th>
            <th>Active</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @foreach($characteristics as $characteristic)
        <tr>
            <td><a href="{{ route('admin/characteristics/characteristic/severity/edit', $characteristic->id) }}">{{ $characteristic->id }}</a></td>
            <td><a href="{{ route('admin/characteristics/characteristic/edit', $characteristic->id) }}">{{$characteristic->name }}</a></td>
            <td><big>{{ $characteristic->isActive() ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>' }}</big></td>
            <td class="text-right">
                <a class="btn btn-danger btn-xs" href="{{ route('admin/health/symptom/characteristic/remove_from_all_severities', [$symptom->id, $characteristic->id]) }}" onclick="return confirm('Are you sure you want to remove this health symptom from that characteristic?');">
                    Remove From All Severities
                </a>
            </td>
        </tr>
        @endforeach

        @if($characteristics->isEmpty())
        <tr>
            <td colspan="4">No characteristics to display</td>
        </tr>
        @endif
    </tbody>
</table>

@stop
