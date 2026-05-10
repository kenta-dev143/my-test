<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>{{ $event_rec['event_name'] }}-登録情報</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="../css/style.css">
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
  <!-- <input type="hidden" name="page" value="{{ $page }}"> -->
  <input type="hidden" name="exec" value="save">
  <input type="hidden" name="mode">
  <input type="hidden" name="token" value="{{ $token }}">

    <h1 class="msr_h103">@if($raijyousya_kbn=="1")招待者 @else 来場者 @endif様</h1>

    <!-- {{--
    <div class="msr_pulldown_01">
      <p>VIP</p>
      @if($user_vip_flg=="1")○ @else × @endif
    </div>
    --}} -->

    <div class="msr_pulldown_01">
      <p>大分類（業種）</p>
      {{ $big_cate_name }}
    </div>

    @if($login['user_raijyou_yotei_time']!="")
    <div class="msr_pulldown_01">
      <p>中分類（業態）</p>
      {{ $mid_cate_name }}
    </div>
     @endif

    <div class="msr_text_01">
      <p>企業名</p>
        @if(empty($user_company_id))
          {{ $user_kigyou_name }}
         @else 
          {{ $user_company_name }}
         @endif
    </div>

    @if($login['user_raijyou_yotei_time']!="")
    <div class="msr_text_01">
      <p>企業名カナ</p>
        @if(empty($user_company_id))
          {{ $user_kigyou_name_kana }}
         @else 
          {{ $user_company_name_kana }}
         @endif
    </div>
     @endif

    <div class="msr_text_01">
      <label>部署</label>
      <input type="text" name="user_busyo" value="{{ $user_busyo }}" />
    </div>

    <div class="msr_text_01">
      <label>役職</label>
      <input type="text" name="user_yakusyoku" value="{{ $user_yakusyoku }}" />
    </div>

    <div class="msr_text_01">
      <label>氏名</label>
      <input type="text" name="user_name" value="{{ $user_name }}" />
    </div>

    @if($login['user_raijyou_yotei_time']!="")
    <div class="msr_text_01">
      <label>氏名カナ</label>
      <input type="text" name="user_name_kana" value="{{ $user_name_kana }}" />
    </div>
     @endif
    <div class="msr_text_01">
      <label>メールアドレス(PC)</label>
      <input type="text" name="user_mail" value="{{ $user_login_id }}" />
    </div>
    <!-- {{--
    <div class="msr_text_01">
      <label>パスワード</label>
      <input type="password" name="user_pass" value="{{ $user_pass }}" />
    </div>
    --}} -->

    <!-- {{--
    <h1 class="msr_h103">来場日時（{{ $event_rec['event_kaijyou_name'] }}）</h1>
    <div class="msr_radio_01">
      {{ $user_raijyou_yotei_time }}
    </div>

    <h1 class="msr_h103">WEB展示会招待</h1>
    <div class="msr_radio_01">
      @if($user_web=="1")ご招待しています @else ご招待していません @endif
    </div>
    --}} -->
    @if($login['user_raijyou_yotei_time']!="")
    <h1 class="msr_h103">ご来場予定日時（{{ $event_rec['event_kaijyou_name'] }}）</h1>
      @if($login['user_big_cate'] <= 4)
        <div>
          <div class="col-md-4 pl-1" style="margin:10px 0 20px 20px;">
            <!--{{--
            @foreach($_conf_raijyou_yotei_time2 as $ymd_hi)
              <label>
                <input type="radio" name="syoutai_yotei_time" value="{{ $ymd_hi }}" @if($user_raijyou_yotei_time==$ymd_hi)checked @else disabled @endif>
                {{ $ymd_hi }}
              </label><br>
             @endforeach
            --}} -->
            @foreach($_conf_raijyou_yotei_time2 as $ymd_hi => $ymd_hi_val)
              
                @if($user_raijyou_yotei_time==$ymd_hi)
                  <label>{{ $ymd_hi_val }}</label><br>
                 @endif
              
              @if($user_raijyou_yotei_time==$ymd_hi)
                <input type="hidden" name="raijyou_yotei_time[]" value="{!! $ymd_hi !!}">
               @endif
             @endforeach
          </div>
        </div>
       @else 
        <div class="msr_radio_01">
          @foreach($_conf_raijyou_yotei_time as $ymd => $info)
            <p class="raijyou" style="margin-bottom:0px;">{{ $info['disp_ymd'] }}</p>
            　
            @foreach($info['his'] as $hi_info)
              <input class="raijyou_chk" type="checkbox" name="raijyou_yotei_time[]" value="{{ $ymd }} {{ $hi_info['hi'] }}" {{ $hi_info['checked'] }} id="raijyou_{!! $ymd !!}{{ $hi_info['hi'] }}">
              <label  class="raijyou" for="raijyou_{!! $ymd !!}{{ $hi_info['hi'] }}">{{ $hi_info['hi'] }}&nbsp;</label>
             @endforeach
           @endforeach
          <br><br>
        </div>
       @endif
     @endif

    <h1 class="msr_h103">当社担当</h1>
    <div class="msr_pulldown_01">
      <p>担当者</p>
      {{ $admin_name }}
    </div>

    <p class="msr_btn15">
      <a href="javascript:document.form_edit.submit();void(0);">　登録内容を上記内容で変更する</a><br>
        <a href="?page=agent_user_list">　戻る</a>
    </p>



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
