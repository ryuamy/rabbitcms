@extends('admin.layout.app')

@section('content')

<?php
    $cur_uri = current_uri();
    $request = Session::get('request') ? Session::get('request') : array();
    $current_route = \Route::currentRouteName();
    $action_url = $admin_url.'/seo/update';
?>

<div class="d-flex flex-column-fluid">
    <div class="container">
        <form class="row form-input" method="POST" action="{{ $action_url }}" id="{{ $table }}" enctype="multipart/form-data">
            <div class="col-md-12 d-flex justify-content-end mb-5">
                <button type="submit" class="btn btn-success mr-2">
                    <i class="fas fa-save"></i> Save
                </button>
                <a class="btn btn-dark" href="{{ $admin_url }}">
                    Cancel
                </a>
            </div>

            @if (Session::has('success-message'))
                <div class="col-md-12 mb-5">
                    <div class="alert alert-custom alert-success d-flex show fade" role="alert">
                        <div class="alert-text" id="alert_message_login">
                            {{ Session::get('success-message') }}
                        </div>
                        <div class="alert-close">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">
                                    <i class="ki ki-close"></i>
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            @if (Session::has('error-message'))
                <div class="col-md-12 mb-5">
                    <div class="alert alert-custom alert-danger d-flex show fade" role="alert">
                        <div class="alert-text" id="alert_message_login">
                            {{ Session::get('error-message') }}
                            @if (Session::has('errors'))
                                <?php $errors = Session::get('errors'); ?>
                                <ul class="m-0">
                                    @foreach ($errors as $err)
                                        <li>{{ $err }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                        <div class="alert-close">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">
                                    <i class="ki ki-close"></i>
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <div class="col-md-12">
                <div class="card card-custom mb-8">
                    <div class="card-header">
                        <h3 class="card-title">Uploading Files</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>
                                Organize Uploads
                            </label>
                            <div class="checkbox-list">
                                <label class="checkbox">
                                    <input type="checkbox" value="1" {{ (isset($request['organize_uploads']) && $request['organize_uploads'] == 1) || $settings['organize_uploads'] == 1 ? 'checked' : '' }} name="organize_uploads">
                                    <span></span>Organize my uploads into month and year based folders
                                </label>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="card card-custom mb-8">
                    <div class="card-header">
                        <h3 class="card-title">
                            Image sizes
                        </h3>
                    </div>
                    <div class="card-body">
                        <p class="form-text text-danger">
                            The sizes listed below determine the maximum dimensions in pixels to use when adding an image to the Media Library.
                        </p>
                        <div class="form-group">
                            <label>
                                Google Verification Code
                            </label>
                            <input type="text" name="google_verification_code" class="form-control"
                                value="{{ isset($request['google_verification_code']) ? $request['google_verification_code'] : $settings['google_verification_code'] }}"
                            />
                            <span class="form-text text-muted">
                                Get your Google verification code in
                                <a href="https://www.google.com/webmasters/verification/verification?hl=en&tid=alternate&siteUrl={{ env('APP_URL') }}">Google Search Console</a>.
                            </span>
                        </div>

                        <div class="form-group">
                            <label>
                                Bing Verification Code
                            </label>
                            <input type="text" name="bing_verification_code" class="form-control"
                                value="{{ isset($request['bing_verification_code']) ? $request['bing_verification_code'] : $settings['bing_verification_code'] }}"
                            />
                            <span class="form-text text-muted">
                                Get your Bing verification code in
                                <a href="https://www.bing.com/toolbox/webmaster/#/Dashboard/?url={{ env('APP_URL') }}">Bing Webmaster Tools</a>.
                            </span>
                        </div>

                    </div>
                </div>
            </div>

            @csrf
        </form>
    </div>
</div>

@endsection
