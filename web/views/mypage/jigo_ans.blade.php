<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>{{ $event_rec['event_name'] }}-ご来場アンケート</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="../css/style.css">

@verbatim
<script>
  function _ans(ans){
    document.form_edit.submit();
  }
</script>
@endverbatim

</head>

<body class="c2">

<header>
  @include('mypage_header')
</header>

<div class="inner_wrap">

  @if($err_msg['0'] !='')
      <div class="errArea">
          @foreach($err_msg as $msg)
              {{ $msg }}<br>
           @endforeach
      </div><br>
  <!-- {{-- @elseif($success_msg!="") --}} -->
  <!-- {{--    <div class="successArea">{!! $success_msg !!}</div> --}} -->
   @endif




    <h1 class="msr_h103">ご来場アンケート</h1>
    <div class="msr_radio_01">
      @if($jigo_enq_open!="1")
           只今準備中です。しばらくお待ち下さい。<br>
      @elseif($kaitouzumi=="1")
           ご来場アンケートは回答済みです。<br>
           <br>
           ご回答にご協力いただき誠にありがとございました。<br>
           <br>
           <ul class="Venue-btn" id="ans">
           <li class="Venue-btn01" onclick="location.href='./';"><a href="javascript:void(0);">マイページに戻る</a></li>
           </ul>
      @elseif($exec=="save" && $err_msg['0'] =='')
           ご来場アンケートご回答にご協力いただき誠にありがとございました。<br>
           <br>
           <br>
           <ul class="Venue-btn" id="ans">
           <li class="Venue-btn01" onclick="location.href='./';"><a href="javascript:void(0);">マイページに戻る</a></li>
           </ul>
       @else 
          {{ $login['user_kigyou_name'] }}<br>
          {{ $login['user_name'] }} 様<br>
        「{{ $event_rec['event_name'] }}」のご来場アンケートにご協力ください。<br>
        <br>
        <form name="form_edit" action="./?page=jigo_ans" method="post" onSubmit="return false;">
        <input type="hidden" name="exec" value="save">

              <dl class="jigo_table">
              @foreach($questions as $field_name => $rec)
                  @if($rec['type'] != "sub_text")
                      @if(!$loop->first)
                          </dd>
                       @endif
                      <hr>
                      <dt style="font-size:20px;font-weight:bold;margin-bottom:15px;">【設問{{ $rec['no'] }}】{{ $rec['question'] }}@if($rec['hissu_flg']=='1')&nbsp;<span style="color:red;">【必須】</span> @endif</dt>
                      <dd>
                   @else 
                      <br />{{ $rec['question'] }}
                   @endif
                  @if($rec['type'] == "radio")
                      @foreach($rec['options'] as $k2 => $r2)
                          <input type="{!! $rec['type'] !!}" style="top:-4px;position:relative;" name={!! $field_name !!} value="{!! $k2 !!}"@if($row[$field_name]==$k2) checked="true"  @endif id="{!! $field_name !!}_{!! $loop->index !!}"><label for="{!! $field_name !!}_{!! $loop->index !!}"><span style="font-size:22px;">{{ $r2 }}</span></label><br>
                       @endforeach
                   @endif
                  @if($rec['type'] == "checkbox")
                      @foreach($rec['options'] as $k2 => $r2)
                          <input type="{!! $rec['type'] !!}" style="top:-4px;position:relative;" name={!! $field_name !!}[{!! $loop->index !!}] value="{!! $k2 !!}"@if($row[$field_name][$loop->index]==$k2) checked="true"  @endif id="{!! $field_name !!}_{!! $loop->index !!}"><label for="{!! $field_name !!}_{!! $loop->index !!}"><span style="font-size:22px;">{{ $r2 }}</span></label><br>
                       @endforeach
                   @endif

                  @if($rec['type'] == "text")
                  <input type=text name="{!! $field_name !!}" style="width:70%;" value="{{ $row[$field_name] }}" class="area">
                   @endif

                  @if($rec['type'] == "sub_text")
                  <br />
                  <textarea name="{!! $field_name !!}" style="width:70%;" class="area">{!! $row[$field_name] !!}</textarea>
                   @endif
                  @if($loop->last)
                      </dd>
                   @endif
               @endforeach
              </dl>
          <ul class="Venue-btn" id="ans">
            <li class="Venue-btn01" onclick="_ans();"><a href="javascript:void(0);">上記内容で回答する</a></li>
          </ul>
          </form>
       @endif
    </div>


</div>
<!--/#contents-->

<footer>

<div id="copyright">
<small>Copyright (c) NIPPON ACCESS, INC. All rights reserved.</small>
</div>

</footer>

</body>
</html>
