<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>{{ $event_rec['event_name'] }}-代理登録対象者一覧</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="../../css/bootstrap.min.css" rel="stylesheet" />
<link rel="stylesheet" href="../css/style.css">


@verbatim
<script>
function qr_disp(uid,eid){
  var url = '../../qr_print.pdf?user_id='+uid+'&event_id='+eid;
  location.href = url;
}

function qr_send(uid){
    if(window.confirm('QRコードURLを記載したメールを送信します。')){
        var url = '?page=agent_user_list&exec=qr_send&user_id=' + uid;
        location.href = url;
    }
}

function _gotoDetail(userId) {
    var url = '?page=agent_user_edit&user_id='+userId;
    location.href = url;
}
</script>
@endverbatim

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

    <h1 class="msr_h103">代理登録対象者一覧</h1>
    <div class="row">
        @if(count($user_recs) === 0)
        代理登録対象者はいません。
         @else 
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <p class="table_txt">左右にスクロールしてください</p>
                        <div class="scroll">
                        <table class="table">
                            <thead class="text-primary-S">
                            <tr>
                                <th>氏名</th>
                                <th>アドレス</th>
                                <th class="text-center">QRコード</th>
                                <th class="text-center">QRメール再送</th>
                                <th class="text-center">編集</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($user_recs as $rec)
                            <tr>
                                <td>
                                    {{ $rec['user_name'] }}
                                </td>
                                <td>
                                    {{ $rec['user_login_id'] }}
                                </td>
                                <td class="text-center">
                                    <button type="button" onClick="qr_disp('{{ $rec['user_id'] }}','{{ $event_rec['event_id'] }}');void(0);" class="btn btn-primary-S btn-round">ダウンロード</button>
                                </td>
                                <td class="text-center">
                                    <button type="button" onClick="qr_send('{{ $rec['user_id'] }}');void(0);" class="btn btn-primary-S btn-round">送信</button>
                                </td>
                                <td class="text-center">
                                    <!-- <{{-- button type="button" onClick="location.href='?page=syoutai_edit&id={{ $rec['syoutai_id'] }}&from_page={{ $page }}';" class="btn btn-primary-S btn-round">詳細</button> --}} -->
                                    <button type="button" onClick="_gotoDetail('{{ $rec['user_id'] }}');" class="btn btn-primary-S btn-round">編集</button>
                                </td>
                            </tr>
                             @endforeach
                            </tbody>
                        </table>
                        </div>
                    </div>
                </div>
            </div><!-- <div class="card"> -->
        </div><!-- <div class="col-md-12"> -->
         @endif
    </div><!-- <div class="row"> -->

</div>
<!--/#contents-->

<footer>

<div id="copyright">
<small>Copyright (c) NIPPON ACCESS, INC. All rights reserved.</small>
</div>

</footer>

</body>
</html>
