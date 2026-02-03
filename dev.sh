#!/bin/bash
# DM8 Docker 开发环境管理脚本
# 适用于Mac本地开发，项目目录在 ~/Sites/

set -e

# 颜色输出
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# 项目目录
PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
SITES_DIR="$HOME/Sites"

# 打印信息
info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# 检查Sites目录
check_sites_dir() {
    if [ ! -d "$SITES_DIR" ]; then
        warning "Sites目录不存在，正在创建..."
        mkdir -p "$SITES_DIR"
        success "Sites目录已创建: $SITES_DIR"
    else
        info "Sites目录: $SITES_DIR"
        info "当前项目列表:"
        ls -la "$SITES_DIR" 2>/dev/null | tail -n +4 || echo "  (目录为空)"
    fi
}

# 启动开发环境
start() {
    info "启动DM8开发环境..."
    check_sites_dir
    
    cd "$PROJECT_DIR"
    
    # 检查docker-compose文件
    if [ ! -f "docker-compose.yml" ]; then
        error "docker-compose.yml 不存在"
        exit 1
    fi
    
    docker-compose up -d
    
    success "环境启动完成！"
    echo ""
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "  访问地址:"
    echo "  • PHP项目:    http://localhost:8080"
    echo "  • PHP信息:    http://localhost:8080/phpinfo.php"
    echo "  • 扩展检测:   http://localhost:8080/check_extensions.php"
    echo "  • 达梦连接:   http://localhost:8080/test_dm_connection.php"
    echo "  • 达梦数据库: localhost:5236"
    echo ""
    echo "  项目目录:     $SITES_DIR"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
}

# 停止环境
stop() {
    info "停止DM8开发环境..."
    cd "$PROJECT_DIR"
    docker-compose down
    success "环境已停止"
}

# 重启环境
restart() {
    stop
    sleep 2
    start
}

# 查看状态
status() {
    info "查看容器状态..."
    cd "$PROJECT_DIR"
    docker-compose ps
}

# 查看日志
logs() {
    cd "$PROJECT_DIR"
    if [ -z "$1" ]; then
        docker-compose logs -f
    else
        docker-compose logs -f "$1"
    fi
}

# 进入容器
enter() {
    cd "$PROJECT_DIR"
    case "$1" in
        php|php-dm)
            docker-compose exec php-dm bash
            ;;
        dm8|dm)
            docker-compose exec dm8 bash
            ;;
        nginx)
            docker-compose exec nginx sh
            ;;
        *)
            echo "用法: $0 enter [php|dm8|nginx]"
            exit 1
            ;;
    esac
}

# 重建PHP镜像（修改Dockerfile后使用）
rebuild() {
    info "重建PHP镜像..."
    cd "$PROJECT_DIR"
    docker-compose build --no-cache php-dm
    success "重建完成，请使用 '$0 restart' 启动"
}

# 安装新项目
new-project() {
    if [ -z "$1" ]; then
        error "请提供项目名称"
        echo "用法: $0 new-project <项目名>"
        exit 1
    fi
    
    PROJECT_NAME="$1"
    PROJECT_PATH="$SITES_DIR/$PROJECT_NAME"
    
    if [ -d "$PROJECT_PATH" ]; then
        error "项目已存在: $PROJECT_PATH"
        exit 1
    fi
    
    info "创建新项目: $PROJECT_NAME"
    mkdir -p "$PROJECT_PATH"
    
    # 创建默认文件
    cat > "$PROJECT_PATH/index.php" << 'EOF'
<?php
/**
 * <?php echo date('Y-m-d'); ?> 创建
 */

// 显示PHP信息
phpinfo();
EOF
    
    cat > "$PROJECT_PATH/.htaccess" << 'EOF'
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
EOF
    
    success "项目已创建: $PROJECT_PATH"
    info "访问地址: http://localhost/$PROJECT_NAME/"
}

# 显示帮助
help() {
    cat << EOF
DM8 Docker 开发环境管理脚本

用法: $0 <命令> [参数]

命令:
  start              启动开发环境（挂载 ~/Sites）
  stop               停止开发环境
  restart            重启开发环境
  status             查看容器状态
  logs [服务名]       查看日志（默认全部）
  enter <服务>        进入容器（php/dm8/nginx）
  rebuild            重建PHP镜像
  new-project <名称>  在Sites目录创建新项目
  help               显示此帮助

示例:
  $0 start                    # 启动环境
  $0 logs php-dm              # 查看PHP日志
  $0 enter php                # 进入PHP容器
  $0 new-project myapp        # 创建新项目

项目目录: ~/Sites/
配置文件: 
  - docker-compose.yml
  - docker-compose.override.yml（开发配置）

EOF
}

# 主逻辑
case "$1" in
    start)
        start
        ;;
    stop)
        stop
        ;;
    restart)
        restart
        ;;
    status)
        status
        ;;
    logs)
        logs "$2"
        ;;
    enter)
        enter "$2"
        ;;
    rebuild)
        rebuild
        ;;
    new-project)
        new-project "$2"
        ;;
    help|--help|-h)
        help
        ;;
    *)
        help
        exit 1
        ;;
esac
