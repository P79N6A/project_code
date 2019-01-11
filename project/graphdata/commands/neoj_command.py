
from .base_command import BaseCommand
#from py2neo import Graph,Node,Relationship
from neo4j.v1 import GraphDatabase


class NeojCommand(BaseCommand):

    def runData(self, start_time, end_time):

        uri = "bolt://localhost:7687"
        driver = GraphDatabase.driver(uri, auth=("neo4j", "123456"))
        a = "CREATE (phone:username { phone:'1891542552'})"
        #driver.session().run(a)


    '''
    随机生成手机号码
    '''
    def createPhone(self, phone_num=10):
        phone_data = []
        for i in range(phone_num):
            prelist = ["130", "131", "132", "133", "134", "135", "136", "137", "138", "139", "147", "150", "151", "152",
                       "153", "155", "156", "157", "158", "159", "186", "187", "188"]
            number = random.choice(prelist) + "".join(random.choice("0123456789") for i in range(8))
            phone_data.append(number)
        return phone_data



