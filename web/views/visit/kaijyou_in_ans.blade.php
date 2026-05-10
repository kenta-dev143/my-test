<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>{{ $event_rec['event_name'] }}　{{ $disp_ymd }}-会場入口</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="../../css/style.css">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" />

<script type="text/javascript" src="../../js/jquery-3.2.1.min.js"></script>

@verbatim
<script>
  $(window).on('load', function() {
    $('#qr_code').focus();
  });
  function _ans(ans){
    document.form_edit.ans.value = ans;
    document.form_edit.submit();
  }
</script>
@endverbatim

</head>

<body class="c2">

<header>
<div class="inner">
<h1 id="logo"><a href="./?ymd={{ $ymd }}"><img src="../../images/logo.png" alt="日本アクセス"></a></h1>
<h2 style="font-size:20px;padding-top:15px;">{{ $event_rec['event_name'] }}<br>{{ $disp_ymd }}@if($ymd_alert!=""){!! $ymd_alert !!} @endif</h2>
</div>
</header>

<div class="Venue_inner_wrap_mapage" style="padding-top:5px;padding-bottom:5px;">
  <div class="form-wrapper" style="width:600px;max-width:600px;margin-bottom:10px;">
    <h1 class="Venue-h">会場入口</h1>
  <form name="form_edit" action="./?page=kaijyou_in&ymd={{ $ymd }}" method="post" onSubmit="return false;">
  <input type="hidden" name="exec" value="save">
  <input type="hidden" name="ans" value="">
  <input type="hidden" name="qr_code" value="{{ $qr_code }}">
        入場時の健康状態のご確認<br>
        新型コロナウイルス感染拡大に伴い、入場者管理対策のため、下記ご回答に関しましてご理解を賜り、ご協力のほどお願い申し上げます。<br>
        <br>
        <h2 id="kigyou_name">{{ $user_rec['user_kigyou_name'] }}</h2>
        <p id="user_name">{{ $user_rec['user_name'] }} 様</p>
        <!-- <p id="how">健康状態は大丈夫ですか？</p> -->
        現在の健康状況について下記ご確認の上、該当なし・あり を押して下さい。<br>
        <span style="font-weight:bold;font-size:20px;line-height: 1.3;">過去1４日以内に発熱（37.5度以上）及び、せき、鼻汁・喉の痛み・倦怠感などの症状</span><br>
        <span>※なお、ワクチン接種後の副反応と思われる場合（接種後に症状が出現し、2日以内に改善した場合）を除く。</span><br>
        <p style="font-weight:bold;font-size:20px;text-align:center;line-height: 1.3;">
          現在の健康状況に問題なければ<br><span style="color:red;text-decoration: underline;">「該当なし」を押してください</span>
        </p>
        <p style="width:200px;margin:20px auto;"><img src="../../images/arrow.png" alt=""></p>
    <ul class="Venue-btn_2" id="ans">
      <li class="Venue-btn_2_01" style="width:220px;background-color:#f6c142;border-color:#f6c142;" onclick="_ans('y');"><a href="javascript:void(0);" style="font-size:20px;">上記の症状<br><span style="font-size:35px;">該当 <span style="color:red;font-size:40px;">なし</span></span></a></li>      　　　　
      <li class="Venue-btn_2_02" style="width:220px;background-color:#c1d7ec;border-color:#c1d7ec;" onclick="_ans('n');"><a href="javascript:void(0);" style="font-size:20px;">上記の症状<br><span style="font-size:35px;">該当 <span style="color:red;font-size:40px;">あり</span></span></a></li>
    </ul>
    </form>
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
