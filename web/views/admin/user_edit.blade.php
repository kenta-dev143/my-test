@verbatim
<script>

window.onload = function() {
  @endverbatim
    // サインアップ承認フラグ(初期データ) 取得
    @if($init_data['user_syounin_flg'] != '')
      var init_syounin_flg = {{ $init_data['user_syounin_flg'] }};
     @endif

  // 未定義だったならば 1(承認済) を設定
  if ( init_syounin_flg === undefined ){
    var init_syounin_flg = 1;
  }

  // 初期データが承認済みの場合
  if ( init_syounin_flg == 1 ){
    // 未承認を使用不可にする
    $('#id_user_syounin_flg0').prop('disabled',true);
    // 承認髄をチェック済みにする
    $('#id_user_syounin_flg1').prop('checked',true);
  }
};


function _regist(_mode){
  if(_mode =='delete'){
    if(window.confirm('本当に削除してもよろしいでしょうか？')){
      document.form_edit.mode.value=_mode;
      document.form_edit.submit();
    }
  }else{
    // ▽ insert / update の流れ

    var admin_id = $('input[name="user_admin_id"]').val();
    if ( admin_id == '' ){
      alert("担当者を選択して下さい。");
      return;
    }

    // メール通知の確認
    var mypage_notice = $('#id_mypage_notice:checked').val();
    if ( mypage_notice === undefined ){
      mypage_notice = 0;
    }

    var syounin_flg = $('input[name="user_syounin_flg"]:checked').val();
    // サインアップの承認 0:未承認
    if ( syounin_flg == 0 ){
      var mail_url = $('#id_mail_url').val();
      // パスワード未設定 (_NEED_PASS_SET_)
      if ( mypage_notice == 1 && mail_url != '' ){
        alert("サインアップが未承認の場合、メール通知は選択出来ません。");
        return;
      }
    }

    // 大分類（業種）
    var bigCate = $('#user_big_cate').val();
    // WEB展示会（ガイドブック）のチェックボックスを取得
    var user_web = $('input[name="user_web"]:checked').val();
    if (user_web === undefined){
      user_web = 0;
    }

    var syoutai_yotei_time = 0;
    if ( bigCate <= 4 ){
      // 招待予定日時(配列)の値を取得
      syoutai_yotei_time = $('input[name="syoutai_yotei_time[]"]:checked').map(function(){
        return $(this).val();
      }).get();
      if ( syoutai_yotei_time.length > 0  ){
        syoutai_yotei_time = 1;
      }

    } else {
      var raijyou_yotei_time = $('input[name="raijyou_yotei_time[]"]:checked').map(function(){
        return $(this).val();
      }).get();

      if ( raijyou_yotei_time.length > 0  ){
        syoutai_yotei_time = 1;
      }
    }

    // 来場予定日時・WEB展示会の必須チェック
    if ( syoutai_yotei_time == 0 && user_web == 0 ){
      alert("来場予定日時の選択、WEB展示会（ガイドブック）のいずれかを選択して下さい。");
      return;
    } else if( syoutai_yotei_time == 0 && user_web == 1){
      // 来場予定日時の未選択の場合の確認
      if( !window.confirm('来場日時設定がされていません。来場日時が未設定の場合QRはお客様に表示されません。よろしいですか？') ){
        return;
      }
    }

    if ( mypage_notice == 1 ){
      if( !window.confirm('招待者へメールが自動送信されますがよろしいですか？') ){
        return;
      }
    }

    document.form_edit.mode.value=_mode;
    document.form_edit.submit();
  }
}

// function syozokuChange(){
//   var syozoku_id = $('select[name="admin_syozoku_id"]').val();
//   var admin_id = $('select[name="user_admin_id"]').val();
//   AJAXCall2json(
//               "{!! $_SYSTEM_ROOT_URLS !!}/ajax_php/getAdminList.php"
//               ,{'event_id':'_PASS_', 'syozoku_id': syozoku_id }
//           )
//           .done(function (json) {
//               if(!$.isEmptyObject( json )){
//                   if(json.status=='NG'){
//                       //NG
//                       alert(json.error_message);
//                   }else{
//                       //OK
//                       var data = json.data;
//                       var html = '';
//                       html += '<option value="">選択してください</option>';
//                       for (var i = 0; i < data.admin_recs.length; i++) {
//                           var syo_name = data.admin_recs[i].syozoku_name;
//                           var name = data.admin_recs[i].admin_name;
//                           var id = data.admin_recs[i].admin_id;
//                           if(admin_id==id){
//                             html += '<option value="'+id+'" selected>'+syo_name+' '+name+'</option>';
//                           }else{
//                             html += '<option value="'+id+'">'+syo_name+' '+name+'</option>';
//                           }
//                       }
//                       $('select[name="user_admin_id"]').html(html);
//                   }
//               }
//           })
//           .fail(function (result, status, errors) {
//               alert('通信エラーが発生しました。');
//           })
//   ;
// }
//
// var midCateArr = [];
// @foreach($_conf_mid_cate as $m_id => $m_nm)
// midCateArr[{!! $loop->index !!}] = { id: {!! $m_id !!}, name: '{{ $m_nm }}' };
//  @endforeach
// @verbatim
// function bigCateCjange(){
//   var bigCate = $('#user_big_cate').val();
//   if(''+bigCate == ''){
//     $('.syoutai').css('color','#cccccc');
//     $('.raijyou').css('color','#cccccc');
//     $('.syoutai_chk').prop('disabled',true);
//     $('.raijyou_chk').prop('disabled',true);
//     $('#sya_title').text('来場者');
//     $('input[name="syoutai_yotei_time[]"]').map(function(){
//       $(this).prop('checked',false);
//     });
//     $('input[name="raijyou_yotei_time[]"]').map(function(){
//       $(this).prop('checked',false);
//     });
//   }else if(bigCate <= 4){
//     //招待者
//     $('.syoutai').css('color','#000000');
//     $('.raijyou').css('color','#cccccc');
//     $('.syoutai_chk').prop('disabled',false);
//     $('.raijyou_chk').prop('disabled',true);
//     $('#sya_title').text('招待者');
//     $('input[name="raijyou_yotei_time[]"]').map(function(){
//       var id = $(this).attr('id');
//       var chk = $(this).prop('checked');
//       var idVal = id.substr(8);
//       if($('#syoutai_'+idVal).length > 0){
//         $('#syoutai_'+idVal).prop('checked',chk);
//       }else{
//         $('#syoutai_'+idVal).prop('checked',false);
//       }
//       $(this).prop('checked',false);
//     });
//   }else{
//     //来場者
//     $('.syoutai').css('color','#cccccc');
//     $('.raijyou').css('color','#000000');
//     $('.syoutai_chk').prop('disabled',true);
//     $('.raijyou_chk').prop('disabled',false);
//     $('#sya_title').text('来場者');
//     $('input[name="syoutai_yotei_time[]"]').map(function(){
//       var id = $(this).attr('id');
//       var chk = $(this).prop('checked');
//       var idVal = id.substr(8);
//       if($('#raijyou_'+idVal).length > 0){
//         $('#raijyou_'+idVal).prop('checked',chk);
//       }else{
//         $('#raijyou_'+idVal).prop('checked',false);
//       }
//       $(this).prop('checked',false);
//     });
//   }
//   var mid_cate = $('select[name="user_mid_cate"]').val();
//   var html = '';
//   html += '<option value="">選択してください</option>';
//   for (var i = 0; i < midCateArr.length; i++) {
//     var inFlg = false;
//     if(bigCate==''){
//       inFlg = true;
//     }else if(parseInt(bigCate) <= 4){
//       if(midCateArr[i].id < 100){
//         inFlg = true;
//       }
//     }else{
//       if(midCateArr[i].id >= 100){
//         inFlg = true;
//       }
//     }
//     if(inFlg){
//       if(mid_cate==midCateArr[i].id){
//         html += '<option value="'+midCateArr[i].id+'" selected>'+midCateArr[i].name+'</option>';
//       }else{
//         html += '<option value="'+midCateArr[i].id+'">'+midCateArr[i].name+'</option>';
//       }
//     }
//   }
//   $('select[name="user_mid_cate"]').html(html);
// }


function _winAdminOpen(){
  // window.open( './index.php?page=admin_list_select_win','admin_list_select_win' ,'width=1750, height=800, menubar=no, toolbar=no, scrollbars=yes' );
  modalDialogOpen( './index.php?page=admin_list_select_win' );
}

function _adminSelect(id,busyo,name){
    $('input[name="user_admin_id"]').val(id);
    $('#tantousya_lbl').html( busyo+'　'+name );
}

function _tantousyaDel(){
  $('input[name="user_admin_id"]').val('');
  $('#tantousya_lbl').text('');
}

function _mailsend(){
  if ( $("#id_nosendchk").prop('checked') ){
    $('#id_mail_url').text(url)
  } else {
    $('#id_mail_url').text('')
  }
}

function _mailcopy(){
  var text = $('#id_mail_url').val();
  copyTextToClipboard(text);
  alert("コピーしました");
}

/**
 * クリップボードコピー関数
 * 入力値をクリップボードへコピーする
 * [引数]   textVal: 入力値
 * [返却値] true: 成功　false: 失敗
 */
 function copyTextToClipboard(textVal){
  // テキストエリアを用意する
  var copyFrom = document.createElement("textarea");
  // テキストエリアへ値をセット
  copyFrom.textContent = textVal;

  // bodyタグの要素を取得
  var bodyElm = document.getElementsByTagName("body")[0];
  // 子要素にテキストエリアを配置
  bodyElm.appendChild(copyFrom);

  // テキストエリアの値を選択
  copyFrom.select();
  // コピーコマンド発行
  var retVal = document.execCommand('copy');
  // 追加テキストエリアを削除
  bodyElm.removeChild(copyFrom);
  // 処理結果を返却
  return retVal;
}

function _ClickNewPass(){
  var reset = $('input[name="new_user_pass_make"]').prop('checked');
  if (reset === true){
    $('#btn_showhide').show();
    $('#id_mypage_notice').prop('checked',true);
  } else {
    $('#btn_showhide').hide();
    $('#id_mypage_notice').prop('checked',false);
  }
}

function _syouninChange(){
  var syounin_flg = $('input[name="user_syounin_flg"]:checked').val();
  var mail_url = $('#id_mail_url').val();
  if ( mail_url == '' ){
    // パスワード設定済の場合はこのまま抜ける
    return;
  }
  if ( syounin_flg == 0 ){
    // 未承認ならメール通知しない
    $('#id_mypage_notice').prop('checked',false);
  } else {
    // 承認済みならメール通知する
    $('#id_mypage_notice').prop('checked',true);
  }
}

function webCheckChange() {
  var isCheck = document.getElementById('user_web').checked;
  if (!isCheck) {
    document.getElementById('user_web_force').checked = false;
  }
}

function webForceCheckChange() {
  var isCheck = document.getElementById('user_web_force').checked;
  if (isCheck) {
    document.getElementById('user_web').checked = true;
  }
}

</script>
@endverbatim

<div class="inner_wrap">

  @if($err_msg['0'] !='')
    <div class="errArea">
      @foreach($err_msg as $msg)
        {{ $msg }}<br>
       @endforeach
    </div><br>
  @elseif($success_msg!="")
    <div class="successArea">{!! $success_msg !!}</div>
   @endif


  <form name="form_edit" action="./?page={{ $page }}" method="post" onSubmit="return false;">
  <input type="hidden" name="exec" value="save">
  <input type="hidden" name="mode">
  <input type="hidden" name="token" value="{{ $token }}">
  <input type="hidden" name="id" value="{{ $id }}">

    <h1 class="msr_h103" id="sya_title">@if($raijyousya_kbn=="1")招待者 @else 来場者 @endif</h1>

    <div class="msr_pulldown_01">
      <p>VIP</p>
      @if($raijyousya_kbn == "1")
        　{{ $_conf_vip[$user_vip_flg] }}
       @else 
        <select name="user_vip_flg">
          <option value="0">---</option>
          {!! blade_html_options(['options' => $_conf_vip, 'selected' => $user_vip_flg]) !!}
        </select>
       @endif
    </div>

    <div class="msr_pulldown_01">
      <p>大分類（業種）</p>
      @if($raijyousya_kbn == "1")
        　{{ $_var_big_cate[$user_big_cate] }}
        <input type="hidden" name="user_big_cate" id="user_big_cate" value="{{ $user_big_cate }}">
       @else 
        <select name="user_big_cate" id="user_big_cate" style="width:240px;">
          <option value="">選択してください</option>
          {!! blade_html_options(['options' => $_var_big_cate, 'selected' => $user_big_cate]) !!}
        </select>
       @endif
    </div>

    <div class="msr_pulldown_01">
      <p>中分類（業態）</p>
      @if($raijyousya_kbn == "1")
        　{{ $_var_mid_cate[$user_mid_cate] }}
       @else 
        <select name="user_mid_cate" style="width:240px;">
          <option value="">選択してください</option>
          {!! blade_html_options(['options' => $_var_mid_cate, 'selected' => $user_mid_cate]) !!}
        </select>
       @endif
    </div>

    <div class="msr_text_01">
      <label>企業名</label>
      @if(empty($user_company_id))
       {{ $user_kigyou_name }}
       @else 
       {{ $user_company_name }}
       @endif
<!--      @if($raijyousya_kbn == "1")
        　{{ $user_kigyou_name }}
       @else 
        <input type="text" name="user_kigyou_name" value="{{ $user_kigyou_name }}" />
       @endif
    </div>

    <div class="msr_text_01">
      <label>企業名カナ</label>
      @if($raijyousya_kbn == "1")
        　{{ $user_kigyou_name_kana }}
       @else 
        <input type="text" name="user_kigyou_name_kana" value="{{ $user_kigyou_name_kana }}" />
       @endif
-->
    </div>

    <div class="msr_text_01 bg-light">
      <label>
        企業名（入力値）
      </label>
      {{ $user_kigyou_name }}
    </div>

    <div class="msr_text_01 bg-light">
      <label>
        企業名カナ（入力値）
      </label>
      {{ $user_kigyou_name_kana }}
    </div>

    <div class="msr_text_01">
      <label>部署</label>
      @if($raijyousya_kbn == "1")
        　{{ $user_busyo }}
       @else 
        <input type="text" name="user_busyo" value="{{ $user_busyo }}" />
       @endif
    </div>

    <div class="msr_text_01">
      <label>役職</label>
      @if($raijyousya_kbn == "1")
        　{{ $user_yakusyoku }}
       @else 
        <input type="text" name="user_yakusyoku" value="{{ $user_yakusyoku }}" />
       @endif
    </div>

    <div class="msr_text_01">
      <label>氏名</label>
      @if($raijyousya_kbn == "1")
        　{{ $user_name }}
       @else 
        <input type="text" name="user_name" value="{{ $user_name }}" />
       @endif
    </div>

    <div class="msr_text_01">
      <label>氏名カナ</label>
      @if($raijyousya_kbn == "1")
      　{{ $user_name_kana }}
       @else 
        <input type="text" name="user_name_kana" value="{{ $user_name_kana }}" />
       @endif
    </div>

    <div class="msr_text_01">
      <label>メールアドレス(PC)</label>
      @if($raijyousya_kbn == "1")
      　{{ $user_mail }}
       @else 
        <input type="text" name="user_mail" value="{{ $user_mail }}" />
       @endif
    </div>

    <div class="msr_text_01">
      <label>ログインID</label>
      @if($raijyousya_kbn == "1")
      　{{ $user_login_id }}
       @else 
        <input type="text" name="user_login_id" value="{{ $user_login_id }}" />
       @endif
    </div>


    <div class="msr_textarea_01">
      <label>備考</label>
      @if($raijyousya_kbn == "1")
        <textarea name="user_biko" readonly>{{ $user_biko }}</textarea>
       @else 
        <textarea name="user_biko">{{ $user_biko }}</textarea>
       @endif
    </div>

    <div class="msr_text_02">
      <label>サインアップ登録の承認</label>
      <div>
        @foreach($_conf_user_syounin_flg as $key => $flg_val)
          <label>
            <input type="radio" id="id_user_syounin_flg{!! $key !!}" name="user_syounin_flg" onchange="_syouninChange();" value="{{ $key }}" @if($user_syounin_flg==$key)checked @endif>
            {{ $flg_val }}
          </label>
         @endforeach
      </div>
    </div>
    <br>

    @if($pass_change_url == '')
      <h1 class="msr_h103">パスワード</h1>
     @else 
      <h1 class="msr_h103">メール通知の指定</h1>
     @endif
    <div class="msr_text_01">
      @if($mode=="insert")
        <input type="hidden" name="new_user_pass_make" value="1">
       @else 

        @if($pass_change_url == '')
          <!-- {{-- <label for="new_user_pass_make"><input type="checkbox" class="largechkbox" name="new_user_pass_make" onclick="_ClickNewPass();" value="1" @if($new_user_pass_make=="1")checked @endif id="new_user_pass_make">&nbsp;パスワードを未設定に戻す</label> --}} -->
          <!-- {{-- パスワードを未設定に戻す機能は廃止 --}} -->
          パスワード設定済み
         @endif
       @endif
      <div id="btn_showhide" style="{!! $NoDisplay !!}">
        <label for="id_mypage_notice" style="color:red">
          <input type="checkbox" class="largechkbox" id="id_mypage_notice" name="mypage_notice" value="1" @if($mypage_notice=="1")checked @endif/>&nbsp;マイページ設定URLをメール通知する
        </label>
        ※メール通知を希望しない方はチェックを外してください。
      </div>
      @if($pass_change_url != '')
      <div class="msr_text_01">
        ご自身でメール通知する場合は、下記よりURLをコピーしてください。
        <p>マイページ設定URL　<button type="button" class="btn btn-primary btn-round" onclick="_mailcopy();">コピー</button></p>
        <input type="text" id="id_mail_url" name="mail_url" readonly value="{{ $pass_change_url }}">
      </div>
       @endif
    </div>

    <h1 class="msr_h103">来場予定日時（{{ $select_event_rec['event_kaijyou_name'] }}）</h1>
    <div>
      <table style="width:100%;">
        <tr>
          @if($raijyousya_kbn == "1")
            <th style="width:50%;"> (招待者)</th>
           @else 
            <th style="width:50%;"> (来場者)</th>
           @endif
        </tr>
        <tr>
          @if($raijyousya_kbn == "1")
            <td style="vertical-align:top;">
              @foreach($_conf_syoutai_yotei_time as $ymd => $info)
                <p class="syoutai" style="margin-bottom:0px;">{{ $info['disp_ymd'] }}</p>
                　
                @foreach($info['his'] as $hi_info)
                  <input class="syoutai_chk largechkbox" type="checkbox" name="syoutai_yotei_time[]" value="{{ $ymd }} {{ $hi_info['hi'] }}" {{ $hi_info['checked'] }} id="syoutai_{!! $ymd !!}{{ $hi_info['hi'] }}" />
                  <label class="syoutai" for="syoutai_{!! $ymd !!}{{ $hi_info['hi'] }}">{{ $hi_info['hi'] }}&nbsp;</label>
                 @endforeach
               @endforeach
            </td>
<!--            <td>-->
<!--              <div class="col-md-4 pl-1" style="margin:10px 0 20px 20px;">-->
<!--                {!! blade_html_radios(['name' => "syoutai_yotei_time", 'options' => $_conf_syoutai_yotei_time2, 'selected' => $user_raijyou_yotei_time, 'separator' => '<br>']) !!}-->
<!--              </div>-->
<!--            </td>-->
           @else 
            <td style="vertical-align:top;">
              @foreach($_conf_raijyou_yotei_time as $ymd => $info)
                <p class="raijyou" style="margin-bottom:0px;">{{ $info['disp_ymd'] }}</p>
                　
                @foreach($info['his'] as $hi_info)
                  <input class="raijyou_chk largechkbox" type="checkbox" name="raijyou_yotei_time[]" value="{{ $ymd }} {{ $hi_info['hi'] }}" {{ $hi_info['checked'] }} id="raijyou_{!! $ymd !!}{{ $hi_info['hi'] }}" />
                  <label  class="raijyou" for="raijyou_{!! $ymd !!}{{ $hi_info['hi'] }}">{{ $hi_info['hi'] }}&nbsp;</label>
                 @endforeach
               @endforeach
              <br><br>
            </td>
           @endif

        </tr>
      </table>
    </div>

    <h1 class="msr_h103">WEB</h1>
    <div class="msr_radio_01">
      <div>
        <input type="checkbox" class="largechkbox" name="user_web" id="user_web" value="1" onchange="webCheckChange()" @if($user_web=="1")checked @endif/>
        <label for="user_web">WEB展示会（ガイドブック）招待者</label>
      </div>
      @if(($user_web_force_kengen))
      <div>
        <input type="checkbox" class="largechkbox" name="user_web_force" id="user_web_force" value="1" onchange="webForceCheckChange()" @if($user_web_force=="1")checked @endif/>
        <label for="user_web_force">WEB展示会強制招待</label>
      </div>
       @endif
    </div>

    <h1 class="msr_h103">当社担当</h1>

    <div _class="msr_pulldown_01">
      <div>
        担当者<span class="searchBtn" style="margin:10px 10px;" onClick="_winAdminOpen();"></span>
        <span id="tantousya_lbl">@if($admin_name!=""){{ $syozoku_name }}　{{ $admin_name }} @endif</span>
        <input type="hidden" name="user_admin_id" value="{{ $user_admin_id }}">
      </div>

      @if(($init_data['user_big_cate'] < 5))
      <div>
        <label style="color:red">
          <input type="checkbox" class="largechkbox" name="reception_mail_flg" value="1" @if($reception_mail_flg=="1")checked @endif/>
          当日の会場受付完了をメール通知する
          </label>
        <br>
        ※メール通知を希望しない方はチェックを外してください。
      </div>
       @endif

      <br>
      <div class="msr_text_01">
        <label>追加担当者アドレス１</label>
        <input type="text" name="user_admin_mail_1" value="{{ $user_admin_mail_1 }}" />
      </div>

      <div class="msr_text_01">
        <label>追加担当者アドレス２</label>
        <input type="text" name="user_admin_mail_2" value="{{ $user_admin_mail_2 }}" />
      </div>

      <div class="msr_text_01">
        <label>追加担当者アドレス３</label>
        <input type="text" name="user_admin_mail_3" value="{{ $user_admin_mail_3 }}" />
      </div>

    </div>
    <br>

    <h1 class="msr_h103">代理登録者</h1>

    <div class="msr_text_01">
        <label>メールアドレス</label>
        <input type="text" name="user_agent_mail" value="{{ $user_agent_mail }}" />
    </div>
    <br>

    <h1 class="msr_h103">タグ</h1>

    <div class="msr_text_01">
      <label>タグ文字列（来場日時登録）</label>
      <input type="text" name="user_tag" value="{{ $user_tag }}" />
    </div>
    <br>

    <h1 class="msr_h103">ログイン履歴</h1>

    <div class="msr_text_01">
      <table class="table">
        <thead class=" text-primary" style="display: block;">
          <th style="width:250px;">ログイン日時</th>
          <th style="width:250px;">ログイン先</th>
        </thead>
        <tbody style="overflow-x: hidden;overflow-y: scroll;height: 100px;display: block;">
        @foreach($log_recs as $rec)
          <tr>
            <td style="width:250px;">{{ $rec['ullog_insert_date'] }}</td>
            <td style="width:250px;">{{ $rec['ullog_kbn'] }}</td>
          </tr>
         @endforeach
        </tbody>
      </table>
    </div>
    <br>

    <h1 class="msr_h103">会場エリア入退場情報</h1>

    <div class="msr_text_01">
      <table class="table">
        <thead class=" text-primary" style="display: block;">
          <th style="width:160px;">エリア名</th>
          <th style="width:150px;">入場時間</th>
          <th style="width:150px;">退場時間</th>
        </thead>
        <tbody style="overflow-x: hidden;overflow-y: scroll;height: 300px;display: block;">
        @foreach($area_inout_recs as $rec)
          <tr>
            <td style="width:160px;">{{ $rec['area_name'] }}</td>
            <td style="width:150px;">{{ $rec['ainout_time_in'] }}</td>
            <td style="width:150px;">{{ $rec['ainout_time_out'] }}</td>
          </tr>
         @endforeach
        </tbody>
      </table>
    </div>
    <br>

    <p class="msr_btn15">
      @if($mode=='insert')
        <a href="javascript:_regist('insert');void(0);">上記内容で新規登録する</a>
       @else 
        <a href="javascript:_regist('update');void(0);">上記内容で更新する</a>
        &nbsp;&nbsp;&nbsp;
        <a href="javascript:_regist('delete');void(0);">削除する</a>
       @endif
      &nbsp;&nbsp;&nbsp;
      @if($from_page=="user_mail_send")
        <a href="javascript:location.href='?page=user_mail_send&sess_no_init=1';void(0);">一覧へ戻る</a>
      @elseif($from_page=="signup_list")
        <a href="javascript:location.href='?page=signup_list&sess_no_init=1';void(0);">一覧へ戻る</a>
       @else 
        <a href="javascript:location.href='?page=user_list&sess_no_init=1';void(0);">一覧へ戻る</a>
       @endif
    </p>
  </form>
</div>