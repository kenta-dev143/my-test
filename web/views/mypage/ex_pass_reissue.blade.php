<div class="inner">
    @if($err_msg['0'] !='')
      @foreach($err_msg as $msg)
        <span style="color:red;">{{ $msg }}</span><br>
       @endforeach
    @elseif($success_msg!="")
      <div class="successArea">
        <div>{{ $success_msg }}</div>
      </div>
     @endif

<form class="login-form"  action="./?page={!! $page !!}" method="post">
<input type="hidden" name="exec" value="send">
<input type="hidden" name="page" value="{!! $page !!}">

<div class="form-group">
<label for="loginid" class="fact-title">ログインID（メールアドレス）</label>
<input type="text" class="form-control" name="login_id" value="{{ $login_id }}" id="loginid" aria-describedby="emailHelp" placeholder="ログイン ID（Ｅメールアドレス）" novalidate="novalidate">
</div>
<div class="button-area">
<button type="submit" name="login" value="1" class="blue-button t-hover"><span>送 信</span></button>
</div>
</form>
<br>
<a href="./">マイページログイン画面へ戻る</a>
</div>
