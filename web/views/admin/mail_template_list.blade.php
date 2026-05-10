
<form name="search_form" action="./" method="post">
  <input type="hidden" name="page" value="{{ $page }}">
  <input type="hidden" name="exec" value="search">
</form>

<button type="button" onClick="location.href='?page=mail_template_edit';" class="btn btn-primary btn-round" style="width:300px;">ユーザーテンプレート新規登録</button>

<div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="card-body">
        @if($count==0)
          <br><br>条件に合うデータがありませんでした。<br><br><br>
         @else 
          <!-- {{-- @include('page_navi') --}} -->
          <div class="table-responsive">
            <table class="table">
              <thead class=" text-primary">
                <th style="width:400px;">
                  メールテンプレート名
                </th>
                <th style="width:600px;">
                  件名
                </th>
                <th>
                  ﾃﾝﾌﾟﾚｰﾄ<br>種類
                </th>
              </thead>
              <tbody>
              @foreach($main_recs as $rec)
                <tr>
                  <td>
                    {{ $rec['mailt_name'] }}
                  </td>
                  <td>
                    {{ $rec['mailt_subject'] }}
                  </td>
                  <td>
                    @if($rec['mailt_delete_fuka']=="1")
                      @if($rec['mailt_system_use_only']=="1")
                        <span style="color:red;">ｼｽﾃﾑ<br>ﾃﾝﾌﾟﾚｰﾄ</span>
                       @else 
                        <span style="color:blue;">ｼｽﾃﾑ<br>ﾃﾝﾌﾟﾚｰﾄ<br>(ﾕｰｻﾞｰ利用可)</span>
                       @endif
                     @else 
                      ﾕｰｻﾞｰ作成<br>ﾃﾝﾌﾟﾚｰﾄ
                     @endif
                  </td>
                  <td>
                    <button type="button" onClick="location.href='?page=mail_template_edit&mailt_key={{ $rec['mailt_key'] }}';" class="btn btn-primary btn-round">詳細</button>
                  </td>
                </tr>
               @endforeach
              </tbody>
            </table>
          </div>
          <!-- {{-- @include('page_navi') --}} -->
         @endif
      </div><!-- <div class="card-body"> -->
    </div><!-- <div class="card"> -->
  </div><!-- <div class="col-md-12"> -->
</div><!-- <div class="row"> -->

