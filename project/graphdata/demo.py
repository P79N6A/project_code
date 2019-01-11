import pyorient
import pdb
import random
import re
import json
import os
# create connection
# pdb.set_trace()
# create connection
client = pyorient.OrientDB("140.143.34.13", 2424)
print(client)
session_id = client.connect("root", "xhh123")
print(session_id)
# create a database
# client.db_create('test', pyorient.DB_TYPE_GRAPH, pyorient.STORAGE_TYPE_MEMORY)

# open databse
res = client.db_open('test', "admin", "admin")
print(res)
# create class

# res_insert = client.command("select expand(both('first')) from Phone where phone='13118002202'")
# res_insert = client.command("select expand(both('Second')).outE('Second') from Phone where phone='18783140266'")
# print(res_insert[0])
os._exit(0)

# cluster_id = client.command("create class Phone extends V")
# cluster_id = client.command("create class address extends E")


# cluster_id = client.command("create class relation extends V")
# print(cluster_id)

# create property
# cluster_id = client.command("create property relation.user_phone String")
# print(cluster_id)
# cluster_id = client.command("create property relation.phone String")

# cluster_id = client.command("create property phone.relation String")
# print(cluster_id)
# print(cluster_id)
# 删除类
# res_drop_class = client.command("DROP CLASS my_class")
# print(res_drop_class)
# print(res_insert)
# 删除顶点
# res_insert = client.command("DELETE VERTEX my_class WHERE name = 'satish'")
# print(res_insert)
file_path = 'D:\python_test\\relation\jaccard\\201803\\29\\1_203968_24919312_jaccard_rela.json'
regex = re.compile(r'\\(?![/u"])')
records = [json.loads(regex.sub(r"\\\\", line).replace("'", '"')) for line in open(file_path)]
# print(records)
# os._exit(0)
relation_data = records[0].get('relation')

def save_relation(relation,phone_list):
    if not phone_list:
        return None
    n = 0
    for phone in phone_list:
        # 创建顶点
        res_insert = client.command('create vertex Phone set phone="' + str(phone) + '"')
        print(res_insert)
        # 创建边
        print(phone)
        res_insert = client.command('CREATE EDGE '+relation+' FROM(select from Phone where phone="'+str(user_phone)+'") to (select from Phone where phone="'+str(phone)+'")')
        print(res_insert)
        # os._exit(0)
        n +=1
    return n

# 创建用户定点
user_phone = '13118002202'
res_insert = client.command("CREATE VERTEX Phone SET phone="+user_phone)
print(res_insert)

for relation,phone_list in relation_data.items():
    res = save_relation(relation,phone_list)
    # os._exit(0)
    print(res)


i=0
n=20
while(i < n):
    phone = random.randint(13000000000, 19000000000)

    res_insert = client.command(sql)
    i+=1

for i in relation_list:
    sql = 'CREATE EDGE '+i+' FROM(select from Phone where user_phone="'+user_phone+'") to (select from Phone where user_phone="'+user_phone+'" and relation="'+ i +'")'
    print(sql)
    res_create_baba = client.command(sql)

# CREATE EDGE
# res_create_baba = client.command("CREATE EDGE first FROM (select from user_phone where user_phone='13120117991') TO (select from phone where user_phone='13120117991')")
# print(res_create_baba)
# print('create %s', res_create_baba)
#
# res_create_son = client.command("CREATE EDGE teacher FROM (select from my_class where name='王永强') TO (select from my_class where name='程振远')")



#DELETE EDGE
# res_delete = client.command("DELETE EDGE FROM (select from my_class where name='王永强') TO (select from my_class where name='程振远')")
# print('delete %s', res_delete)
# for db in res1:
    # print(db.name)
# print(res1)