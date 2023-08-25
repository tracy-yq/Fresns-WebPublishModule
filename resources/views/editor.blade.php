@extends('WebPublishModule::commons.master')

@section('content')
    <div class="container">
        <form class="form-post-box my-4" action="{{ route('web-publish-module.publish.web.submit') }}" method="post" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="authKey" value="{{ $authKey }}">

            <div class="input-group mb-3">
                <span class="input-group-text">User</span>
                <input type="number" class="form-control" name="uid" placeholder="uid">
            </div>

            <div class="input-group mb-3">
                <span class="input-group-text">Group</span>
                <input type="text" class="form-control" name="postGid" placeholder="postGid">
            </div>

            <div class="input-group mb-3">
                <span class="input-group-text">Title</span>
                <input type="text" class="form-control" name="postTitle" placeholder="postTitle">
            </div>

            <div class="input-group mb-3">
                <span class="input-group-text">Content</span>
                <textarea class="form-control" name="content"></textarea>
            </div>

            <div class="input-group mb-3">
                <span class="input-group-text">Content Markdown</span>
                <div class="form-control">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="isMarkdown" value="1" id="isMarkdown">
                        <label class="form-check-label" for="isMarkdown">Content Markdown</label>
                    </div>
                </div>
            </div>

            <div class="input-group mb-3">
                <span class="input-group-text">Image</span>
                <input type="file" class="form-control" name="image">
            </div>

            <div class="input-group mb-3">
                <span class="input-group-text">Publish Time</span>
                <input type="datetime-local" class="form-control" name="datetime">
            </div>

            <button type="submit" class="btn btn-success btn-lg">Publish</button>
        </form>
    </div>

    @push('script')
        <script>
            // show loading spinner while processing a form
            // https://getbootstrap.com/docs/5.1/components/spinners/
            $(document).on('submit', 'form', function () {
                var btn = $(this).find('button[type="submit"]');
                btn.prop('disabled', true);
                if (0 === btn.children('.spinner-border').length) {
                    btn.prepend(
                        '<span class="spinner-border spinner-border-sm mg-r-5 d-none" role="status" aria-hidden="true"></span> '
                    );
                }
                btn.children('.spinner-border').removeClass('d-none');
            });

            // submit
            $('.form-post-box').submit(function (e) {
                e.preventDefault();
                let form = $(this),
                    data = new FormData($(this)[0]),
                    btn = $(this).find('button[type="submit"]'),
                    actionUrl = $(this).attr('action');

                $.ajax({
                    type: 'POST',
                    url: actionUrl,
                    data: data, // serializes the form's elements.
                    processData: false,
                    cache: false,
                    contentType: false,
                    success: function (res) {
                        window.tips(res.message, res.code);
                        if (res.code != 0) {
                            return;
                        }

                        setTimeout(function() {
                            window.location.reload();
                        }, 2000);
                    },
                    complete: function (e) {
                        btn.prop('disabled', false);
                        btn.find('.spinner-border').remove();
                    },
                });
            });
        </script>
    @endpush
@endsection
