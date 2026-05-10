var post_fld_id;
var todoufuken_fld_id;
var addr_fld_id;

//*******************************************************************************************
// （必  須）_post_id       : 郵便番号フィールドのID
// （省略可）_todoufuken_id : 都道府県プルダウンフィールドのID（都道府県プルダウンないなら''を渡す）
// （必  須）_addr_id       : 住所フィールドのID（都道府県プルダウンなければここに都道府県名も入ります）
//
//*******************************************************************************************
function postSearchCGIRescueVer( _cgi_url, _post_id, _todoufuken_id, _addr_id ){
    document.PostData.set.value = _cgi_url;
    post_fld_id       = _post_id;
    todoufuken_fld_id = _todoufuken_id;
    addr_fld_id       = _addr_id;

    subWindow = window.open("","pSearch","status=no,resizable=yes,scrollbars=yes,menubar=no,width=600,height=600");
    subWindow.document.write('<body>郵便番号検索サービスに接続しています。<br>');
    subWindow.document.write('しばらくしても画面が変わらない場合は手入力してください。</body>');
    var inp_post = document.getElementById(post_fld_id);
    document.PostData.keyword.value = inp_post.value;
    document.PostData.submit();
}
function setPostcode(arg_post,arg_addr){
    var inp_post = document.getElementById(post_fld_id);
    var inp_addr = document.getElementById(addr_fld_id);

    inp_post.value = arg_post;
    if(todoufuken_fld_id==''){
        //都道府県プルダウン無い場合
        inp_addr.value = arg_addr;
    }else{
        //都道府県プルダウン有る場合
        var inp_todoufuken = document.getElementById(todoufuken_fld_id);
        for(var i=0 ; i<inp_todoufuken.options.length ; i++){
            var now_pref = inp_todoufuken.options[i].innerText;
            if( now_pref.length <= arg_addr.length){
                if( now_pref == arg_addr.substring(0,now_pref.length) ){
                    inp_todoufuken.selectedIndex = i;
                    inp_addr.value = arg_addr.substring(now_pref.length);
                }
            }
        }
    }

}

document.writeln('<form method="post" name="PostData" target="pSearch" action="http://www.rescue.ne.jp/zip/gateway.cgi">');
document.writeln('<input name="set" value="" type="hidden">');
document.writeln('<input name="zip_style" value="0" type="hidden">');
document.writeln('<input name="addr_style" value="0" type="hidden">');
document.writeln('<INPUT TYPE="hidden" NAME="mode" VALUE="search">');
document.writeln('<INPUT TYPE="hidden" NAME="start" VALUE="0">');
document.writeln('<INPUT TYPE="hidden" NAME="end" VALUE="400">');
document.writeln('<INPUT TYPE="hidden" NAME="keyword" VALUE="">');
document.writeln('</form>');

