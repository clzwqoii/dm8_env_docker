# 达梦数据库 + PHP 8.3 Docker 开发环境

专为Mac开发环境设计，解决本地PHP无法安装达梦扩展的问题。

# 默认创建镜像流程
# 如果开启vpn, 网络不稳定, 可以关闭vpn
```
# 重建PHP镜像
docker compose build --no-cache php-dm

# 构建（并启动）
docker compose up -d --build

# 启动（已存在的容器）
docker compose start

# 停止
docker compose stop

# 重启
docker compose restart

# 验证 PHP 扩展
# 我们需要确认编译的扩展已正确加载。我们将运行：
docker compose exec php-dm php -m | grep -E 'phalcon|swoole|yac|dm|pdo_dm'
# 在浏览器中打开
http://dm8.local:8080/ 或 http://localhost:8080/phpinfo.php
```

**极简启动**: `docker-compose up -d`

## 架构

```
Mac本地 ~/Sites/          Docker容器
     │                        │
     └──────────┬─────────────┘
                │
         ┌──────┴──────┐
         │             │
       Nginx ───────► PHP 8.3 FPM ───────► DM8
       (8080端口)     (达梦/Swoole/Phalcon/YAC)  (5236端口)
```

## 使用方式（极简）

### 1. 把项目放在 ~/Sites/

```
~/Sites/
├── myapp/
│   └── index.php
├── project2/
│   └── index.php
└── ...
```

### 2. 启动容器

```bash
cd /Users/chenliang/DockerData/dm8
docker-compose up -d
```

第一次启动会自动：
- 构建PHP 8.3镜像（含达梦/Swoole/Phalcon/YAC）
- 拉取达梦数据库镜像
- 挂载 ~/Sites 到容器
- 启动Nginx+PHP+达梦

### 3. 配置域名（推荐，避免与本地localhost冲突）

编辑 hosts 文件：
```bash
sudo nano /etc/hosts
```

添加：
```
127.0.0.1  dm8.local
127.0.0.1  myapp.dm8.local
```

刷新DNS缓存：
```bash
sudo dscacheutil -flushcache; sudo killall -HUP mDNSResponder
```

详见 [HOSTS_CONFIG.md](./HOSTS_CONFIG.md)

### 4. 访问项目

使用域名访问（推荐）：
- http://dm8.local:8080/ - 显示项目列表
- http://dm8.local:8080/myapp/ - 访问myapp项目
- http://dm8.local:8080/check_extensions.php - 检测PHP扩展

或使用端口访问：
- http://localhost:8080/myapp/
- http://localhost:8080/

### 4. 停止容器

```bash
docker-compose down
```

## 目录说明

| 本地路径 | Docker路径 | 用途 |
|---------|-----------|------|
| ~/Sites/ | /var/www/html/ | 你的PHP项目代码 |
| ./dm8/data/ | /opt/dmdbms/data/ | 达梦数据库文件 |
| ./php/php.ini | /usr/local/etc/php/php.ini | PHP配置 |
| ./nginx/conf.d/ | /etc/nginx/conf.d/ | Nginx配置 |

## 特性

- ✅ PHP 8.3 + FPM
- ✅ 达梦数据库扩展（需手动安装驱动文件）
- ✅ Swoole 5.x（协程、异步IO）
- ✅ Phalcon 5.8（C语言高性能框架）
- ✅ YAC 2.3（无锁共享内存缓存）
- ✅ Redis扩展
- ✅ Nginx反向代理
- ✅ 自动挂载 ~/Sites 目录

## 数据库连接

```php
<?php
// PDO方式
$pdo = new PDO('dm:host=dm8;port=5236;dbname=SYSTEM', 'SYSDBA', '123abc!@#');

// 达梦原生方式
$conn = dm_connect('dm8:5236', 'SYSDBA', '123abc!@#');
?>
```

## 达梦镜像说明

使用镜像: `sizx/dm8`

**支持的Tag：**
- X86: `latest`, `1-2-128-22.08.04-166351-20005-CTM`
- ARM(M1/M2/M3): `1-3-12-2023.04.17-187846-20040-ENT`

默认账号: SYSDBA / 123abc!@#

## 常用命令

```bash
# 查看容器状态
docker-compose ps

# 查看日志
docker-compose logs -f [dm8|php-dm|nginx]

# 进入PHP容器
docker-compose exec php-dm bash

# 进入达梦容器
docker-compose exec dm8 bash


# 完全重置（删除数据库）
docker-compose down -v

# 重建PHP镜像
docker-compose build --no-cache php-dm

# 构建（并启动）
docker compose up -d --build

# 启动（已存在的容器）
docker compose start

# 停止
docker compose stop

# 重启
docker compose restart
```

## 开发提示

### PHPStorm配置
1. 设置 → PHP → CLI解释器 → 从Docker选择dm8_php-dm_1
2. 路径映射: ~/Sites/ → /var/www/html/

### 项目创建
```bash
mkdir ~/Sites/my-dm-app
cd ~/Sites/my-dm-app
echo "<?php phpinfo();" > index.php
```

然后访问 http://localhost/my-dm-app/

## 参考

- 达梦官网: https://www.dameng.com/
- Swoole: https://www.swoole.com/
- Phalcon: https://phalcon.io/
