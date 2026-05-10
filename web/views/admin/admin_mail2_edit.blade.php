@verbatim
<script>
function _regist( mode ){
  if ( mode == "clear" ){
    $('input[name=admin_mail2]').val('');
  }
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
  <input type="hidden" name="token" value="{{ $token }}">
  <input type="hidden" name="id" value="{{ $id }}">

    <div class="msr_text_01">
      <label>通知先メールアドレス１<br>(ログインID)</label>
      <span style="font-weight:bold;">{{ ($admin_mail ?? "なし") }}</span>
    </div>

    <div class="msr_text_01">
      <label>通知先メールアドレス２</label>
      <input type="text" name="admin_mail2" value="{{ $admin_mail2 }}" />
    </div>

    <p class="msr_btn15">
      <a href="javascript:_regist('save');void(0);">設定する</a>
      @if($admin_mail2 != '')
        &nbsp;&nbsp;&nbsp;
        <a href="javascript:_regist('clear');void(0);">解除する</a>
       @endif
    </p>

  </form>

</div>
