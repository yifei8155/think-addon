<?php
namespace think\addons;
use app\admin\controller\Com;
use app\common\library\Auth;
use think\Config;
use think\Hook;
use think\Lang;
use think\Loader;
use think\Request;

/**
 * 插件基类控制器
 * @package think\addons
 */
class AdminCom extends Com
{
    // 当前插件操作
    protected $addon = null;
    protected $controller = null;
    protected $action = null;
    // 当前template
    protected $template;

    /**
     * 无需登录的方法,同时也就不需要鉴权了
     * @var array
     */
    protected $noNeedLogin = ['*'];

    /**
     * 无需鉴权的方法,但需要登录
     * @var array
     */
    protected $noNeedRight = ['*'];

    /**
     * 权限Auth
     * @var Auth
     */
    protected $auth = null;

    /**
     * 布局模板
     * @var string
     */
    protected $layout = null;

    /**
     * 架构函数
     * @param Request $request Request对象
     * @access public
     */
    public function __construct(Request $request = null)
    {
        if (is_null($request))
        {
            $request = Request::instance();
        }
        // 生成request对象
        $this->request = $request;

        //移除HTML标签
        $this->request->filter('trim,strip_tags,htmlspecialchars');

        // 是否自动转换控制器和操作名
        $convert = Config::get('url_convert');

        $filter = $convert ? 'strtolower' : 'trim';
        // 处理路由参数
        $param = $this->request->param();
        $dispatch = $this->request->dispatch();
        $var = isset($dispatch['var']) ? $dispatch['var'] : [];
        $var = array_merge($param, $var);
        if (isset($dispatch['method']) && substr($dispatch['method'][0], 0, 7) == "\\addons")
        {
            $arr = explode("\\", $dispatch['method'][0]);
            $addon = strtolower($arr[2]);
            $controller = strtolower(end($arr));
            $action = $dispatch['method'][1];
        }
        else
        {
            $addon = isset($var['addon']) ? $var['addon'] : '';
            $controller = isset($var['controller']) ? $var['controller'] : '';
            $action = isset($var['action']) ? $var['action'] : '';
        }

        $this->addon = $addon ? call_user_func($filter, $addon) : '';
        $this->controller = $controller ? call_user_func($filter, $controller) : 'index';
        $this->action = $action ? call_user_func($filter, $action) : 'index';

        // 重置配置
        Config::set('template.view_path', ADDON_PATH . $this->addon . DS . 'view' . DS);

        // 父类的调用必须放在设置模板路径之后
        parent::__construct($this->request);
    }

    public function _initialize() {
        parent::_initialize();
    }
}
