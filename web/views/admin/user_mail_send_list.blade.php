
<form name="search_form" action="./" method="post">
  <input type="hidden" name="page" value="{{ $page }}">
  <input type="hidden" name="mailhd_id" value="">
  <input type="hidden" name="exec" value="now_send">
</form>

<button type="button" onClick="location.href='?page=user_mail_send';" style="width:200px;" class="btn btn-primary btn-round">新規送信予約</button>

<div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="card-body">
        @if($count==0)
          <br><br>データがありませんでした。<br><br><br>
         @else 
          @include('page_navi')
          <div class="table-responsive">
            <table class="table">
              <thead class=" text-primary">
                <th>
                  予約日時
                </th>
                <th>
                  登録者
                </th>
                <th>
                  メール種類(メールテンプレート)
                </th>
                <th>
                  送信対象件数
                </th>
                <th>
                  ステータス
                </th>
              </thead>
              <tbody>
              @foreach($main_recs as $rec)
                <tr>
                  <td>
                    @if($rec['mailhd_yoyaku_ymdhi']=="2099/12/31 23:59")
                      <span style="color:red;">未設定</span>　<input type="button" value="今すぐ送信する" onClick="document.search_form.mailhd_id.value='{{ $rec['mailhd_id'] }}';document.search_form.submit();">
                     @else 
                      {{ $rec['mailhd_yoyaku_ymdhi'] }}
                     @endif
                  </td>
                  <td>
                    {{ $rec['syozoku_name'] }}<br>
                    {{ $rec['admin_name'] }}
                  </td>
                  <td>
                    {{ $rec['mailhd_mailt_name'] }}
                  </td>
                  <td>
                    {!! $rec['detail_cnt'] !!}
                  </td>
                  <td>
                    @if($rec['mailhd_status']==0)
                      送信実行待ち
                    @elseif($rec['mailhd_status']==1)
                      <span style="color:green;">送信中</span>
                    @elseif($rec['mailhd_status']==2)
                      <span style="color:blue;">送信完了</span>
                    @elseif($rec['mailhd_status']==9)
                      <span style="color:blue;">送信エラー</span>
                     @endif
                  </td>
                </tr>
               @endforeach
              </tbody>
            </table>
          </div>
          @include('page_navi')
         @endif
      </div>
    </div>
  </div>
</div>

