<?php
/**
 * Plugin Name:       My React Settings Page
 * Plugin URI:        https://example.com
 * Description:       一个使用 React 构建设置页面的演示插件。
 * Version:           1.0.0
 * Author:            你的名字
 * Author URI:        https://example.com
 * Text Domain:       mrsp
 * Domain Path:       /languages
 */

// 防止直接访问文件
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
function mrsp_register_settings_page() {
    // add_menu_page 是 WordPress 的核心函数，用于添加顶级菜单
    add_menu_page(
        'React 设置页面',          // 页面标题 (显示在浏览器标签页)
        'React 设置',              // 菜单标题 (显示在侧边栏)
        'manage_options',           // 所需权限 (只有管理员能看到)
        'my-react-settings-page',   // 菜单的唯一标识 (slug)
        'mrsp_render_settings_page',// 渲染页面内容的回调函数
        'dashicons-admin-generic'   // 菜单图标
    );
}
// 使用 'admin_menu' 钩子来确保在正确的时机执行我们的函数
add_action( 'admin_menu', 'mrsp_register_settings_page' );

/**
 * 渲染设置页面的 HTML 内容
 */
function mrsp_render_settings_page() {
    // 这个 div 就是我们 React 应用的家
    echo '<div id="my-react-settings-app"></div>';
}
/**
 * 为我们的设置页面加载编译后的 React 脚本
 */
function mrsp_enqueue_admin_scripts( $hook_suffix ) {
    // $hook_suffix 是当前页面的钩子后缀
    // toplevel_page_ 是顶级菜单页面的前缀
    // my-react-settings-page 是我们注册菜单时用的 slug
    // 这个判断确保脚本只在我们的插件页面加载，避免影响其他后台页面
    if ( 'toplevel_page_my-react-settings-page' !== $hook_suffix ) {
        return;
    }

    // 引入由 @wordpress/scripts 自动生成的资源清单文件
    $asset_file_path = plugin_dir_path( __FILE__ ) . 'build/index.asset.php';
    if ( ! file_exists( $asset_file_path ) ) {
        throw new Error( 'You need to run `npm install` and `npm run build` first.' );
    }
    $asset_file = include( $asset_file_path );

    // 使用 wp_enqueue_script 加载我们的主 JS 文件
    // wp_enqueue_script(
    //     'my-react-settings-page-script', // 脚本的唯一句柄
    //     plugin_dir_url( __FILE__ ) . 'build/index.js', // 脚本的 URL
    //     $asset_file['dependencies'], // 依赖项，由 asset.php 提供
    //     $asset_file['version'],      // 版本号，由 asset.php 提供
    //     true // true 表示在 body 底部加载
    // );
    wp_enqueue_script(
        'mrsp-script',
        plugins_url('build/index.js', __FILE__),
        $asset_file['dependencies'],
        $asset_file['version'],
        true
    );

}
// 使用 'admin_enqueue_scripts' 钩子来加载后台脚本
add_action( 'admin_enqueue_scripts', 'mrsp_enqueue_admin_scripts' );

/**
 * 注册 REST API 端点
 */
function mrsp_register_rest_routes() {
    // 注册一个用于获取设置的路由
    register_rest_route( 'my-react-settings-page/v1', '/settings',
        [
            'methods'  => 'GET',
            'callback' => 'mrsp_get_settings',
            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            },
        ]
    );

    // 注册一个用于更新设置的路由
    register_rest_route( 'my-react-settings-page/v1', '/settings',
        [
            'methods'  => 'POST',
            'callback' => 'mrsp_save_settings',
            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            },
        ]
    );
}
add_action( 'rest_api_init', 'mrsp_register_rest_routes' );

/**
 * 获取设置的回调函数
 */
function mrsp_get_settings() {
    $message = get_option( 'mrsp_message', '' );
    return new WP_REST_Response( [ 'message' => $message ], 200 );
}

/**
 * 保存设置的回调函数
 */
function mrsp_save_settings( $request ) {
    $message = sanitize_text_field( $request->get_param( 'message' ) );
    update_option( 'mrsp_message', $message );
    return new WP_REST_Response( [ 'success' => true, 'message' => $message ], 200 );
}

/**
 * 在网站前台显示公告栏
 */
function mrsp_display_top_banner() {
    // 1. 获取设置
    // 直接读取保存的 message 选项
    $message = get_option('mrsp_message', '');

    // 2. 检查是否需要显示
    // 如果 'display' 选项为 true 并且消息不为空
    if ( ! empty( $message ) ) {
        // 3. 输出 HTML
        // 使用 esc_html() 来清理消息，防止 XSS 攻击
        echo '<div class="mrsp-top-banner">' . esc_html($message) . '</div>';
    }
}
// 使用 'wp_body_open' 钩子，它会在 <body> 标签刚开始的地方执行我们的函数
// 这是一个现代且推荐的方式来添加页面顶部内容
add_action('wp_body_open', 'mrsp_display_top_banner');

/**
 * 为公告栏添加一些基本样式
 */
function my_plugin_enqueue_frontend_styles() {
    // 只有在需要显示公告时才加载 CSS
    $options = get_option('mrsp_save_settings', ['display' => false]);
    if (!$options['display']) {
        return;
    }

    $css = '.mrsp-top-banner { background-color: #0073aa; color: #fff; text-align: center; padding: 10px; font-size: 16px; }';
    // 使用 wp_add_inline_style 在页面上直接输出少量 CSS，避免了为这点样式多一次文件请求
    wp_register_style('mrsp-banner-style', false);
    wp_enqueue_style('mrsp-banner-style');
    wp_add_inline_style('mrsp-banner-style', $css);
}
// 使用 'wp_enqueue_scripts' 钩子来加载前台的脚本和样式
add_action('wp_enqueue_scripts', 'mrsp_enqueue_frontend_styles');