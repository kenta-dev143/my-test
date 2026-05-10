@verbatim
<script>
function _adminSelect( id,busyo,name,set_pattern ="1" ){
  if (set_pattern == ""){
    window.parent._adminSelect(id,busyo,name);
  }else{
    window.parent._adminSelect2(id,busyo,name);
  }
  window.parent.$('#tantousya_modal').dialog( 'close' );
}

function _regist(exec){
  document.search_form.exec.value=exec;
  document.search_form.submit();
}

</script>
@endverbatim


<div class="row">
  <div class="col-md-12">
    <div class="card card-user">
      <form name="search_form" action="./" method="post">
        <input type="hidden" name="page" value="{{ $page }}">
        <input type="hidden" name="exec" value="search">
        <input type="hidden" name="set_pattern" value="{{ $set_pattern }}">

        <br>

        <div class="row">
          <div class="col-md-4 pl-1">
            <div class="msr_text_01">
              <label>企業ID</label>
              <input type="text" name="company_id" value="{{ $search_condition['company_id'] }}" style="width:50%;" />
            </div>
          </div>

          <div class="col-md-4 pl-1">
            <div class="msr_text_01">
              <label>企業名</label>
              <input type="text" name="company_name" value="{{ $search_condition['company_name'] }}"  style="width:50%;"/>を含む
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-4 pl-1">
            <div class="msr_text_01">
              <label>並び順</label>
              <select name="order_by">
                <option value="">選択してください</option>
                {!! blade_html_options(['options' => $order_by_arr, 'selected' => $search_condition['order_by']]) !!}
              </select>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="update ml-auto mr-auto">
            <button type="button" class="btn btn-primary btn-round" onClick="_regist('search');">検　索</button>
            @if($login['admin_master_kengen'] == 1)                　　
            <button type="button" class="btn btn-primary btn-round" style="width:300px;" onClick="_regist('csv_download');">上記条件でCSVダウンロード</button>                　　
             @endif
            <button type="button" class="btn btn-primary btn-round" onClick="location.href='?page={{ $page }}';">リセット</button>
            <button type="button" class="btn btn-primary btn-round" style="width: 150px;" onClick="location.href='?page={{ $page }}&exec=none_update';">企業割り当て</button>
          </div>
        </div>

      </form>
    </div><!-- <div class="card card-user"> -->
  </div><!-- <div class="col-md-12"> -->
</div><!-- <div class="row"> -->

<button type="button" onClick="location.href='?page=company_edit';" class="btn btn-primary btn-round">新規登録</button>
<button type="button" onClick="location.href='?page=company_ikkatsu_touroku';" class="btn btn-primary btn-round" style="width:200px;">一括新規登録</button>

<div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="card-body">
        @if($count==0)
          <br><br>条件に合うデータがありませんでした。<br><br><br>
         @else 
          @include('page_navi')
          <div class="table-responsive">
            <table class="table table2">
              <thead class=" text-primary">
                <th>
                  ID
                </th>
                <th>
                  企業名
                </th>
                <th>
                  表示名
                </th>
                <th>
                  企業名カナ
                </th>
                <th>
                  WEB登録区分
                </th>
                <th>
                  DAISY
                </th>
                <th>
                  WEB展示会
                </th>
              </thead>
              <tbody>
              @foreach($main_recs as $rec)
                <tr>
                  <td>
                    {{ $rec['company_id'] }}
                  </td>
                  <td>
                    {{ $rec['company_name'] }}
                  </td>
                  <td>
                    {{ $rec['company_display_name'] }}
                  </td>
                  <td>
                    {{ $rec['company_name_kana'] }}
                  </td>
                  <td>
                    {{ $big_cate[$rec['company_big_cate']] }}
                  </td>
                  <td>
                    {{ $rec['company_daisy'] }}
                  </td>
                  <td>
                    {{ $rec['company_web_showcases'] }}
                  </td>
                  <td>
                    {{ $rec['admin_master_kengen_disp'] }}
                  </td>
                  <td>
                  <button type="button" onClick="location.href='?page=company_edit&id={{ $rec['company_id'] }}';" class="btn btn-primary btn-round">詳細</button>
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

