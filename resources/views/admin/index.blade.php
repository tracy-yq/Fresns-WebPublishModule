@extends('WebPublishModule::commons.fresns')

@section('content')
    <div class="p-5 mx-5">
        <form action="{{ route('web-publish-module.admin.update') }}" method="post">
            @csrf
            <div class="input-group mb-3">
                <span class="input-group-text">authKey</span>
                @if ($authKey)
                    <input type="text" class="form-control" value="{{ $authKey }}" readonly>
                    <button type="submit" class="btn btn-danger">{{ __('FsLang::panel.button_reset') }}</button>
                @else
                    <input type="text" class="form-control" value="" readonly>
                    <button type="submit" class="btn btn-primary">{{ __('FsLang::panel.button_config') }}</button>
                @endif
            </div>
        </form>
        <div class="input-group mb-3">
            <span class="input-group-text">URL</span>
            <div class="form-control">
                @if ($authKey)
                    <a href="{{ route('web-publish-module.publish.editor') }}" class="link-primary" target="_blank">{{ route('web-publish-module.publish.editor') }}</a>
                @endif
            </div>
        </div>
    </div>
@endsection
