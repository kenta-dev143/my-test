<div class="inner header_inner">
  <h1 id="logo">
    @if($direct_login=="")<a href="./"> @endif<img src="../../images/logo.png" alt="日本アクセス">@if($direct_login=="")</a> @endif
  </h1>
  <div class="header_right">
    @if($direct_login=="")
        @if($init_pass_change=="")
            <a class="Logout" href="./">マイページTOP</a>
            <a class="Logout" href="?page=login&exec=logout">ログアウト</a>            
         @else 
          @if($user_raijyou_yotei_time!="")
              <a class="Logout" href="./">ログイン画面</a>
           @endif
         @endif
     @endif
  </div>
</div>
<p class="header_kaijyou">{{ $event_rec['event_name'] }}<span class="kaijyouNmae">({{ $event_rec['event_kaijyou_name'] }})</span></p>
