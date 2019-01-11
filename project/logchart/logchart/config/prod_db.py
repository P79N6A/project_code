DATABASES = {
    'default':{
        'ENGINE': 'django.db.backends.mysql',
        'NAME': 'logchart',
        'USER': 'logchart',
        'PASSWORD': 'u2cEUZKxH2(vt8Nq',
        'HOST': 'rm-bp1too86h67480p2b.mysql.rds.aliyuncs.com',
        'PORT': '3306',
        'CONN_MAX_AGE': 300,
        'OPTIONS': {
            'init_command': "SET sql_mode='STRICT_TRANS_TABLES'"
        }
    },
    'yiyiyuan':{
        'ENGINE':'django.db.backends.mysql',
        'NAME':'xhh_yiyiyuan',
        'USER': 'xhh_yyy_read',
        'PASSWORD': 'X1rR2JLy3Y_Only',
        'HOST':'rr-bp190xh1w7n22flti.mysql.rds.aliyuncs.com',
        'PORT': '3306',
        'CONN_MAX_AGE': 300,
    },
    'peanut':{
        'ENGINE':'django.db.backends.mysql',
        'NAME':'xhh_peanut',
        'USER': 'logchart_r',
        'PASSWORD': 'EFX7LcV5f+3fh0ih',
        'HOST':'rr-bp1pk79y50uxbxlue.mysql.rds.aliyuncs.com',
        'PORT': '3306',
        'CONN_MAX_AGE': 300,
    },
}
DATABASE_ROUTERS = ['logchart.databaseRouter.DatabaseAppsRouter']

CLICKHOUSE_HOST = 'http://10.253.125.234:8123'
CLICKHOUSE_USER = 'default'
CLICKHOUSE_PASSWORD = 'iY5WbB1HxMy69qx'


