<script>
var select_event_id = '{{ $select_event_id }}';

</script>

　<br>
  @if($err_msg['0'] !='')
    <div class="errArea">
      @foreach($err_msg as $msg)
        {{ $msg }}<br>
       @endforeach
    </div><br>
  @elseif($success_msg!="")
    <div class="successArea">{{ $success_msg }}</div>
   @endif

    <div class="col-md-12">
      <div class="card">
        <br><br><br><br>
        　　　　　送信予約完了
        <br><br><br><br><br>
        <button type="button" style="width:250px;margin-left:60px;" class="btn btn-primary btn-round" onClick="location.href='?page=user_mail_send_list';">一覧へ戻る</button>
      </div>
    </div>
