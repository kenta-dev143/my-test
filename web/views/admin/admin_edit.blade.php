<script>
function _regist(_mode){
  if(_mode =='delete'){
    if(window.confirm('本当に削除してもよろしいでしょうか？')){
      document.form_edit.mode.value=_mode;
      document.form_edit.submit();
    }
  }else{
    // メール通知の確認
    var mail_notice = $('#id_mail_notice:checked').val();
    var admin_login_kengen = $('input[name="admin_login_kengen"]:checked').val();

    if ( mail_notice === undefined ){
      mail_notice = 0;
    }

    if ( mail_notice == 1 ){
      if(admin_login_kengen=='0'){
        alert('ログイン権限がない担当者にはメール通知できません。');
        return;
      }else{
        if( !window.confirm('担当者へメールが自動送信されますがよろしいですか？') ){
          return;
        }
      }
    }

    document.form_edit.mode.value=_mode;
    document.form_edit.submit();
  }
}

function tanareaChange(){
  var tanarea_id = $('select[name="admin_tanarea_id"]').val();

  if ( tanarea_id == '' ){
    return;
  }

  AJAXCall2json(
              "{!! $_SYSTEM_ROOT_URLS !!}/ajax_php/getSyozokuList.php"
              ,{'tanarea_id': tanarea_id }
          )
          .done(function (json) {

  if(!$.isEmptyObject( json )){
    if(json.status=='NG'){
      //NG
      alert(json.error_message);
    }else{
        //OK
        var data = json.data;
        var html = '';
        html += '<option value="">選択してください</option>';
        for (var i = 0; i < data.syozoku_recs.length; i++) {
          var syo_name = data.syozoku_recs[i].syozoku_name;
          var id = data.syozoku_recs[i].syozoku_id;
          html += '<option value="'+id+'">'+syo_name+'</option>';
        }
        $('select[name="admin_syozoku_id"]').html(html);
      }
    }
  })
  .fail(function (result, status, errors) {
      alert('通信エラーが発生しました。');
  });
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
    $('#id_mail_notice').prop('checked',true);
  } else {
    $('#btn_showhide').hide();
    $('#id_mail_notice').prop('checked',false);
  }
}

function kigyouSelect(obj){
  var index = obj.selectedIndex;
  var value = obj.options[index].value;
  var name = obj.options[index].text;

  var selected = $('input[name="kyouryoku_kigyou_ids[]"][value="' + value + '"]');

  if (selected.length > 0 || value == '') {
    return;
  }

  var div_kigyou = $('#selected_kigyou');
  var add_kigyou_div = $('<div>', {text: name, class: 'pl-2'});
  var add_kigyou_input = $('<input>',
          {
            type: 'hidden',
            name: 'kyouryoku_kigyou_ids[]',
            value: value
          }
  );
  var add_delete_button = $('<button>', {text: '削除', class: 'delete_kigyou btn-danger ml-2'});
  add_delete_button.click(deleteKyouryokuKigyou);
  div_kigyou.append(add_kigyou_div);
  add_kigyou_div.append(add_kigyou_input);
  add_kigyou_div.append(add_delete_button);
}

function deleteKyouryokuKigyou() {
   $(this).parent().remove();
}

function changeKyouryokuKigyouSelectVisibility() {
   var check = $('#kyouryoku_kigyou_flg');
   if (check.prop("checked")) {
     $('#kyouryoku_kigyou_select').show();
   } else {
     $('#kyouryoku_kigyou_select').hide();
   }
}

$(function () {
  $('.delete_kigyou').on('click', deleteKyouryokuKigyou);
  changeKyouryokuKigyouSelectVisibility();
  console.log($('#kyouryoku_kigyou_flg').prop("checked"));
  $('#kyouryoku_kigyou_flg').on('change', changeKyouryokuKigyouSelectVisibility);
});
</script>


<div class="inner_wrap">
  @if($err_msg['0'] !='')
      <div class="errArea">
          @foreach($err_msg as $msg)
              {{ $msg }}<br>
           @endforeach
      </div><br>
  @elseif($success_msg!="")
      <div class="successArea">{{ $success_msg }}</div>
   @endif

  <form name="form_edit" action="./?page={{ $page }}" method="post" onSubmit="return false;">
  <input type="hidden" name="exec" value="save">
  <input type="hidden" name="mode">
  <input type="hidden" name="token" value="{{ $token }}">
  <input type="hidden" name="id" value="{{ $id }}">

    <div class="msr_text_01">
      <label>担当者名</label>
      <input type="text" name="admin_name" value="{{ $admin_name }}" />
    </div>

    <div class="msr_text_01">
      <label>ログインID(メールアドレス)<br>※兼通知先メールアドレス１</label>
      <input type="text" name="admin_mail" value="{{ $admin_mail }}" />
    </div>

    <div class="msr_text_01">
      <!-- {{-- <label>ログインパスワード@if($mode!="insert")(変更する場合指定) @endif</label> --}} -->
      <!-- {{-- <input type="text" name="admin_login_pass" /> --}} -->
      @if($mode=="insert")
        <input type="hidden" name="new_user_pass_make" value="1">
       @else 
        @if($pass_change_url == '')
          <label for="new_user_pass_make"><input type="checkbox" class="largechkbox" name="new_user_pass_make" onclick="_ClickNewPass();" value="1" @if($new_user_pass_make=="1")checked @endif id="new_user_pass_make">&nbsp;パスワードを未設定に戻す</label>
         @endif
       @endif
      <div id="btn_showhide" style="{!! $NoDisplay !!}">
        <label for="id_mail_notice" style="color:red;">
          <input type="checkbox" class="largechkbox" id="id_mail_notice" name="mail_notice" value="1" @if($mail_notice=="1")checked @endif/>&nbsp;パスワード設定URLをメール通知する
        </label>
        ※メール通知を希望しない方はチェックを外してください。
      @if($pass_change_url != '')
        <div class="msr_text_01">
          ご自身でメール通知する場合は、下記よりURLをコピーしてください。
          <p>パスワード設定URL　<button type="button" class="btn btn-primary btn-round" onclick="_mailcopy();">コピー</button></p>
          <input type="text" id="id_mail_url" name="mail_url" value="{{ $pass_change_url }}">
        </div>
       @endif
      </div>

    </div>

    <div class="msr_text_01">
      <label>担当エリア</label>
      <select name="admin_tanarea_id" style="width:460px;" onChange="tanareaChange();">
        <option value="">選択してください</option>
        {!! blade_html_options(['options' => $_conf_tanarea, 'selected' => $admin_tanarea_id]) !!}
      </select>
    </div>

    <div class="msr_text_01">
      <label>所属(支店・部署)</label>

      <!-- {{-- ****************** 所属フィルター ****************** --}} -->
      <input type="text" name="syozoku_filter" placeholder="選択肢をフィルター" onKeyup="syozokuFilter();">
      <select id="hidden_syozoku_id_select" style="display:none;">
        <option value="">選択してください</option>
        {!! blade_html_options(['options' => $_conf_syozoku, 'selected' => $search_condition['admin_syozoku_id']]) !!}
      </select>
      @verbatim
      <script>
        function syozokuFilter(){
          var filter = $('input[name="syozoku_filter"]').val();
          if(filter==''){
            $('select[name="admin_syozoku_id"]').html( $("#hidden_syozoku_id_select").html() );
          }else{
            $('select[name="admin_syozoku_id"]').html('');
            $('select[name="admin_syozoku_id"]').append($("<option>").val('').text( '選択してください' ));
            $("#hidden_syozoku_id_select option").each(function(i){
              if($(this).text().indexOf(filter) != -1){
                $('select[name="admin_syozoku_id"]').append($("<option>").val($(this).val()).text( $(this).text() ));
              }
            });
          }
        }
      </script>
      @endverbatim
      <!-- {{-- ****************** /所属フィルター ****************** --}} -->

      <select name="admin_syozoku_id" style="width:460px;">
        <option value="">選択してください</option>
        {!! blade_html_options(['options' => $_conf_syozoku, 'selected' => $admin_syozoku_id]) !!}
      </select>

    </div>

    <div class="msr_pulldown_01">
      <p>来場者データでの大分類</p>
      　AC社員
    </div>

    <div class="msr_pulldown_01">
      <p>中分類（担当者管理）</p>
      <select name="admin_mid_cate" style="width:240px;">
        <option value=""></option>
        {!! blade_html_options(['options' => $_ac_mid_cate, 'selected' => $admin_mid_cate]) !!}
      </select>
    </div>

    <div class="msr_text_01">
      <label>役職</label>
      <input type="text" name="admin_yakusyoku" value="{{ $admin_yakusyoku }}" />
    </div>

    <div class="msr_text_01">
      <label>企業</label>
      <div id="company_select" class="bg-light p-2">
        <div class="msr_text_01">
          <!-- {{-- ****************** 企業フィルター ****************** --}} -->
          <input type="text" name="admin_company_filter" placeholder="選択肢をフィルター" onKeyup="adminCompanyFilter();"><br>
          <select id="hidden_admin_company_id_select" style="display:none;">
            <option value="">選択してください</option>
            {!! blade_html_options(['options' => $_conf_company, 'selected' => $search_condition['admin_company_id']]) !!}
          </select>
          @verbatim
          <script>
            function adminCompanyFilter(){
              var filter = $('input[name="admin_company_filter"]').val();
              if(filter==''){
                $('select[name="admin_company_id"]').html( $("#hidden_admin_company_id_select").html() );
              }else{
                $('select[name="admin_company_id"]').html('');
                $('select[name="admin_company_id"]').append($("<option>").val('').text( '株式会社日本アクセス' ));
                $("#hidden_admin_company_id_select option").each(function(i){
                  if($(this).text().indexOf(filter) != -1){
                    $('select[name="admin_company_id"]').append($("<option>").val($(this).val()).text( $(this).text() ));
                  }
                });
              }
            }
          </script>
          @endverbatim
          <!-- {{-- ****************** /企業フィルター ****************** --}} -->

          <select name="admin_company_id" style="width:460px;">
            <option value="">株式会社日本アクセス</option>
            {!! blade_html_options(['options' => $_conf_company, 'selected' => $admin_company_id]) !!}
          </select>
        </div>
      </div>
    </div>

    <div>
      <label>ログイン権限</label>
      <div>
        {!! blade_html_radios(['name' => "admin_login_kengen", 'options' => $_conf_login_kengen, 'selected' => $admin_login_kengen, 'style' => "margin-left:1em;"]) !!}
      </div>
    </div>

    <div>
      <label>マスター管理権限</label>
      <div>
        {!! blade_html_radios(['name' => "admin_master_kengen", 'options' => $_conf_master_kengen, 'selected' => $admin_master_kengen, 'style' => "margin-left:1em;"]) !!}
      </div>
    </div>

    <div>
      <label>ユーザー閲覧権限</label>
      <div>
        {!! blade_html_radios(['name' => "admin_user_kengen", 'options' => $_conf_user_kengen, 'selected' => $admin_user_kengen, 'style' => "margin-left:1em;"]) !!}
      </div>
    </div>

    <div>
      <label>集計閲覧権限</label>
      <div>
        {!! blade_html_radios(['name' => "admin_syuukei_etsuran_kengen", 'options' => $_conf_syuukei_etsuran_kengen, 'selected' => $admin_syuukei_etsuran_kengen, 'style' => "margin-left:1em;"]) !!}
      </div>
    </div>

    <div class="msr_text_01">
      <label>外部企業</label>

      <label for="kyouryoku_kigyou_flg">
        <input type="checkbox" class="largechkbox" id="kyouryoku_kigyou_flg" name="kyouryoku_kigyou_flg" value="1" @if($admin_kyouryoku_kigyou_flg=="1")checked @endif/>&nbsp;外部企業担当者として登録する
      </label>
    </div>
    <div id="kyouryoku_kigyou_select" class="bg-light p-2">
      <div class="msr_text_01">
        <!-- {{-- ****************** 企業フィルター ****************** --}} -->
        <input type="text" name="company_filter" placeholder="選択肢をフィルター" onKeyup="companyFilter();"><br>
        選択肢フィルタに企業名を入力（例:日本アクセス等）後、プルダウン候補から企業名を選択<br><br>
        <select id="hidden_company_id_select" style="display:none;">
          <option value="">選択してください</option>
          {!! blade_html_options(['options' => $_conf_company, 'selected' => $search_condition['syoutai_company_id']]) !!}
        </select>
        @verbatim
        <script>
          function companyFilter(){
            var filter = $('input[name="company_filter"]').val();
            if(filter==''){
              $('select[name="syoutai_company_id"]').html( $("#hidden_company_id_select").html() );
            }else{
              $('select[name="syoutai_company_id"]').html('');
              $('select[name="syoutai_company_id"]').append($("<option>").val('').text( '選択してください' ));
              $("#hidden_company_id_select option").each(function(i){
                if($(this).text().indexOf(filter) != -1){
                  $('select[name="syoutai_company_id"]').append($("<option>").val($(this).val()).text( $(this).text() ));
                }
              });
            }
          }
        </script>
        @endverbatim
        <!-- {{-- ****************** /企業フィルター ****************** --}} -->

        <select name="syoutai_company_id" style="width:460px;" onchange="kigyouSelect(this);">
          <option value="">選択してください</option>
          {!! blade_html_options(['options' => $_conf_company]) !!}
        </select>
      </div>

      <div id="selected_kigyou">
        @foreach($kyouryoku_kigyou as $kigyou)
        <div class="pl-2">{{ $kigyou['company_name'] }}<input type="hidden" name="kyouryoku_kigyou_ids[]" value="{!! $kigyou['company_id'] !!}"/><button class="delete_kigyou btn-danger ml-2">削除</button></div>
         @endforeach
      </div>
    </div>

    <p class="msr_btn15">
      @if($mode=='insert')
        <a href="javascript:_regist('insert');void(0);">上記内容で新規登録する</a>
       @else 
        <a href="javascript:_regist('update');void(0);">上記内容で更新する</a>
        &nbsp;&nbsp;&nbsp;
        <a href="javascript:_regist('delete');void(0);">削除する</a>
       @endif
      &nbsp;&nbsp;&nbsp;
      <a href="javascript:location.href='?page=admin_list&sess_no_init=1';void(0);">一覧へ戻る</a>
    </p>

  </form>

</div>
