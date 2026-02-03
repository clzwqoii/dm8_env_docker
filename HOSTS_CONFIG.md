# Mac 开发环境 hosts 配置指南

## 为什么需要配置 hosts？

默认情况下：
- 本地Nginx使用 `localhost:80`
- Docker Nginx使用 `localhost:8080`（端口不同，不容易区分）

配置自定义域名后：
- 本地项目 → `http://localhost`
- Docker项目 → `http://dm8.local:8080` 或 `http://project.dm8.local:8080`

## 配置步骤

### 1. 编辑 hosts 文件

```bash
sudo nano /etc/hosts
```

### 2. 添加域名映射

```
# DM8 Docker 开发环境
127.0.0.1       dm8.local
127.0.0.1       phpinfo.dm8.local
127.0.0.1       test.dm8.local

# 项目子域名（可选，为每个项目配置独立域名）
# 格式: 127.0.0.1  <项目名>.dm8.local
127.0.0.1       myapp.dm8.local
127.0.0.1       project1.dm8.local
127.0.0.1       project2.dm8.local
```

### 3. 刷新 DNS 缓存

```bash
# macOS 12 (Monterey) 及以上
sudo dscacheutil -flushcache; sudo killall -HUP mDNSResponder

# macOS 11 (Big Sur)
sudo dscacheutil -flushcache; sudo killall -HUP mDNSResponder

# macOS 10.15 (Catalina)
sudo killall -HUP mDNSResponder

# macOS 10.14 (Mojave) 及以下
sudo killall -HUP mDNSResponder
```

### 4. 测试配置

```bash
# 检查域名解析
ping dm8.local

# 应该显示 127.0.0.1
```

## 访问方式对比

| 方式 | 地址 | 适用场景 |
|------|------|----------|
| 本地Nginx | `http://localhost` | 本地PHP项目 |
| Docker Nginx | `http://localhost:8080` | 临时访问 |
| Docker域名 | `http://dm8.local:8080` | **推荐，清晰区分** |
| 项目子域名 | `http://myapp.dm8.local:8080` | 独立项目访问 |

## 示例项目访问

假设你的项目结构：
```
~/Sites/
├── myapp/
│   └── index.php
└── api/
    └── index.php
```

### 访问方式 1：使用主域名 + 路径
```
http://dm8.local:8080/myapp/       → 访问 myapp 项目
http://dm8.local:8080/api/         → 访问 api 项目
http://dm8.local:8080/             → 显示项目列表
```

### 访问方式 2：使用子域名（需要配置hosts）
```
http://myapp.dm8.local:8080/       → 直接访问 myapp 项目
http://api.dm8.local:8080/         → 直接访问 api 项目
```

## 配置项目子域名（可选）

如果你想为每个项目配置独立域名，创建 `nginx/conf.d/projects.conf`：

```nginx
# myapp 项目专用域名
server {
    listen 80;
    server_name myapp.dm8.local;
    root /var/www/html/myapp;
    index index.php;

    location ~ \.php$ {
        fastcgi_pass php-dm:9000;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}

# api 项目专用域名
server {
    listen 80;
    server_name api.dm8.local;
    root /var/www/html/api;
    index index.php;

    location ~ \.php$ {
        fastcgi_pass php-dm:9000;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

然后重启容器：
```bash
docker-compose restart nginx
```

## 常见问题

### 1. 浏览器缓存导致域名不生效
- Chrome/Edge: 按 `Ctrl+Shift+R` (Windows) 或 `Cmd+Shift+R` (Mac) 强制刷新
- 或者清空浏览器缓存

### 2. 其他软件占用了 hosts 配置
某些软件（如 SwitchHosts、iHosts）会管理 hosts 文件，确保它们没有冲突。

### 3. DNS 解析失败
```bash
# 检查域名解析
dscacheutil -q host -a name dm8.local

# 或者直接查看 hosts 文件
cat /etc/hosts | grep dm8
```

### 4. 端口访问被拒绝
如果访问 `dm8.local:8080` 显示拒绝连接，检查：
```bash
# 容器是否运行
docker-compose ps

# 端口是否监听
lsof -i :8080

# 防火墙设置
sudo /usr/libexec/ApplicationFirewall/socketfilterfw --getglobalstate
```

## 简化配置（推荐）

如果你不想配置 hosts，可以直接使用端口访问：
```
http://localhost:8080/myapp/
http://localhost:8080/api/
```

但配置域名后更清晰，推荐开发时使用。
