<?php
/**
 * PHP扩展检测页面
 * 
 * 检测以下扩展是否已安装：
 * - Swoole
 * - Phalcon
 * - YAC
 * - Redis
 * - 达梦相关扩展
 */

// 扩展列表
$extensions = [
    'swoole' => 'Swoole - 协程、异步IO、高性能网络通信框架',
    'phalcon' => 'Phalcon - C语言编写的高性能PHP框架',
    'yac' => 'YAC - 无锁共享内存缓存',
    'redis' => 'Redis - Redis客户端',
    'mysqli' => 'MySQLi - MySQL数据库扩展',
    'pdo_mysql' => 'PDO MySQL - PDO MySQL驱动',
    'gd' => 'GD - 图像处理库',
    'zip' => 'ZIP - ZIP文件处理',
    'mbstring' => 'Mbstring - 多字节字符串处理',
    'bcmath' => 'BCMath - 高精度数学计算',
    'opcache' => 'OPcache - 字节码缓存',
    'sockets' => 'Sockets - Socket编程',
    'pcntl' => 'PCNTL - 进程控制',
];

// 达梦扩展（如果已安装）
$dm_extensions = [
    'dm' => '达梦原生扩展',
    'pdo_dm' => '达梦PDO驱动',
];

// 检测函数
function checkExtension($ext) {
    if (extension_loaded($ext)) {
        $info = phpversion($ext);
        return ['status' => '✅ 已安装', 'version' => $info ?: '未知版本', 'class' => 'success'];
    }
    return ['status' => '❌ 未安装', 'version' => '-', 'class' => 'error'];
}

// 检测Swoole详细信息
function getSwooleInfo() {
    if (!extension_loaded('swoole')) {
        return null;
    }
    
    $info = [];
    if (function_exists('swoole_version')) {
        $info['version'] = swoole_version();
    }
    
    // 获取配置
    $info['enable_coroutine'] = ini_get('swoole.enable_coroutine');
    $info['reactor_num'] = ini_get('swoole.reactor_num');
    $info['worker_num'] = ini_get('swoole.worker_num');
    
    return $info;
}

// 检测Phalcon详细信息
function getPhalconInfo() {
    if (!extension_loaded('phalcon')) {
        return null;
    }
    
    return [
        'version' => \Phalcon\Version::get(),
        'enable' => ini_get('phalcon.enable'),
    ];
}

// 检测YAC详细信息
function getYacInfo() {
    if (!extension_loaded('yac')) {
        return null;
    }
    
    return [
        'enable' => ini_get('yac.enable'),
        'keys_memory' => ini_get('yac.keys_memory'),
        'values_memory' => ini_get('yac.values_memory'),
    ];
}

// YAC测试
function testYac() {
    if (!extension_loaded('yac')) {
        return ['success' => false, 'message' => 'YAC扩展未安装'];
    }
    
    try {
        $yac = new Yac('test');
        
        // 写入测试
        $yac->set('test_key', 'Hello YAC!', 60);
        
        // 读取测试
        $value = $yac->get('test_key');
        
        // 删除测试
        $yac->delete('test_key');
        
        return [
            'success' => true, 
            'message' => "测试成功！写入和读取值: '$value'"
        ];
    } catch (Exception $e) {
        return ['success' => false, 'message' => '测试失败: ' . $e->getMessage()];
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP扩展检测 - DM8环境</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 1000px;
            margin: 30px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #1890ff;
            padding-bottom: 10px;
            margin-bottom: 30px;
        }
        h2 {
            color: #555;
            margin-top: 30px;
            margin-bottom: 15px;
            padding-left: 10px;
            border-left: 4px solid #1890ff;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e8e8e8;
        }
        th {
            background: #fafafa;
            font-weight: 600;
            color: #333;
        }
        .success {
            color: #52c41a;
            font-weight: 600;
        }
        .error {
            color: #f5222d;
            font-weight: 600;
        }
        .info-box {
            background: #e6f7ff;
            border: 1px solid #91d5ff;
            padding: 15px;
            border-radius: 4px;
            margin: 15px 0;
        }
        .warning-box {
            background: #fffbe6;
            border: 1px solid #ffe58f;
            padding: 15px;
            border-radius: 4px;
            margin: 15px 0;
        }
        .code {
            background: #f1f1f1;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
        }
        .detail {
            background: #f6ffed;
            border: 1px solid #b7eb8f;
            padding: 15px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .detail h3 {
            margin-top: 0;
            color: #389e0d;
        }
        .detail-item {
            margin: 5px 0;
            font-size: 0.95em;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>PHP扩展检测 - DM8环境</h1>
        
        <div class="info-box">
            <strong>PHP版本：</strong> <?php echo PHP_VERSION; ?> | 
            <strong>操作系统：</strong> <?php echo PHP_OS; ?> | 
            <strong>服务器API：</strong> <?php echo php_sapi_name(); ?>
        </div>

        <!-- 核心扩展检测 -->
        <h2>🚀 核心扩展</h2>
        <table>
            <tr>
                <th>扩展名称</th>
                <th>说明</th>
                <th>状态</th>
                <th>版本</th>
            </tr>
            <?php foreach ($extensions as $ext => $desc): ?>
            <?php $result = checkExtension($ext); ?>
            <tr>
                <td><span class="code"><?php echo $ext; ?></span></td>
                <td><?php echo $desc; ?></td>
                <td class="<?php echo $result['class']; ?>"><?php echo $result['status']; ?></td>
                <td><?php echo $result['version']; ?></td>
            </tr>
            <?php endforeach; ?>
        </table>

        <!-- 达梦扩展检测 -->
        <h2>🗄️ 达梦数据库扩展</h2>
        <table>
            <tr>
                <th>扩展名称</th>
                <th>说明</th>
                <th>状态</th>
                <th>版本</th>
            </tr>
            <?php foreach ($dm_extensions as $ext => $desc): ?>
            <?php $result = checkExtension($ext); ?>
            <tr>
                <td><span class="code"><?php echo $ext; ?></span></td>
                <td><?php echo $desc; ?></td>
                <td class="<?php echo $result['class']; ?>"><?php echo $result['status']; ?></td>
                <td><?php echo $result['version']; ?></td>
            </tr>
            <?php endforeach; ?>
        </table>

        <?php if (!extension_loaded('dm') && !extension_loaded('pdo_dm')): ?>
        <div class="warning-box">
            <strong>⚠️ 提示：</strong>达梦PHP扩展需要从达梦官网下载并手动安装。<br>
            下载地址：<a href="https://www.dameng.com/list_97.html" target="_blank">https://www.dameng.com/list_97.html</a>
        </div>
        <?php endif; ?>

        <!-- Swoole详细信息 -->
        <?php $swoole_info = getSwooleInfo(); ?>
        <?php if ($swoole_info): ?>
        <h2>📡 Swoole 详细信息</h2>
        <div class="detail">
            <h3>Swoole 版本: <?php echo $swoole_info['version']; ?></h3>
            <div class="detail-item"><strong>协程支持：</strong> <?php echo $swoole_info['enable_coroutine'] ? '已启用' : '未启用'; ?></div>
            <div class="detail-item"><strong>Reactor线程数：</strong> <?php echo $swoole_info['reactor_num']; ?></div>
            <div class="detail-item"><strong>Worker进程数：</strong> <?php echo $swoole_info['worker_num']; ?></div>
        </div>
        <?php endif; ?>

        <!-- Phalcon详细信息 -->
        <?php $phalcon_info = getPhalconInfo(); ?>
        <?php if ($phalcon_info): ?>
        <h2>⚡ Phalcon 详细信息</h2>
        <div class="detail">
            <h3>Phalcon 版本: <?php echo $phalcon_info['version']; ?></h3>
            <div class="detail-item"><strong>状态：</strong> <?php echo $phalcon_info['enable'] ? '已启用' : '未启用'; ?></div>
        </div>
        <?php endif; ?>

        <!-- YAC详细信息 -->
        <?php $yac_info = getYacInfo(); ?>
        <?php if ($yac_info): ?>
        <h2>💾 YAC 缓存详细信息</h2>
        <div class="detail">
            <h3>YAC 配置</h3>
            <div class="detail-item"><strong>已启用：</strong> <?php echo $yac_info['enable'] ? '是' : '否'; ?></div>
            <div class="detail-item"><strong>键内存：</strong> <?php echo $yac_info['keys_memory']; ?></div>
            <div class="detail-item"><strong>值内存：</strong> <?php echo $yac_info['values_memory']; ?></div>
            
            <h3>YAC 功能测试</h3>
            <?php $yac_test = testYac(); ?>
            <?php if ($yac_test['success']): ?>
                <div class="detail-item" style="color: #52c41a;">✅ <?php echo $yac_test['message']; ?></div>
            <?php else: ?>
                <div class="detail-item" style="color: #f5222d;">❌ <?php echo $yac_test['message']; ?></div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- 使用示例 -->
        <h2>💡 使用示例</h2>
        <div class="info-box">
            <h3>Swoole HTTP服务器示例</h3>
            <pre class="code" style="display: block; padding: 15px; overflow-x: auto;">
&lt;?php
use Swoole\Http\Server;
use Swoole\Http\Request;
use Swoole\Http\Response;

$server = new Server('0.0.0.0', 9501);
$server->on('request', function (Request $request, Response $response) {
    $response->end('Hello Swoole!');
});
$server->start();
?&gt;</pre>
        </div>

        <div class="info-box">
            <h3>YAC 缓存使用示例</h3>
            <pre class="code" style="display: block; padding: 15px; overflow-x: auto;">
&lt;?php
$yac = new Yac('myapp');

// 存储数据（TTL 3600秒）
$yac->set('user:1', $userData, 3600);

// 读取数据
$user = $yac->get('user:1');

// 删除数据
$yac->delete('user:1');

// 清空所有缓存
$yac->flush();
?&gt;</pre>
        </div>

        <div class="info-box">
            <h3>Phalcon 框架初始化示例</h3>
            <pre class="code" style="display: block; padding: 15px; overflow-x: auto;">
&lt;?php
use Phalcon\Mvc\Application;
use Phalcon\Di\FactoryDefault;

$di = new FactoryDefault();
$app = new Application($di);

echo $app->handle($_SERVER['REQUEST_URI'])->getContent();
?&gt;</pre>
        </div>

        <hr style="margin: 40px 0;">
        <p style="text-align: center; color: #999;">
            <a href="/" style="color: #1890ff; text-decoration: none;">← 返回首页</a> | 
            <a href="test_dm_connection.php" style="color: #1890ff; text-decoration: none;">测试达梦连接 →</a>
        </p>
    </div>
</body>
</html>
