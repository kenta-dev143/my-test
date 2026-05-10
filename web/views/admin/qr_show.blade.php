@verbatim
<script>
function _regist(){
  document.form_edit.submit();
}

function _clear() {
    document.form_edit.qr_code.value = "";
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

  <form name="form_edit" action="./?page={{ $page }}" method="post">
  <input type="hidden" name="exec" value="input">

    <div class="msr_text_01">
      <label>QRコード</label>
      <input type="text" name="qr_code" value="" />
    </div>

    <p class="msr_btn15">
<!--        <input type="submit" value="PDF表示"/>-->
      <a href="javascript:_regist();void(0);">PDF表示</a>
      <a href="javascript:_clear();void(0);">クリア</a>
    </p>

  </form>

</div>
