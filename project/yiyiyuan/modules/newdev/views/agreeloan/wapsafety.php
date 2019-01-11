<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
    <title><?= $this->title; ?></title>
    <link href="/css/dev/common.css?v=2015041002" rel="stylesheet">
    <link href="/css/zebra_dialog.css" rel="stylesheet">
    <style type="text/css">
        html,body{background: #e7edf0; font-family: "Microsoft YaHei"; padding: 0; margin:0;}
        .xieyidm{background: #fff; margin: 0; padding-bottom: 50px;}
        .xieyidm p{ line-height:24px; font-size:13px; padding:0 5%; text-indent: 24px;}
        .xieyidm h3{margin:0;font-size:15px;font-style:normal;text-align:center;padding-top:10px; }
        .xieyidm h4{ font-size: 14px; padding-left:5%;}
        .xieyidm h5{ font-size: 14px; padding:20px 5% 0; margin: 0;}
        .xieyidm .xxdduo{ text-align: right; }
    </style>
</head>
<body>
<div class="xieyidm">
    <h3></h3>
    <p>本人授权保险公司，除法律另有规定之外，将本人提供给保险公司的信息、享受保险公司服务产生的信息（包括本〔单证〕签署之前提供和产生的信息）以及保险公司根据本条约定查询、收集的信息，用于保险公司及其因服务必要委托的合作伙伴为本人提供服务、推荐产品、开展市场调查与信息数据分析。</p>
    <p>本人授权保险公司，除法律另有规定之外，基于为本人提供更优质服务和产品的目的，向保险公司因服务必要开展合作的伙伴提供、查询、收集本人的信息。为确保本人信息的安全，保险公司及其合作伙伴对上述信息负有保密义务，并采取各种措施保证信息安全。</p>
    <p>本条款自本〔单证〕签署时生效，具有独立法律效力 , 不受合同成立与否及效力状态变化的影响。</p>
</div>
<div class="footer text-center" style="background-color:rgb(43,43,48);">
    <a href="javascript:void(0);" onclick="returnPage()"><button class="btn" style="width:100%; margin:auto;">同意</button></a>
</div>
<script>
    function returnPage() {
        window.history.go(-1);
    }
</script>
</body>
</html>