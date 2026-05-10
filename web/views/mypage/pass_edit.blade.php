<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>{{ $event_rec['event_name'] }}-パスワード変更</title>
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

@if($init_pass_change=="")
  <form name="form_edit" action="./?page={!! $page !!}" method="post" onSubmit="return false;">
  <!-- <input type="hidden" name="page" value="{{ $page }}"> -->
  <input type="hidden" name="exec" value="save">
  <input type="hidden" name="mode">
  <input type="hidden" name="token" value="{{ $token }}">

    <h1 class="msr_h103">パスワード変更</h1>

    <div class="msr_text_01">
      <label>新規パスワード</label>
      <input type="password" name="new_user_pass" value="{{ $new_user_pass }}" />
    </div>
    <div class="msr_text_01">
      <label>新規パスワード(確認用)</label>
      <input type="password" name="new_user_pass_chk" value="{{ $new_user_pass_chk }}" />
    </div>

    <p class="msr_btn15">
      <a href="javascript:document.form_edit.submit();void(0);">　パスワードを上記内容で変更する</a>
    </p>

  </form>
 @else 
    <p class="msr_btn15">
      <!-- {{--
      @if($user_raijyou_yotei_time!="" || $goto_mypage == 1)
        <a href="./">ログイン画面へ</a>
       @else 
        <a href="{{ $ex_login_url }}">WEB展示会(ガイドブック)へ</a>
       @endif
      --}} -->
      <a href="./">マイページログイン画面へ</a>
    </p>
 @endif

</div>
<!--/#contents-->

<footer>

<div id="copyright">
<small>Copyright (c) NIPPON ACCESS, INC. All rights reserved.</small>
</div>

</footer>

</body>
</html>
