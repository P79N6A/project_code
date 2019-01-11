## <h3 id="0">[xhh图片服务器](/)</h3>

### 图片上传
---------

######
[1.1 图片上传](#1)

---------

### <h3 id="1">1.1 [图片上传](#0)</h3>
> 业务端请求携带请求参数，图片服务器获取并保存图片到服务器并返回下载路径

#### 接口访问地址
> http://domain/transfer/index

#### HTTP请求方式
> POST

#### 入参类型
> STRING

#### 返回值类型
> JSON

#### 请求参数
>|参数|必选|类型|说明
|:---- |:-----  |:------   |:------  |:------  
| | encrypt  |must |encrypt   |must     

#### 返回字段

>|返回字段|字段类型|说明
|:---- |:-----  |:------   |:------ 
| | res_code  |string |返回码   
| | res_data  |string |res_code!=0表示失败，res_data纪录失败的原因;res_code=0表示成功, res_data为图片链接地址  


#### 接口访问
```php
$apiUrl = "http://domain/transfer/index";
$str = [
    'imgUrls' => [
        '1'=>'http://www.xianhuahua.com/index/images/014.png',
        '2'=>'http://www.xianhuahua.com/index/images/012.png'
    ],
    'project' => 'yiyiyuan'
];
$jsonStr = json_encode($str);
$encrypt = Crypt3Des::encrypt($jsonStr,xxx);
$res = new Request($apiUrl,$params);
```

  



#### 接口返回值示例
```php
{
    "res_code": "0",
    "res_data": "{"1":"\/yiyiyuan\/transfer\/2017\/05\/23\/1913584486.jpg","2":"\/yiyiyuan\/transfer\/2017\/05\/23\/1913586121.jpg"}"
}
```

