<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>展示会来場者管理システム-{{ $contents_title }}</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="css/style.css?{!! $rand !!}">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" />

  <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700,200" rel="stylesheet" />
  <link href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" rel="stylesheet">
  <!-- CSS Files -->
  <link href="css/bootstrap.min.css" rel="stylesheet" />
  <link href="css/paper-dashboard.css?v=2.0.1" rel="stylesheet" />
  <!-- CSS Just for demo purpose, don't include it in your project -->
  <!-- <link href="demo.css" rel="stylesheet" /> -->
  <link type="text/css" href="./js/jquery-ui/jquery-ui.css" rel="stylesheet">

  <script type="text/javascript" src="js/jquery-3.2.1.min.js"></script>
  <script type="text/javascript" src="js/jquery-ui/jquery-ui.js"></script>
  <script type="text/javascript" src="js/jquery-ui/datepicker-ja.js"></script>
  <script type="text/javascript" src="js/common.js"></script>
<script type="text/javascript" src="../lib/ajax/ajax.js"></script>
  <script type="text/javascript" src="js/bootstrap.min.js"></script>

  @if($success_msg != "")
    <script language="JavaScript">
    $(window).on('load', function() {
        var html = '';
        html += '<div ';
        html += 'style="';
        html += 'position:absolute;top:200px;';
        html += 'left:'+((document.body.clientWidth - 300) / 2)+'px;';
        html += 'display:block;';
        html += '" ';
        html += 'id="id_success_msg">';
        html += '';
        html += '<table border="1" width="300">';
        html += '<tr>';
        html += '<td style="';
        html += 'font-weight:bold;';
        html += 'width:300px;';
        html += 'height:150px;';
        html += 'text-align:center;';
        html += 'vertical-align:middle;';
        html += 'font-size:20px;';
        html += 'color:#FFFFFF;';
        html += 'background-color:#0A090B;';
        html += 'filter:alpha(opacity=70)';
        html += '">';
        html += '{!! $success_msg !!}';
        html += '</td>';
        html += '</tr>';
        html += '</table>';
        html += '</div>';
        $('body').append(html);
        var tid=setTimeout("_successMsgHide()",2000);
    });
    function _successMsgHide(){
        document.getElementById('id_success_msg').style.display = 'none';
    }
    </script>
 @endif

@verbatim
<script>
    function sideBarClic(){
      if(event.altKey){
        $('.sidebar').hide();
      }
    }
</script>
@endverbatim
</head>

@if($syoutai_page == '1')
  <!-- <body class="c2 body_syoutai"> -->
  <body class="c2" style="background: linear-gradient(#2d882d,#7da77d,#b1dab1) fixed;">
 @else 
  <body class="c2">
 @endif
<!--
<header>
<div class="inner">
<h1 id="logo"><a href="./"><img src="images/logo.png" alt="日本アクセス"></a></h1>
</div>
</header>
-->
  <div class="wrapper ">
    @if($page!="admin_list_select_win")
    <form name="sel_eve_form" method="post">
    <input type="hidden" name="eve_chg" value="1">
    <div class="sidebar" onClick="sideBarClic();" data-color="white" data-active-color="danger">
      <div class="logo">
          <div class="logo-image-big">
            @if($_KANKYOU_STR=="local")
              <div style="position:absolute;z-index:100;top:6px;left:70px;text-align:center;background-color:violet;color:white;font-weight:bold;width:180px;">ローカル環境</div>
            @elseif($_KANKYOU_STR=="test")
              <div style="position:absolute;z-index:100;top:6px;left:70px;text-align:center;background-color:red;color:white;font-weight:bold;width:180px;">ステージング環境</div>
             @endif
            <a href="./"><img src="images/logo.png" alt="日本アクセス" style="width:60%;margin:5px;"></a><br>
            <select name="select_event_id" onChange="document.sel_eve_form.submit();" style="margin-top:4px;">
              <option value="">イベントを選択してください</option>
              {!! blade_html_options(['options' => $_conf_puldown_event, 'selected' => $select_event_id]) !!}
            </select>
          </div>
      </div>
      <div class="sidebar-wrapper">
        <ul class="nav">

          @if($login['admin_kyouryoku_kigyou_flg'] == 0)
          <!-- **************************** -->
          <li><p class="title-t">来場者集計</p></li>
          <!-- **************************** -->
          @if($login['admin_syuukei_etsuran_kengen'] != 1)
          <li @if($active_menu=="kaijyou_syuukei")class="active" @endif>
            <a href="?page=kaijyou_syuukei">
              <p>
                <img class="text-icon" src="images/traffic-signal.png">
                  会場全体集計
              </p>
            </a>
          </li>

          <li @if($active_menu=="kaijyou_area_syuukei")class="active" @endif>
            <a href="?page=kaijyou_area_syuukei">
              <p>
                <img class="text-icon" src="images/traffic-signal.png">
                会場エリア別集計
              </p>
            </a>
          </li>

          <li @if($active_menu=="kaijyou_syuukei_hikaku")class="active" @endif>
            <a href="?page=kaijyou_syuukei_hikaku">
              <p>
                <img class="text-icon" src="images/traffic-signal.png">
                  会場全体集計比較
              </p>
            </a>
          </li>
           @endif

          <li @if($active_menu=="area_syuukei")class="active" @endif>
            <a href="?page=area_syuukei">
              <p>
                <img class="text-icon" src="images/traffic-signal.png">
                  会場エリア集計
              </p>
            </a>
          </li>

          @if($login['admin_syuukei_etsuran_kengen'] != 1)
          <li @if($active_menu=="kaijyou_syuukei_ruikei")class="active" @endif>
            <a href="?page=kaijyou_syuukei_ruikei">
              <p>
                <img class="text-icon" src="images/traffic-signal.png">
                  会場全体集計<br>　（累計）
              </p>
            </a>
          </li>

          <li @if($active_menu=="kaijyou_syuukei_hikaku_ruikei")class="active" @endif>
            <a href="?page=kaijyou_syuukei_hikaku_ruikei">
              <p>
                <img class="text-icon" src="images/traffic-signal.png">
                  会場全体集計比較<br>　（累計）
              </p>
            </a>
          </li>

<!--          <li @if($active_menu=="area_syuukei_ruikei")class="active" @endif>-->
<!--            <a href="?page=area_syuukei_ruikei">-->
<!--              <p>-->
<!--                <img class="text-icon" src="images/traffic-signal.png">-->
<!--                  会場エリア集計<br>　（累計）-->
<!--              </p>-->
<!--            </a>-->
<!--          </li>-->
           @endif

           @endif

          <!-- **************************** -->
          <li><p class="title-t">来場者</p></li>
          <!-- **************************** -->
          <li>
          <li @if($active_menu=="user_list")class="active" @endif>
            <a href="?page=user_list">
              <p>
                <img class="text-icon" src="images/traffic-signal.png">
                  来場日時登録
              </p>
            </a>
          </li>

          @if($login['admin_kyouryoku_kigyou_flg'] == 0)
          @if($login['admin_syuukei_etsuran_kengen']=="0")
            <li @if($active_menu=="user_mail_send")class="active" @endif>
              <a href="?page=user_mail_send_list">
                <p>
                  <img class="text-icon" src="images/traffic-signal.png">
                    メール送信
                </p>
              </a>
            </li>
           @endif

          <li>
          <li @if($active_menu=="signup_list")class="active" @endif>
            <a href="?page=signup_list">
              <p>
                <img class="text-icon" src="images/traffic-signal.png">
                  ｻｲﾝｱｯﾌﾟ承認
              </p>
            </a>
          </li>
           @endif

          @if($login['admin_kyouryoku_kigyou_flg'] == 0 && $login['admin_master_kengen'] == 1)
          <li @if($active_menu=="qr_show")class="active" @endif>
            <a href="?page=qr_show">
              <p>
                <img class="text-icon" src="images/traffic-signal.png">
                QRコードPDF
              </p>
            </a>
          </li>
           @endif

          @if($login['admin_kyouryoku_kigyou_flg'] == 0)
          @if($login['admin_master_kengen'] == 1)
            <!-- **************************** -->
            <li><p class="title-t">{{ $select_event_rec['event_pulldown_name'] }}設定</p></li>
            <!-- **************************** -->
            <li @if($active_menu=="area_list")class="active" @endif>
              <a href="?page=area_list">
                <p>
                  <img class="text-icon" src="images/traffic-signal.png">
                    会場エリア設定
                </p>
              </a>
            </li>

            <li @if($active_menu=="question_edit")class="active" @endif>
              <a href="?page=question_edit">
                <p>
                  <img class="text-icon" src="images/traffic-signal.png">
                    事後アンケート
                </p>
              </a>
            </li>

           @endif
           @endif

          <!-- **************************** -->
          <li><p class="title-t">マスター管理</p></li>
          <!-- **************************** -->
          @if($login['admin_kyouryoku_kigyou_flg'] == 0)
          @if($login['admin_master_kengen'] == 1)
          <li @if($active_menu=="event_list")class="active" @endif>
            <a href="?page=event_list">
              <p>
                <img class="text-icon" src="images/traffic-signal.png">
                  イベント管理
              </p>
            </a>
          </li>
           @endif
          @if($login['admin_master_kengen'] == 1)
          <li @if($active_menu=="company_list")class="active" @endif>
            <a href="?page=company_list">
              <p>
                <img class="text-icon" src="images/traffic-signal.png">
                企業管理
              </p>
            </a>
          </li>
           @endif
          <li @if($active_menu=="company_request_list")class="active" @endif>
            <a href="?page=company_request_list">
              <p>
                <img class="text-icon" src="images/traffic-signal.png">
                企業マスタ申請
              </p>
            </a>
          </li>
           @endif
          <li @if($active_menu=="syoutai_list")class="active" @endif>
            <a href="?page=syoutai_list">
              <p>
                <img class="text-icon" src="images/traffic-signal.png">
                  来場者マスタ
              </p>
            </a>
          </li>
          @if($login['admin_kyouryoku_kigyou_flg'] == 0)
          @if($login['admin_master_kengen'] == 1)
          <li @if($active_menu=="admin_list")class="active" @endif>
            <a href="?page=admin_list">
              <p>
                <img class="text-icon" src="images/traffic-signal.png">
                  担当者管理
              </p>
            </a>
          </li>
          <li @if($active_menu=="admin_ikkatsu_touroku")class="active" @endif>
            <a href="?page=admin_ikkatsu_touroku">
              <p>
                <img class="text-icon" src="images/traffic-signal.png">
                  担当者一括登録
              </p>
            </a>
          </li>
          <li @if($active_menu=="syozoku_group_list")class="active" @endif>
            <a href="?page=syozoku_group_list">
              <p>
                <img class="text-icon" src="images/traffic-signal.png">
                閲覧部署グループ管理
              </p>
            </a>
          </li>
          <li @if($active_menu=="syozoku_list")class="active" @endif>
            <a href="?page=syozoku_list">
              <p>
                <img class="text-icon" src="images/traffic-signal.png">
                  支店・部署管理
              </p>
            </a>
          </li>
          <li @if($active_menu=="tantou_area_list")class="active" @endif>
            <a href="?page=tantou_area_list">
              <p>
                <img class="text-icon" src="images/traffic-signal.png">
                  担当エリア管理
              </p>
            </a>
          </li>
          <li @if($active_menu=="mail_template_list")class="active" @endif>
            <a href="?page=mail_template_list">
              <p>
                <img class="text-icon" src="images/traffic-signal.png">
                  ﾒｰﾙﾃﾝﾌﾟﾚｰﾄ管理
              </p>
            </a>
          </li>
           @endif
           @endif

          <!-- **************************** -->
          <hr>
          <!-- **************************** -->
          <li @if($active_menu=="admin_pass_change")class="active" @endif>
            <a href="?page=admin_pass_change">
              <p>
                <img class="text-icon" src="images/traffic-signal.png">
                  パスワード変更
              </p>
            </a>
          </li>
          @if($login['admin_kyouryoku_kigyou_flg'] == 0)
          <li @if($active_menu=="admin_mail2_edit")class="active" @endif>
            <a href="?page=admin_mail2_edit">
              <p>
                <img class="text-icon" src="images/traffic-signal.png">
                通知先メール設定
              </p>
            </a>
          </li>
          <li>
            <a href="./manual/exhibition_manage_manual.pdf" target="_blank">
              <p>
                <img class="text-icon" src="images/traffic-signal.png">
                登録マニュアル
              </p>
            </a>
          </li>
           @endif
          <li @if($active_menu=="admin_agreement")class="active" @endif>
            <a href="?page=admin_agreement">
              <p>
                <img class="text-icon" src="images/traffic-signal.png">
                個人情報の取り扱い
              </p>
            </a>
          </li>
          <li>
            <a href="?page=login&exec=logout">
              <p>
                <img class="text-icon" src="images/traffic-signal.png">
                  ログアウト
              </p>
            </a>
          </li>
        </ul>
      </div>
    </div>
    </form>

   @endif
    <div class="main-panel" @if($page=="admin_list_select_win")style="width:100%;float:left;" @endif>
      <!-- Navbar -->
      <nav class="navbar navbar-expand-lg navbar-absolute fixed-top navbar-transparent" @if($title_bgcolor!="")style="background-color:{{ $title_bgcolor }} !important;" @endif>
            <a class="navbar-brand">{{ $contents_title }}</a>
      </nav>
      <!-- End Navbar -->

      <div class="content" style="overflow: auto;height: calc(100vh - 50px);">
        @include($contents_tpl)

        <footer>

        @if($syoutai_page == '1')
          <div id="copyright-syoutai">
         @else 
          <div id="copyright">
         @endif

        <small>Copyright (c) NIPPON ACCESS, INC. All rights reserved. (Server:{{ $server_info }})</small>
        </div>

        </footer>

      </div>
    </div>
  </div>



<!-- {{-- modal_dialog --}} -->
@verbatim
<script>
$(function(){
  var w = document.body.scrollWidth - 420;
  var h = window.innerHeight - 80;
  $('#tantousya_modal').dialog({
                      autoOpen: false
                    , closeOnEscape: false
                    , resizable: true
                    , width : w
                    , height : h
                    , position : { my: "right bottom", at: "right-20 bottom-20", of: window }
                    ,show: { effect: "clip", duration: 200 }
                    ,hide: {effect: "clip", duration: 200 }
                    ,draggable: true
                    ,modal: true
                    //,open:function(event, ui){ $(".ui-dialog-titlebar-close").hide();}
  });
  $('iframe[name=tantousya_iframe]').width( w - 20 );
  $('iframe[name=tantousya_iframe]').height( h - 55 );
});
function modalDialogOpen(url){
  $('iframe[name=tantousya_iframe]').attr({'src': url });
  $('iframe[name=tantousya_iframe]')[0].onload = function(){ $("#tantousya_modal").dialog('open') };
}
</script>
@endverbatim
<div id="tantousya_modal" style="background-color:#ffffff;overflow: auto;padding:0px 0px 0px 0px;" title="担当者選択">
  <iframe name="tantousya_iframe" src="" frameborder="0" scrolling="auto" __style="height:100%;"></iframe>
</div>
<!-- // {{-- modal_dialog --}} -->

</body>
</html>