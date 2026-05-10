@verbatim
<script>
function _regist(){
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
      <label>担当者名</label>
      <div>
        {{ $admin_name }}
      </div>
    </div>
    <br>

    <div class="msr_text_01">
      <label>新しいパスワード</label>
      <input type="password" name="admin_login_pass" />
    </div>

    <div class="msr_text_01">
      <label>新しいパスワード（確認用）</label>
      <input type="password" name="admin_login_pass_chk" />
    </div>


    <p class="msr_btn15">
      <a href="javascript:_regist();void(0);">変更する</a>
    </p>

  </form>

</div>
