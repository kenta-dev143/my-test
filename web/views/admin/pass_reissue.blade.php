<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>展示会来場者管理システム - ログイン</title class="login">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" />
@verbatim
<style>
.loginTitle {
    max-width: 800px;
    padding: 0px 50px 0px 50px;
    background: #0370c0;
    box-shadow: 2px 2px 10px #ccc;
    height: 40px;
    margin:50px auto 0px auto;
    display: block;
    text-align:center;
    color:white;
    font-size:20px;
}
</style>
@endverbatim
</head>

<body class="c2">

<header>
<div class="inner">
<h1 id="logo"><a href="./"><img src="images/logo.png" alt="日本アクセス"></a></h1>
</div>
</header>
<div class="loginTitle">
来 場 者 管 理 シ ス テ ム　管 理 画 面
</div>
<div class="inner_wrap_mapage" style="margin-top:0px;">
  <div class="form-wrapper">
    <h1 class="login-h">パスワード 設定</h1>

    @if($err_msg['0'] !='')
          @foreach($err_msg as $msg)
              <span style="color:red;">{{ $msg }}</span><br>
           @endforeach
    @elseif($success_msg!="")
      <div class="successArea">
        <div>{{ $success_msg }}</div>
      </div>
     @endif

    <form name="login_form" action="index.php" method="post">
    <input type="hidden" name="page" value="{{ $page }}">
    <input type="hidden" name="exec" value="send">
    <input type="hidden" name="token" value="{{ $token }}">
      <div class="form-item">
        <label for="login_id"></label>
        <input type="text" name="login_id" id="login_id" value="{{ $login_id }}" required="required" placeholder="Email Address"></input>
      </div>
      <div class="button-panel">
        <input type="submit" class="button" title="Sign In" value="送信"></input>
      </div>
      <div style="margin: 10px auto 10px auto;color:blue;font-size:small;">
        <a href="./">
          ログイン画面へ 戻る
        </a>
      </div>
    </form>
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
