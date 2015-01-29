@extends($layout)

{{-- Page content --}}
@section('content')

<div class="page-header">
    <h1>New Community Challenge</h1>
</div>

<form class="form-horizontal" role="form" method="post" action="{{ route('admin/goals/community/challenge/create') }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

    <div class="form-group">
        <label for="cp-communitychallenge-characteristics" class="col-sm-2 control-label"># Characteristics</label>
        <div class="col-sm-10">
            <input type="number" name="total_characteristics" class="form-control" id="cp-communitychallenge-characteristics" value="{{{ Input::old('total_characteristics') }}}" required/>
        </div>
    </div>

    <div class="form-group">
        <label for="cp-communitychallenge-start_date" class="col-sm-2 control-label">Start Date</label>
        <div class="col-sm-10">
            <div class="input-group date">
                <input type="text" name="start_date" class="form-control" id="cp-communitychallenge-start_date" value="{{{ Input::old('start_date') }}}" required/>
                <span class="input-group-addon">
                    <i class="fa fa-calendar"></i>
                </span>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        $(function () {
            $("#cp-communitychallenge-start_date").datetimepicker({
                pickTime: false
            });
        });
    </script>

    <div class="form-group">
        <label for="cp-communitychallenge-end_date" class="col-sm-2 control-label">End Date</label>
        <div class="col-sm-10">
            <div class="input-group date">
                <input type="text" name="end_date" class="form-control" id="cp-communitychallenge-end_date" value="{{{ Input::old('end_date') }}}" required/>
                <span class="input-group-addon">
                    <i class="fa fa-calendar"></i>
                </span>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        $(function () {
            $("#cp-communitychallenge-end_date").datetimepicker({
                pickTime: false
            });
        });
    </script>

    <div class="form-group">
        <label for="cp-communitychallenge-start-date" class="col-sm-2 control-label">Healthy?</label>
        <div class="col-sm-10">
            <div class="checkbox">
                <label>
                    <input type="checkbox" name="healthy" value="yes" {{ (Input::old('healthy') == 'yes') ? 'checked' : '' }} /> Yes
                </label>
            </div>
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-10 col-sm-offset-2 text-right">
            <button type="submit" name="create_community_challenge" class="btn btn-primary">Create</button>
        </div>
    </div>
</form>

@stop
