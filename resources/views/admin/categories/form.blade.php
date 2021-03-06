@extends('admin.layout.app')

@section('content')

<?php
    $cur_uri = current_uri();
    $request = Session::get('request') ? Session::get('request') : array();
    $current_route = \Route::currentRouteName();
    $action_url = (str_contains($current_route, 'detail')) ? $admin_url.'/update/'.$current['uuid'] : $admin_url.'/save';
?>

<div class="d-flex flex-column-fluid">
    <div class="container">
        <form class="row form-input" method="POST" action="{{ $action_url }}" id="{{ $table }}" enctype="multipart/form-data">
            <div class="col-md-12 d-flex justify-content-end mb-5">
                @if ( check_admin_access($admindata->role_id, $staticdata['module_slug'], 'edit') == true )
                    <button type="submit" class="btn btn-success mr-2">
                        <i class="fas fa-save"></i> Save
                    </button>
                @endif
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

            <div class="col-md-8">
                <div class="card card-custom mb-8">
                    <div class="card-body">
                        <div class="form-group">
                            <label>
                                Title
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="title" class="form-control"
                                <?php if(str_contains($current_route, 'detail')) { ?>
                                    value="{{ isset($request['title']) ? $request['title'] : $current['name'] }}"
                                <?php } else { ?>
                                    value="{{ isset($request['title']) ? $request['title'] : '' }}"
                                <?php } ?>
                            />
                            <?php if(str_contains($current_route, 'detail')) { ?>
                                <?php 
                                    $permalink = env('APP_URL');

                                    if($cur_uri[4] == 'news') {
                                        $permalink = $permalink.'/'.get_site_settings('permalink_news').'/'.get_site_settings('permalink_news_category').'/';
                                    }
                                ?>
                                <span class="form-text text-muted d-flex align-items-center">
                                    Permalink:&nbsp;
                                     <a href="{{ $permalink }}{{ isset($request['permalink']) ? $request['permalink'] : $current['slug'] }}">
                                        {{ $permalink }}<span id="permalink_slug" class="mr-1 d-inline-block">{{ isset($request['permalink']) ? $request['permalink'] : $current['slug'] }}</span>
                                    </a>
                                    <input type="text" value="{{ isset($request['permalink']) ? $request['permalink'] : $current['slug'] }}"
                                        id="field_permalink_slug"
                                        class="form-control mr-1 d-none w-auto h-auto pt-0 pb-0"
                                        name="permalink"
                                    />
                                    <a class="label label-success label-inline" href="Javascript:;" id="edit_permalink_slug">
                                        edit
                                    </a>
                                </span>
                            <?php } ?>
                        </div>

                        <div class="form-group">
                            <label for="type">
                                Type
                                <span class="text-danger">*</span>
                            </label>
                            <?php
                                $datas = [
                                    'staticdata' => [
                                        'category_tag_type' => $staticdata['category_tag_type']
                                    ]
                                ]
                            ?>
                            {{ view( 'admin.'.$cur_uri[4].'.category_tag_type', $datas ) }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card card-custom mb-8">
                    <div class="card-header">
                        <h3 class="card-title">
                            Setting
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="status">
                                Status
                                <span class="text-danger">*</span>
                            </label>
                            <select class="form-control" name="status" id="status">
                                <option value="">Select Status</option>
                                @foreach ($staticdata['default_status'] as $kS => $status)
                                    @if ($kS != 2)
                                        <option value="{{ $kS }}"
                                            {{ isset($current['status']) && $current['status'] == $kS ? 'selected' : '' }}
                                        >{{ $status }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            @csrf
        </form>
    </div>
</div>

@endsection
