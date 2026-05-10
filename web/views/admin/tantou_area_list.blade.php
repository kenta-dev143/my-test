
<form name="search_form" action="./" method="post">
  <input type="hidden" name="page" value="{{ $page }}">
  <input type="hidden" name="exec" value="search">
</form>

<button type="button" onClick="location.href='?page=tantou_area_edit';" class="btn btn-primary btn-round">新規登録</button>

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
                <!--
                <th>
                  担当エリアID
                </th>
                -->
                <th>
                  担当エリア名
                </th>
              </thead>
              <tbody>
              @foreach($main_recs as $rec)
                <tr>
                  <!--
                  <td>
                    {{ $rec['tanarea_id'] }}
                  </td>
                  -->
                  <td>
                    {{ $rec['tanarea_name'] }}
                  </td>
                  <td>
                  <button type="button" onClick="location.href='?page=tantou_area_edit&id={{ $rec['tanarea_id'] }}';" class="btn btn-primary btn-round">詳細</button>
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

