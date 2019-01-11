import logging
import logging.config as config
import os.path as path

config_path = path.join(path.split(path.realpath(__file__))[0], "logger.conf")
config.fileConfig(config_path)
logger = logging.getLogger("mylog")
