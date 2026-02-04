<?php
// test_dm.php
header('Content-Type: text/plain; charset=utf-8');

$host = 'dm8';
$port = 5236;
$user = 'SYSDBA';
$pass = 'Dameng123';

echo "正在尝试连接到达梦数据库...\n";
echo "Host: $host\n";
echo "Port: $port\n";
echo "User: $user\n";

try {
    // 检查 PDO 驱动
    if (!extension_loaded('pdo_dm')) {
        die("错误：pdo_dm 扩展未加载！请检查 php.ini 配置。\n");
    }

    $dsn = "dm:host=$host;port=$port;charset=utf8";
    $conn = new PDO($dsn, $user, $pass);

    // 设置错误模式
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "连接成功！\n";

    // 查询版本
    $stmt = $conn->query("SELECT * FROM V\$VERSION LIMIT 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "数据库版本信息：\n";
    print_r($row);

} catch (PDOException $e) {
    echo "连接失败：\n";
    echo $e->getMessage() . "\n";
}
