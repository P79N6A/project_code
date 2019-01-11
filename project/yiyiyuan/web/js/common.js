// JavaScript Document
$(function () {
    var click_num = 1;
    try {
        $("#main-container").height($(window.top).height() - 82);
    } catch (e) {
    }
    $(window).bind("resize", function () {
        try {
            var p_window = window.top.document.getElementById("main");
            $("#mainContent").height($(p_window).height());
            $("#main-container").height($(window.top).height() - 82);
        } catch (e) {
        }
    });

    $(".li-header").each(function () {
        $(this).bind("click", function () {
            $(".li-header").each(function () {
                $(".icon", $(this)).removeClass("active");
                $(this).removeClass("active");
            });
            $(this).addClass("active");
            $(".icon", $(this)).addClass("active");
        });
    });
    $(".son_list a").each(function () {
        $(this).bind("click", function () {
            $(".son_list a").each(function () {
                $(this).removeClass("active");
            });
            $(this).addClass("active");
            $(".li-header").each(function () {
                $(".icon", $(this)).removeClass("active");
                $(this).removeClass("active");
            });
            $(this).parent().parent().siblings().addClass("active");
            $(".icon", $(this).parent().parent().siblings()).addClass("active");
        });
    });

    $("#shenhe_all").click(function () {
        var arr_v = [];
        $("input[type='checkbox']:checked").each(function () {
            arr_v.push($(this).val());
        });
        var sUserid = arr_v.join(',');
        if (sUserid.length > 0) {
            $.Zebra_Dialog('是否确认审核通过', {
                'type': 'question',
                'title': '确认审核',
                'buttons': [
                    {caption: '确定', callback: function () {
                            $.post("/backend/user/settongguoall", {sUserid: sUserid}, function (result) {
                                if (result == 'nouser')
                                {
                                    alert('没有选中用户，审核失败');
                                    return false;
                                }
                                else if (result == 'fail')
                                {
                                    alert('审核失败');
                                    return false;
                                }
                                else
                                {
                                    window.location.reload();
                                }
                            });
                        }},
                    {caption: '取消', callback: function () {
                        }},
                ]
            });
        }
        else
        {
            alert('请选择用户');
            return false
        }
    });
    $("#allbohui").click(function () {
        var arr_v = [];
        $("input[type='checkbox']:checked").each(function () {
            arr_v.push($(this).val());
        });
        var sUserid = arr_v.join(',');
        if (sUserid.length > 0) {
            $.Zebra_Dialog('是否确认驳回', {
                'type': 'question',
                'title': '确认驳回',
                'buttons': [
                    {caption: '确定', callback: function () {
                            $.post("/backend/user/setbohuiall", {sUserid: sUserid}, function (result) {
                                if (result == 'nouser')
                                {
                                    alert('没有选中用户，审核失败');
                                    return false;
                                }
                                else if (result == 'fail')
                                {
                                    alert('审核失败');
                                    return false;
                                }
                                else
                                {
                                    window.location.reload();
                                }
                            });
                        }},
                    {caption: '取消', callback: function () {
                        }},
                ]
            });
        }
        else
        {
            alert('请选择用户');
            return false
        }
    });


    $(".underline").click(function () {
        var remit_id = $(this).attr('remit');
        var payment_channel = $(this).attr('payment_channel');
        var html = '';
        if (payment_channel == 1) {
            html = '是否确认线下出款<p>';
            html += '请选择出款通道<p>';
            html += '<input type="radio" name="payment_channel" value="1" />新浪出款<p>';
            html += '<input type="radio" name="payment_channel" value="2" />中信出款<p>';
            html += '<input type="radio"  name="payment_channel" value="4" />恒丰出款<p>';
            html += '<input type="radio" checked="checked" name="payment_channel" value="5" />广发出款<p>';
        } else {
            html = '是否确认线下出款<p>';
            html += '<input type="radio" name="payment_channel" value="2" />中信出款<p>';
            html += '<input type="radio"  name="payment_channel" value="4" />恒丰出款<p>';
            html += '<input type="radio" checked="checked" name="payment_channel" value="5" />广发出款<p>';
        }
        if (click_num == 1) {
            $.Zebra_Dialog(html, {
                'type': 'question',
                'title': '确认线下出款',
                'buttons': [
                    {caption: '确定', callback: function () {
                            click_num += 1;
                            var payment_channel = $("input[name='payment_channel']:checked").val();
                            if (!payment_channel) {
                                payment_channel = 0;
                            }
                            $.post("/backend/loan/underline", {id: remit_id, remit_status: 'SUCCESS', payment_channel: payment_channel}, function (result) {
                                result = eval('(' + result + ')');
                                if (result.code != 0) {
                                    alert(result.msg);
                                    return false;
                                } else {
                                    window.location.reload();
                                }
                            });
                        }},
                    {caption: '取消', callback: function () {
                        }},
                ]
            });
        }
        ;
    });

    $(".reject_faild").click(function () {
        // alert(1);return false;
        var remit_id = $(this).attr('remit');
        var html = "确认驳回？<p>";
        html += '请填写驳回理由<p>';
        html += '<textarea id="reason" placeholder="确认驳回？请输入驳回原因"></textarea><p><div id="reject_error" style="color:red;"></div>';
        if (click_num == 1) {
            $.Zebra_Dialog(html, {
                'type': 'question',
                'title': '确认驳回',
                'buttons': [
                    {caption: '确定', callback: function () {
                            var reason = $('#reason').val();
                            if (!reason) {
                                $('#reject_error').html('请填写驳回理由');
                                return false;
                            } else {
                                click_num += 1;
                                $.post("/backend/loan/reject_faild", {id: remit_id, reason: reason}, function (result) {
                                    result = eval('(' + result + ')');
                                    if (result.code != 0) {
                                        alert(result.msg);
                                        return false;
                                    } else {
                                        window.location.reload();
                                    }
                                });
                            }
                        }},
                    {caption: '取消', callback: function () {
                        }},
                ]
            });
        }
        ;
    });


    //PRE-REMIT,FAIL驳回操作
    $(".reject_extend_loan").click(function () {
//         alert(click_num);return false;
        var extend_id = $(this).attr('remit');
        var html = "确认驳回？<p>";
        html += '请填写驳回理由<p>';
        html += '<textarea id="extend_reason" placeholder="确认驳回？请输入驳回原因"></textarea><p><div id="reject_extend_error" style="color:red;"></div>';
        if (click_num == 1) {
            $.Zebra_Dialog(html, {
                'type': 'question',
                'title': '确认驳回',
                'buttons': [
                    {caption: '确定', callback: function () {
                            var reason = $('#extend_reason').val();
                            if (!reason) {
                                $('#reject_extend_error').html('请填写驳回理由');
                                return false;
                            } else {
                                click_num += 1;
                                $.post("/backend/loan/reject_loan", {id: extend_id, reason: reason}, function (result) {
                                    result = eval('(' + result + ')');
                                    if (result.code != 0) {
                                        alert(result.msg);
                                        return false;
                                    } else {
                                        window.location.reload();
                                    }
                                });
                            }
                        }},
                    {caption: '取消', callback: function () {
                        }},
                ]
            });
        }
        ;
    });

    $(".pull_black").click(function () {
        var user_id = $(this).attr('black');
        $.Zebra_Dialog('是否确认拉黑', {
            'type': 'question',
            'title': '确认拉黑',
            'buttons': [
                {caption: '确定', callback: function () {
                        $.post("/backend/user/pullblack", {user_id: user_id}, function (result) {
                            result = eval('(' + result + ')');
                            if (result.code != 0) {
                                alert(result.msg);
                                return false;
                            } else {
                                window.location.reload();
                            }
                        });
                    }},
                {caption: '取消', callback: function () {
                    }},
            ]
        });
    });

    $(".remove_black").click(function () {
        var user_id = $(this).attr('black');
        $.Zebra_Dialog('是否确认移出黑名单', {
            'type': 'question',
            'title': '确认移出黑名单',
            'buttons': [
                {caption: '确定', callback: function () {
                        $.post("/backend/user/removeblack", {user_id: user_id}, function (result) {
                            result = eval('(' + result + ')');
                            if (result.code != 0) {
                                alert(result.msg);
                                return false;
                            } else {
                                window.location.reload();
                            }
                        });
                    }},
                {caption: '取消', callback: function () {
                    }},
            ]
        });
    });

    $(".remit_confirm").click(function () {
        var remit_id = $(this).attr('remit');
        $.Zebra_Dialog('确认出款成功', {
            'type': 'question',
            'title': '确认出款成功',
            'buttons': [
                {caption: '确定', callback: function () {
                        $.post("/backend/loan/underline", {id: remit_id, remit_status: 'SUCCESS'}, function (result) {
                            result = eval('(' + result + ')');
                            if (result.code != 0) {
                                alert(result.msg);
                                return false;
                            } else {
                                window.location.reload();
                            }
                        });
                    }},
                {caption: '取消', callback: function () {
                    }},
            ]
        });
    });

    $(".reject_confirm").click(function () {
        var remit_id = $(this).attr('remit');
        $.Zebra_Dialog('确认出款失败', {
            'type': 'question',
            'title': '确认出款失败',
            'buttons': [
                {caption: '确定', callback: function () {
                        $.post("/backend/loan/underline", {id: remit_id, remit_status: 'FAIL'}, function (result) {
                            result = eval('(' + result + ')');
                            if (result.code != 0) {
                                alert(result.msg);
                                return false;
                            } else {
                                window.location.reload();
                            }
                        });
                    }},
                {caption: '取消', callback: function () {
                    }},
            ]
        });
    });

    try
    {
        var p_window = window.top.document.getElementById("main");
    }
    catch (e)
    {
    }
//    var p_window = window.top.document.getElementById("main");
    $("#mainContent").height($(p_window).height());


    $('.son_list').each(function () {
        $(this).collapse('show');
    });

    $("#changecardnumber").on('dblclick', function () {
        var sid = $(this).attr('sid');
        var td = $(this);
        var textval = td.text();
        var input = $('<input type="text" class="input40" value="' + textval + '"/>');
        td.html(input);
        input.click(function () {
            return false;
        });
        input.trigger("focus").trigger("select");
        input.blur(function () {

            var input_blur = $(this);
            var newText = input_blur.val();
            if (textval == newText) {
                td.html(textval);
                return;
            }
            else
                updatechangecardnumber(sid, newText, td);
        });
    });

    var updatechangecardnumber = function (id, newText, node) {
        $.post('/backend/loan/changecardnumber', {id: id, number: newText}, function (data) {
            if (data == "success")
                node.html(newText);
            else
                return false;
        });
    };
    $("#changebank").on('dblclick', function () {
        var sid = $(this).attr('sid');
        var td = $(this);
        var textval = td.text();
        var input = $('<input type="text" class="input40" value="' + textval + '"/>');
        td.html(input);
        input.click(function () {
            return false;
        });
        input.trigger("focus").trigger("select");
        input.blur(function () {

            var input_blur = $(this);
            var newText = input_blur.val();
            if (textval == newText) {
                td.html(textval);
                return;
            }
            else
                updatechangebank(sid, newText, td);
        });
    });
    var updatechangebank = function (id, newText, node) {
        $.post('/backend/loan/changebank', {id: id, name: newText}, function (data) {
            if (data == "success")
                node.html(newText);
            else
                return false;
        });
    };
    $("#changesubbank").on('dblclick', function () {
        var sid = $(this).attr('sid');
        var td = $(this);
        var textval = td.text();
        var input = $('<input type="text" class="input40" value="' + textval + '"/>');
        td.html(input);
        input.click(function () {
            return false;
        });
        input.trigger("focus").trigger("select");
        input.blur(function () {

            var input_blur = $(this);
            var newText = input_blur.val();
            if (textval == newText) {
                td.html(textval);
                return;
            }
            else
                updatechangesubbank(sid, newText, td);
        });
    });
    var updatechangesubbank = function (id, newText, node) {
        $.post('/backend/loan/changesubbank', {id: id, name: newText}, function (data) {
            if (data == "success")
                node.html(newText);
            else
                return false;
        });
    };
    $("#changeprovince").on('dblclick', function () {
        var sid = $(this).attr('sid');
        var td = $(this);
        var textval = td.text();
        var pid = $(this).attr('pid');
        var htmlOption = "";
        $.getJSON("/dev/bind/getcity", {'pid': 0}, function (json) {
            $.each(json, function (i, item) {
                if (pid == item.id)
                {
                    htmlOption += "<option selected value='" + item.id + "'>" + item.name + "</option>";
                }
                else
                {
                    htmlOption += "<option value='" + item.id + "'>" + item.name + "</option>";
                }

            });
            var sel_data = '<select>' + htmlOption + '</select>';
            var input = $(sel_data);
            td.html(input);
            input.click(function () {
                return false;
            });
            input.trigger("focus").trigger("select");
            input.blur(function () {

                var input_blur = $(this);
                var newPid = input_blur.val();
                var newText = input_blur.find("option:selected").text()
                if (pid == newPid) {
                    td.html(textval);
                    return;
                }
                else
                    updatechangeprovince(sid, newPid, td, newText);
            });
        });
    });
    var updatechangeprovince = function (id, newPid, node, newText) {
        $.post('/backend/loan/changeprovince', {id: id, name: newPid}, function (data) {
            if (data == "success") {
                node.attr("pid", newPid);
                node.html(newText);

            }
            else
                return false;
        });
    };
    $("#changecity").on('dblclick', function () {
        var opid = $('#changeprovince').attr('pid');
        var sid = $(this).attr('sid');
        var td = $(this);
        var textval = td.text();
        var pid = $(this).attr('pid');
        var htmlOption = "";
        $.getJSON("/dev/bind/getcity", {'pid': opid}, function (json) {
            $.each(json, function (i, item) {
                if (pid == item.id)
                {
                    htmlOption += "<option selected value='" + item.id + "'>" + item.name + "</option>";
                }
                else
                {
                    htmlOption += "<option value='" + item.id + "'>" + item.name + "</option>";
                }

            });
            var sel_data = '<select>' + htmlOption + '</select>';
            var input = $(sel_data);
            td.html(input);
            input.click(function () {
                return false;
            });
            input.trigger("focus").trigger("select");
            input.blur(function () {

                var input_blur = $(this);
                var newPid = input_blur.val();
                var newText = input_blur.find("option:selected").text()
                if (pid == newPid) {
                    td.html(textval);
                    return;
                }
                else
                    //alert(newPid);
                    updatechangecity(sid, newPid, td, newText);
            });
        });
    });
    var updatechangecity = function (id, newPid, node, newText) {
        $.post('/backend/loan/changecity', {id: id, name: newPid}, function (data) {
            if (data == "success") {
                node.attr("pid", newPid);
                node.html(newText);

            }
            else
                return false;
        });
    };

    $("#changeusertype").on('dblclick', function () {
        var td = $(this);
        var textval = td.text();
        var input = $('<select><option selected value="0">普通用户</option><option  value="5">黑名单</option>');
        td.html(input);
        input.click(function () {
            return false;
        });
    });
    $("#normalPass").click(function () {
        var lid = $(this).attr('lid');
        $.Zebra_Dialog('是否确认审核通过', {
            'type': 'question',
            'title': '确认审核',
            'buttons': [
                {caption: '确定', callback: function () {
                        $.post("/backend/loan/normalpass", {lid: lid}, function (result) {
                            if (result == 'success') {
                                alert('成功');
                                window.location = '/backend/loan/abnor';
                            }
                            else
                            {
                                alert('fail');
                            }
                        });
                    }},
                {caption: '取消', callback: function () {
                    }},
            ]
        });
    });

    $("#now_doing").click(function () {
        var sid = $(this).attr('sid');
        var now_page = $(this).attr('now_page');
        $.Zebra_Dialog('是否确认为处理中', {
            'type': 'question',
            'title': '处理中',
            'buttons': [
                {caption: '确定', callback: function () {
                        $.post("/backend/loan/process", {sid: sid, now_page: now_page}, function (result) {
                            var data = eval('(' + result + ')');
                            if (data.ret == '0') {
                                alert('操作成功');
                                window.location = '/backend/loan/withlist?page=' + data.now_page;
                            } else if (data.ret == '1') {
                                alert('操作失败');
                            } else if (data.ret == '2') {
                                alert('操作失败');
                                window.location = '/backend/loan/withlist?page=' + data.now_page;
                            }
                        });
                    }},
                {caption: '取消', callback: function () {
                    }},
            ]
        });
    });

    $("#guaranteepass").click(function () {
        var lid = $(this).attr('lid');
        $.Zebra_Dialog('是否确认审核通过', {
            'type': 'question',
            'title': '确认审核',
            'buttons': [
                {caption: '确定', callback: function () {
                        $.post("/backend/loan/guaranteepass", {lid: lid}, function (result) {
                            if (result == 'success') {
                                alert('成功');
                                window.location = '/backend/loan/guaranteelist';
                            }
                            else
                            {
                                alert('fail');
                            }
                        });
                    }},
                {caption: '取消', callback: function () {
                    }},
            ]
        });
    });

    $("#guaranteereject").click(function () {
        var lid = $(this).attr('lid');
        $.Zebra_Dialog('是否确认驳回', {
            'type': 'question',
            'title': '确认审核',
            'buttons': [
                {caption: '确定', callback: function () {
                        $.post("/backend/loan/guaranteereject", {lid: lid}, function (result) {
                            var data = eval("(" + result + ")");
                            if (data.ret == '0') {
                                alert('成功');
                                window.location = '/backend/loan/guaranteelist';
                            }
                            else
                            {
                                alert('fail');
                            }
                        });
                    }},
                {caption: '取消', callback: function () {
                    }},
            ]
        });
    });

    $("#normalNo").click(function () {
        var lid = $(this).attr('lid');
        $.Zebra_Dialog('是否确认驳回', {
            'type': 'question',
            'title': '确认审核',
            'buttons': [
                {caption: '确定', callback: function () {
                        $.post("/backend/loan/normalno", {lid: lid}, function (result) {
                            var data = eval("(" + result + ")");
                            if (data.ret == '0') {
                                alert('成功');
                                window.location = '/backend/loan/abnor';
                            }
                            else
                            {
                                alert('fail');
                            }
                        });
                    }},
                {caption: '取消', callback: function () {
                    }},
            ]
        });
    });
    $('#c_cost').change(function () {
        var z = /^(\d+)(\.?)(\d{0,2})$/;
        var cost = $("#c_cost").val();
        if (!z.test(cost)) {
            $("#c_message").html('说好的就是数字呢');
            $("#c_message").show();
        } else {
            $("#c_message").hide();
        }
    });
    $('#c_desc').change(function () {
        var desc = $("#c_desc").val();
        if (desc == '') {
            $("#c_message").html('写点什么呢');
            $("#c_message").show();
        } else {
            $("#c_message").hide();
        }

    });
    $("#c_sms_sub").click(function () {
        var z = /^(\d+)(\.?)(\d{0,2})$/;
        var cost = $("#c_cost").val();
        var desc = $("#c_desc").val();
        var type = 1;
        if (z.test(cost) && desc != '') {
            var loan_id = $("#loan_id").html();
            $.post("/backend/collection/save", {loan_id: loan_id, cost: cost, desc: desc, type: type}, function (result) {
                if (result == 'success') {
                    alert('成功');
                    window.location = '/backend/loan/infoview?id=' + loan_id;
                } else {
                    alert('失败');
                }
            });
            //$(this).submit();
        } else {
            alert('不符合规则就敢点提交~');
        }
        //alert(cost);
        //$("#c_message").html(cost+desc);
    });
    $('#c_cost_t').change(function () {
        var z = /^(\d+)(\.?)(\d{0,2})$/;
        var cost = $("#c_cost_t").val();
        if (!z.test(cost)) {
            $("#c_message_t").html('说好的就是数字呢');
            $("#c_message_t").show();
        } else {
            $("#c_message_t").hide();
        }
    });
    $('#c_desc_t').change(function () {
        var desc = $("#c_desc_t").val();
        if (desc == '') {
            $("#c_message_t").html('写点什么呢');
            $("#c_message_t").show();
        } else {
            $("#c_message_t").hide();
        }

    });
    $("#c_tel_sub").click(function () {
        var z = /^(\d+)(\.?)(\d{0,2})$/;
        var cost = $("#c_cost_t").val();
        var desc = $("#c_desc_t").val();
        var type = 2;
        if (z.test(cost) && desc != '') {
            var loan_id = $("#loan_id").html();
            $.post("/backend/collection/save", {loan_id: loan_id, cost: cost, desc: desc, type: type}, function (result) {
                if (result == 'success') {
                    alert('成功');
                    window.location = '/backend/loan/infoview?id=' + loan_id;
                } else {
                    alert('失败');
                }
            });
            //$(this).submit();
        } else {
            alert('不符合规则就敢点提交~');
        }
        //alert(cost);
        //$("#c_message").html(cost+desc);
    });
    $('#c_cost_d').change(function () {
        var z = /^(\d+)(\.?)(\d{0,2})$/;
        var cost = $("#c_cost_d").val();
        if (!z.test(cost)) {
            $("#c_message_d").html('说好的就是数字呢');
            $("#c_message_d").show();
        } else {
            $("#c_message_d").hide();
        }
    });
    $('#c_desc_d').change(function () {
        var desc = $("#c_desc_d").val();
        if (desc == '') {
            $("#c_message_d").html('写点什么呢');
            $("#c_message_d").show();
        } else {
            $("#c_message_d").hide();
        }

    });
    $("#c_door_sub").click(function () {
        var z = /^(\d+)(\.?)(\d{0,2})$/;
        var cost = $("#c_cost_d").val();
        var desc = $("#c_desc_d").val();
        var type = 3;
        if (z.test(cost) && desc != '') {
            var loan_id = $("#loan_id").html();
            $.post("/backend/collection/save", {loan_id: loan_id, cost: cost, desc: desc, type: type}, function (result) {
                if (result == 'success') {
                    alert('成功');
                    window.location = '/backend/loan/infoview?id=' + loan_id;
                } else {
                    alert('失败');
                }
            });
            //$(this).submit();
        } else {
            alert('不符合规则就敢点提交~');
        }
        //alert(cost);
        //$("#c_message").html(cost+desc);
    });


    $('.invoke').click(function () {
        $('.mask').css('display', 'block');
        $('.pop').css('display', 'block');
    });
    $('.cancel').click(function () {
        $('.mask').css('display', 'none');
        $('.pop').css('display', 'none');
    });


    radio('#price0', '#price1', '#price2');
    radio('#limit0', '#limit1', '#limit2');

    $('#way1').click(function () {
        $('#showway2').hide();
        $('#showway1').show();
    });
    $('#way2').click(function () {
        $('#showway1').hide();
        $('#showway2').show();
    });
});



function openWin(href) {
    $("#main").attr("src", href);
    return false;
}

function selectAll() {
    var selectall = $("#selectAll").attr('pid');
    if (selectall == '') {
        $("input[type='checkbox']").prop("checked", true);
        $("#selectAll").attr('pid', '1');
    } else {
        $("input[type='checkbox']").prop("checked", false);
        $("#selectAll").attr('pid', '');
    }
}


function radio(a, b, c) {
    if ($(c).attr("checked")) {
        $(b).attr("disabled", "disabled")
    }

    $(a).click(function () {
        $(b).removeAttr("disabled")
    })
    $(c).click(function () {
        $(b).attr("disabled", "disabled")
    })
}











