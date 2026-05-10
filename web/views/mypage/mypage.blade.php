<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>{{ $event_rec['event_name'] }}-マイページ</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="../css/style.css">
</head>

<body class="c2">

<header>
   @include('mypage_header')
</header>

<p class="name" style="font-weight:bold;">{{ $login['user_name'] }} 様のマイページ</p>

<div class="inner_wrap_mapage">
    <ul class="msr_list06">
      <li>
      	<a href="?page=raijyou_yotei" @if($login['user_raijyou_yotei_time']=="" && $login['user_big_cate'] != 7)style="background-color:#dddddd;color:gray;pointer-events: none;" @endif>
      		<img src="../../images/timetable.png" @if($login['user_raijyou_yotei_time']=="" && $login['user_big_cate'] != 7)style="opacity:0.5" @endif />
    			<h1>ご来場予定</h1>
		    	<span class="small">来場日時の確認・変更ができます。</span>
			     <p>@if($sanka_shinai_only==true)　 @else QRコード発行はこちら @endif</p>
		    </a>
      </li>

<!--
      <li>
      	<a href="#">
      		<img src="../../images/list.png"/>
          <h1>来場履歴一覧</h1>
          <span class="small">過去のご来場履歴が確認できます。</span>
          <p> </p>
  		  </a>
  	  </li>
 -->

      @if($login['user_big_cate'] != 7)
      <li>
      	<a href="?page=user_edit">
      		<img src="../../images/id-card.png"/>
      		<h1>登録情報</h1>
      		<span class="small">ご登録情報の確認・変更ができます。</span>
      		<p>登録情報の変更はこちら</p>
      	</a>
      </li>
       @endif
      
      <li>
      	<a href="?page=pass_edit">
      		<img src="../../images/lock.png"/>
			    <h1>パスワード変更</h1>
			    <span class="small">パスワードの変更ができます。</span>
          <p>パスワード変更はこちら</p>
		    </a>
      </li>

        @if($agent_flag == 1)
        <li>
            <a href="?page=agent_user_list">
                <img src="../../images/id-card.png"/>
                <h1>代理登録対象者一覧</h1>
                <span class="small">代理で登録を行うユーザーの確認・変更ができます。</span>
                <p>代理登録対象者一覧はこちら</p>
            </a>
        </li>
         @endif

      <!-- {{-- @if($login['user_big_cate'] != 7) --}} 
      <li>
      	<a href="?page=jizen_ans" style="line-height:1;@if($login['user_raijyou_yotei_time']=="" || $sanka_shinai_only==true)background-color:#dddddd;color:gray;pointer-events: none; @endif" >
      		<img src="../../images/exam.png" @if($login['user_raijyou_yotei_time']=="" || $sanka_shinai_only==true)style="opacity:0.5" @endif />
      		<h1>事前アンケート</h1>
      		<span class="small"><br>お客様の健康状態に関するアンケートです。来場当日にご回答をお願いいたします。</span>
      		<p>事前アンケートはこちら</p>
      	</a>
      </li>
      <!-- {{--  @endif --}} -->

      <!--
      <li>
        <a href="javascript:void(0);">
          <img src="../../images/exam-01.png" />
          <h1>アンケート</h1>
          <span class="small">この度はご来場<br>ありがとうございました。</span>
          <p>アンケートにご協力ください</p>
        </a>
      </li>
      -->
      <!-- {{-- @if($login['user_big_cate'] != 7) --}} -->
      @if($login['user_big_cate'] <= 4)
      <li>
        @if($jigo_enq_open=="1" && $sanka_shinai_only==false && $login['user_raijyou_yotei_time']!="")
          <a href="?page=jigo_ans" style="line-height:1;">
         @else 
          <a href="javascript:void(0);" style="line-height:1;background-color:#dddddd;color:gray;pointer-events: none;">
         @endif
          <img src="../../images/exam-01.png" @if($jigo_enq_open!="1" || $sanka_shinai_only==true || $login['user_raijyou_yotei_time']=="")style="opacity:0.5" @endif />
          <h1>アンケート</h1>
          <span class="small" @if($jigo_enq_open!="1" || $sanka_shinai_only==true || $login['user_raijyou_yotei_time']=="")style="background-color:#dddddd;color:gray;" @endif><br>ご来場に関するアンケートです。ご協力のほど宜しくお願いします<br>　</span>
          <p>ご来場アンケートはこちら</p>
        </a>
      </li>
       @endif
    </ul>
</div>
<!--/#contents-->

<footer>

<div id="copyright">
<small>Copyright (c) NIPPON ACCESS, INC. All rights reserved.</small>
</div>

</footer>

</body>
</html>
