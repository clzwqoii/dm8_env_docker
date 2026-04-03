#!/bin/sh

set -eu

DM_PATH=/home/dmdba/dmdbms
DM_DATA_ROOT=/home/dmdba/data
DM_DATA_DIR=${DM_DATA_ROOT}/DAMENG
export LD_LIBRARY_PATH=${DM_PATH}/bin:${LD_LIBRARY_PATH:-}

PAGE_SIZE=${PAGE_SIZE:-16}
EXTENT_SIZE=${EXTENT_SIZE:-16}
DB_NAME=${DB_NAME:-DAMENG}
INSTANCE_NAME=${INSTANCE_NAME:-DMSERVER}
PORT_NUM=${PORT_NUM:-5236}
CHARSET=${CHARSET:-1}
LENGTH_IN_CHAR=${LENGTH_IN_CHAR:-1}
CASE_SENSITIVE=${CASE_SENSITIVE:-1}
SYSDBA_PWD=${SYSDBA_PWD:-SYSDBA}

NEW_INIT=0

check_is_init() {
    if [ -d "${DM_DATA_DIR}" ] && [ -f "${DM_DATA_DIR}/dm.ini" ]; then
        DATABASE_ALREADY_EXISTS=true
    else
        DATABASE_ALREADY_EXISTS=
    fi
}

db_init() {
    mkdir -p "${DM_DATA_DIR}"
    chown -R dmdba:dinstall "${DM_DATA_ROOT}"

    # Use explicit init args so charset is controllable instead of image defaults.
    gosu dmdba "${DM_PATH}/bin/dminit" \
        PATH="${DM_DATA_ROOT}" \
        DB_NAME="${DB_NAME}" \
        INSTANCE_NAME="${INSTANCE_NAME}" \
        PORT_NUM="${PORT_NUM}" \
        PAGE_SIZE="${PAGE_SIZE}" \
        EXTENT_SIZE="${EXTENT_SIZE}" \
        CASE_SENSITIVE="${CASE_SENSITIVE}" \
        CHARSET="${CHARSET}" \
        LENGTH_IN_CHAR="${LENGTH_IN_CHAR}"
}

install_services_if_needed() {
    if [ ! -f "${DM_PATH}/bin/DmAPService" ]; then
        "${DM_PATH}/script/root/dm_service_installer.sh" -s "${DM_PATH}/bin/DmAPService"
    fi
    if [ ! -f "${DM_PATH}/bin/DmService${INSTANCE_NAME}" ]; then
        "${DM_PATH}/script/root/dm_service_installer.sh" -t dmserver -p "${INSTANCE_NAME}" -dm_ini "${DM_DATA_DIR}/dm.ini"
    fi
}

start_services() {
    gosu dmdba "${DM_PATH}/bin/DmAPService" start
    gosu dmdba "${DM_PATH}/bin/DmService${INSTANCE_NAME}" start
}

check_is_init
if [ -z "${DATABASE_ALREADY_EXISTS}" ]; then
    NEW_INIT=1
    db_init
fi

install_services_if_needed
start_services

if [ "${NEW_INIT}" = "1" ] && [ "${SYSDBA_PWD}" != "SYSDBA" ]; then
    printf "ALTER USER SYSDBA IDENTIFIED BY \"%s\";\nCOMMIT;\nEXIT;\n" "${SYSDBA_PWD}" | \
        gosu dmdba "${DM_PATH}/bin/disql" "SYSDBA/SYSDBA@localhost:${PORT_NUM}" || true
fi

exec gosu dmdba tail -f "/home/dmdba/dmdbms/log/DmService${INSTANCE_NAME}.log"
