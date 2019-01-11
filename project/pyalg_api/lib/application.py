# -*- coding: utf-8 -*-
# 程序启动应用程序
from flask import Flask,Blueprint
from werkzeug.utils import find_modules, import_string
from util.restplus import api
from flask_sqlalchemy import SQLAlchemy
from .config import get_config
from util.error import APIError
from util.error_handlers import error_404_handler, error_429_handler, error_handler

app = Flask(__name__)
db = SQLAlchemy()
# __all__ = [app, db]

def init_config():
    app.config.from_object(get_config())


def init_db():
    db.init_app(app)

def register_blueprints():
    blueprint = Blueprint('api', __name__, url_prefix='/api')
    api.init_app(blueprint)
    modules = find_modules('controllers', recursive=True)
    for name in modules:
        print('=============', name)
        module = import_string(name)
        if hasattr(module, 'ns'):
            print(module.ns)
            api.add_namespace(module.ns)
            app.register_blueprint(blueprint)



def init_error_handlers():
    app.register_error_handler(404, error_404_handler)
    app.register_error_handler(429, error_429_handler)
    app.register_error_handler(APIError, error_handler)

def init_app():
    init_config()
    init_error_handlers()
    init_db()
    # register_blueprints()
    

init_app()
