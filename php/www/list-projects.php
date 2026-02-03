<?php
/**
 * 项目列表页面
 * 自动扫描 ~/Sites/ 目录下的项目
 */

$sitesDir = '/var/www/html';
$projects = [];

// 扫描目录
if (is_dir($sitesDir)) {
    $dirs = scandir($sitesDir);
    foreach ($dirs as $dir) {
        // 跳过隐藏文件和特殊目录
        if ($dir === '.' || $dir === '..' || $dir[0] === '.') {
            continue;
        }
        
        $path = $sitesDir . '/' . $dir;
        if (is_dir($path)) {
            $projects[] = [
                'name' => $dir,
                'url' => '/' . $dir . '/',
                'has_index' => file_exists($path . '/index.php') || file_exists($path . '/index.html'),
            ];
        }
    }
}

// 输出HTML
if (empty($projects)) {
    echo '<div class="project">暂无项目，使用 <code>./dev.sh new-project 项目名</code> 创建</div>';
} else {
    foreach ($projects as $project) {
        $icon = $project['has_index'] ? '📁' : '📂';
        echo '<div class="project">';
        echo $icon . ' <a href="' . htmlspecialchars($project['url']) . '">' . htmlspecialchars($project['name']) . '</a>';
        echo '</div>';
    }
}
