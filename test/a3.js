Copyright (C) DICP-CMC Innovation Institute of Medicine.
var HTTP_URL = "http://www.castim.cn/zuneihaocai/sample.php";//接口地址
var _pidCode;//物品编码pid
var _wzCode;//物品code
var _childNode='';//子节点Lable
var _parentNode='';//父节点Lable
var _currInfo ='';//当前节点信息
var _num_index = 0;//计数器 
var _flag;//根节点:0 子节点:1

//查找样本编码
//vrif  判断是否提示--search：false check:--true
function getSampleCode(params,vrif){
    switch(params){
        case 0://查找样本编码
            var s_wzbm=$("#wzbm").val(); //物资编码
            if(s_wzbm.length==1 || s_wzbm.length==5 || s_wzbm.length>5){
                getParentbyRegexName(s_wzbm,vrif);
                return;
            }
            break;
        case 1://查找样本树
            var s_wzbm=$("#wzfind").val(); //物资编码
            getParentbyRegexName(s_wzbm,vrif);
            break;
        default:break;
    }
}

//获取父节点id
function getParentbyRegexName(params,vrif) {
    $.ajax({           
            type: "get",     
            //方法所在页面和方法名      
            url: HTTP_URL,     
            contentType: "application/json; charset=utf-8",     
            dataType:'json',
            data: {"action":"getParentbyRegexName","str":params},
            success: function(data) {
                    try{
                        $("#wz_ele_list").children("option").remove();
                        var pid =  data.data.parents;
                        //
                        if(pid.length != 0 && !vrif){
                            $.each(pid, function(i, item) {
                                var pid_ = item.pid;//父节点ID
                                var id_ = item.id;
                                var code_ = item.code;//样品编码
                                var wz_ele_list_str = "";
                                //物资拼音/中药名称
                                wz_ele_list_str = "<option id =\""+pid_+"\">"+code_+"</option>";
                                $("#wz_ele_list").append(wz_ele_list_str);
                                _wzCode = code_ ;
                                //如果是根节点，取根节点的id
                                //否则取其父节点id
                                if(pid_ == 0){
                                    _pidCode = id_;
                                }else{
                                    _pidCode = pid_;
                                }
                            })
                        }
                        //样本不存在数据库中
                        else if(pid.length == 0 && vrif){
                            alert("样本编码不存在");
                        }
                        //样本存在数据库中，解析编码并实时展示
                         else if(pid.length != 0 && vrif){
                            splitWzCode();
                            showCodeText();
                        }
                    }catch(err){
                        alertInfos("无父节点");
                     }
            },     
            error: function(err) {     
                alertInfos("err--->"+err);     
            }     
        });     
}

//sub节点数据
function submitFuc(){
        var s_wzdd=$("#wzdd option:selected").val(); //物资地点
        var s_wzpy=$("#wzpy").val(); //物资拼音 中药名称
        var s_wzkind=$("#wzkind option:selected").val(); //物资种类
		var s_wzcz=$("#wzcz option:selected").val(); //物资操作
		var s_wzxm=$("#wzxm option:selected").val(); //物资项目
		var s_wzrq=$("#wzrq").val(); //物资日期
		var s_weight=$("#weight").val(); //物资重量
		var s_position=$("#position:selected").val(); //物资存放位置
        var s_description=$("#description").val(); //物资描述 
        var s_sampleNum=$("#sampleNum").val(); //物资样本数量
        $("#childSamplediv").children("lable").remove(); 
        $("#childSamplediv").children("br").remove(); 
        switch(_flag)
            {
                case 0:
                    //根节点
                     //验证数据
                if(isNullObj(s_wzdd,1) && isNullObj(s_wzpy,1)&&isNullObj(s_wzkind,1)){
                    //物品编码
                    var s_code = `${s_wzdd}${s_wzpy}${s_wzkind}`;
                }else{
                    alertRegx(s_wzdd,"样本地址");
                    alertRegx(s_wzpy,"中药名称");
                    alertRegx(s_wzkind,"样本种类");
                    return;
                }
                    $.ajax({
                    type: "post",
                    url: HTTP_URL,
                    dataType:'json',
                    data: {"action":"saveCodeName","pid":"0,","code":s_code},
                    success: function(data) {     
                                    //返回的数据 获取内容      
                                        alert(data.remark);
                                },     
                                error: function(err) {  
                                    alert(err);     
                                }     
                        });
                    break;
                case 1:
                    //子节点
                    //日期格式化 去掉-
                    var s_wzrq_regx = s_wzrq.replace(/\-/g, "").substr(2,6);
                    var vrifCode =  _wzCode.substr(6,3);
                    //验证数据
                    if  (isNullObj(s_wzdd,1) && isNullObj(s_wzpy,1) && 
                        isNullObj(s_wzkind,1)&& isNullObj(s_wzcz,1)&& 
                        isNullObj(s_wzxm,1) && isNullObj(s_wzrq_regx,1)&&isNullObj(s_sampleNum,1)){
                        //物品编码
                        var s_code = `${s_wzdd}${s_wzpy}${s_wzkind}${vrifCode}${s_wzcz}${s_wzxm}${s_wzrq_regx}`;}
                    else{
                        alertRegx(s_wzdd,"样本地址");
                        alertRegx(s_wzpy,"中药名称");
                        alertRegx(s_wzkind,"样本种类");
                        alertRegx(s_wzcz,"样本操作");
                        alertRegx(s_wzxm,"样本项目");
                        alertRegx(s_wzrq_regx,"样本日期");
                        alertRegx(s_sampleNum,"样本数量");
                    }
                    
                    $.ajax({
                    type: "post",
                    url: HTTP_URL,
                    dataType:'json',
                    data: {"action":"saveCodeName","pid":_pidCode,"code":s_code,
                            "weight":s_weight,"place":s_position,"discription":s_description,"number":s_sampleNum},
                    success: function(data) {     
                                    //返回的数据 获取内容     
                                    if(data.status != 0){
                                     $.each(data.data.code, function(i, item) {
                                        var czstr = "</br><lable value =\""+item+"\" ><font color=\"#0c00af\" weight=\"bold\">"+item+"</font></lable></br>";
                                        $("#childSamplediv").append(czstr);
                                    })
                                    alert(data.remark); 
                                 }
                                   
                                },     
                                error: function(err) {  
                                    alert(err);     
                                }     
                        });
                    break;
                    case 2:
                    //手动输入根节点
                     //验证数据
                        var cRootVal = $("#crootText").val().toUpperCase();
                        if(regExpRootCode(cRootVal)){
                            $.ajax({
                                type: "post",
                                url: HTTP_URL,
                                dataType:'json',
                                data: {"action":"saveRootCodeName","code":cRootVal},
                                success: function(data) {     
                                                //返回的数据 获取内容      
                                                    alert(data.remark);
                                                    if(data.status==1){
                                                        var czstr = "<lable><font color=\"#0c00af\" weight=\"bold\">"+data.data.code+"</font></lable></br>";
                                                        $("#childSamplediv").append(czstr);
                                                    }
                                                  
                                            },     
                                            error: function(err) {  
                                                alert(err);     
                                            }     
                                    });
                            return;
                        }else {
                            alert("编码不符合规范");
                            return;
                        }
                        break;
                    default:break;
            }
}

//获取父节点、子节点编码信息
function getInfo() {
    $("#show").children("lable").remove();
    var s_wzfind=$("#wzfind").val(); //物资编码
    if(undefined === s_wzfind || '' === s_wzfind){
        alertInfos("编码信息不能为空");
        return;
    }
   
    $.ajax({           
            type: "get",     
            //方法所在页面和方法名      
            url: HTTP_URL,     
            contentType: "application/json; charset=utf-8",     
            dataType:'json',
            data: {"action":"getInfo","code":s_wzfind},
            success: function(data) {
                    try{
                        ///////////////////当前节点///////////////////////////////
                        var current_code =  data.data.current.code;//节点编码
                        var current_id =  data.data.current.id;//节点ID
                        var current_weight =  data.data.current.weight;//节点重量
                        var current_place =  data.data.current.place;//节点位置
                        var current_user =  data.data.current.user;//节点用户
                        var current_description =  data.data.current.description;//节点描述
                        var current_create_time =  data.data.current.create_time;//节点创建时间
                        var current_update_time =  data.data.current.update_time;//更新时间

                        _currInfo ="当前样本:"+current_code
                                    +"\n样本ID:"+current_id
                                    +"\n样本重量:"+current_weight+"mg"
                                    +"\n样本位置:"+current_place
                                    +"\n样本用户:"+current_user
                                    +"\n样本描述:"+current_description
                                    +"\n样本创建时间:"+current_create_time
                                    +"\n更新时间:"+current_update_time;
                        //////////////////////////父节点//////////////////////////////
                        var parents =  data.data.ancestors;
                        $.each(parents, function(i, item) {
                            var parent_id = item.id;//父节点ID
                            var parent_code = item.code;//父节点编码
                            _num_index++;
                            var parent_node = _num_index+"级样本&nbsp<lable value =\""+parent_id+"\" >"+parent_code+"</lable></br>";
                            _parentNode += parent_node;
                        })
                        //////////////////////////子节点////////////////////////////
                        var childss =  data.data.childs;
                        if(childss.length!=0){
                            var child_id = childss[0].id;//子节点ID
                            var child_code = childss[0].code;//子节点编码
                            _num_index++;
                            var child_node = _num_index+"级样本&nbsp<lable value =\""+child_id+"\" >"+child_code+"</lable></br>";
                            _childNode += child_node;
                        }
                        ///////////////////////节点拼接展示////////////////////////
                        //当前节点
                        currend_node = "<lable value =\""+current_id+"\" onclick=\"onCurrentInfo();\"><font color=\"#FF0000\">"+current_code+"</font></lable></br>";
                        $("#show")
                       .append(_parentNode)
                       .append("<lable >当前样本</lable>")
                       .append(currend_node)
                       .append(_childNode);
                    }catch(err){
                        alertInfos("系统异常"+err);
                     }
            }, 
            error: function(err) {     
                alertInfos("err--->"+err);     
            }
        });     
}

//当前节点信息
function onCurrentInfo(){
    alertInfos(_currInfo);
}

//根节点、子节点创建
function checkradio(){ 
    var item = $(":radio:checked");
    var len=item.length; 
    if(len>0){
        var checkedVal = $(":radio:checked").val(); 
        if(checkedVal == 0)
        {
            $("#left_div").show();
            $("#crootdiv").hide();
            //创建根节点
            $("#wzbm").attr({"disabled":"disabled"}); 
            $("#wzdd").removeAttr("disabled");
            $("#wzpy").removeAttr("disabled");
            $("#wzkind").removeAttr("disabled");
            _flag = 0;
            //
            $("#wzcz").attr({"disabled":"disabled"});
            $("#wzxm").attr({"disabled":"disabled"}); 
            $("#wzrq").attr({"disabled":"disabled"}); 
            $("#weight").attr({"disabled":"disabled"}); 
            $("#position").attr({"disabled":"disabled"});
            $("#description").attr({"disabled":"disabled"});
            $("#sampleNum").attr({"disabled":"disabled"});
            clearData();
            
        }else if(checkedVal == 1){
            $("#left_div").show();
            $("#crootdiv").hide();
            //创建子节点
            $("#wzbm").removeAttr("disabled");
            $("#wzdd").attr({"disabled":"disabled"});
            $("#wzpy").attr({"disabled":"disabled"});
            $("#wzkind").attr({"disabled":"disabled"});
            _flag = 1;
            //
            $("#wzcz").removeAttr("disabled");
            $("#wzxm").removeAttr("disabled");
            $("#wzrq").removeAttr("disabled");
            $("#weight").removeAttr("disabled");
            $("#position").removeAttr("disabled");
            $("#description").removeAttr("disabled");
            $("#sampleNum").removeAttr("disabled");
        }else if(checkedVal == 2){
            _flag = 2;
            $("#crootdiv").show();
            $("#left_div").hide();
            $("#childSamplediv").html("");
            $("#childSamplediv").children("lable").remove(); 
            $("#childSamplediv").children("br").remove(); 
           
        }
    } 
} 


function alertInfos(params){
    alert(params);
}

//截取wzcode填充数据
function splitWzCode(){
    //截取编码
    var wzbmstr = $("#wzbm").val();
    const wzbmlength= wzbmstr.length;
    if(wzbmlength>0 && wzbmlength<=8){
        //填充数据
        var s_wzdd = $("#wzdd").val(wzbmstr.substr(0,1));//物资地址
        var s_wzpy = $("#wzpy").val(wzbmstr.substr(1,4));//中药名称
        var s_wzkind = $("#wzkind").val(wzbmstr.substr(5,1));//物资种类
    }else if(wzbmlength>8){
        //填充数据
        var s_wzdd = $("#wzdd").val(wzbmstr.substr(0,1));//物资地址
        var s_wzpy = $("#wzpy").val(wzbmstr.substr(1,4));//中药名称
        var s_wzkind = $("#wzkind").val(wzbmstr.substr(5,1));//物资种类
        // var s_wzcz = $("#wzcz").val(wzbmstr.substr(8,1));//物资操作
        // var s_wzxm = $("#wzxm").val(wzbmstr.substr(9,2));//物资项目
        //样本日期
        // var wzRqCode = wzbmstr.substr(11,6);//物资日期
        // var year = wzRqCode.substr(0,2);
        // var month = wzRqCode.substr(2,2);
        // var day = wzRqCode.substr(4,2);
        // var date="20"+year+"-"+month+"-"+day;
        // var s_wzrq = $("#wzrq").val(date);
    }else{
        alertInfos("样品编码不正确");
    }
    
}

//实时展示物资编码
function showCodeText(child = false){
    var s_wzdd=$("#wzdd option:selected").val(); //物资地点
    var s_wzpy=$("#wzpy").val(); //物资拼音 中药名称
    var s_wzkind=$("#wzkind option:selected").val(); //物资种类
    var s_wzcz=$("#wzcz option:selected").val(); //物资操作
    var s_wzxm=$("#wzxm option:selected").val(); //物资项目
    var s_wzrq=$("#wzrq").val().replace(/\-/g, "").substr(2,6);; //物资日期
    if(undefined !=_wzCode && ''!=_wzCode){
        var vrifCode =  _wzCode.substr(6,3);
    }
    let str= isNullObj(s_wzdd,0,1)+isNullObj(s_wzpy,0,4)+isNullObj(s_wzkind,0,1)+isNullObj(vrifCode,0,1);
    if(child) str += isNullObj(s_wzcz,0,1)+isNullObj(s_wzxm,0,2)+isNullObj(s_wzrq,0,6);
    $("#childSamplediv").html(str+"</br>");
    
}
//刷新清除数据
function clearData(){
    var wzbmStr = $("#wzbm").val();
    var showCodeStr = $("#showcodetext").val();
    if(wzbmStr!="" && wzbmStr!=null){
        location.reload();
    }
}

//验证对象
function isNullObj(_params, _flag, _posNum){
    switch(_flag){
        case 0:
            if(_params!=null && _params!=undefined && _params!="" & _params !="new")
                return _params;
            let rt = "";
            for(let i=0; i<_posNum; i++) {
                rt +="#";
            }
            return rt;
            break;
        case 1:
            if(_params!=null && _params!=undefined && _params!="" & _params !="new")
                return true;
            else
                return false;
        break;
    }
}
//验证数据
function regxParams(_params,_msg){
        if(_params!=null && _params!=undefined && _params!="" & _params !="new")
            return "";
        else
            return "请检查  "+_msg+" 是否正确";
}

//alert数据验证
function alertRegx(_params,msg){
    if(regxParams(_params,1)!="")
        alert(regxParams(_params,msg));
    else
        return;
}

//正则验证根编码
function regExpRootCode(_code) {
    var patrn = /^[D,T]{1}[A-Z][A-Z0-9]{3}[A-Z]{1}[0-9]{3}$/;
    if (!patrn.exec(_code)) return false
    return true
}

// 小写转大写
//_params 控件id
function rootUpperCase(_params){
    // $("#crootText").val($("#crootText").val().toUpperCase());
    $("#"+_params+"").val($("#"+_params+"").val().toUpperCase());
    
}