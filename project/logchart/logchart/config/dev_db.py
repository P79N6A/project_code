DATABASES = {
    'default':{
        'ENGINE': 'django.db.backends.mysql',
        'NAME': 'logchart',
        'USER': 'xhhadmin',
        'PASSWORD': 'Xhuahua#Db!332',
        'HOST': '182.92.80.211',
        'PORT': '3306',
        'CONN_MAX_AGE': 300,
        'OPTIONS': {
            'init_command': "SET sql_mode='STRICT_TRANS_TABLES'"
        }
    },
    'yiyiyuan':{
        'ENGINE':'django.db.backends.mysql',
        'NAME':'xhh_test',
        'USER': 'xhhadmin',
        'PASSWORD': 'Xhuahua#Db!332',
        'HOST':'182.92.80.211',
        'PORT': '3306',
        'CONN_MAX_AGE': 300,
    },
    'peanut':{
        'ENGINE':'django.db.backends.mysql',
        'NAME':'xhh_peanut_new',
        'USER': 'xhhadmin',
        'PASSWORD': 'Xhuahua#Db!332',
        'HOST':'182.92.80.211',
        'PORT': '3306',
        'CONN_MAX_AGE': 300,
    },
}
DATABASE_ROUTERS = ['logchart.databaseRouter.DatabaseAppsRouter']

CLICKHOUSE_HOST = 'http://192.168.116.129:8123'
CLICKHOUSE_USER = 'default'
CLICKHOUSE_PASSWORD = ''