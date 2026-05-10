var _search_success;
var _search_todoufuken_no;
var _search_todoufuken;
var _search_sikugun;
var _search_tyou;
var _open_mode;
var _call_fnc_name;

function _postSearchResultSet(_a_success,_a_tno,_a_tnm,_a_shi,_a_tyo){
    _search_success = _a_success;
    _search_todoufuken_no = _a_tno;
    _search_todoufuken = _a_tnm;
    _search_sikugun = _a_shi;
    _search_tyou = _a_tyo;
    if(_open_mode=='pop'){

        //_postSearch();
        eval(_call_fnc_name + "();");

        _open_mode = '';
        _search_success = -1;
        _search_todoufuken_no = '';
        _search_todoufuken = '';
        _search_sikugun = '';
        _search_tyou = '';
        _call_fnc_name = '';
    }
}

// function _postSearchRequest(){
//     var _a_url = _postSearchRequest.arguments[0];
//     var _a_postno = _postSearchRequest.arguments[1];
function _postSearchRequest(_a_url, _a_postno){

    if(_open_mode=='pop'){
        return;
    }

    _call_fnc_name = _postSearchRequest.caller.name;
    _open_mode = '';
    _search_success = -1;
    _search_todoufuken_no = '';
    _search_todoufuken = '';
    _search_sikugun = '';
    _search_tyou = '';

    if(window.showModalDialog){
        _open_mode = 'dlg';
        window.showModalDialog(
            _a_url+'?post_no='+_a_postno+'&opn=dlg&rnd='+Math.random(),
            this,
            'dialogWidth=500px; ' +
            'dialogHeight=480px; ' +
            'center=yes; ' +
            'status=no; ' +
            'scroll=yes; ' +
            'resizable=no; ' +
            'help=no; ' +
            'minimize=yes; ' +
            'maximize=no; '
        );
    }else{
        _open_mode = 'pop';
         var newWin = window.open(
             _a_url+'index.php?post_no='+_a_postno+'&opn=pop&rnd='+Math.random(),
             "jyuusyoSearch",
             "width=1,height=1,left=3000,top=3000"
         );
        //閉じるまで待つ
        //while( (!newWin.closed) && _search_success==-1){
        //    //
        //}
    }
}
