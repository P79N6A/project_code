from lib.application import app,register_blueprints

application = app
register_blueprints()

if __name__ == "__main__":
    application.run(host='0.0.0.0',port=8888,debug=True)