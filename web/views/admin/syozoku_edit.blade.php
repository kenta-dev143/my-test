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
      <label>所属(支店・部署)名</label>
      <input type="text" name="syozoku_name" value="{{ $syozoku_name }}" />
    </div>

    <div class="msr_text_01">
      <label>コード</label>
      <input type="text" name="syozoku_code" value="{{ $syozoku_code }}" />
    </div>

    <div class="msr_pulldown_01">
      <label>閲覧部署グループ名</label>
      <select name="syozoku_szkgrp_id" style="width:400px;">
        <option value="">選択してください</option>
        {!! blade_html_options(['options' => $_conf_syozoku_group, 'selected' => $syozoku_szkgrp_id]) !!}
      </select>
    </div>

    <div class="msr_pulldown_01">
      <label>エリア</label>
      <select name="syozoku_tanarea_id" style="width:400px;">
        <option value="">選択してください</option>
        {!! blade_html_options(['options' => $_conf_tanarea, 'selected' => $syozoku_tanarea_id]) !!}
      </select>
    </div>

    <div class="msr_text_01">
      <label for="syozoku_hidden_flg">
        <input type="checkbox" class="largechkbox" id="syozoku_hidden_flg" name="syozoku_hidden_flg" value="1" @if($syozoku_hidden_flg=="1")checked @endif/>&nbsp;検索時非表示にする
      </label>
    </div>

    <p class="msr_btn15">
      @if($mode=='insert')
        <a href="javascript:_regist('insert');void(0);">上記内容で新規登録する</a>
       @else 
        <a href="javascript:_regist('update');void(0);">上記内容で更新する</a>
       @endif
      &nbsp;&nbsp;&nbsp;
      <a href="javascript:location.href='?page=syozoku_list&sess_no_init=1';void(0);">一覧へ戻る</a>
    </p>

  </form>

</div>
