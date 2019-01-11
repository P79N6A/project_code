var id = "";
var name = "";

var idPre = "";
var namePre = "";

var isInit = true;

var AllArray = new Array();
var pinyin = true;
function ClientInit() {
    if(pinyin){
        $("#searchInput").val('请输入关键字进行查询');
    }
    // 创建信息模拟通讯录
    builePeopleList();
}

function selectPeople(thisObject) {
    if (thisObject != null) {
        var nameId = "name" + thisObject.id;
        var statusId = "status" + thisObject.id;
        id = thisObject.id;
        name = document.getElementById(nameId).innerHTML;
        //alert(" 1 -- id:" + id + ",name:" + name + ",idPre:" + idPre + ",namePre:" + namePre);
        if (idPre != id) {
            if (document.getElementById(statusId).className == "status") {
                document.getElementById(statusId).className = "statusOnclick";
            } else {
                document.getElementById(statusId).className = "status";
            }
            var statusIdPre = "status" + idPre;
            if (statusIdPre != "status") {
                var preClass = document.getElementById(statusIdPre).className;
                document.getElementById(statusIdPre).className = "status";
            }
        }
        idPre = id;
        namePre = name;
    }
}

function selectAction() {
    if (name == '' || name == null) {
        alert("请选择学校");
        return;
    }
    alert("选择学校为：" + name + ", ID:" + id);
}

var ChineseArray = new Array();

function builePeopleList() {

    isInit = true; // 初始化效果

    requestPeopleList(); // 请求人员适时数据
}

var resultData = '';

function buildPeopleSelectListHtml() {
    resultData = '';
    // A - Z
    buildPeoples(PY_Str_1, 'A');
    buildPeoples(PY_Str_2, 'B');
    buildPeoples(PY_Str_3, 'C');
    buildPeoples(PY_Str_4, 'D');
    buildPeoples(PY_Str_5, 'E');
    buildPeoples(PY_Str_6, 'F');

    buildPeoples(PY_Str_7, 'G');
    buildPeoples(PY_Str_8, 'H');
    buildPeoples(PY_Str_9, 'I');
    buildPeoples(PY_Str_10, 'J');
    buildPeoples(PY_Str_11, 'K');
    buildPeoples(PY_Str_12, 'L');

    buildPeoples(PY_Str_13, 'M');
    buildPeoples(PY_Str_14, 'N');
    buildPeoples(PY_Str_15, 'O');
    buildPeoples(PY_Str_16, 'P');
    buildPeoples(PY_Str_17, 'Q');
    buildPeoples(PY_Str_18, 'R');

    buildPeoples(PY_Str_19, 'S');
    buildPeoples(PY_Str_20, 'T');
    buildPeoples(PY_Str_21, 'U');
    buildPeoples(PY_Str_22, 'V');
    buildPeoples(PY_Str_23, 'W');
    buildPeoples(PY_Str_24, 'X');

    buildPeoples(PY_Str_25, 'Y');
    buildPeoples(PY_Str_26, 'Z');

    //alert("resultData:" + resultData);

    if (resultData == null || resultData == '')
    {
        $("#peopleList").html('<div class="peopleNoOne"><div></div><div class="name" style="text-align:center; float:none;">无符合条件的学校信息</div> </div>');
    } else {

        // 人员总数显示
        var peopleSum = '<div class="peopleSearch"><div></div><div class="name" style="text-align:center; float:none;"> 搜索结果 </div> </div>';

        // 新增100位人员信息按钮
        var buttonGetMore = '';

        // 最终构建人员列表视图
        // resultData = peopleSum + resultData + buttonGetMore ;
//alert(resultData)
        resultData = resultData + buttonGetMore;
//        alert(resultData);
        $("#peopleList").html('<div class="slider-content"><ul>' + resultData + '</ul></div>');
        $('#peopleList').sliderNav();
    }

}

function setWindowScrollTop(win, topHeight)

{

    if(win.document.documentElement)

    {

        win.document.documentElement.scrollTop = topHeight;

    }

    if(win.document.body){

        win.document.body.scrollTop = topHeight;

    }

}




function clickIt(e) {
    var text = $(e).text();
    var sch_id = $(e).attr('id');
    var school = parent.document.getElementById("reg_school_name");
    var school_id = parent.document.getElementById("reg_school");
    var bodyf = parent.document.getElementById("bodyframe");
    school.value = text;
    school_id.value=sch_id;
    bodyf.style.display='none';    
}

function buildPeoples(PY_Str, ZM) {
    if (PY_Str != null && PY_Str != '' && ZM != null && ZM != '') {
        var pys = PY_Str.split(',');
        if (pys.length >= 2) {

            resultData +=
                    '<li id="' + ZM + '">'
                    + '<div id="list1" class="barDiv"> '
                    + '<div class="ZM title" id="' + ZM + '" name="' + ZM + '">' + ZM + '</div>'
                    + '</div>';
        }
        for (var i = 0; i < pys.length; i++) {
            if (pys[i] != null && pys[i] != '') {
                var peopleInfo = pys[i].split(':');
                var s = peopleInfo[1];
                resultData +=
                        '<div class="people" onclick="clickIt(this)" id="' + peopleInfo[0] + '">'
                        + '<div class="myInfo fl">'
                        + ' <dl>'
                        + '<dt class="" id="name' + peopleInfo[0] + '" style="overflow:hidden;white-space: nowrap;text-overflow: ellipsis;">'
                        + s.substring(0, s.indexOf('_'))
                        + '</dt>'
                        + '</dl>'
                        + '</div>'
                        + '</div>';
            }
        }
        resultData += '</li>';
    }
}
var keyword4Searching = '';
$(function() {
    $("#searchInput").click(function() {
        if ($("#searchInput").val() == '请输入关键字进行查询') {
            $("#searchInput").val('');
        }
    });

    $("#searchBtn").click(function() {
        idPre = ""; // 清除前个选择人员id
        namePre = ""; // 清除前个选择人员name
        id = ""; // 清除前个选择人员id
        name = ""; // 清除前个选择人员name
        var peopleName = $("#searchInput").val();
        var name_ = Trim(peopleName);
        if (name_ == '请输入关键字进行查询' || name_.length==0) {
            alert('请输入关键字');
            return;
        }else if(name_.length>0){
            pinyin=false;
        }else{
            pinyin=true;
        }
        keyword4Searching = name_;
        requestPeopleList(); // 请求人员适时数据  
    });

    $("#ok").click(function() {
        selectAction();
    });
    $('.people').click(function() {
        console.log('sss');
    });
});
//实时搜索
function keyUp(e) {
    idPre = ""; // 清除前个选择人员id
    namePre = ""; // 清除前个选择人员name
    id = ""; // 清除前个选择人员id
    name = ""; // 清除前个选择人员name
    var peopleName = $("#searchInput").val();
    var name_ = Trim(peopleName);
    if (name_ == '请输入学校名称') {
        alert('请输入学校名称');
        return;
    }
    keyword4Searching = name_;
    requestPeopleList(); // 请求人员适时数据
}
function searchPeople() {
    dPre = ""; // 清除前个选择人员id
    namePre = ""; // 清除前个选择人员name
    id = ""; // 清除前个选择人员id
    name = ""; // 清除前个选择人员name
    var peopleName = $("#searchInput").val();
    var name_ = Trim(peopleName);
    if (name_ == '请输入学校名称') {
        alert('请输入学校名称');
        return;
    }
    keyword4Searching = name_;

    requestPeopleList(); // 请求人员适时数据
    //mySearchCodeEnd
    // }
}


function requestPeopleList() {
    // 名字
    var k = 0;
    $.ajax({
        type: "POST",
        dataType: "json",
        //url:"bs",
        url: "/dev/reg/getschool",
        async: false,
        success: function(data) {
            ChineseArray = data;
        }
    });
    if (isInit) {
        // 传值
        var ChineseArrayIn = new Array();
        ChineseArrayIn = ChineseArray;
        for (var i = 0; i < ChineseArrayIn.length; i++) {
            //myConsole1
            // console.log(ChineseArrayIn[i]);
        }
        ;
        // 排序操作
        sortChinese(ChineseArrayIn);
        isInit = false;
    } else {
        // 搜索
        choiceByName(ChineseArray, keyword4Searching);
    }

}

 