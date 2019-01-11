<?php

// 测试账号
return [

    // start 商汤分配的appid
    'linkface_api_id' => '7a72e836d73b421893872c59a663373f',
    // end 
    // start 商汤分配的API秘钥 
    'linkface_api_secret' => '8b4a8594bfb64c48802d911466a0d4f1',
    // end
    // start 商汤人脸对比请求URL
    'linkface_api_url' => 'https://v1-auth-api.visioncloudapi.com/identity/historical_selfie_verification',
    // end
    // start 商汤姓名和身份证号匹配请求URL
    'idnumber_verification_api_url' => 'https://v1-auth-api.visioncloudapi.com/police/idnumber_verification',
    // end 
    // start 商汤自拍照防伪请求URL
    'hack_api_url' => 'https://v1-auth-api.visioncloudapi.com/hackness/selfie_hack_detect',
    // end
    
    //start 将某url上的图片上传到商汤
    'h5_upload_url'=>'https://v2-auth-api.visioncloudapi.com/resource/image/url',
    //end
    //start 用于识别存在商汤的静态身份证图片上的文字信息 通过h5_upload_url返回的image_id
    'h5_ocr_api_url'=>'https://v2-auth-api.visioncloudapi.com/ocr/idcard',
    //end
];
