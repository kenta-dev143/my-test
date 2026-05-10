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

<script>
  function _taiou(ans){
    $('#passPanel').show();
  }
  var pass = '';
  function _passIn(val){
      $('#err').text('');
    if(val=='GO'){
      if(pass=='{{ $now_md }}'){
        location.href='./?page=kaijyou_in&ymd={{ $ymd }}';
      }else{
        $('#err').text('ﾊﾟｽﾜｰﾄﾞが違います');
      }
    }else if(val=='CLR'){
      pass = '';
    }else{
      pass += val;
    }
    var mask = '';
    for (var i = 0; i < pass.length; i++) {
      mask += '＊';
    }
    $('#mask').text(mask);

    if(val=='CLR'){
      $('#passPanel').hide();
    }
  }
</script>


</head>

<body class="c2">

<header>
<div class="inner">
<h1 id="logo"><a href="./?ymd={{ $ymd }}"><img src="../../images/logo.png" alt="日本アクセス"></a></h1>
<h2 style="font-size:30px;padding-top:20px;">{{ $event_rec['event_name'] }}　{{ $disp_ymd }}@if($ymd_alert!=""){!! $ymd_alert !!} @endif</h2>
</div>
</header>

<div class="Venue_inner_wrap_mapage" style="padding-top:10px;position:relative">
  <div class="form-wrapper" style="width:700px;max-width:700px;">
    <h1 class="Venue-h">会場入口</h1>
        <h2 id="kigyou_name">係員が対応いたします</h2>
        しばらくお待ち下さい。。。。。。

    <ul class="Venue-btn" id="ans">
      <li class="Venue-btn02" onclick="_taiou();"><a href="javascript:void(0);" style="font-size:12px;">係員対応...</a></li>
    </ul>
  </div>

  <div id="passPanel" style="position:absolute;top:100px;left:300px;background-color:gray;padding:20px;display:none">
    <div style="background-color:#fafafa;margin-bottom:10px;font-size:20px;height:30px;" id="mask"></div>
    <div style="background-color:gray;margin-bottom:10px;font-size:20px;height:30px;color:red;" id="err"></div>
    <button value="7" onClick="_passIn(this.value);" style="width:60px;height:60px;">７</button>
    <button value="8" onClick="_passIn(this.value);" style="width:60px;height:60px;">８</button>
    <button value="9" onClick="_passIn(this.value);" style="width:60px;height:60px;">９</button><br>
    <button value="4" onClick="_passIn(this.value);" style="width:60px;height:60px;">４</button>
    <button value="5" onClick="_passIn(this.value);" style="width:60px;height:60px;">５</button>
    <button value="6" onClick="_passIn(this.value);" style="width:60px;height:60px;">６</button><br>
    <button value="1" onClick="_passIn(this.value);" style="width:60px;height:60px;">１</button>
    <button value="2" onClick="_passIn(this.value);" style="width:60px;height:60px;">２</button>
    <button value="3" onClick="_passIn(this.value);" style="width:60px;height:60px;">３</button><br>
    <button value="CLR" onClick="_passIn(this.value);" style="width:60px;height:60px;background-color:#ffaaaa;">Ｃ</button>
    <button value="0" onClick="_passIn(this.value);" style="width:60px;height:60px;">０</button>
    <button value="GO" onClick="_passIn(this.value);" style="width:60px;height:60px;background-color:#aaaaff;">OK</button>
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
