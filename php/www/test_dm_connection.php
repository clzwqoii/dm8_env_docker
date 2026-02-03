<?php
/**
 * 达梦数据库连接测试
 * 
 * 本文件演示了如何使用PHP连接达梦数据库
 * 支持两种方式：PDO和原生dm扩展
 */

// 错误处理
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 数据库配置
$db_config = [
    'host' => $_ENV['DM_HOST'] ?? 'dm8',
    'port' => $_ENV['DM_PORT'] ?? '5236',
    'user' => $_ENV['DM_USER'] ?? 'SYSDBA',
    'password' => $_ENV['DM_PWD'] ?? '123456',
    'database' => 'SYSTEM'
];

echo "<h1>达梦数据库连接测试</h1>";
echo "<hr>";

// ============ PDO方式连接 ============
echo "<h2>方式一：PDO连接</h2>";

try {
    // PDO达梦连接字符串格式：dm:host=主机;port=端口;dbname=数据库
    $dsn = "dm:host={$db_config['host']};port={$db_config['port']};dbname={$db_config['database']}";
    
    $pdo = new PDO($dsn, $db_config['user'], $db_config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    
    echo "<p style='color: green;'>PDO连接成功！</p>";
    
    // 测试查询
    $stmt = $pdo->query("SELECT 'Hello DM8' as message FROM DUAL");
    $result = $stmt->fetch();
    echo "<p>测试查询结果: " . $result['message'] . "</p>";
    
    // 获取数据库信息
    $stmt = $pdo->query("SELECT * FROM v\$instance");
    $instance_info = $stmt->fetch();
    echo "<h3>数据库实例信息</h3>";
    echo "<pre>";
    print_r($instance_info);
    echo "</pre>";
    
    $pdo = null;
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>PDO连接失败: " . $e->getMessage() . "</p>";
}

echo "<hr>";

// ============ 原生dm扩展方式 ============
echo "<h2>方式二：原生dm扩展连接</h2>";

if (extension_loaded('dm')) {
    try {
        // 使用dm_connect函数
        $conn = dm_connect(
            "{$db_config['host']}:{$db_config['port']}",
            $db_config['user'],
            $db_config['password']
        );
        
        if ($conn) {
            echo "<p style='color: green;'>dm扩展连接成功！</p>";
            
            // 执行查询
            $result = dm_exec($conn, "SELECT 'Hello from dm extension' as message FROM DUAL");
            if ($result) {
                $row = dm_fetch_array($result);
                echo "<p>测试查询结果: " . $row['MESSAGE'] . "</p>";
            }
            
            dm_close($conn);
        } else {
            echo "<p style='color: red;'>dm扩展连接失败</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>dm扩展错误: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: orange;'>dm扩展未安装或未启用</p>";
}

echo "<hr>";

// ============ 环境信息 ============
echo "<h2>环境信息</h2>";
echo "<ul>";
echo "<li>PHP版本: " . PHP_VERSION . "</li>";
echo "<li>达梦主机: {$db_config['host']}</li>";
echo "<li>达梦端口: {$db_config['port']}</li>";
echo "<li>达梦用户: {$db_config['user']}</li>";
echo "<li>已加载扩展: " . implode(', ', get_loaded_extensions()) . "</li>";
echo "</ul>";

echo "<hr>";
echo "<p><a href='/' style='color: blue;'>返回首页</a></p>";
