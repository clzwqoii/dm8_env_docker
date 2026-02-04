<?php
header("Content-type:text/html;charset=utf-8"); //防止页面乱码

/**
 * 达梦数据库连接主机名说明：
 * 
 * 1. 在Docker容器内（PHP代码）：
 *    - 使用 "dm8:5236" - Docker内部DNS会解析到dm8容器
 *    - ❌ 不能用 127.0.0.1 或 localhost（这是PHP容器自己的localhost）
 * 
 * 2. 在宿主机上（SQLark百灵等客户端）：
 *    - 使用 "localhost:5236" 或 "127.0.0.1:5236" - 通过端口映射连接
 *    - ❌ 不能用 dm8（宿主机无法解析Docker内部主机名）
 */

try {
    // PHP容器内必须使用 dm8 作为主机名
    ($link = dm_connect("dm8:5236", "SYSDBA", "123456")) or
        die("Could not connect : " . dm_error() . "\n");

    //使用 dm_error 会显示 dm 的 php 接口返回的错误，执行成功，则继续往下执行。

    print "php: Connected successfully" . "\n";

    /*断开连接*/

    dm_close($link);
} catch (Exception $e) {
    $e->getMessage() . "<br/>";
}
?>