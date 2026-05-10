@verbatim
<script>
function _regist(_mode){
    if(_mode =='delete'){
        if(window.confirm('本当に削除してもよろしいでしょうか？')){
            document.form_edit.mode.value=_mode;
            document.form_edit.submit();
        }
    }else{
        document.form_edit.mode.value=_mode;
        document.form_edit.submit();
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
      <div class="successArea">{{ $success_msg }}</div>
   @endif

  <form name="form_edit" action="./?page={{ $page }}" method="post" onSubmit="return false;">
  <input type="hidden" name="exec" value="save">
  <input type="hidden" name="mode">
  <input type="hidden" name="token" value="{{ $token }}">
  <input type="hidden" name="id" value="{{ $id }}">

    <div class="msr_text_01">
      <label>担当エリア名</label>
      <input type="text" name="tanarea_name" value="{{ $tanarea_name }}" />
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
      <a href="javascript:location.href='?page=tantou_area_list&sess_no_init=1';void(0);">一覧へ戻る</a>
    </p>

  </form>

</div>
