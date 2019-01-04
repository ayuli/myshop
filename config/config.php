<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return [
    // +----------------------------------------------------------------------
    // | 应用设置
    // +----------------------------------------------------------------------

    // 应用命名空间
    'app_namespace'          => 'app',
    // 应用调试模式
    'app_debug'              => true,
    // 应用Trace
    'app_trace'              => false,
    // 应用模式状态
    'app_status'             => '',
    // 是否支持多模块
    'app_multi_module'       => true,
    // 入口自动绑定模块
    'auto_bind_module'       => false,
    // 注册的根命名空间
    'root_namespace'         => [],
    // 扩展函数文件
    'extra_file_list'        => [THINK_PATH . 'helper' . EXT],
    // 默认输出类型
    'default_return_type'    => 'html',
    // 默认AJAX 数据返回格式,可选json xml ...
    'default_ajax_return'    => 'json',
    // 默认JSONP格式返回的处理方法
    'default_jsonp_handler'  => 'jsonpReturn',
    // 默认JSONP处理方法
    'var_jsonp_handler'      => 'callback',
    // 默认时区
    'default_timezone'       => 'PRC',
    // 是否开启多语言
    'lang_switch_on'         => false,
    // 默认全局过滤方法 用逗号分隔多个
    'default_filter'         => '',
    // 默认语言
    'default_lang'           => 'zh-cn',
    // 应用类库后缀
    'class_suffix'           => false,
    // 控制器类后缀
    'controller_suffix'      => false,

    // +----------------------------------------------------------------------
    // | 模块设置
    // +----------------------------------------------------------------------

    // 默认模块名
    'default_module'         => 'index',
    // 禁止访问模块
    'deny_module_list'       => ['common'],
    // 默认控制器名
    'default_controller'     => 'Index',
    // 默认操作名
    'default_action'         => 'index',
    // 默认验证器
    'default_validate'       => '',
    // 默认的空控制器名
    'empty_controller'       => 'Error',
    // 操作方法后缀
    'action_suffix'          => '',
    // 自动搜索控制器
    'controller_auto_search' => false,

    // +----------------------------------------------------------------------
    // | URL设置
    // +----------------------------------------------------------------------

    // PATHINFO变量名 用于兼容模式
    'var_pathinfo'           => 's',
    // 兼容PATH_INFO获取
    'pathinfo_fetch'         => ['ORIG_PATH_INFO', 'REDIRECT_PATH_INFO', 'REDIRECT_URL'],
    // pathinfo分隔符
    'pathinfo_depr'          => '/',
    // URL伪静态后缀
    'url_html_suffix'        => 'html',
    // URL普通方式参数 用于自动生成
    'url_common_param'       => false,
    // URL参数方式 0 按名称成对解析 1 按顺序解析
    'url_param_type'         => 0,
    // 是否开启路由
    'url_route_on'           => true,
    // 路由使用完整匹配
    'route_complete_match'   => false,
    // 路由配置文件（支持配置多个）
    'route_config_file'      => ['route'],
    // 是否强制使用路由
    'url_route_must'         => false,
    // 域名部署
    'url_domain_deploy'      => false,
    // 域名根，如thinkphp.cn
    'url_domain_root'        => '',
    // 是否自动转换URL中的控制器和操作名
    'url_convert'            => true,
    // 默认的访问控制器层
    'url_controller_layer'   => 'controller',
    // 表单请求类型伪装变量
    'var_method'             => '_method',
    // 表单ajax伪装变量
    'var_ajax'               => '_ajax',
    // 表单pjax伪装变量
    'var_pjax'               => '_pjax',
    // 是否开启请求缓存 true自动缓存 支持设置请求缓存规则
    'request_cache'          => false,
    // 请求缓存有效期
    'request_cache_expire'   => null,

    // +----------------------------------------------------------------------
    // | 模板设置
    // +----------------------------------------------------------------------

    'template'               => [
        // 模板引擎类型 支持 php think 支持扩展
        'type'         => 'Think',
        // 模板路径
        'view_path'    => '',
        // 模板后缀
        'view_suffix'  => 'html',
        // 模板文件名分隔符
        'view_depr'    => DS,
        // 模板引擎普通标签开始标记
        'tpl_begin'    => '{',
        // 模板引擎普通标签结束标记
        'tpl_end'      => '}',
        // 标签库标签开始标记
        'taglib_begin' => '{',
        // 标签库标签结束标记
        'taglib_end'   => '}',
    ],

    // 视图输出字符串内容替换
    'view_replace_str'       => [],
    // 默认跳转页面对应的模板文件
    'dispatch_success_tmpl'  => THINK_PATH . 'tpl' . DS . 'dispatch_jump.tpl',
    'dispatch_error_tmpl'    => THINK_PATH . 'tpl' . DS . 'dispatch_jump.tpl',

    // +----------------------------------------------------------------------
    // | 异常及错误设置
    // +----------------------------------------------------------------------

    // 异常页面的模板文件
    'exception_tmpl'         => THINK_PATH . 'tpl' . DS . 'think_exception.tpl',

    // 错误显示信息,非调试模式有效
    'error_message'          => '页面错误！请稍后再试～',
    // 显示错误信息
    'show_error_msg'         => false,
    // 异常处理handle类 留空使用 \think\exception\Handle
    'exception_handle'       => '',

    // +----------------------------------------------------------------------
    // | 日志设置
    // +----------------------------------------------------------------------

    'log'                    => [
        // 日志记录方式，内置 file socket 支持扩展
        'type'  => 'File',
        // 日志保存目录
        'path'  => LOG_PATH,
        // 日志记录级别
        'level' => [],
    ],

    // +----------------------------------------------------------------------
    // | Trace设置 开启 app_trace 后 有效
    // +----------------------------------------------------------------------
    'trace'                  => [
        // 内置Html Console 支持扩展
        'type' => 'Html',
    ],

    // +----------------------------------------------------------------------
    // | 缓存设置
    // +----------------------------------------------------------------------

    'cache'                  => [
        // 驱动方式
        'type'   => 'File',
        // 缓存保存目录
        'path'   => CACHE_PATH,
        // 缓存前缀
        'prefix' => '',
        // 缓存有效期 0表示永久缓存
        'expire' => 0,
    ],

    // +----------------------------------------------------------------------
    // | 会话设置
    // +----------------------------------------------------------------------

    'session'                => [
        'id'             => '',
        // SESSION_ID的提交变量,解决flash上传跨域
        'var_session_id' => '',
        // SESSION 前缀
        'prefix'         => 'think',
        // 驱动方式 支持redis memcache memcached
        'type'           => '',
        // 是否自动开启 SESSION
        'auto_start'     => true,
    ],

    // +----------------------------------------------------------------------
    // | Cookie设置
    // +----------------------------------------------------------------------
    'cookie'                 => [
        // cookie 名称前缀
        'prefix'    => '',
        // cookie 保存时间
        'expire'    => 0,
        // cookie 保存路径
        'path'      => '/',
        // cookie 有效域名
        'domain'    => '',
        //  cookie 启用安全传输
        'secure'    => false,
        // httponly设置
        'httponly'  => '',
        // 是否使用 setcookie
        'setcookie' => true,
    ],

    //分页配置
    'paginate'               => [
        'type'      => 'bootstrap',
        'var_page'  => 'page',
        'list_rows' => 15,
    ],

        //验证码
    'captcha'  => [
        // 验证码字符集合
        'codeSet'  => '2345678abcdefhijkmnpqrstuvwxyzABCDEFGHJKLMNPQRTUVWXY', 
        // 验证码字体大小(px)
        'fontSize' => 25, 
        // 是否画混淆曲线
        'useCurve' => true, 
            // 验证码图片高度
        'imageH'   => 60,
        // 验证码图片宽度
        'imageW'   => 180, 
        // 验证码位数
        'length'   => 4,
        // 验证成功后是否重置        
        'reset'    => true
    ],
    // 支付宝支付配置
    'alipay_config'=>[
        //应用ID,您的APPID。
        'app_id' => "2016092200571678",

        //商户私钥
        'merchant_private_key' => "MIIEpQIBAAKCAQEAxo6KYizOSHkrVVCeRbaJcz29immoz/2EnpRr0eQZGLlH3cHexy0IndKiU8ZUwnbe7j3WjCPQtYWOtHeDWsLpbglr0MhWZ7j6rtON0hHeLqVwW39EMobSKPIRCkwefKLy3JYThCWi87GEtrbEgKFDEy3XV8zcLuFEDx8TDbNixRkEJoOPEp1uI9JY1CvWFihW7LgffHe7ZRZKwEw2B9vj7wXP+t/z1cdwK9gFWpb/q2TbeTeok4Dg/CQrGsgHLlWIJ6Vaz3WZyf/PsNKJArWiBDxxXi4mjEiDceNr22nog75l455aJzs8jxaFX7odNCnODDfRIgbJBbzXSPdOnCmWIQIDAQABAoIBAQCpSjz4qaUTq6PjsMHRRGn6xKhHTADvnQKmxnsLUMSJDOgWTcao4V+YWE+ox8/aock/LaAwihTXv9DFU6S+DLND62pub3EkLFYoGx2u/ycFdG7QxDicL9Edsg4PTokr0S7qHlbzc7d0uWUi97+gwF3VC1+wb21H1y7VCbSQGONckY4dmwi1yNusb05UDz1TihrKAXlNINh84kxkil+PIjEggXKWIzFAvVTI2DYg+UQ25ZZ798j3s1yX++Mfpxg0ZkhGRCct3rkg50QjooHaYUn3MRcmZqytySiHrH6dqTv0bfbWA9xt3W3jE6r2Cw/KEmu0X/sqQGqMqkvUHcTYTI/lAoGBAPqE2L5S0gq5sNY20MPyWc3PidJfU6GAK64PnqCJBSsJeDDb2trEoPqV/uvHMWxlzubP6p0ibKIOAlFMmuvi438GN4J7UN0RWAqkklOEbutYqXz0v0/aLeG0zAXA0wb88Hl3k0O4/gt5nC22m2OGUdFYx848+t6CzG4fV4OFdPC7AoGBAMrmp5fbaLukwqWdl2TCSH5PcXW8M1h0qWcF9q5jk1BGb3TkEPiqwFAELMpGHUdKp19vXeQhWuQdQoQvGepYAp/b8IxhlgdACV+lapEzlWF5pQp4L6jWY+Ntx/WDU9nV9rmeM+8lRTAEx8h/32xxlRbVrllkZ56/OkfOtNKR6MTTAoGAG7yJosjYqIdWpOD8zDUQ7FCGGdykDcV7EJJ6JZwGDnXF8OLmoEIU8Kmax9AOEMpU1gk/GJavIGIcr5Ojs2ozdeTiUHgGw/MfpE4ERWrsPPJcEZGnfNfTLKIv1JM8yhFHLqumimD4/5w/RnBpU+OTO3Jw7Myjme8xYx/lOJKWF/ECgYEAs/mspxLD783Rqj4DUCjVTNUC+0AY+1lf+jZb/Yvt28LeknzKYQh01YUehb1zsw0RMaIDUpwTl0ws+DsfbbLrgQkCckMNZRj0N5bu1CVBV9kjAApjnAuvRsjWGXyIIC2eUD94KaSRnmpfHAXxFKwbZsnbgQeCsllFRPShiDZMlmkCgYEAhkG4Yh1MPfpVwOsnn9E7uhxJdNNSJRb8WcIqjkgyiXHxJItzf08TIkXyxdrhz2sX13vY6GgdJCV3PxjghIarqZEZbHY3AZf2eHFP3Gtl+ZbXr6wIvbjuLs8mUkqKQEO/qhllRDN1Z2iraSizQ5EKa7ml7UGK6ayGngRxWpXVZXw=",

        //异步通知地址
        'notify_url' => "http://119.23.186.148/myshop/public/index.php/order/notify",

        //同步跳转
        'return_url' => "http://119.23.186.148/myshop/public/index.php/order/paysuccess",

        //编码格式
        'charset' => "UTF-8",

        //签名方式
        'sign_type'=>"RSA2",

        //支付宝网关
        'gatewayUrl' => "https://openapi.alipaydev.com/gateway.do",

        //支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
        'alipay_public_key' => "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA8acZAUQsVFMWXpD0i09SCX8EPxSwbQWtoNmjQnvKeYjJwj6jhL6Ol9dDUBRO+SsB8qb6mXZAx4HyAEwiivt3EeKuDzJxc2bAro02oPFbU4UCff3qZH608gBiXxm9zpAE/CIu8CLjjTUHL+ZwdJ1xc/FN31g1lwLAhnTN2ipz6eazHJtTIpqhOvuaYwOcNg9hiBN4ikS+MF4DvvqumL7zWq5MlHOIVc5nGLzjqU6alnoj6nGIjk+pPMjF3JS9QcO4Zk+ocNF0g5jO7Si2kHnIM0EA2psZa1I1QpsdadhpL0V7rZ4Hf0+bd9+0u3yciPsQrWzbrfQntSE5aen1Y+JwrQIDAQAB",

    ],
];
