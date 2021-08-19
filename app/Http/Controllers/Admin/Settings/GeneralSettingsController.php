<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
// use App\Http\Requests\PagesRequest;
use App\Models\Admins;
use App\Models\Adminlogs;
use App\Models\Categories;
use App\Models\News;
use App\Models\Newscategories;
use App\Models\Newslogs;
use App\Models\Newstags;
use App\Models\Tags;
use App\Models\Settings;
use App\Models\Settinglogs;
use App\Models\Staticdatas;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class GeneralSettingsController extends Controller
{
    protected $validationRules = [
        'title' => 'required|alpha_num_spaces',
        'content' => 'required',
        'status' => 'required',
    ];

    protected $validationMessages = [
        'title.required' => 'Title can not be empty.',
        'title.alpha_num_spaces' => 'Title only allowed alphanumeric with spaces.',
        'content.required' => 'Content can not be empty.',
        'status.required' => 'Status must be selected.',
    ];

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->admin = Auth::guard('admin')->user();
        $this->table = 'news';
        $this->admin_url = admin_uri().$this->table;
    }

    public function detail()
    {
        $datas = [
            'table' => $this->table,
            'admin_url' =>$this->admin_url,
            'meta' => [
                'title' => 'Update General Settings',
                'heading' => 'News Management'
            ],
            'css' => [],
            'js' => [
                'admin/set-feature-image',
                'admin/wysiwyg-editor',
            ],
            'breadcrumb' => [
                array(
                    'title' => 'Dashboard',
                    'url' => 'dashboard'
                ),
                array(
                    'title' => 'Settings',
                    'url' => 'settings'
                ),
                array(
                    'title' => 'General Settings',
                    'url' => 'settings/general'
                ),
            ],
            'admindata' => $this->admin,
            'staticdata' => [
                'default_status' => Staticdatas::default_status(),
                'category_tag_type' => Staticdatas::category_tag_type()
            ],
            'settings' => Settings::where('deleted_at', NULL)->get()
        ];

        dd($datas['settings']);

        return view('admin.settings.general', $datas);
    }

    public function update($uuid, Request $request)
    {
        $current = News::where('uuid', $uuid)->first();

        if(!$current) {
            return redirect($this->admin_url)->with([
                'error-message' => 'Not found'
            ]);
        }

        $this->validationRules['permalink'] = 'required|slug';
        $this->validationMessages['permalink.required'] = 'Permalink can not be empty.';
        $this->validationMessages['permalink.slug'] = 'Permalink only allowed aplhanumeric with dash or underscore.';

        $validation = Validator::make($request->all(), $this->validationRules, $this->validationMessages);
        if ($validation->fails()) {
            $errors = $validation->errors()->all();
            Session::flash('errors', $errors );
            Session::flash('request', $request->input() );
            return redirect($this->admin_url.'/detail/'.$uuid)->with([
                'error-message' => 'There is some errors, please check again'
            ]);
        }

        $admin_id = $this->admin->id;

        $path_featured_image = create_uploads_folder();

        $featured_image = $current->featured_image;
        $featured = $request->file('featured');
        if(!empty($featured)) {
            $image_mime_type = $featured->getMimeType();
            $image_extention = $featured->getClientOriginalExtension();
            $image_size = $featured->getSize();

            $image_new_name = uniqid().'.'.$image_extention;

            $featured->move($path_featured_image, $image_new_name);

            $featured_image = $path_featured_image.'/'.$image_new_name;
        }

        $slug = ($request->input('permalink') != $current->slug) ? create_slug($this->table, $request->input('permalink')) : $request->input('permalink');

        News::where('uuid', $uuid)->update(
            array(
                'name' => $request->input('title'),
                'slug' => $slug,
                'featured_image' => $featured_image,
                'content' => $request->input('content'),
                'status' => $request->input('status'),
                'updated_by' => $admin_id
            )
        );

        if(!empty($request->input('tags'))) {
            Newstags::where('news_id', $current->id)->delete();

            $data_log = new Newslogs();
            $data_log->admin_id = $admin_id;
            $data_log->news_id = $current->id;
            $data_log->action = 'DELETE';
            $data_log->action_detail = 'Delete previous news tags';
            $data_log->ipaddress = get_client_ip();
            $data_log->save();

            foreach($request->input('tags') as $tags) {
                $tag_data = Tags::where('deleted_at', NULL)->whereRaw('name = "'.$tags['value'].'"')->first();

                $data_tag = new Newstags();
                $data_tag->tag_id = $tag_data->id;
                $data_tag->news_id = $current->id;
                $data_tag->status = 1;
                $data_tag->created_by = $admin_id;
                $data_tag->updated_by = $admin_id;
                $data_tag->save();

                $data_log = new Newslogs();
                $data_log->admin_id = $admin_id;
                $data_log->news_id = $current->id;
                $data_log->action = 'UPDATE';
                $data_log->action_detail = 'Update news tags';
                $data_log->ipaddress = get_client_ip();
                $data_log->save();
            }
        }

        if(!empty($request->input('categories'))) {
            Newscategories::where('news_id', $current->id)->delete();

            $data_log = new Newslogs();
            $data_log->admin_id = $admin_id;
            $data_log->news_id = $current->id;
            $data_log->action = 'DELETE';
            $data_log->action_detail = 'Delete previous categories of news';
            $data_log->ipaddress = get_client_ip();
            $data_log->save();

            foreach($request->input('categories') as $categories) {
                $category_data = Categories::where('deleted_at', NULL)->whereRaw('name = "'.$categories['value'].'"')->first();

                $data_category = new Newscategories();
                $data_category->category_id = $category_data->id;
                $data_category->news_id = $current->id;
                $data_category->status = 1;
                $data_category->created_by = $admin_id;
                $data_category->updated_by = $admin_id;
                $data_category->save();

                $data_log = new Newslogs();
                $data_log->admin_id = $admin_id;
                $data_log->news_id = $current->id;
                $data_log->action = 'UPDATE';
                $data_log->action_detail = 'Update categories of news';
                $data_log->ipaddress = get_client_ip();
                $data_log->save();
            }
        }

        $action_detail = ($current->name != $request->input('title')) ?
            'Update content and rename title from '.$current->name.' to '.$request->input('title'):
            'Update news '.$current->name;

        $data_log = new Newslogs();
        $data_log->admin_id = $admin_id;
        $data_log->news_id = $current->id;
        $data_log->action = 'UPDATE';
        $data_log->action_detail = $action_detail;
        $data_log->ipaddress = get_client_ip();
        $data_log->save();

        $admin_log = new Adminlogs();
        $admin_log->admin_id = $admin_id;
        $admin_log->table = strtoupper($this->table);
        $admin_log->table_id = $current->id;
        $admin_log->action = 'UPDATE';
        $admin_log->action_detail = $action_detail;
        $admin_log->ipaddress = get_client_ip();
        $admin_log->save();

        return redirect($this->admin_url.'/detail/'.$current['uuid'])->with([
            'success-message' => 'Success update news.'
        ]);
    }
}
