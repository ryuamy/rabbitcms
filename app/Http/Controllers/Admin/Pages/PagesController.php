<?php

namespace App\Http\Controllers\Admin\Pages;

use App\Http\Controllers\Controller;
// use App\Http\Requests\PagesRequest;
use App\Models\Admins;
use App\Models\Adminrolemodules;
use App\Models\Pages;
use App\Models\Pagelogs;
use App\Models\Staticdatas;
use App\Rules\IndonesianAddressRule;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class PagesController extends Controller
{
    protected $validationRules = [
        'title' => 'required|alpha_num_spaces',
        'content' => 'required',
        'seo_title' => 'nullable|alpha_num_spaces|max:20',
        'seo_description' => 'nullable|alpha_num_spaces|max:60',
        'seo_facebook_title' => 'nullable|alpha_num_spaces|max:20',
        'seo_facebook_description' => 'nullable|alpha_num_spaces|max:60',
        'seo_twitter_title' => 'nullable|alpha_num_spaces|max:20',
        'seo_twitter_description' => 'nullable|alpha_num_spaces|max:60',
        'status' => 'required',
    ];

    protected $validationMessages = [
        'title.required' => 'Title can not be empty.',
        'title.alpha_num_spaces' => 'Title only allowed alphanumeric with spaces.',
        'content.required' => 'Content can not be empty.',
        'seo_title.alpha_num_spaces' => 'SEO meta title only allowed alphanumeric with spaces.',
        'seo_title.max' => 'SEO meta title may not be greater than 20 characters.',
        'seo_description.alpha_num_spaces' => 'SEO meta description only allowed alphanumeric with spaces.',
        'seo_description.max' => 'SEO meta description may not be greater than 60 characters.',
        'seo_facebook_title.alpha_num_spaces' => 'SEO meta facebook title only allowed alphanumeric with spaces.',
        'seo_facebook_title.max' => 'SEO meta facebook title may not be greater than 20 characters.',
        'seo_facebook_description.alpha_num_spaces' => 'SEO meta facebook description only allowed alphanumeric with spaces.',
        'seo_facebook_description.max' => 'SEO meta facebook description may not be greater than 60 characters.',
        'seo_twitter_title.alpha_num_spaces' => 'SEO meta twitter title only allowed alphanumeric with spaces.',
        'seo_twitter_title.max' => 'SEO meta twitter title may not be greater than 20 characters.',
        'seo_twitter_description.alpha_num_spaces' => 'SEO meta title description only allowed alphanumeric with spaces.',
        'seo_twitter_description.max' => 'SEO meta title description may not be greater than 60 characters.',
        'status.required' => 'Status must be selected.',
    ];

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // dd(Auth::guard('admin')->user());
        // dd(Auth::guard('admin')->check());
        // $this->middleware('admin');
        $this->middleware('auth:admin');
        if(Auth::guard('admin')->user() != null) {
            $admin_id = Auth::guard('admin')->user()->id;
            $this->admin = Admins::where('id', $admin_id)->with('role')->first();
        }
        $this->table = 'pages';
        $this->admin_url = admin_uri().$this->table;
    }

    public function index()
    {
        $datas = [
            'table' => $this->table,
            'admin_url' =>$this->admin_url,
            'meta' => [
                'title' => 'CMS Pages',
                'heading' => 'Pages Management'
            ],
            'css' => [],
            'js' => [
                'admin/bulk-edit',
                'admin/filter-data'
            ],
            'breadcrumb' => [
                array(
                    'title' => 'Dashboard',
                    'url' => 'dashboard'
                ),
                array(
                    'title' => 'Pages',
                    'url' => $this->table
                ),
            ],
            'admindata' => $this->admin,
            'staticdata' => [
                'default_status' => Staticdatas::default_status()
            ],
            'admin_modules' => Adminrolemodules::where('admin_role_id', $this->admin->role_id)->get(),
        ];

        $param_get = isset($_GET) ? $_GET : [];

        $datas_list = custom_admin_sort_filter('pages', $param_get);
        
        $datas['total'] = $datas_list['total'];
        $datas['list'] = json_decode(json_encode($datas_list['datas_list']), true);

        $base_sort_link = custom_sort_link($this->table, $param_get);
        $datas['pagination']['base_sort_link'] = $base_sort_link;

        $page_link = custom_pagination_link($this->table, $param_get);
        $datas['pagination']['page_link'] = $page_link;

        $current_page = isset($param_get['page']) ? (int)$param_get['page'] : 1;
        $pagination_prep = custom_pagination_prep($datas['total'], $current_page);
        $datas['pagination']['showing_from'] = $pagination_prep['showing_from'];
        $datas['pagination']['showing_to'] = $pagination_prep['showing_to'];

        $datas['pagination']['view'] = custom_pagination(
            array(
                'base' => $page_link,
                'page' => $pagination_prep['page'],
                'pages' => $pagination_prep['pages'],
                'key' => 'page',
                'next_text' => '&rsaquo;',
                'prev_text' => '&lsaquo;',
                'first_text' => '&laquo;',
                'last_text' => '&raquo;',
                'show_dots' => TRUE
            )
        );

        $table_head = [
            'table' => $this->table,
            'head' => [ 'title', 'featured_image', 'status', 'created_at', 'updated_at' ],
            'disabled_head' => [ 'featured_image' ]
        ];
        $datas['table_head'] = admin_table_head($table_head);
        $datas['table_body_colspan'] = count($table_head['head']);

        return view('admin.pages.index', $datas);
    }

    public function create()
    {
        $datas = [
            'table' => $this->table,
            'admin_url' =>$this->admin_url,
            'meta' => [
                'title' => 'Create New Page',
                'heading' => 'Pages Management'
            ],
            'css' => [],
            'js' => [
                'admin/edit-permalink',
                'admin/set-feature-image',
                'admin/wysiwyg-editor'
            ],
            'breadcrumb' => [
                array(
                    'title' => 'Dashboard',
                    'url' => 'dashboard'
                ),
                array(
                    'title' => 'Pages',
                    'url' => $this->table
                ),
                array(
                    'title' => 'Create Page',
                    'url' => $this->table.'/create'
                ),
            ],
            'admindata' => $this->admin,
            'staticdata' => [
                'default_status' => Staticdatas::default_status()
            ],
            'admin_modules' => Adminrolemodules::where('admin_role_id', $this->admin->role_id)->get(),
        ];

        return view('admin.pages.form', $datas);
    }

    public function save(Request $request)
    {
        $this->validationRules['seo_focus_keyphrase'] = ['nullable', new IndonesianAddressRule()];
        $this->validationMessages['seo_focus_keyphrase.IndonesianAddressRule'] = 'Focus keyphrase only accept letters, numeric and comma.';

        $validation = Validator::make($request->all(), $this->validationRules, $this->validationMessages);
        if ($validation->fails()) {
            $errors = $validation->errors()->all();
            Session::flash('errors', $errors );
            Session::flash('request', $request->input() );
            return redirect($this->admin_url.'/create')->with([
                'error-message' => 'There is some errors, please check again'
            ]);
        }

        $admin_id = $this->admin->id;

        $path_featured_image = create_uploads_folder();

        $image_new_name = '';
        $featured = $request->file('featured');
        //http://image.intervention.io/api/crop
        if(!empty($featured)) {
            $image_mime_type = $featured->getMimeType();
            $image_extention = $featured->getClientOriginalExtension();
            $image_size = $featured->getSize();

            $image_new_name = uniqid().'.'.$image_extention;

            $featured->move($path_featured_image, $image_new_name);
        }

        $slug = create_slug($this->table, $request->input('title'));

        $seo_title = (!empty($request->input('seo_title'))) ? 
            $request->input('seo_title') : 
            substr(strip_tags($request->input('title')), 0, 20);

        $seo_description = (!empty($request->input('seo_description'))) ? 
            $request->input('seo_description') : 
            substr(strip_tags($request->input('content')), 0, 60);

        $seo_facebook_title = (!empty($request->input('seo_facebook_title'))) ? 
            $request->input('seo_facebook_title') : 
            substr(strip_tags($request->input('title')), 0, 20);

        $seo_facebook_description = (!empty($request->input('seo_facebook_description'))) ? 
            $request->input('seo_facebook_description') : 
            substr(strip_tags($request->input('content')), 0, 60);

        $seo_twitter_title = (!empty($request->input('seo_twitter_title'))) ? 
            $request->input('seo_twitter_title') : 
            substr(strip_tags($request->input('title')), 0, 20);

        $seo_twitter_description = (!empty($request->input('seo_twitter_description'))) ? 
            $request->input('seo_twitter_description') : 
            substr(strip_tags($request->input('content')), 0, 60);

        $seo_focus_keyphrase = (!empty($request->input('seo_focus_keyphrase'))) ? 
            $request->input('seo_focus_keyphrase') : 
            get_site_settings('focus_keyphrase');

        $insert = new Pages();
        $insert->uuid = (string) Str::uuid();
        $insert->name = $request->input('title');
        $insert->slug = $slug;
        $insert->featured_image = $path_featured_image.'/'.$image_new_name;
        $insert->content = $request->input('content');
        $insert->seo_title = $seo_title;
        $insert->seo_description = $seo_description;
        $insert->seo_focus_keyphrase = $seo_focus_keyphrase;
        $insert->seo_facebook_title = $seo_facebook_title;
        $insert->seo_facebook_description = $seo_facebook_description;
        $insert->seo_twitter_title = $seo_twitter_title;
        $insert->seo_twitter_description = $seo_twitter_description;
        $insert->status = $request->input('status');
        $insert->created_by = $admin_id;
        $insert->updated_by = $admin_id;
        $insert->save();

        $new_data = Pages::where('deleted_at', NULL)->whereRaw('name = "'.$request->input('title').'"')->orderByRaw('id desc')->first();

        $data_log = new Pagelogs();
        $data_log->admin_id = $admin_id;
        $data_log->page_id = $new_data->id;
        $data_log->action = 'INSERT';
        $data_log->action_detail = 'Created page';
        $data_log->ipaddress = get_client_ip();
        $data_log->save();

        insert_admin_logs(
            $admin_id,
            $this->table,
            $new_data->id,
            'INSERT',
            'Create new pages with title '.$new_data->name
        );

        return redirect($this->admin_url.'/detail/'.$new_data['uuid'])->with([
            'success-message' => 'Success add new page.'
        ]);
    }

    public function detail($uuid)
    {
        $current = Pages::where('uuid', $uuid)->first();

        if(!$current) {
            return redirect($this->admin_url)->with([
                'error-message' => 'Not found'
            ]);
        }

        $datas = [
            'table' => $this->table,
            'admin_url' =>$this->admin_url,
            'meta' => [
                'title' => 'Detail '.$current['name'].' Page',
                'heading' => 'Pages Management'
            ],
            'css' => [],
            'js' => [
                'admin/edit-permalink',
                'admin/set-feature-image',
                'admin/wysiwyg-editor'
            ],
            'breadcrumb' => [
                array(
                    'title' => 'Dashboard',
                    'url' => 'dashboard'
                ),
                array(
                    'title' => 'Pages',
                    'url' => $this->table
                ),
                array(
                    'title' => 'Detail Page',
                    'url' => $this->table.'/detail/'.$uuid
                ),
            ],
            'current' => $current,
            'admindata' => $this->admin,
            'staticdata' => [
                'default_status' => Staticdatas::default_status()
            ],
            'admin_modules' => Adminrolemodules::where('admin_role_id', $this->admin->role_id)->get(),
        ];

        return view('admin.pages.form', $datas);
    }

    public function update($uuid, Request $request)
    {
        $current = Pages::where('uuid', $uuid)->first();

        if(!$current) {
            return redirect($this->admin_url)->with([
                'error-message' => 'Not found'
            ]);
        }

        $this->validationRules['permalink'] = 'required|slug';
        $this->validationMessages['permalink.required'] = 'Permalink can not be empty.';
        $this->validationMessages['permalink.slug'] = 'Permalink only allowed letters and numbers with dash or underscore.';

        $this->validationRules['seo_focus_keyphrase'] = ['nullable', new IndonesianAddressRule()];
        $this->validationMessages['seo_focus_keyphrase.IndonesianAddressRule'] = 'Focus keyphrase only accept letters, numeric and comma.';

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

        $slug = ($request->input('permalink') != $current->slug) ? 
            create_slug($this->table, $request->input('permalink')) : 
            $request->input('permalink');

        $seo_title = (!empty($request->input('seo_title'))) ? 
            $request->input('seo_title') : 
            substr(strip_tags($request->input('title')), 0, 20);

        $seo_description = (!empty($request->input('seo_description'))) ? 
            $request->input('seo_description') : 
            substr(strip_tags($request->input('content')), 0, 60);

        $seo_facebook_title = (!empty($request->input('seo_facebook_title'))) ? 
            $request->input('seo_facebook_title') : 
            substr(strip_tags($request->input('title')), 0, 20);

        $seo_facebook_description = (!empty($request->input('seo_facebook_description'))) ? 
            $request->input('seo_facebook_description') : 
            substr(strip_tags($request->input('content')), 0, 60);

        $seo_twitter_title = (!empty($request->input('seo_twitter_title'))) ? 
            $request->input('seo_twitter_title') : 
            substr(strip_tags($request->input('title')), 0, 20);

        $seo_twitter_description = (!empty($request->input('seo_twitter_description'))) ? 
            $request->input('seo_twitter_description') : 
            substr(strip_tags($request->input('content')), 0, 60);

        $seo_focus_keyphrase = (!empty($request->input('seo_focus_keyphrase'))) ? 
            $request->input('seo_focus_keyphrase') : 
            get_site_settings('focus_keyphrase');

        Pages::where('uuid', $uuid)->update(
            array(
                'name' => $request->input('title'),
                'slug' => $slug,
                'featured_image' => $featured_image,
                'content' => $request->input('content'),
                'seo_title' => $seo_title,
                'seo_description' => $seo_description,
                'seo_focus_keyphrase' => $seo_focus_keyphrase,
                'seo_facebook_title' => $seo_facebook_title,
                'seo_facebook_description' => $seo_facebook_description,
                'seo_twitter_title' => $seo_twitter_title,
                'seo_twitter_description' => $seo_twitter_description,
                'status' => $request->input('status'),
                'updated_by' => $admin_id
            )
        );

        $action_detail = ($current->name != $request->input('title')) ?
            'Update content and rename title from '.$current->name.' to '.$request->input('title'):
            'Update pages '.$current->name;

        $data_log = new Pagelogs();
        $data_log->admin_id = $admin_id;
        $data_log->page_id = $current->id;
        $data_log->action = 'UPDATE';
        $data_log->action_detail = $action_detail;
        $data_log->ipaddress = get_client_ip();
        $data_log->save();

        insert_admin_logs(
            $admin_id,
            $this->table,
            $current->id,
            'UPDATE',
            $action_detail
        );

        return redirect($this->admin_url.'/detail/'.$current['uuid'])->with([
            'success-message' => 'Success update page.'
        ]);
    }
}
