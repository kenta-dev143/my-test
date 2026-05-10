<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>{{ $event_rec['event_name'] }}-ご来場アンケート</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="../css/style.css" rel="stylesheet" type="text/css">


<script>
    const BACK_SECOND = {!! $back_second !!};
@verbatim
  function _ans(ans){
    document.form_edit.submit();
  }

  function _top() {
      setTimeout(function() {
          location.href = './';
      }, BACK_SECOND * 1000);
  }
</script>
@endverbatim

</head>

<body class="c2">

<div class="inner_wrap">

  @if($err_msg['0'] !='')
      <div class="errArea">
          @foreach($err_msg as $msg)
              {{ $msg }}<br>
           @endforeach
      </div><br>
   @endif

    <h1 class="msr_h103">ご来場アンケート</h1>
    <div class="msr_radio_01">
      @if($jigo_enq_open!="1")
           只今準備中です。しばらくお待ち下さい。<br>
           このページは{!! $back_second !!}秒後にトップに戻ります。
        <script>
            _top();
        </script>
      @elseif($kaitouzumi=="1")
           ご来場アンケートは回答済みです。<br>
           <br>
           ご回答にご協力いただき誠にありがとございました。<br>
           このページは{!! $back_second !!}秒後にトップに戻ります。
           <br>
           <ul class="Venue-btn" id="ans">
           <li class="Venue-btn01" onclick="location.href='./';"><a href="javascript:void(0);">トップに戻る</a></li>
           </ul>
        <script>
            _top();
        </script>
      @elseif($exec=="save" && $err_msg['0'] =='')
           ご来場アンケートご回答にご協力いただき誠にありがとございました。<br>
           このページは{!! $back_second !!}秒後にトップに戻ります。
           <br>
           <br>
           <ul class="Venue-btn" id="ans">
           <li class="Venue-btn01" onclick="location.href='./';"><a href="javascript:void(0);">トップに戻る</a></li>
           </ul>
        <script>
            _top();
        </script>
       @else 
          {{ $login['user_kigyou_name'] }}<br>
          {{ $login['user_name'] }} 様<br>
        「{{ $event_rec['event_name'] }}」のご来場アンケートにご協力ください。<br>
        <br>
        <form name="form_edit" action="./?page=answer" method="post" onSubmit="return false;">
        <input type="hidden" name="exec" value="save">

              <dl class="jigo_table">
              @foreach($questions as $field_name => $rec)
                  @if($rec['type'] != "sub_text")
                      @if(!$loop->first)
                          </dd>
                       @endif
                      <!-- 20250707　全てのdtのclassに「question」追加 -->
                      <!-- 20250707　ループ1つ目のdtのみ、classに「active」を追加する制御が必要 -->
                      <dt class="question @if($loop->first) active  @endif" style="font-size:20px;font-weight:bold;margin-bottom:15px;">

                          【設問{{ $rec['no'] }}】{{ $rec['question'] }}@if($rec['hissu_flg']=='1')&nbsp;<span style="color:red;">【必須】</span> @endif
                      </dt>
                      <dd>
                   @else 
                      <br />{{ $rec['question'] }}
                   @endif
                  @if($rec['type'] == "radio")
                      @foreach($rec['options'] as $k2 => $r2)
                          <input type="{!! $rec['type'] !!}" style="top:-4px;position:relative;" name={!! $field_name !!} value="{!! $k2 !!}"@if($row[$field_name]==$k2) checked="true"  @endif id="{!! $field_name !!}_{!! $loop->index !!}"><label for="{!! $field_name !!}_{!! $loop->index !!}"><span style="font-size:22px;">{{ $r2 }}</span></label><br>
                       @endforeach
                   @endif
                  @if($rec['type'] == "checkbox")
                      @foreach($rec['options'] as $k2 => $r2)
                          <input type="{!! $rec['type'] !!}" style="top:-4px;position:relative;" name={!! $field_name !!}[{!! $loop->index !!}] value="{!! $k2 !!}"@if($row[$field_name][$loop->index]==$k2) checked="true"  @endif id="{!! $field_name !!}_{!! $loop->index !!}"><label for="{!! $field_name !!}_{!! $loop->index !!}"><span style="font-size:22px;">{{ $r2 }}</span></label><br>
                       @endforeach
                   @endif

                  @if($rec['type'] == "text")
                  <input type=text name="{!! $field_name !!}" style="width:70%;" value="{{ $row[$field_name] }}" class="area">
                   @endif

                  @if($rec['type'] == "sub_text")
                  <br />
                  <textarea name="{!! $field_name !!}" style="width:70%;" class="area">{!! $row[$field_name] !!}</textarea>
                   @endif
                  <span class="page">{!! $loop->index + 1 !!}/{count($questions)}</span><!-- 20250707　全設問の何問目かを表示 -->           
                  @if($loop->last)
                      </dd>
                   @endif
               @endforeach
              </dl>
            <!--20250707　ulのclassに「Venue-btn_page」と「question」追加、文言を「上記内容で回答する」から「回答する」に変更 -->
            <ul class="Venue-btn Venue-btn_page question" id="ans">
                <li class="Venue-btn01" onclick="_ans();"><a href="javascript:void(0);">回答する</a></li>
            </ul>

            <!-- 20250707ナビゲーション追加ここから -->
            <div class="Prevnext-btn">
                <button id="prevBtn" disabled>戻る</button>
                <button id="nextBtn">次へ</button>
            </div>
            <!-- 20250707ナビゲーション追加ここまで -->
          </form>
       @endif
    </div>


</div>
<!--/#contents-->

<footer>

<div id="copyright">
<small>Copyright (c) NIPPON ACCESS, INC. All rights reserved.</small>
</div>

</footer>

<!-- 20250707script追加ここから -->
@verbatim
<script>

    const questions = document.querySelectorAll('.question');

    let current = 0;


    const prevBtn = document.getElementById('prevBtn');

    const nextBtn = document.getElementById('nextBtn');


    function showQuestion(index) {

        questions.forEach((q, i) => {

            q.classList.toggle('active', i === index);

        });

        prevBtn.disabled = index === 0;

        nextBtn.disabled = index === questions.length - 1;

    }


    prevBtn.addEventListener('click', () => {

        if (current > 0) {

            current--;

            showQuestion(current);

        }

    });


    nextBtn.addEventListener('click', () => {

        if (current < questions.length - 1) {

            current++;

            showQuestion(current);

        }
        else {
        }

    });

</script>
@endverbatim
<!-- 20250707script追加ここまで -->

</body>
</html>
