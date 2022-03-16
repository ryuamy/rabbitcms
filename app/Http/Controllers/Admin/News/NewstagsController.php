<?php

namespace App\Http\Controllers\Admin\News;

use App\Http\Controllers\Controller;
use App\Models\Admins;
use App\Models\Adminrolemodules;
use App\Models\Staticdatas;
use App\Models\Tags;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class NewstagsController extends Controller
{
    protected $validationRules = [
        'title' => 'required|alpha_num_spaces',
        'status' => 'required',
    ];

    protected $validationMessages = [
        'title.required' => 'Title can not be empty.',
        'title.alpha_num_spaces' => 'Title only allowed alphanumeric with spaces.',
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
        if(Auth::guard('admin')->user() != null) {
            $admin_id = Auth::guard('admin')->user()->id;
            $this->admin = Admins::where('id', $admin_id)->with('role')->first();
        }
        $this->table = 'tags';
        $this->admin_url = admin_uri().'news/'.$this->table;
    }

    public function index()
    {
        $datas = [
            'table' => $this->table,
            'admin_url' =>$this->admin_url,
            'meta' => [
                'title' => 'CMS News Tags',
                'heading' => 'News Tags Management'
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
                    'title' => 'News',
                    'url' => 'news'
                ),
                array(
                    'title' => 'Tags',
                    'url' => 'news/'.$this->table
                ),
            ],
            'admindata' => $this->admin,
            'staticdata' => [
                'default_status' => Staticdatas::default_status(),
                'module_slug' => 'news_tags',
            ],
            'admin_modules' => Adminrolemodules::where('admin_role_id', $this->admin->role_id)->get(),
        ];

        $param_get = isset($_GET) ? $_GET : [];

        $datas_list = Tags::where('deleted_at', NULL);

        //*** Filter
        if(isset($param_get['action'])) {
            if(isset($param_get['title'])) {
                $name = $param_get['title'];
                if( $param_get['condition'] === 'like' ) {
                    $datas_list = $datas_list->where('name', 'like', '%'.$name.'%');
                }
                if( $param_get['condition'] === 'equal' ) {
                    $datas_list = $datas_list->where('name', $name);
                }
            }
            if( $param_get['status'] !== 'all' ) {
                $datas_list = $datas_list->where('status', $param_get['status']);
            }
            if(isset($param_get['created_from']) && isset($param_get['created_to'])) {
                $datas_list = $datas_list
                    ->where('created_at', '>', date('Y-m-d', strtotime($param_get['created_from'])).' 00:00:00')
                    ->where('created_at', '<', date('Y-m-d', strtotime($param_get['created_to'])).' 23:59:59');
            }
        }
        //*** Filter

        //*** Sort
        $order = 'id';
        if(isset($param_get['order'])) {
            $order = $param_get['order'];
            if($param_get['order'] == 'title') {
                $order = 'name';
            }
            if($param_get['order'] == 'created_date') {
                $order = 'created_at';
            } elseif($param_get['order'] == 'updated_date') {
                $order = 'updated_at';
            }
        }
        $sort = (isset($param_get['sort'])) ? strtoupper($param_get['sort']) : 'DESC';
        $datas_list = $datas_list->orderByRaw($order.' '.$sort);
        //*** Sort

        $datas['total'] = count($datas_list->get());

        $limit = custom_pagination_limit();
        $offset = (isset($param_get['page']) && $param_get['page'] > 1) ? ($param_get['page'] * $limit) - $limit : 0;
        $datas['list'] = $datas_list->offset($offset)->limit($limit)->get();

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
            'head' => [ 'title', 'status', 'created_at', 'updated_at' ],
            'disabled_head' => []
        ];
        $datas['table_head'] = admin_table_head($table_head);
        $datas['table_body_colspan'] = count($table_head['head']);

        return view('admin.tags.index', $datas);
    }

    public function create()
    {
        $datas = [
            'table' => $this->table,
            'admin_url' =>$this->admin_url,
            'meta' => [
                'title' => 'Create News Tag',
                'heading' => 'News Tags Management'
            ],
            'css' => [],
            'js' => [],
            'breadcrumb' => [
                array(
                    'title' => 'Dashboard',
                    'url' => 'dashboard'
                ),
                array(
                    'title' => 'News',
                    'url' => 'news'
                ),
                array(
                    'title' => 'Tag',
                    'url' => 'news/'.$this->table
                ),
                array(
                    'title' => 'Create News Tag',
                    'url' => 'news/'.$this->table.'/create'
                ),
            ],
            'admindata' => $this->admin,
            'staticdata' => [
                'default_status' => Staticdatas::default_status(),
                'category_tag_type' => Staticdatas::category_tag_type(),
                'module_slug' => 'news_tags',
            ],
            'admin_modules' => Adminrolemodules::where('admin_role_id', $this->admin->role_id)->get(),
        ];

        return view('admin.tags.form', $datas);
    }

    public function save(Request $request)
    {
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

        $slug = create_slug($this->table, $request->input('title'));

        $insert = new Tags();
        $insert->uuid = (string) Str::uuid();
        $insert->name = $request->input('title');
        $insert->slug = $slug;
        $insert->type = 1;
        $insert->status = $request->input('status');
        $insert->created_by = $admin_id;
        $insert->updated_by = $admin_id;
        $insert->save();

        $new_data = Tags::where('deleted_at', NULL)
            ->whereRaw('name = "'.$request->input('title').'"')
            ->orderByRaw('id desc')
            ->first();

        insert_admin_logs(
            $admin_id,
            $this->table,
            $new_data->id,
            'INSERT',
            'Create news tags with title '.$new_data->name
        );

        return redirect($this->admin_url.'/detail/'.$new_data['uuid'])->with([
            'success-message' => 'Success add news tags.'
        ]);
    }

    public function detail($uuid)
    {
        $current = Tags::where('uuid', $uuid)->whereRaw('type = 1')->first();

        if(!$current) {
            return redirect($this->admin_url)->with([
                'error-message' => 'Not found'
            ]);
        }

        $datas = [
            'table' => $this->table,
            'admin_url' =>$this->admin_url,
            'meta' => [
                'title' => 'Detail '.$current['name'].' News Tag',
                'heading' => 'News Tags Management'
            ],
            'css' => [],
            'js' => [
                'admin/edit-permalink'
            ],
            'breadcrumb' => [
                array(
                    'title' => 'Dashboard',
                    'url' => 'dashboard'
                ),
                array(
                    'title' => 'News',
                    'url' => 'news'
                ),
                array(
                    'title' => 'Tag',
                    'url' => 'news/'.$this->table
                ),
                array(
                    'title' => 'Detail News Tag',
                    'url' => 'news/'.$this->table.'/detail/'.$uuid
                ),
            ],
            'current' => $current,
            'admindata' => $this->admin,
            'staticdata' => [
                'default_status' => Staticdatas::default_status(),
                'category_tag_type' => Staticdatas::category_tag_type(),
                'module_slug' => 'news_tags',
            ],
            'admin_modules' => Adminrolemodules::where('admin_role_id', $this->admin->role_id)->get(),
        ];

        return view('admin.tags.form', $datas);
    }

    public function update($uuid, Request $request)
    {
        $current = Tags::where('uuid', $uuid)->whereRaw('type = 1')->first();

        if(!$current) {
            return redirect($this->admin_url)->with([
                'error-message' => 'Not found'
            ]);
        }

        $this->validationRules['permalink'] = 'required|slug';
        $this->validationMessages['permalink.required'] = 'Permalink can not be empty.';
        $this->validationMessages['permalink.slug'] = 'Permalink only allowed letters and numbers with dash or underscore.';

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

        $slug = ($request->input('permalink') != $current->slug) ? create_slug($this->table, $request->input('permalink')) : $request->input('permalink');

        Tags::where('uuid', $uuid)->update(
            array(
                'name' => $request->input('title'),
                'slug' => $slug,
                'status' => $request->input('status'),
                'updated_by' => $admin_id
            )
        );

        $action_detail = ($current->name != $request->input('title')) ?
            'Update content and rename title from '.$current->name.' to '.$request->input('title'):
            'Update news tags '.$current->name;

            insert_admin_logs(
                $admin_id,
                $this->table,
                $current->id,
                'UPDATE',
                $action_detail
            );

        return redirect($this->admin_url.'/detail/'.$current['uuid'])->with([
            'success-message' => 'Success update news tags.'
        ]);
    }
}
