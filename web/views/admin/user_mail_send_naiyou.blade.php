<script type="text/javascript" src="{!! $_SYSTEM_ROOT_URLS !!}/js/ckeditor/ckeditor.js"></script>
<script>

$(function(){
  var editor = CKEDITOR.replace( 'mail_body' );
});

var select_event_id = '{{ $select_event_id }}';

function onChangeMailTiming() {
  var mail_timing = $('select[name="mail_timing"]').val();
  if (mail_timing == 'yoyaku') {
    $('.yoyaku_time').show();
  }
  else {
    $('.yoyaku_time').hide();
  }
}

$(document).ready(onChangeMailTiming);

</script>
　<br>

  @if($err_msg['0'] !='')
    <div class="errArea">
      @foreach($err_msg as $msg)
        {{ $msg }}<br>
       @endforeach
    </div><br>
  @elseif($success_msg!="")
    <div class="successArea">{{ $success_msg }}</div>
   @endif

  @if($select_event_id!="")


        <div class="row">

          <div class="col-md-12">


            <div class="card card-user" style="padding-top:10px;">

              <form name="form_tpl" action="./" method="post" onSubmit="return false;">
                <input type="hidden" name="page" value="{{ $page }}">
                <input type="hidden" name="exec" value="">
                <input type="hidden" name="sess_no_init" value="">

                <span style="font-weight:bold;color:#51cecb;font-size:18px;">Step4: メール種類の選択</span>



                <!-- ***************************** -->
                <div class="row">
                  <div class="col-md-4 pl-1">
                    <div class="msr_pulldown_01">
                      <label style="width:300px;">メール種類(メールテンプレート)</label>
                      <select name="mail_tpl" style="width:300px;" onChange="document.form_tpl.sess_no_init.value='';document.form_tpl.exec.value='tpl_change';document.form_tpl.submit();">
                        <option value="">選択してください</option>
                        @foreach($tpl_recs as $rec)
                          <option value="{{ $rec['mailt_key'] }}" @if($rec['mailt_key']==$mail_tpl)selected @endif {{ $rec['disabled'] }}>{{ $rec['pulldown_str'] }}</option>
                         @endforeach
                      </select>
                    </div>
                  </div>
                  <button type="button" style="width:250px;position:absolute;top:20px;right:70px;" class="btn btn-primary btn-round" onClick="document.form_tpl.sess_no_init.value='1';document.form_tpl.exec.value='';document.form_tpl.submit();">前のStepへ戻る</button>
                </div>

              </form>

            </div><!-- <div class="card card-user"> -->

            @if($mail_tpl!="")
            <div class="card card-user" style="padding-top:10px;">

              <form name="form_edit" action="./" method="post" onSubmit="return false;">
                <input type="hidden" name="page" value="{{ $page }}">
                <input type="hidden" name="mail_tpl" value="{{ $mail_tpl }}">
                <input type="hidden" name="exec" value="">

                <span style="font-weight:bold;color:#51cecb;font-size:18px;">Step5: メール内容の確認と編集</span>


                <!-- ***************************** -->
                <div class="row">
                  <div class="pl-1">
                    <div class="msr_text_01" style="width:700px;">
                      <label>件名</label>
                      <input type="text" name="mail_subject" value="{{ $mail_subject }}" style="width:100%;" />
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="pl-1">
                    <div class="msr_textarea_01" style="width:700px;">
                      <label>本文</label>
                      <textarea id="mail_body" name="mail_body">{{ $mail_body }}</textarea>
                    </div>
                  </div>
                  <div class="pl-1" style="margin-left:20px;">
                    <div class="msr_textarea_01">
                      <label>テスト送信</label>
                      <input type="text" name="test_addr" value="{{ $test_addr }}" style="width:260px;" />
                      <input type="button" value="　上記アドレスに１件分テスト送信する　" onClick="document.form_edit.exec.value='test_send';document.form_edit.submit();">
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="pl-1">
                    <div class="msr_textarea_01" style="width:700px;">
                      <label>送信対象者</label>
                      <span style="font-weight:bold;font-size:16px;">{!! $sess_sentakuTaisyouCnt !!} 人</span>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="pl-1">
                    <div class="msr_textarea_01" style="width:700px;">
                      <label>送信タイミング</label>
                      <select name="mail_timing" onChange="onChangeMailTiming();">
                        <option value="now" @if($mail_timing!="now")selected @endif >今すぐ送信する</option>
                        <option value="ato" @if($mail_timing=="ato")selected @endif >後から送信する</option>
                        <option value="yoyaku" @if($mail_timing=="yoyaku")selected @endif >予約送信する</option>
                      </select>
                    </div>
                    <div class="msr_textarea_01 yoyaku_time" style="width:700px; display:none">
                      <label>予約日時</label>
                      <input type="text" name="yoyaku_ymd" value="{{ $yoyaku_ymd }}" style="width:100px;" class="datepicker" />
                      　時刻：
                      <select name="yoyaku_hh">
                        <option value=""></option>
                        {!! blade_html_options(['options' => $_conf_hh, 'selected' => $yoyaku_hh]) !!}
                      </select>
                      時
                      <select name="yoyaku_ii">
                        <option value=""></option>
                        {!! blade_html_options(['options' => $_conf_mm, 'selected' => $yoyaku_ii]) !!}
                      </select>
                      分<br>
                    </div>
                  </div>
                </div>


                  <button type="button" style="width:250px;margin-left:60px;" class="btn btn-primary btn-round" onClick="document.form_edit.exec.value='send';document.form_edit.submit();">上記内容でメール送信</button>

              </form>
            </div><!-- <div class="card card-user"> -->
             @endif

          </div><!-- <div class="col-md-12"> -->


        </div><!-- <div class="row"> -->



   @else 
    <div class="col-md-12">
      <div class="card">
        <br><br><br><br>
        　　　　　イベントを選択してください
        <br><br><br><br><br>
      </div>
    </div>
   @endif