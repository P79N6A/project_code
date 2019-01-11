import os
from logchart.config.common import BASE_DIR

LOGGING = {
    'version': 1,
    'disable_existing_loggers': False,
    'filters': {
        'require_debug_true': {
            '()': 'django.utils.log.RequireDebugTrue',
        },
    },
    'formatters': {
        'standard': {
            'format': '%(levelname)s %(asctime)s %(pathname)s %(filename)s %(module)s %(funcName)s %(lineno)d: %(message)s'
        }, # django标准日志格式
        'simple': {
            'format': '%(levelname)s %(asctime)s %(filename)s %(lineno)d %(message)s'
        }, # django简易日志格式
        'manage': {
            'format': '%(asctime)s %(filename)s[line:%(lineno)d] %(levelname)s %(message)s'
        }, # 自定义的manage.py执行脚本的输出日志格式
        'apscheduler': {
            'format': '%(asctime)s %(filename)s[line:%(lineno)d] %(levelname)s %(message)s'
        }, # 自定义的apscheduler定时脚本的输出日志格式
    },
    'handlers': {
        'server': {
            'level': 'INFO',
            'class': 'logging.handlers.TimedRotatingFileHandler',
            'filename':  os.path.join(BASE_DIR, "log/server/","server.log"),
            'when': 'D',
            'interval': 1,
            'backupCount': 7,
            'encoding': 'utf8',
            'formatter':'standard'
        },
        'mysql': {
            'level': 'INFO',
            'class': 'logging.handlers.TimedRotatingFileHandler',
            'filename':  os.path.join(BASE_DIR, "log/mysql/","mysql.log"),
            'when': 'D',
            'interval': 1,
            'backupCount': 7,
            'encoding': 'utf8',
            'formatter':'simple'
        },
        'apscheduler':{
            'level': 'INFO',
            'class': 'logging.handlers.TimedRotatingFileHandler',
            'filename': os.path.join(BASE_DIR,"log/apscheduler/",'apscheduler.log'),
            'when': 'D',
            'interval': 1,
            'backupCount': 7,
            'encoding': 'utf8',
            'formatter': 'apscheduler',
        }, # apscheduler 定时脚本的执行的日志输出选项
        'importYyyUser': {
            'level': 'INFO',
            'class': 'logging.handlers.TimedRotatingFileHandler',
            'filename': os.path.join(BASE_DIR,"log/manage/importYyyUser/",'importYyyUser.log'),
            'when': 'D',
            'interval': 1,
            'backupCount': 7,
            'encoding': 'utf8',
            'formatter': 'manage',
        }, # importYyyUser 执行时的日志输出参数
        'importPeanutUser': {
            'level': 'INFO',
            'class': 'logging.handlers.TimedRotatingFileHandler',
            'filename': os.path.join(BASE_DIR,"log/manage/importPeanutUser/",'importPeanutUser.log'),
            'when': 'D',
            'interval': 1,
            'backupCount': 7,
            'encoding': 'utf8',
            'formatter': 'manage',
        }, # importPeanutUser 执行时的日志输出参数
    },
    'loggers': {
        'django': {
            'handlers' :['server'],
            'level':'DEBUG',
            'propagate': False
        },
        'django.db.backends': {
            'handlers' :['mysql'],
            'level':'DEBUG',
            'propagate': False
        },
        'apscheduler': {
            'handlers': ['apscheduler'],
            'level': 'INFO',
            'propagate': False,
        }, # 记录 apscheduler定时日志信息的操作对象
        'importYyyUser': {
            'handlers': ['importYyyUser'],
            'level': 'INFO',
            'propagate': False,
        }, # 记录 importYyyUser定时日志信息的操作对象
        'importPeanutUser': {
            'handlers': ['importPeanutUser'],
            'level': 'INFO',
            'propagate': False,
        }, # 记录 importPeanutUser定时日志信息的操作对象
    }
}
