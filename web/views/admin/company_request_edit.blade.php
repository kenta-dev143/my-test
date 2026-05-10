@verbatim
<script>
function _regist(_mode){
  document.form_edit.mode.value=_mode;
  document.form_edit.submit();
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
      <div class="successArea">{{ $success_msg }}</div>
   @endif



  <form name="form_edit" action="./?page={{ $page }}" method="post" onSubmit="return false;">
  <input type="hidden" name="exec" value="save">
  <input type="hidden" name="mode">
  <input type="hidden" name="token" value="{{ $token }}">
  <input type="hidden" name="id" value="{{ $id }}">

    <div>
      <p>
        申請者：{{ $admin_name }}
      </p>
    </div>

    <div class="msr_text_01">
      <label>法人格</label>
      <select name="legal_personality" id="legal_personality" class="selectbox" style="width: 200px !important;">
        <option value="" disabled selected>選択してください。</option>
        {!! blade_html_options(['options' => $_conf_legal_personality, 'selected' => $legal_personality]) !!}
      </select>
      <select name="legal_personality_position" id="legal_personality_position" class="selectbox" style="width: 100px !important;">
        {!! blade_html_options(['options' => $_conf_legal_personality_position, 'selected' => $legal_personality_position]) !!}
      </select><br><br>
    </div>
    <div class="msr_text_01">
      <label>企業名　例）日本アクセス</label>
      <input type="text" name="name" value="{{ $name }}" />
    </div>

    <div class="msr_text_01">
      <label>表示名　例）(株)日本アクセス　<a href="https://npacs.sharepoint.com/:x:/s/00364/fc2025aw/EdfxCmiqlAhCs-C3pCD9mS0BFWy1E9OTpLQtJv68HzTASA?e=DZaKVe" target="blank">※法人格一覧</a></label>
      <input type="text" name="display_name" value="{{ $display_name }}" />
    </div>

    <div class="msr_text_01">
      <label>企業名カナ 例）ニッポンアクセス　※全角カナで登録してください。</label>
      <input type="text" name="name_kana" value="{{ $name_kana }}" />
    </div>

    <div class="msr_pulldown_01">
      <label>登録区分</label>
      <select name="big_cate" style="width: 100% !important;">
        <option value="">選択してください</option>
        {!! blade_html_options(['options' => $_conf_big_cate, 'selected' => $big_cate]) !!}
      </select>
    </div>

    <div class="msr_text_01">
      <label>住所</label>
      <input type="text" name="address" value="{{ $address }}" />
    </div>

    <div class="msr_text_01">
      <label>電話番号</label>
      <input type="text" name="tel" value="{{ $tel }}" />
    </div>

    <div class="msr_text_01">
      <label>ホームページ</label>
      <input type="text" name="url" value="{{ $url }}" />
    </div>

    <div class="msr_text_01">
      <label>確認メモ</label>
      <textarea name="memo" class="w-100" placeholder="確認結果や問題点などをメモしてください">{{ $url }}</textarea>
    </div>

    <p class="msr_btn15">
      <a href="javascript:_regist('insert');void(0);">申請する</a>
      &nbsp;&nbsp;&nbsp;
      <a href="javascript:_regist('insert_next');void(0);">連続で申請する</a>
    </p>

  </form>

</div>
