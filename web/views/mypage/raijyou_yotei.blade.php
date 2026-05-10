<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>{{ $event_rec['event_name'] }}-ご来場予定</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="../css/style.css">

@verbatim
<script>
function qr_disp(uid,eid){
  var url = '../../qr_print.pdf?user_id='+uid+'&event_id='+eid;
  location.href = url;
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
  @elseif($success_msg!="")
      <div class="successArea">{!! $success_msg !!}</div>
   @endif

  <form name="form_edit" action="./?page={!! $page !!}" method="post" onSubmit="return false;">
  <input type="hidden" name="exec" value="save">
  <input type="hidden" name="token" value="{{ $token }}">
@if($sanka_shinai_only==false)
    <h1 class="msr_h103">QRコード</h1>
    <div class="row">
      <div class="col-6 msr_radio_01">
          <!-- {{--
        <img src="{!! $_SYSTEM_ROOT_URLS !!}/qr_disp.php?uid={{ $user_id }}&eid={{ $event_rec['event_id'] }}" /><br>
        　{{ $qr_code }}
          --}} -->
        <p class="msr_btn15">
          <a href="javascript:qr_disp('{{ $user_id }}','{{ $event_rec['event_id'] }}');void(0);">印刷用QRコードダウンロード</a>
        </p>
      </div>
      <div class="col-6">
          @if(strstr($_SERVER['REQUEST_URI'], "e2022fc-f"))
          <img src="../../images/mypage_info.png"/>
           @endif
      </div>
    </div>
 @endif
    <h1 class="msr_h103">ご来場予定日時（{{ $event_rec['event_kaijyou_name'] }}）</h1>
      <!-- {{--@if($login['user_big_cate'] <= 4)
      <div>
        <div class="col-md-4 pl-1" style="margin:10px 0 20px 20px;">
          <!-- {*
          @foreach($_conf_raijyou_yotei_time2 as $ymd_hi)
            <label>
              <input type="radio" name="syoutai_yotei_time" value="{{ $ymd_hi }}" @if($user_raijyou_yotei_time==$ymd_hi)checked @else disabled @endif>
              {{ $ymd_hi }}
            </label><br>
           @endforeach
         
          @foreach($_conf_raijyou_yotei_time2 as $ymd_hi => $ymd_hi_val)

              @if($user_raijyou_yotei_time==$ymd_hi)
                <label>{{ $ymd_hi_val }}</label>
               @endif

           @endforeach

        </div>
      </div>
     @else 　--}} -->
      <div class="msr_radio_01">
        @foreach($_conf_raijyou_yotei_time as $ymd => $info)
          <p class="raijyou" style="margin-bottom:0px;">{{ $info['disp_ymd'] }}</p>
          　
          @foreach($info['his'] as $hi_info)
          <div class="checkbox-item">
            <!-- {{-- <input class="raijyou_chk" type="checkbox" name="raijyou_yotei_time[]" value="{{ $ymd }} {{ $hi_info['hi'] }}" {{ $hi_info['checked'] }} id="raijyou_{!! $ymd !!}{{ $hi_info['hi'] }}" @if($login['user_big_cate'] != 7)disabled='disabled' @endif> --}} -->
            <input class="raijyou_chk" type="checkbox" name="raijyou_yotei_time[]" value="{{ $ymd }} {{ $hi_info['hi'] }}" {{ $hi_info['checked'] }} id="raijyou_{!! $ymd !!}{{ $hi_info['hi'] }}" >
            <label  class="raijyou" for="raijyou_{!! $ymd !!}{{ $hi_info['hi'] }}">{{ $hi_info['hi'] }}&nbsp;</label>
          </div>
           @endforeach
         @endforeach
      </div>


    <!-- {{--    @if($login['user_big_cate'] >= 5)　--}} -->
    <p class="msr_btn15">
      <a href="javascript:document.form_edit.submit();void(0);">ご来場予定日時を上記内容で変更する</a>
    </p>

    <br>
    <!-- {{-- @endif　--}} -->

    <!--h1 class="msr_h103">{{ $event_rec['event_exhibition_name'] }}招待</h1>
    <div class="msr_radio_01">
      @if($user_web=="1")ご招待しています @else ご招待していません @endif
    </div-->

  </form>

</div>
<!--/#contents-->

<footer>

<div id="copyright">
<small>Copyright (c) NIPPON ACCESS, INC. All rights reserved.</small>
</div>

</footer>

</body>
</html>
