# 达梦数据库 Dockerfile
# 注意：由于达梦是商业软件，你需要从官方获取安装包或镜像
# 本文件提供了两种方案：
# 方案1：基于官方镜像（推荐）
# 方案2：从头构建（需要DM安装包）

# ============ 方案1：基于已有镜像（推荐）============
# 如果你已经从达梦官方获取了镜像，使用以下方式：
# 在docker-compose.yml中直接使用官方镜像，不需要此Dockerfile

# ============ 方案2：从头构建 ============
# 需要准备：
# 1. DM8安装包（如：dm8_20230104_x86_rh6_64.iso 或 .zip）
# 2. 授权文件（dm.key）

FROM centos:7

# 安装依赖
RUN yum install -y \
    glibc \
    libgcc \
    libstdc++ \
    libaio \
    numactl-libs \
    unzip \
    && yum clean all

# 创建安装目录
RUN mkdir -p /opt/dmdbms /opt/dmdbms/data /opt/dmdbms/backup /tmp/dm_install

# 复制安装包（请将安装包放在dm8目录下）
# COPY dm8_install.zip /tmp/dm_install/
# COPY dm.key /opt/dmdbms/bin/

# 解压并安装达梦数据库（根据实际情况修改）
# RUN cd /tmp/dm_install && \
#     unzip dm8_install.zip && \
#     ./DMInstall.bin -i -q && \
#     rm -rf /tmp/dm_install

# 设置环境变量
ENV DM_HOME=/opt/dmdbms
ENV PATH=$DM_HOME/bin:$PATH
ENV LD_LIBRARY_PATH=$DM_HOME/bin:$LD_LIBRARY_PATH

# 初始化数据库脚本
COPY init-db.sh /opt/dmdbms/scripts/
RUN chmod +x /opt/dmdbms/scripts/init-db.sh

# 暴露端口
EXPOSE 5236 8080

# 启动脚本
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["start"]
