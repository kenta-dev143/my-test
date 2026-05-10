//*********************************************************
//  BaseURL取得
//*********************************************************

var getScriptPath = function(){
  var f=function(e){
    var name=e.tagName;
    if(!!name && name.toUpperCase()=='SCRIPT') return e;
    var c=e.lastChild;
    return (!!c)?f(c):null;
  };
  var es=f(document);
  if(!es) return window.location;
  return es.getAttribute('src') || window.location;
};
var this_js_path = getScriptPath();
var pfld = this_js_path.split('/');
var pfld2 = new Array();
for(var g=0;g<pfld.length-1;g++){
    pfld2[g] = pfld[g];
}
var base_path = pfld2.join('/');


//*********************************************************
//  Ajax送受信
//*********************************************************
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
                    ajax_return_value = retVal.substring(2)
                    eval(arg_function + '();');
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




//*********************************************************
//  郵便番号検索
//*********************************************************

function _searchPost(){
    var post_no = def_post;
    var err_flg = false;

    //*** replaceした文字列
    var re_def_post = def_post.value.replace("-","");

    //NULL Check
    if(re_def_post == ""){
        alert("郵便番号を入力してください。");
        err_flg = true;
        return false;
    }
    //整数チェック
    if(!re_def_post.match(/[0-9]+/g)){
        alert("郵便番号が不正です。");
        err_flg = true;
        return false;
    }
    //7桁チェック
    if(re_def_post.length != 7){
        alert("郵便番号が不正です。");
        err_flg = true;
        return false;
    }

    //郵便番号検査
    if(err_flg == false){
        //var url = '../lib/post_search/post_search_ajax/get_post.php';
        var url = base_path + '/post_search_ajax.php';
        var param = 'no='+ re_def_post;
        var func = '_getPost';
        _AjaxPost(url,param,func)
    }
    return err_flg;
}

function _getPost(){
    var post_no = def_post;
    var todou   = def_todoufuken;
    var addr   = def_address;
    var returnResult = new Array();

    //返ってきた値
    ret = ajax_return_value;
    returnResult = ret.split("_");

    var tansuu_flg = false;
    if(returnResult.length == 2){
        tansuu_flg = true;
    }

    if(tansuu_flg == true){
        w_arg = returnResult[0].split("#");
        if(w_arg[0] == 'sippai'){
            alert("存在しない郵便番号です。");
            return false;
        }else{
            for(var i=0;i<todou.options.length;i++){
                if(w_arg[1] == todou.options[i].value){
                    todou.options[i].selected = true;
                }
            }
            addr.value = w_arg[2] + w_arg[3];
            return true;
        }
    }else{
        //該当する郵便番号がない場合
        w_arg = returnResult[0].split("#");
        if(w_arg[0] == "sippai"){
            alert("存在しない郵便番号です。");
            return false;
        }

        //複数ある場合
        $(document).ready(
            function() {
                if($('#window').css('display') == 'none') {
                    $('#windowOpen').TransferTo( {
                        to:'window',
                        className:'transferer2',
                        duration: 400,
                        complete: function(){
                            $('#window').show();
                        }
                    } );
                }
                return false;
            }
        );
        //郵便番号を振る
        inner_HTML  = "";
        w_arg = "";
        for(var i=0;i<returnResult.length;i++){
            //取得した場合
            if(i == 0){
                w_arg = returnResult[i].split("#");
                inner_HTML += '<a href="#" onClick="_setPost('+w_arg[1]+',\''+w_arg[2]+'\',\''+w_arg[3]+'\',1);">'+ w_arg[4] + w_arg[2] + w_arg[3] + '</a><br>';
            }else{
                if(returnResult[i] != "" && i != 0){
                    w_arg = returnResult[i].split("#");
                    inner_HTML += '<a href="#" onClick="_setPost('+w_arg[0]+',\''+w_arg[1]+'\',\''+w_arg[2]+'\',2);">'+ w_arg[3] + w_arg[1] + w_arg[2] + '</a><br>';
                }
            }
        }
        document.getElementById("resultPost").innerHTML = inner_HTML;
    }
}

function _setPost(arg1,arg2,arg3,kbn){

    var todou   = def_todoufuken;

    for(var i=0;i<todou.options.length;i++){
        if(arg1 == todou.options[i].value){
            todou.options[i].selected = true;
        }
    }
    def_address.value = arg2 + arg3;

    //widowを閉じる
    $(document).ready(
        function() {
            $('#window').TransferTo(
            {
                to:'windowOpen',
                className:'transferer2',
                duration: 400
            }
            ).hide();
        }
    );
}

    //widowを閉じる
    $(document).ready(
    	function()
    	{
    		$('#windowClose').bind(
    			'click',
    			function()
    			{
    				$('#window').TransferTo(
    					{
    						to:'windowOpen',
    						className:'transferer2',
    						duration: 400
    					}
    				).hide();
    			}
    		);

		$('#window').Resizable(
			{
				minWidth: 200,
				minHeight: 60,
				maxWidth: 700,
				maxHeight: 400,
				dragHandle: '#windowTop',
				handlers: {
					se: '#windowResize'
				},
				onResize : function(size, position) {
					$('#windowBottom, #windowBottomContent').css('height', size.height-33 + 'px');
					var windowContentEl = $('#windowContent').css('width', size.width - 25 + 'px');
					if (!document.getElementById('window').isMinimized) {
						windowContentEl.css('height', size.height - 48 + 'px');
					}
				}
			}
		);


    	}
    );



//*********************************************************
//  Style
//*********************************************************
document.write('<style>');
document.write('#window');
document.write('{');
document.write('	position: absolute;');
//document.write('	left: 200px;');
//document.write('	top: 100px;');
document.write('	left: '+((document.body.clientWidth - 400) / 2)+'px;');
document.write('	top: '+((document.body.clientHeight - 300) / 2)+'px;');
document.write('	width: 400px;');
document.write('	height: 300px;');
document.write('	overflow: hidden;');
document.write('	display: none;');
document.write('}');
document.write('#windowTop');
document.write('{');
document.write('	height: 30px;');
document.write('	overflow: 30px;');
document.write('	background-color: '+def_bgcolor+';');
document.write('	background-position: right top;');
document.write('	background-repeat: no-repeat;');
document.write('	position: relative;');
document.write('	overflow: hidden;');
document.write('	cursor: move;');
document.write('}');
document.write('#windowTopContent');
document.write('{');
document.write('	margin-right: 5px;');
document.write('	background-color: '+def_bgcolor+';');
document.write('	background-position:left top;');
document.write('	background-repeat: no-repeat;');
document.write('	overflow: hidden;');
document.write('	height: 30px;');
document.write('	line-height: 30px;');
document.write('	text-indent: 10px;');
document.write('	font-family:Arial, Helvetica, sans-serif;');
document.write('	font-weight: bold;');
document.write('	font-size: 14px;');
document.write('	color: #FFFFFF;');
document.write('}');
document.write('#windowMin');
document.write('{');
document.write('	position: absolute;');
document.write('	right: 25px;');
document.write('	top: 10px;');
document.write('	cursor: pointer;');
document.write('}');
document.write('#windowMax');
document.write('{');
document.write('	position: absolute;');
document.write('	right: 25px;');
document.write('	top: 10px;');
document.write('	cursor: pointer;');
document.write('	display: none;');
document.write('}');
document.write('#windowClose');
document.write('{');
document.write('	position: absolute;');
document.write('	right: 10px;');
document.write('	top: 10px;');
document.write('	cursor: pointer;');
document.write('}');
document.write('#windowBottom');
document.write('{');
document.write('	position: relative;');
document.write('	height: 270px;');
document.write('	background-color: '+def_bgcolor+';');
document.write('	background-position: right bottom;');
document.write('	background-repeat: no-repeat;');
document.write('}');
document.write('#windowBottomContent');
document.write('{');
document.write('	position: relative;');
document.write('	height: 270px;');
document.write('	/* background-image: url(../../lib/ajax/images/window_bottom_start.png); */');
document.write('	background-color: '+def_bgcolor+';');
document.write('	background-position: left bottom;');
document.write('	background-repeat: no-repeat;');
document.write('	margin-right: 13px;');
document.write('}');
document.write('#windowResize');
document.write('{');
document.write('	position: absolute;');
document.write('	right: 3px;');
document.write('	bottom: 5px;');
document.write('	cursor: se-resize;');
document.write('}');
document.write('#windowContent');
document.write('{');
document.write('	position:absolute;');
document.write('	top: 30px;');
document.write('	left: 10px;');
document.write('	width: auto;');
document.write('	height: auto;');
document.write('	overflow: auto;');
document.write('	margin-right: 10px;');
document.write('	border: 1px solid '+def_bgcolor+';');
document.write('	height: 255px;');
document.write('	width: 375px;');
document.write('	font-family:Arial, Helvetica, sans-serif;');
document.write('	font-size: 11px;');
document.write('	background-color: #fff;');
document.write('}');
document.write('#windowContent *');
document.write('{');
document.write('	margin: 10px;');
document.write('}');
document.write('.transferer2');
document.write('{');
document.write('	border: 1px solid '+def_bgcolor+';');
document.write('	background-color: '+def_bgcolor+';');
document.write('	filter:alpha(opacity=30);');
document.write('	-moz-opacity: 0.3;');
document.write('	opacity: 0.3;');
document.write('}');
document.write('</style>');


document.write('<div id="window">');
document.write('    <div id="windowTop">');
document.write('    <div id="windowTopContent">郵便番号検索</div>');
document.write('    <img src="'+base_path+'/images/window_close.jpg" id="windowClose" />');
document.write('    </div>');
document.write('    <div id="windowBottom"><div id="windowBottomContent">&nbsp;</div></div>');
document.write('    <div id="windowContent">');
document.write('    <div id="resultPost"></div>');
document.write('    </div>');
document.write('</div>');

