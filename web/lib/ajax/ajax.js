var ajax_return_value = '';

var cUNINITIALIZED = 0;
var cLOADING = 1;
var cLOADED = 2;
var cINTERACTIVE = 3;
var cCOMPLETED = 4;

function _getXmlHttp(){
    var xmlhttp;
    try {
        xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
    } catch (e) {
        try {
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        } catch (e) {
            xmlhttp = false;
        }
    }
    if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
        xmlhttp = new XMLHttpRequest();
    }
    return xmlhttp;
}

function _AjaxPost( arg_url, arg_param, arg_function ){
    var xmlhttp = _getXmlHttp();
    if (xmlhttp) {
        xmlhttp.onreadystatechange = function() {
            if (xmlhttp.readyState == cCOMPLETED && xmlhttp.status == 200) {
                //文字表示
                var retVal = xmlhttp.responseText;
                if(retVal.substring(0,2) == 'OK'){
                    ajax_return_value = retVal.substring(3)
                    eval(arg_function);
                }else{
                    alert(retVal);
                }
            }
        }
        xmlhttp.open('POST', arg_url);
        xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xmlhttp.send( arg_param );
        delete xmlhttp;
    }

}

// *******************************
// AJAXをJSONデータパラメータでコール
// *******************************
var AJAXCall2json = function(url,params) {
    var dfd = jQuery.Deferred();

    var ajaxDataToTarget = encodeURIComponent(params);
    //var ajaxDataToTarget = params;
    $.ajax({
        type: "POST",
        url: url,
        contentType: "application/x-www-form-urlencoded; charset=UTF-8",
        data: params,
        async: true,
        dataType: 'json',
        cache: false,
        timeout: 10000,
        crossDomain: true,
        success: function(ret){
            var json_data = {};
            if(!$.isEmptyObject( ret )){
              json_data = ret;
            }
            dfd.resolve(json_data);
        },
        error: function(result, status, errors){
            //エラー出力
            dfd.reject(result, status, errors);
        }
    });
    return dfd.promise();
};

// *******************************
// (同期版)AJAXをJSONデータパラメータでコール
// *******************************
var latTsuushinErrText='';
var SYNC_AJAXCall2json = function(url,params,arg_async,arg_timeout) {
    var async = false;
    var timeout = 10 * 1000;
    if(arg_async!==undefined) async = arg_async; 
    if(arg_timeout!==undefined) timeout = arg_timeout; 

    var dfd = jQuery.Deferred();

    var ajaxDataToTarget = encodeURIComponent(params);
    //var ajaxDataToTarget = params;

    $.ajax({
        type: "POST",
        url: url,
        contentType: "application/x-www-form-urlencoded; charset=UTF-8",
        data: params,
        async: async,
        dataType: 'json',
        cache: false,
        timeout: timeout,
        crossDomain: true,
        success: function(ret){
            var json_data = {};
            if(!$.isEmptyObject( ret )){
              json_data = ret;
            }
            dfd.resolve(json_data);
        },
        error: function(result, status, errors){
            //エラー出力
            latTsuushinErrText = result.responseText;
            $('#id_terr_btn').show();
            dfd.reject(result, status, errors);
        }
    });
    return dfd.promise();
};
