var date_chk_new_str;

function isInteger( x ) {
    //整数を判定
    if(x.match(/^-?[0-9]+$/)){
        return true;
    }else{
        return false;
    }
}

function isPlusInteger( x ) {
    //整数を判定
    if(x.match(/^[0-9]+$/)){
        return true;
    }else{
        return false;
    }
}

function isPlusFloat( x ) {
    // 符号無し小数を判定
    if(x.match(/^[0-9]+\.[0-9]+$/)){
        return true;
    }else{
        return isPlusInteger(x);
    }
}

function _numberFormat(str){
    if(str ==''||str == null ){
        return 0;
    }else{
        var num = new String(str).replace(/,/g, "");
        while(num != (num = num.replace(/^(-?\d+)(\d{3})/, "$1,$2")));
        return num;
    }
}

//2017/11/23 Add -------- Start --------
function getTodayYmd(){
    var date = new Date();
    var year = date.getYear();
    var year4 = (year < 2000) ? year+1900 : year;
    var month = date.getMonth() + 1;
    var date = date.getDate();
    if (month < 10) {
        month = "0" + month;
    }
    if (date < 10) {
        date = "0" + date;
    }
    var strDate = year4 + "/" + month + "/" + date;
    return strDate;
}
//2017/11/23 Add -------- End --------

//function _dateChk(ymd){
//2017/11/23 Mod ------- Before -------
function _dateChk(ymd){
    _dateChkFuturePast(ymd,false,false)
}
function _dateChkFuturePast(ymd,future,past){
//2017/11/23 Mod ------- After -------
    date_chk_new_str = ymd;
    if(ymd==''){
        return true;
    }

    var ok_flg = false;

    var arrDate = ymd.split("/");
    if(arrDate.length == 3) {
        if(arrDate[0].length == 1){
            arrDate[0] = '200' + arrDate[0];
        }else if(arrDate[0].length == 2){
            arrDate[0] = '20' + arrDate[0];
        }
        var date = new Date(arrDate[0] , arrDate[1] - 1 ,arrDate[2]);
        if(date.getFullYear() == arrDate[0] &&
          (date.getMonth() == arrDate[1] - 1) &&
           date.getDate() == arrDate[2]) {
            var mm = date.getMonth()+1;
            if(mm < 10) mm = '0' + mm;
            var dd = date.getDate();
            if(dd < 10) dd = '0' + dd;
            date_chk_new_str = date.getFullYear() + '/' + mm + '/' + dd;
            ok_flg = true;
        }else{
            alert('日付が不正です。');
            ok_flg = false;
        }
    }else if(arrDate.length == 2) {
        var w_now = new Date();
        var now_yyyy = w_now.getFullYear(); // 年
        var date = new Date(now_yyyy , arrDate[0]-1 ,arrDate[1]);
        if( (date.getMonth() == arrDate[0]-1) && date.getDate() == arrDate[1]) {
            var mm = date.getMonth()+1;
            if(mm < 10) mm = '0' + mm;
            var dd = date.getDate();
            if(dd < 10) dd = '0' + dd;
            date_chk_new_str = now_yyyy + '/' + mm + '/' + dd;
            ok_flg = true;
        }else{
            alert('日付が不正です。');
            ok_flg = false;
        }
    }else if(arrDate.length == 1) {
        if(!isNaN(arrDate[0])){
            var wNum = parseInt(arrDate[0]);
            var dt = new Date();
            dt.setDate(dt.getDate() + wNum);
            var now_yyyy = dt.getFullYear(); // 年
            var mm = dt.getMonth()+1;
            if(mm < 10) mm = '0' + mm;
            var dd = dt.getDate();
            if(dd < 10) dd = '0' + dd;
            date_chk_new_str = now_yyyy + '/' + mm + '/' + dd;
            ok_flg = true;
        }else{
            alert('日付が不正です。');
            ok_flg = false;
        }

    }else{
        alert('日付が不正です。');
        ok_flg = false;
    }

    //2017/11/23 Add -------- Start -------
    var today = getTodayYmd();
    if(ok_flg && future){
        //未来不可チェック
        if(date_chk_new_str > today){
            alert('未来の日付は指定できません。');
            ok_flg = false;
        }
    }
    if(ok_flg && past){
        //未来不可チェック
        if(date_chk_new_str < today){
            alert('過去の日付は指定できません。');
            ok_flg = false;
        }
    }
    //2017/11/23 Add -------- End -------

    return ok_flg;    
}

//2017/11/23 Mod -------------- Before ----------
// function _dateChkFormat(obj){
//     ymd = obj.val();
//
//     var ok_flg = _dateChk(ymd);
//
//     if(ok_flg==false){
//         obj.focus();
//     }else{
//         obj.val(date_chk_new_str);
//     }
// }
//2017/11/23 Mod -------------- Before ----------
function _dateChkFormat(obj,future,past){
    ymd = obj.val();

    var ok_flg = _dateChkFuturePast(ymd,future,past);

    if(ok_flg==false){
        obj.focus();
    }else{
        obj.val(date_chk_new_str);
    }
}
//2017/11/23 Mod -------------- End ----------

jQuery(function($) {

    $("nav .menuSub").hover(
      function () {
        $(this).children("ul").fadeIn();
      },
      function () {
        $(this).children("ul").fadeOut();
      }
    );



    //リンク画像の透過率変更
    var postfix = '_on';
    $('a.imgover img').not('[src*="'+ postfix +'."]').each(function() {
        var img = $(this);
        var src = img.attr('src');
        var src_on = src.substr(0, src.lastIndexOf('.')) + postfix + src.substring(src.lastIndexOf('.'));
        $('<img>').attr('src', src_on);
        img.hover(function() { img.attr('src', src_on); }, function() {img.attr('src', src); });
    });
    $('a.over img').hover(function(){
        $(this).fadeTo(150,0.7);
    }, function(){
        $(this).fadeTo(150,1);
    });
    $('.anchor').click(function(){
        $('html,body').animate({ scrollTop: $($(this).attr("href")).offset().top });
        return false;
    });
    $("#pagetop").hide();
    
    $(function () {
        $(window).scroll(function () {
            if($(this).scrollTop() > 150) {
                $('#pagetop').fadeIn();
            } else {
                $('#pagetop').fadeOut();
            }
        });
    });

    $('.pagetop a').click(function(){
        $('html,body').animate({ scrollTop: '0px' });
        return false;
    });
    /*$("#tab li").click(function() {
        var num = $("#tab li").index(this);
        $(".comBlock").addClass('disN');
        $(".comBlock").eq(num).removeClass('disN');
        $("#tab li").removeClass('open');
        $(this).addClass('open')
    });*/

    $('textarea,input[type="text"]').map(function(index, el){
        var maxlength = $(this).attr('chk_maxlength');
        if(maxlength){
            var buff = $(this).val();
            buff = buff.replace(/^[\s\r\n　]+|[\s\r\n　]+$/g,'');
            buff = buff.replace(/\r\n/g, '\n');
            buff = buff.replace(/\r/g, '\n');
            buff = buff.replace(/\n/g, '##');
            var nokori = parseInt(maxlength) - buff.length;
            $(this).nextAll('span:first').text(nokori);
            if(nokori < 0){
                $(this).nextAll('span:first').css('color','red');
            }else{
                $(this).nextAll('span:first').css('color','black');
            }
            $(this).on('input',function(){
                var buff2 = $(this).val();
                buff2 = buff2.replace(/^[\s\r\n　]+|[\s\r\n　]+$/g,'');
                buff2 = buff2.replace(/\r\n/g, '\n');
                buff2 = buff2.replace(/\r/g, '\n');
                buff2 = buff2.replace(/\n/g, '##');
                var nokori = parseInt(maxlength) - buff2.length;
                $(this).nextAll('span:first').text(nokori);
                if(nokori < 0){
                    $(this).nextAll('span:first').css('color','red');
                }else{
                    $(this).nextAll('span:first').css('color','black');
                }
            });
        }
    });

});

jQuery(document).ready(function() {

    // ime-modeが使えるか
    var supportIMEMode = ('ime-mode' in document.body.style);

    // 非ASCII
    var noSbcRegex = /[^\x00-\x7E]+/g;

    // 1バイト文字専用フィールド
    jQuery('.ime_off')
    .on('keydown blur paste', function(e) { 

        // ime-modeが使えるならスキップ
        if (e.type == 'keydown' || e.type == 'blur')
            if (supportIMEMode) return;

        // 2バイト文字が入力されたら削除
        var target = jQuery(this);
        if(!target.val().match(noSbcRegex)) return;
        window.setTimeout( function() {
          target.val( target.val().replace(noSbcRegex, '') );
        }, 1);        

    });

    // カレンダー初期化する.
    if($('.datepicker').length > 0){
        $('.datepicker').datepicker();
    }

    //カレンダーアイコンクリックイベント
    if($('.calBtn').length > 0){
        $('.calBtn').on('click',function(){
            var w_id = $(this).prev('input').attr('id');
            $('#'+w_id).datepicker('show');
        });
    }

    if($('.datepicker').length > 0){
        $('.datepicker').on('change',function(){
            //2017/11/23 Mod ---- Before ----
            //_dateChkFormat($(this));
            //2017/11/23 Mod ---- After ----
            var notFuture = false;
            var notPast = false;
            if($(this).hasClass('notFuture')){
                notFuture = true;
            }
            if($(this).hasClass('notPast')){
                notPast = true;
            }
            _dateChkFormat($(this),notFuture,notPast);
            //2017/11/23 Mod ---- End ----
        })
    }

    if($('.dateChkFormat').length > 0){
        $('.dateChkFormat').on('change',function(){
            //2017/11/23 Mod ---- Before ----
            //_dateChkFormat($(this));
            //2017/11/23 Mod ---- After ----
            var notFuture = false;
            var notPast = false;
            if($(this).hasClass('notFuture')){
                notFuture = true;
            }
            if($(this).hasClass('notPast')){
                notPast = true;
            }
            _dateChkFormat($(this),notFuture,notPast);
            //2017/11/23 Mod ---- End ----
        })
    }


});

var escapeHtml = (function (String) {
  var escapeMap = {
    '&': '&amp;',
    "'": '&#x27;',
    '`': '&#x60;',
    '"': '&quot;',
    '<': '&lt;',
    '>': '&gt;'
  };
  var escapeReg = '[';
  var reg;
  for (var p in escapeMap) {
    if (escapeMap.hasOwnProperty(p)) {
      escapeReg += p;
    }
  }
  escapeReg += ']';
  reg = new RegExp(escapeReg, 'g');
 
  return function escapeHtml (str) {
    str = (str === null || str === undefined) ? '' : '' + str;
    return str.replace(reg, function (match) {
      return escapeMap[match];
    });
  };
}(String));

function _clog(obj){
    console.log(obj);
}

/*
 *  EqualHeight.js - v1.0.0
 *  https://github.com/JorenVanHee/EqualHeight.js
 *
 *  Made by Joren Van Hee
 *  Under MIT License
 */
!function(a,b){function c(b,c){this.elements=b,this.options=a.extend({},e,c),this._defaults=e,this._name=d,this.active=!1,this.init()}var d="equalHeight",e={wait:!1,responsive:!0};c.prototype={init:function(){this.options.wait||this.start(),this.options.responsive&&a(b).on("resize",a.proxy(this.onWindowResize,this))},magic:function(){var b=0;this.reset();for(var c=0;c<this.elements.length;c++){var d=a(this.elements[c]).height();b=d>b?d:b}for(var e=0;e<this.elements.length;e++){var f=a(this.elements[e]);"table-cell"===f.css("display")?f.css("height",b):f.css("min-height",b)}},reset:function(){this.elements.css("min-height",""),this.elements.css("height","")},start:function(){this.active=!0,this.magic()},stop:function(){this.active=!1,this.reset()},onWindowResize:function(){this.active&&this.magic()}},a.fn[d]=function(a){return new c(this,a)}}(jQuery,window,document);