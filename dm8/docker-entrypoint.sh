#!/bin/bash
# 达梦数据库启动脚本

set -e

# 初始化数据库
init_db() {
    echo "初始化达梦数据库..."
    
    # 检查数据目录是否已初始化
    if [ ! -f "$DM_HOME/data/DAMENG/dm.ini" ]; then
        echo "首次启动，初始化数据库实例..."
        
        # 使用dminit初始化数据库
        $DM_HOME/bin/dminit \
            PATH=$DM_HOME/data \
            DB_NAME=DAMENG \
            INSTANCE_NAME=DMSERVER \
            PORT_NUM=5236 \
            PAGE_SIZE=${PAGE_SIZE:-16} \
            EXTENT_SIZE=${EXTENT_SIZE:-16} \
            CASE_SENSITIVE=${CASE_SENSITIVE:-Y} \
            CHARSET=${CHARSET:-1} \
            LENGTH_IN_CHAR=${LENGTH_IN_CHAR:-Y}
        
        echo "数据库初始化完成"
    else
        echo "数据库已存在，跳过初始化"
    fi
}

# 启动数据库服务
start_db() {
    echo "启动达梦数据库服务..."
    
    # 检查授权文件
    if [ -f "$DM_HOME/bin/dm.key" ]; then
        echo "授权文件已存在"
    else
        echo "警告：授权文件不存在，请确保已放置授权文件"
    fi
    
    # 启动数据库
    $DM_HOME/bin/dmserver $DM_HOME/data/DAMENG/dm.ini &
    
    # 等待数据库启动
    sleep 10
    
    # 检查是否启动成功
    if pgrep -x "dmserver" > /dev/null; then
        echo "达梦数据库启动成功！"
        echo "端口: 5236"
        echo "默认用户: SYSDBA"
        echo "默认密码: 123456"
    else
        echo "达梦数据库启动失败，请检查日志"
        exit 1
    fi
}

# 修改默认密码
change_password() {
    if [ -n "$SYSDBA_PWD" ] && [ "$SYSDBA_PWD" != "123456" ]; then
        echo "修改SYSDBA密码..."
        echo "ALTER USER SYSDBA IDENTIFIED BY \"$SYSDBA_PWD\";" | \
            $DM_HOME/bin/disql SYSDBA/123456@localhost:5236
    fi
}

# 主逻辑
case "$1" in
    start)
        init_db
        start_db
        change_password
        
        # 保持容器运行
        tail -f /dev/null
        ;;
    stop)
        echo "停止达梦数据库..."
        $DM_HOME/bin/dm_service_stop DMSERVER
        ;;
    restart)
        $0 stop
        sleep 2
        $0 start
        ;;
    status)
        $DM_HOME/bin/dm_service_status DMSERVER
        ;;
    bash|sh)
        /bin/bash
        ;;
    *)
        exec "$@"
        ;;
esac
