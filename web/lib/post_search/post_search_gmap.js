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


var fld_post_code = '';
var fld_todoufuken_id = '';
var fld_todoufuken_type = '';
var fld_address1 = '';
var fld_address2 = '';

function _postSearchGMapApi(arg_post_code,arg_todoufuken_id,arg_todoufuken_type,arg_address1,arg_address2){
    fld_post_code = arg_post_code;
    fld_todoufuken_id = arg_todoufuken_id;
    fld_todoufuken_type = arg_todoufuken_type;
    fld_address1 = arg_address1;
    fld_address2 = arg_address2;

    var def_post = document.getElementById(fld_post_code);
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
        //var url = '../lib/post_search/post_search_gmap.php';
        var url = base_path + '/post_search_gmap.php';
        var param = 'post_no='+ re_def_post;
        _AjaxPost(url,param,'_postSearchReturn()');
    }

}

function _postSearchReturn(){
    //返ってきた値
    var ret = ajax_return_value;
    var returnResult = ret.split("_");
    //returnResult[0] 都道府県ID
    //returnResult[1] 都道府県名
    //returnResult[2] 市区群
    //returnResult[3] 町名

    if(returnResult){
        if(fld_todoufuken_type=='select'){
            //都道府県SELECT設定
            var emp_idx = -1;
            var find_todo = false;
            var todou = document.getElementById(fld_todoufuken_id);
            for(var i=0;i<todou.options.length;i++){
                if(todou.options[i].value==''){
                    emp_idx = i;
                }
                if(returnResult[0] == todou.options[i].value){
                    todou.options[i].selected = true;
                    find_todo = true;
                    break;
                }
            }
            if(find_todo==false && emp_idx != -1){
                todou.options[emp_idx].selected = true;
            }
        }else{
            //都道府県TEXT設定
            document.getElementById(fld_todoufuken_id).value = returnResult[1];
        }

        if(fld_address2!=""){
            //市区群TEXT設定
            document.getElementById(fld_address1).value = returnResult[2];
            //町名TEXT設定
            document.getElementById(fld_address2).value = returnResult[3];
        }else{
            //市区群町名TEXT設定
            document.getElementById(fld_address1).value = returnResult[2]+returnResult[3];
        }

    }
}
