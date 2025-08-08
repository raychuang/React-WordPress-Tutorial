### 从零开始，用 React 构建现代化 WordPress 插件
当前绝大部分的前端UI构建已经采用React或者Vue等现代前端框架，虽然wordpress已经内置了React环境，然而很多插件开发者依然停留在传统的 PHP 表单与 jQuery 交互模式，错过了这艘驶向现代化的“巨轮”。

这不仅仅是关于“新”与“旧”的选择，更是关于**效率、体验和未来**
*   **告别繁琐**：用声明式 UI 替代冗长的 PHP/HTML 混编和面条式的 jQuery 代码。
*   **体验飞跃**：构建如桌面应用般流畅、即时响应的设置页面和交互模块。
*   **拥抱生态**：无缝接入庞大的 React 生态系统，复用海量高质量组件。

本指南将作为你的领航员，带你一步步揭开 WordPress 体内这股 React 力量的神秘面纱。我们将从基础配置开始，亲手打造一个功能完备、体验一流的现代化插件设置页面，让你彻底超越那些“古老”的开发模式。

准备好升级你的 WordPress 开发技能了吗？让我们一起驾驭 React，开启全新的创造之旅！

### 第 0 步：准备工作与基础概念科普

在敲代码之前，我们先搞清楚需要什么，以及一些基本原理。

**1. 开发环境：**
*   **本地 WordPress 网站**：你需要一个安装在自己电脑上的 WordPress 网站来做实验。有很多工具可以帮你快速搭建，比如 `Local`, `XAMPP`, `MAMP` 等。
*   **Node.js 和 npm**：React 是一个 JavaScript 库，我们需要 Node.js 环境来运行相关的构建工具。npm (Node Package Manager) 是随 Node.js 一起安装的包管理器，用来安装我们需要的开发库。你可以从 [Node.js 官网](https://nodejs.org/) 下载并安装。

**2. 核心概念科普：WordPress 的“工厂流水线”—— 钩子 (Hooks)**

想象一下，WordPress 的每一次运行，都像一个巨大的、精密的工厂在运作。从接收一个请求开始，到最终输出一个完整的页面，就像一件产品在流水线上被一步步加工、组装。

这条流水线上有很多预设好的**“工位”**。比如，“准备加工文章标题”是一个工位，“开始组装后台菜单”是另一个工位，“即将把最终产品（页面）送出工厂”又是最后一个工位。

我们的插件，就像是给这个工厂添加的**“外挂工序”**。

*   **动作钩子 (Action Hooks)**：就像在流水线的某个特定工位旁，加装一个机器人。当产品（数据流）经过这个工位时，工厂的广播系统会大喊一声（比如 `admin_menu` 信号），我们的机器人听到后，就会立刻执行一个**额外的动作**——比如在侧边栏上钻个孔，装上我们自己的菜单。这个过程通过 `add_action()` 来实现。

    > **`add_action('工位名称', '我们的机器人要执行的任务');`**

*   **过滤器钩子 (Filter Hooks)**：这个更像是流水线上的“质检与再加工”工位。当产品（比如文章标题这个零件）流经这里时，我们的“工序”会把它拿下来，**检查并修改**一下——比如给标题文字前面加上“【HOT】”——然后再放回流水线，让它继续往下走。这个过程通过 `add_filter()` 实现。

理解了“流水线”、“工位”和“外挂工序”这套比喻，你就掌握了 WordPress 插件开发的精髓。我们所有的工作，本质上都是在正确的时间点（工位），挂载上我们自己的功能（工序）。

**3. 核心概念科普：国际化的“护照”—— 文本域 (Text Domain)**

如果说“钩子”是插件功能的“骨架”，那么“文本域”就是你的插件走向世界的“护照”。

*   **它是什么？** `Text Domain` 是一个独一无二的字符串，用来标识你的插件中所有可供翻译的文本。它就像一个专属的命名空间，告诉 WordPress：“嘿，所有标记着 `mrsp` 这个域名的文本，都请使用我提供的翻译文件来显示。”

*   **为什么至关重要？** WordPress 是一个全球化的平台，用户遍布世界各地。如果你希望你的插件能被不同语言的用户使用，就需要提供翻译。`Text Domain` 是实现这一目标的前提。当你把插件提交到官方的 WordPress.org 插件目录时，WordPress 的翻译系统会自动扫描你的代码，找出所有使用了 `Text Domain` 的文本，并为社区翻译者提供在线翻译的平台。**没有正确设置 `Text Domain`，你的插件将无法被官方系统识别和翻译，这几乎是上架审核的硬性要求。**

*   **如何使用？** 它主要体现在两个地方：
    1.  **在插件头声明**：你需要在主插件文件的头部注释中，明确声明你的 `Text Domain` 和存放翻译文件的目录（通常是 `languages`）。
    2.  **在代码中包裹文本**：在代码中，所有需要被翻译的字符串（比如按钮文字、标签、说明等），都需要用特定的国际化函数（如 `__()` 或 `_e()`）包裹起来，并传入 `Text Domain` 作为参数。


### 第 1 步：创建插件并让 WordPress “认识”它

WordPress 如何知道你上传的文件夹是一个插件呢？答案是：通过一个特殊格式的注释，我们称之为 **插件头 (Plugin Header)**。

1.  在你的本地 WordPress 网站的 `wp-content/plugins/` 目录下，创建一个新的文件夹，我们叫它 `my-react-settings-page`。
2.  在这个文件夹里，创建一个 PHP 文件，名字也叫 `my-react-settings-page.php`。
3.  用你的代码编辑器打开这个 PHP 文件，并把下面的代码复制进去：

```php
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

// 后续的代码会加在这里...
```

现在，登录你的 WordPress 后台，进入“插件”页面。你会惊喜地发现，我们名为“My React Settings Page”的插件已经出现在列表里了！这就是插件头的魔力。现在你可以点击“启用”它了。
![wp-plugin-1](https://assets.sitebillion.com/wp-plugin-1.webp)


### 第 2 步：创建后台菜单和设置页面容器

我们需要在 WordPress 后台的侧边栏添加一个菜单项，点击它之后，会跳转到一个页面。这个页面最初是空白的，但它会包含一个关键的 `<div>` 元素，作为我们 React 应用的“挂载点”。

将以下代码添加到你的 `my-react-settings-page.php` 文件中：

```php
// ... 插件头代码之后 ...

/**
 * 注册后台菜单页面
 */
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
```

**代码解读**：
*   我们定义了 `mrsp_register_settings_page` 函数，它使用 `add_menu_page()` 创建了一个菜单。
*   我们用 `add_action()` 把这个函数挂载到了 `admin_menu` 这个钩子上。这意味着当 WordPress 准备渲染后台菜单时，就会调用我们的函数。
*   当用户点击菜单时，WordPress 会调用我们在 `add_menu_page` 中指定的回调函数 `mrsp_render_settings_page`。
*   这个函数非常简单，只输出一个空的 `<div>`，并给它一个 ID `my-react-settings-app`。记住这个 ID，我们马上会用到它。
![wp-plugin-2](https://assets.sitebillion.com/wp-plugin-2.webp)

### 第 3 步：初始化 React 开发环境

现在，轮到 JavaScript 上场了。

1.  打开你的终端 (命令行工具)，进入我们创建的插件目录：
    ```bash
    cd /path/to/your/wordpress/wp-content/plugins/my-react-settings-page
    ```

2.  初始化一个新的 Node.js 项目。这个命令会创建一个 `package.json` 文件，用来管理项目信息和依赖。
    ```bash
    npm init -y
    ```

3.  安装 WordPress 官方提供的构建工具包 `@wordpress/scripts`。它为我们预设好了所有编译 React 和现代 JavaScript 所需的配置，非常方便。
    ```bash
    npm install @wordpress/scripts --save-dev
    ```

4.  打开 `package.json` 文件，找到 `"scripts"` 部分，修改成这样：
    ```json
    "scripts": {
      "build": "wp-scripts build",
      "start": "wp-scripts start"
    },
    ```
    *   `npm run build`: 会编译我们的代码，生成用于生产环境的文件。
    *   `npm run start`: 会启动一个开发模式，它会监视文件变动并自动重新编译，非常适合开发时使用。

5.  在插件目录下创建一个 `src` 文件夹，这里将存放我们所有的 React 源码。
6.  在 `src` 文件夹里，创建一个入口文件 `index.js`。

### 第 4 步：连接 PHP 和 React（加载脚本）

我们的 React 代码需要被编译成浏览器能理解的 JavaScript 文件，然后加载到我们的设置页面上。

首先，运行一次构建命令，生成初始文件：
```bash
npm run build
```
你会发现插件目录下多了一个 `build` 文件夹。这里面就是编译后的 `index.js` 和一个很重要的 `index.asset.php` 文件。

现在，回到 `my-react-settings-page.php`，添加以下代码来加载这些文件：

```php
// ... 之前的 PHP 代码之后 ...

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
    wp_enqueue_script(
        'my-react-settings-page-script', // 脚本的唯一句柄
        plugin_dir_url( __FILE__ ) . 'build/index.js', // 脚本的 URL
        $asset_file['dependencies'], // 依赖项，由 asset.php 提供
        $asset_file['version'],      // 版本号，由 asset.php 提供
        true // true 表示在 body 底部加载
    );
}
// 使用 'admin_enqueue_scripts' 钩子来加载后台脚本
add_action( 'admin_enqueue_scripts', 'mrsp_enqueue_admin_scripts' );
```

**代码解读（这部分很重要！）：**
*   我们把加载脚本的函数 `mrsp_enqueue_admin_scripts` 挂载到了 `admin_enqueue_scripts` 钩子上，这是 WordPress 官方推荐的加载后台脚本的地方。
*   `$hook_suffix` 判断是关键，它保证了我们的 React 代码只在自己的设置页面加载，不会污染其他页面。
*   **`index.asset.php` 的秘密**：这个由 `npm run build` 自动生成的文件是一个 PHP 数组，它包含了两个重要信息：`dependencies` (我们的代码依赖哪些 WordPress 内置的 JS 库，比如 `react`, `wp-element` 等) 和 `version` (一个根据文件内容生成的版本号，用于浏览器缓存控制)。这让我们无需手动管理依赖，非常强大！
*   `wp_enqueue_script()` 是 WordPress 加载脚本的标准方式。我们把从 `asset.php` 文件里读到的信息传给它，WordPress 就会妥善地处理好一切。

### 第 5 步：编写第一个 React 组件

基础架构都搭好了，现在是有趣的部分了！我们的目标是创建一个简单的设置界面，包含一个输入框让用户填写公告内容，以及一个保存按钮。

1.  **安装依赖**：WordPress 提供了一套官方的 React UI 组件库 `@wordpress/components`，以及用于数据交互的库 `@wordpress/data` 和 `@wordpress/api-fetch`。我们先把它们装上。

    ```bash
    npm install @wordpress/components @wordpress/data @wordpress/api-fetch --save
    ```

2.  **创建 App 组件**：在 `src` 目录下新建一个 `App.js` 文件。这是我们设置页面的主组件。

    ```javascript
    import { useState } from '@wordpress/element';
    import { Button, Panel, PanelBody, TextControl } from '@wordpress/components';

    function App() {
        const [message, setMessage] = useState('');

        const handleSave = () => {
            // 保存逻辑稍后添加
            console.log('即将保存:', message);
        };

        return (
            <div className="wrap">
                <h1>公告栏设置</h1>
                <Panel>
                    <PanelBody title="公告内容设置">
                        <TextControl
                            label="公告消息"
                            value={message}
                            onChange={(newMessage) => setMessage(newMessage)}
                            help="输入你想在网站顶部显示的公告内容。"
                        />
                        <Button variant="primary" onClick={handleSave}>
                            保存设置
                        </Button>
                    </PanelBody>
                </Panel>
            </div>
        );
    }

    export default App;
    ```

3.  **渲染 App 组件**：修改入口文件 `src/index.js`，让它把 `App` 组件渲染到我们在 PHP 中创建的 `<div>` 里。

    ```javascript
    import domReady from '@wordpress/dom-ready';
    import { createRoot } from '@wordpress/element';
    import App from "./App";

    domReady( () => {
        const root = createRoot(
            document.getElementById( 'my-react-settings-app' )
        );
        root.render( <App/> );
    } );

    ```

**代码解读**：
*   我们从 `@wordpress/components` 引入了 `Panel`, `PanelBody`, `TextControl`, `Button` 等官方组件，它们能让我们的页面风格与 WordPress 后台保持一致。<mcreference link="https://developer.wordpress.org/news/2024/03/how-to-use-wordpress-react-components-for-plugin-pages/" index="0">0</mcreference>
*   我们使用 React 的 `useState` 来管理输入框中的文本状态。
*   在 `index.js` 中，我们找到了 ID 为 `my-react-settings-app` 的容器元素，并使用 `createRoot` API 将我们的 `App` 组件渲染进去。

现在，再次运行 `npm run start`，刷新你的插件设置页面，你应该能看到一个由 React 驱动的、带有输入框和按钮的设置界面了！
![wp-plugin-3](https://assets.sitebillion.com/wp-plugin-3.webp)

### 第 6 步：通过 REST API 保存和读取设置

现在，我们的设置页面还只是一个“花架子”，输入的数据并不会被保存。要实现数据的持久化，最现代、最优雅的方式就是通过 WordPress 的 REST API。

**1. 在 PHP 中注册 REST API 端点**

首先，我们需要在服务器端（PHP）创建一个“接口”，让前端的 React 应用可以访问。这个接口将负责处理数据的读取和写入。

将以下代码添加到你的主插件文件 `my-react-settings-page.php` 中：

```php
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
```

**代码解读**：
*   我们把注册函数挂载到了 `rest_api_init` 钩子上。
*   `register_rest_route` 创建了两个端点：一个 `GET` 请求用于获取数据，一个 `POST` 请求用于保存数据。它们的地址都是 `[你的域名]/wp-json/my-react-settings-page/v1/settings`。
*   `permission_callback` 是安全性的关键，它确保了只有具备 `manage_options` 权限的用户（即管理员）才能访问这些接口。
*   `mrsp_get_settings` 和 `mrsp_save_settings` 函数分别使用 WordPress 的 `get_option` 和 `update_option` 函数来操作数据库中的 `wp_options` 表，这是存储插件设置的标准做法。
*   `sanitize_text_field` 用于清理用户输入，防止恶意代码，这是一个非常重要的安全习惯。

**2. 在 React 中调用 API**

现在，回到我们的 React 组件 `src/App.js`，让它变得“智能”起来。

```javascript
import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { Button, Panel, PanelBody, TextControl } from '@wordpress/components';

function App() {
    const [message, setMessage] = useState('');
    const [isLoading, setIsLoading] = useState(true);

    // 1. 组件加载时，获取初始数据
    useEffect(() => {
        apiFetch({ path: '/my-react-settings-page/v1/settings' })
            .then((settings) => {
                setMessage(settings.message);
                setIsLoading(false);
            });
    }, []);

    // 2. 点击保存按钮时，发送数据
    const handleSave = () => {
        setIsLoading(true);
        apiFetch({
            path: '/my-react-settings-page/v1/settings',
            method: 'POST',
            data: { message: message },
        }).then((response) => {
            console.log('保存成功!', response);
            setIsLoading(false);
        });
    };

    if (isLoading) {
        return <div>正在加载...</div>;
    }

    return (
        <div className="wrap">
            <h1>公告栏设置</h1>
            <Panel>
                <PanelBody title="公告内容设置">
                    <TextControl
                        label="公告消息"
                        value={message}
                        onChange={(newMessage) => setMessage(newMessage)}
                        help="输入你想在网站顶部显示的公告内容。"
                    />
                    <Button variant="primary" onClick={handleSave} isBusy={isLoading}>
                        保存设置
                    </Button>
                </PanelBody>
            </Panel>
        </div>
    );
}

export default App;
```

**代码解读**：
*   我们引入了 `useEffect` 和 `@wordpress/api-fetch`。
*   `useEffect` Hook 会在组件第一次渲染后执行，我们在这里通过 `apiFetch` 发送一个 GET 请求到刚刚创建的端点，获取已保存的数据，并更新到 `message` 状态中。
*   `handleSave` 函数现在会发送一个 POST 请求，将当前输入框中的 `message` 作为 `data` 发送给后端。
*   我们还增加了一个 `isLoading` 状态，用于在数据加载或保存时显示“正在加载...”提示，并让保存按钮处于“忙碌”状态，提升了用户体验。

现在，你的设置页面已经功能完备了！你可以试着修改内容、点击保存，然后刷新页面——你会发现数据被成功地持久化了。

### 第7步：在前台显示公告栏
我们需要编写一些 PHP 代码，来完成以下三件事：

1. 获取设置 ：从数据库里把我们保存的公告内容和开关状态读出来。
2. 注入内容 ：如果“显示公告”是开启状态，就把公告栏的 HTML 代码插入到网站页面的某个位置。
3. 添加样式 ：给公告栏加上一点 CSS，让它看起来更美观。
请将以下代码添加到你的主插件文件 `my-react-settings-page.php` 的末尾：

```php
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
```
![wp-plugin-4](https://assets.sitebillion.com/wp-plugin-4.webp)

### 总结与展望
恭喜你！从零开始，你已经成功构建了一个功能完整、采用现代前后端分离架构的 WordPress 插件。让我们回顾一下我们所走过的路：

1. 项目初始化 ：我们创建了标准的插件文件结构，并使用 @wordpress/scripts 快速搭建了现代化的 React 开发环境。
2. 核心概念 ：我们用“工厂流水线”的比喻深入理解了 WordPress 的钩子（Hooks）机制，这是插件开发的基石。
3. 后台界面 ：我们用 React 构建了一个动态、响应迅速的设置页面，并利用 WordPress 内置的 api-fetch 模块与后端通信。
4. 数据持久化 ：我们注册了自定义的 REST API 端点，让 React 前端可以安全地将数据保存到 WordPress 数据库中。
5. 前台展示 ：我们利用 PHP 和 Action Hooks 将后台保存的设置读取出来，并动态地展示在网站的前端。
6. 体验优化 ：我们为后台应用增加了加载状态和反馈提示，提升了可用性。
这篇教程为你打开了通往现代 WordPress 开发的大门。以此为基础，你可以探索更多可能性：

- 使用 WordPress UI 组件 ：探索 @wordpress/components 包，用官方的、符合 WordPress 风格的组件（如 ToggleControl , TextControl , Panel 等）来替换原生的 HTML 元素，让你的后台界面更加原生和专业。
- 更复杂的设置 ：增加更多选项，比如设置公告栏的背景颜色、字体大小等。
- 块编辑器（Gutenberg）集成 ：学习如何创建自定义的古腾堡块，让用户可以直接在编辑器中控制你的插件功能。
- 国际化 ：使用 WordPress 的国际化函数（如 __() , _e() ）让你的插件支持多语言。
WordPress 的生态依然充满活力，而掌握现代开发技能的你，无疑将更具竞争力。希望这篇教程能成为你开发者道路上的一块坚实踏板。编码愉快！