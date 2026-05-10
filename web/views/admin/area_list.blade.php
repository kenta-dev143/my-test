
<form name="search_form" action="./" method="post">
  <input type="hidden" name="page" value="{{ $page }}">
  <input type="hidden" name="exec" value="search">
</form>

@if($select_event_id!="")
  <button type="button" onClick="location.href='?page=area_edit';" class="btn btn-primary btn-round">新規登録</button>
 @endif

<div class="row">
  <div class="col-md-12">
    <div class="card">
      @if($select_event_id!="")
        <div class="card-body">
          @if($count==0)
            <br><br>条件に合うデータがありませんでした。<br><br><br>
           @else 
            @include('page_navi')
            <div class="table-responsive">
              <table class="table">
                <thead class=" text-primary">
                  <th style="width:300px;">
                    エリア名
                  </th>
                  <th>
                    最大人数
                  </th>
                  <th style="width:200px;">
                    端末配置
                  </th>
                </thead>
                <tbody>
                @foreach($main_recs as $rec)
                  <tr>
                    <td>
                      {{ $rec['area_name'] }}
                    </td>
                    <td>
                      {{ $rec['area_max'] }}
                    </td>
                    <td>
                      {{ $rec['area_tanmatsuhaichi_disp'] }}
                    </td>
                    <td>
                    <button type="button" onClick="location.href='?page=area_edit&id={{ $rec['area_id'] }}';" class="btn btn-primary btn-round">詳細</button>
                    </td>
                  </tr>
                 @endforeach
                </tbody>
              </table>
            </div>
            @include('page_navi')
           @endif
        </div>
       @else 
        @include('parts.select_event')
       @endif

    </div>
  </div>
</div>

