<?php

namespace App\Http\Controllers\Admin\Countries;

use App\Http\Controllers\Controller;
use App\Models\Admins;
use App\Models\Adminrolemodules;
use App\Models\Cities;
use App\Models\Staticdatas;
use Illuminate\Support\Facades\Auth;

class CitiesController extends Controller
{
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
        $this->table = 'cities';
        $this->admin_url = admin_uri().$this->table;
    }

    public function index()
    {
        $datas = [
            'table' => $this->table,
            'admin_url' =>$this->admin_url,
            'meta' => [
                'title' => 'CMS Cities',
                'heading' => 'Cities Management'
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
                    'title' => 'Countries',
                    'url' => $this->table
                ),
                array(
                    'title' => 'Cities',
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

        $datas_list = Cities::where('deleted_at', NULL)->with('province')->with('country');

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
        $order = 'name';
        if(isset($param_get['order'])) {
            $order = $param_get['order'];
            if($param_get['order'] == 'created_date') {
                $order = 'created_at';
            } elseif($param_get['order'] == 'updated_date') {
                $order = 'updated_at';
            }
        }
        $sort = (isset($param_get['sort'])) ? strtoupper($param_get['sort']) : 'ASC';
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
            'head' => [ 'name', 'administration_code', 'postcode', 'area_level', 'province', 'country', 'status', 'created_at', 'updated_at' ],
            'disabled_head' => [ ]
        ];
        $datas['table_head'] = admin_table_head($table_head);
        $datas['table_body_colspan'] = count($table_head['head']);

        return view('admin.cities.index', $datas);
    }
}
