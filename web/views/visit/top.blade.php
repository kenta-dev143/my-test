<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>{{ $event_rec['event_name'] }}-入退場</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="../../css/style.css">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" />

<script type="text/javascript" src="../../js/jquery-3.2.1.min.js"></script>

@verbatim
<script>
function _gotoK(page){
  var ymd = $('#select_ymd').val();
  location.href = './?page='+page+'&ymd='+ymd;
}
function _gotoA(page,area){
  var ymd = $('#select_ymd').val();
  location.href = './?page='+page+'&area_id='+area+'&ymd='+ymd;
}
</script>
@endverbatim
</head>

<body class="c2">

<header>
<div class="inner">
<h1 id="logo"><a href="./"><img src="../../images/logo.png" alt="日本アクセス"></a></h1>
<h2 style="font-size:30px;padding-top:20px;">{{ $event_rec['event_name'] }}</h2>
</div>
</header>

<div class="Venue_inner_wrap_mapage">
  <div class="form-wrapper">
    日付選択：
    <select id="select_ymd">
      {!! blade_html_options(['options' => $select_ymd_arr, 'selected' => $ymd]) !!}
    </select>
    <h1 class="Venue-h">会場出入口</h1>
    <div class="form-item Venue-text">
      <a href="javascript:_gotoK('kaijyou_in');void(0);">会場入口</a>　<a href="javascript:_gotoK('kaijyou_out');void(0);">会場出口</a>
    </div>

    <h1 class="Venue-h">エリア出入口</h1>
    <div class="form-item Venue-text">
      @foreach($area_recs as $rec)
        @if($rec['area_tanmatsuhaichi_kbn'] == 1)
          <a href="javascript:_gotoA('area_in','{{ $rec['area_id'] }}');void(0);">{{ $rec['area_name'] }} 入口</a>　<a href="javascript:_gotoA('area_out','{{ $rec['area_id'] }}');void(0);">{{ $rec['area_name'] }} 出口</a><br>
           @else 
          <a href="javascript:_gotoA('area_in_out','{{ $rec['area_id'] }}');void(0);">{{ $rec['area_name'] }} 出入口</a><br>
         @endif
       @endforeach
    </div>
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
