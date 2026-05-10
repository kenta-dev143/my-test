<div class="inner">
    @if($err_msg['0'] !='')
      @foreach($err_msg as $msg)
        <span style="color:red;">{{ $msg }}</span><br>
       @endforeach
     @endif

<form class="login-form" action="./" method="post" novalidate>
<input type="hidden" name="exec" value="login">
<input type="hidden" name="page" value="login">
<div class="form-group">
<label for="loginid" class="fact-title">ログインID（Ｅメールアドレス）</label>
<input type="text" class="form-control" name="login_id" value="{{ $login_id }}" id="loginid" aria-describedby="emailHelp" placeholder="ログイン ID（Ｅメールアドレス）" novalidate="novalidate">
</div>
<div class="form-group">
<label for="loginpassword" class="fact-title">パスワード</label>
<input type="password" class="form-control" name="login_pass" value="{{ $login_pass }}" id="loginpassword" placeholder="パスワード">
</div>
<div class="button-area">
<button type="submit" name="login" value="1" class="blue-button t-hover"><span>送 信</span></button>
</div>
</form>
<div class="attention-area">
<p><strong>パスワードをお忘れの方</strong>
パスワードをお忘れの方は<a href="./?page=pass_reissue" class="t-hover"><u>こちら</u></a>を押してパスワードの再発行をお願い致します。</p>
</div>
</div>
