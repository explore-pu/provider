<div class="box-footer">

    {{ csrf_field() }}

    <div class="container">
        <div class="col-md-12">
            @if(in_array('submit', $buttons))
                <div class="btn-group pull-right">
                    <button type="submit" class="btn btn-primary">{{ admin_trans('submit') }}</button>
                </div>

                @foreach($submit_redirects as $value => $redirect)
                    @if(in_array($redirect, $checkboxes))
                        <label class="pull-right hidden-xs" style="margin: 5px 10px 0 0;">
                            <input type="checkbox" class="after-submit" name="after-save" value="{{ $value }}" {{ ($default_check == $redirect) ? 'checked' : '' }}> {{ trans("admin.{$redirect}") }}
                        </label>
                    @endif
                @endforeach

            @endif

            @if(in_array('reset', $buttons))
                <div class="btn-group pull-left">
                    <button type="reset" class="btn btn-warning">{{ admin_trans('reset') }}</button>
                </div>
            @endif
        </div>
    </div>
</div>
