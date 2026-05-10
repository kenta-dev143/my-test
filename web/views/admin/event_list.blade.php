
<form name="search_form" action="./" method="post">
  <input type="hidden" name="page" value="{{ $page }}">
  <input type="hidden" name="exec" value="search">
</form>

<button type="button" onClick="location.href='?page=event_edit';" class="btn btn-primary btn-round">新規登録</button>

<div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="card-body">
        @if($count==0)
          <br><br>条件に合うデータがありませんでした。<br><br><br>
         @else 
          @include('page_navi')
          <div class="table-responsive">
            <table class="table">
              <thead class=" text-primary">
                <th style="width:300px;">
                  イベント名
                </th>
                <th style="width:200px;">
                  会場名
                </th>
                <th>
                  プルダウン表示
                </th>
                <th style="width:100px;">
                  URL用Key
                </th>
                <th style="width:200px;">
                  開催期間
                </th>
                <th style="width:200px;">
                  来場管理期間
                </th>
              </thead>
              <tbody>
              @foreach($main_recs as $rec)
                <tr>
                  <td>
                    {{ $rec['event_name'] }}
                  </td>
                  <td>
                    {{ $rec['event_kaijyou_name'] }}
                  </td>
                  <td style="text-align:center;">
                    @if($rec['event_pulldown_disp_flg']=="1")<span style="color:blue;">○</span> @else <span style="color:red;">×</span> @endif
                  </td>
                  <td>
                    {{ $rec['event_url_key'] }}
                  </td>
                  <td>
                    {{ $rec['event_kaisai_ymd_st'] }} 〜 {{ $rec['event_kaisai_ymd_ed'] }}
                  </td>
                  <td>
                    {{ $rec['event_raikainri_ymd_st'] }} 〜 {{ $rec['event_raikainri_ymd_ed'] }}
                  </td>
                  <td>
                  <button type="button" onClick="location.href='?page=event_edit&id={{ $rec['event_id'] }}';" class="btn btn-primary btn-round">詳細</button>
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

