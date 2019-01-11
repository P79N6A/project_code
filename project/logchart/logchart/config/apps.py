SYSTEM_APPS = [
    'django.contrib.admin',
    'django.contrib.auth',
    'django.contrib.contenttypes',
    'django.contrib.sessions',
    'django.contrib.messages',
    'django.contrib.staticfiles',
]
CUSTOM_APPS = [
    'apps.logchart',
    'apps.manage',
    'apps.helper',
    'apps.clickhouse',
    'apps.yiyiyuan',
    'apps.peanut',
]

INSTALLED_APPS = SYSTEM_APPS + CUSTOM_APPS