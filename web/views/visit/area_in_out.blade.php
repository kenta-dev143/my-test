<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>{{ $event_rec['event_name'] }}　{{ $disp_ymd }}-{{ $area_rec['area_name'] }}出入口</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="../../css/style.css">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" />

<script type="text/javascript" src="../../js/jquery-3.2.1.min.js"></script>

@verbatim
<script>
  $(window).on('load', function() {
    $('#qr_code').focus();
  });
</script>
@endverbatim

</head>

<body class="c2">

<header>
<div class="inner">
<h1 id="logo"><a href="./?ymd={{ $ymd }}"><img src="../../images/logo.png" alt="日本アクセス"></a></h1>
<h2 style="font-size:30px;padding-top:20px;">{{ $event_rec['event_name'] }}　{{ $disp_ymd }}@if($ymd_alert!=""){!! $ymd_alert !!} @endif</h2>
</div>
</header>

<div class="Venue_inner_wrap_mapage">
  <div class="form-wrapper">
    <h1 class="Venue-h">{{ $area_rec['area_name'] }}<br>出入口</h1>
  <form name="form_edit" action="./?page=area_in_out&area_id={{ $area_rec['area_id'] }}&ymd={{ $ymd }}" method="post">
  <input type="hidden" name="exec" value="save">
      <div class="form-item Venue-text">
        <label for="qr_code"></label>
        @verbatim
        <input type="password" name="qr_code" id="qr_code" placeholder="" onblur="obj = this; setTimeout(function(){ obj.focus(); }, 1);" />
        @endverbatim
      </div>
        <h2 class="Venue-h2" @if($now_cnt > $area_rec['area_max'])style="color:red;" @endif>{{ $now_cnt }}/{{ $area_rec['area_max'] }}人</h2>
    </form>
  @if($err_msg['0'] !='')
    <span style="color:red;">{{ $err_msg['0'] }}</span>
  @elseif($success_msg != '')
    <div style="color:green; text-align:right;">{{ $success_msg }}</div>
   @endif
  </div>
</div>
<!--/#contents-->

<!-- <footer>

<div id="copyright">
<small>Copyright (c) NIPPON ACCESS, INC. All rights reserved.</small>
</div>

</footer>
 -->
</body>
</html>
