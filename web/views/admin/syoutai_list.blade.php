@verbatim
<script>

function _winAdminOpen(){
  var event_id = $('select[name="join_event_id"]').val();
  if (event_id == ''){
    alert("招待した担当者による検索をする場合は、イベントを選択してください。");
    return;
  }

  // window.open( './index.php?page=admin_list_select_win&select_event_id='+event_id+'&syoutai_only=1','admin_list_select_win' ,'width=1750, height=800, menubar=no, toolbar=no, scrollbars=yes' );
  modalDialogOpen( './index.php?page=admin_list_select_win&select_event_id='+event_id+'&syoutai_only=1' );
}

function _syozokuClcik(){
  var event_id = $('select[name="join_event_id"]').val();
  if (event_id == ''){
    alert("招待した所属グループによる検索をする場合は、イベントを選択してください。");
    return;
  }
}

function _adminSelect(id,busyo,name){
    $('input[name="join_admin_id"]').val(id);
    $('#tantousya_lbl').html('<a href="javascript:_tantousyaDel();void(0);" style="color:red;">[×]</a> '+busyo+' '+name+' が担当');
}

function _tantousyaDel(){
  $('input[name="join_admin_id"]').val('');
  $('#tantousya_lbl').text('');
}

function _regist(exec){
  var admin_id = $('input[name="join_admin_id"]').val();
  var event_id = $('select[name="join_event_id"]').val();
  var syozoku_id = $('select[name="admin_syozoku_id"]').val();

  if ( admin_id != '' && event_id == ''){
    alert("招待した担当者による検索をする場合は、イベントを選択してください。");
    return;
  }else if ( syozoku_id != '' && event_id == ''){
    alert("招待した所属グループによる検索をする場合は、イベントを選択してください。");
    return;
  }

  document.search_form.exec.value=exec;
  document.search_form.submit();
}

function _syoutai_delete() {
  let count = $('input[name="syoutai_delete_ids[]"]:checked').length;
  if (count <= 0) {
    alert("削除対象を選択してください。");
  } else {
    if (confirm('選択した来場者を削除しますか？')) {
      document.syoutai_delete.submit();
    }
  }
}

function _toggleAllCheck(obj) {
  $('input[name="syoutai_delete_ids[]"]').prop("checked", obj.checked);
}

</script>
@endverbatim
  @if($err_msg['0'] !='')
      <div class="errArea">
          @foreach($err_msg as $msg)
              {{ $msg }}<br>
           @endforeach
      </div><br>
  @elseif($success_msg!="")
      <div class="successArea">{{ $success_msg }}</div>
  @elseif($delete_msg!="")
      <div class="successArea">{{ $delete_msg }}</div>
   @endif

  <div class="row">
    <div class="col-md-12">
      <div class="card card-user">
        <form name="search_form" action="./" method="post" onSubmit="return false;">
          <input type="hidden" name="page" value="{{ $page }}">
          <input type="hidden" name="exec" value="">

          <!-- ***************************** -->
          <div class="row">
            <div class="col-md-4 pl-1">
              <div class="msr_pulldown_01">
                <label>VIP</label><br>
                <select name="syoutai_vip_flg" style="width: 100% !important;">
                  <option value="">選択してください</option>
                  {!! blade_html_options(['options' => $_conf_vip, 'selected' => $search_condition['syoutai_vip_flg']]) !!}
                </select>
              </div>
            </div>
            <div class="col-md-4 pl-1">
              <div class="msr_pulldown_01">
                <label>大分類（業種）</label>
                <select name="syoutai_big_cate[]" style="width: 100% !important;" multiple="multiple" class="select-multiple">
                  <option value="">選択してください</option>
                  {!! blade_html_options(['options' => $_conf_big_cate, 'selected' => $search_condition['syoutai_big_cate']]) !!}
                </select>
                <p class="small">※Ctrlキーで複数選択できます</p>
              </div>
            </div>
            <div class="col-md-4 pl-1">
              <div class="msr_pulldown_01">
                <label>中分類（業態）</label>
                <select name="syoutai_mid_cate[]" style="width: 100% !important;" multiple="multiple" class="select-multiple">
                  <option value="">選択してください</option>
                  {!! blade_html_options(['options' => $_conf_mid_cate, 'selected' => $search_condition['syoutai_mid_cate']]) !!}
                </select>
                <p class="small">※Ctrlキーで複数選択できます</p>
              </div>
            </div>
          </div>
          <!-- ***************************** -->

          <div class="row">

            <div class="col-md-4 pl-1">
            <div class="msr_text_01">
              <label>企業名(マスタ)</label>

              <!-- {{-- ****************** 企業フィルター ****************** --}} -->
              <input type="text" name="company_filter" placeholder="選択肢をフィルター" onKeyup="companyFilter();">
              <select id="hidden_company_id_select" style="display:none;">
                <option value="">選択してください</option>
                {!! blade_html_options(['options' => $_conf_company, 'selected' => $search_condition['syoutai_company_id']]) !!}
              </select>
              @verbatim
              <script>
                function companyFilter(){
                  var filter = $('input[name="company_filter"]').val();
                  if(filter==''){
                    $('select[name="syoutai_company_id"]').html( $("#hidden_company_id_select").html() );
                  }else{
                    $('select[name="syoutai_company_id"]').html('');
                    $('select[name="syoutai_company_id"]').append($("<option>").val('').text( '選択してください' ));
                    $("#hidden_company_id_select option").each(function(i){
                      if($(this).text().indexOf(filter) != -1){
                        $('select[name="syoutai_company_id"]').append($("<option>").val($(this).val()).text( $(this).text() ));
                      }
                    });
                  }
                }
              </script>
              @endverbatim
              <!-- {{-- ****************** /企業フィルター ****************** --}} -->
              <br>
              <select name="syoutai_company_id" style="width:460px;">
                <option value="">選択してください</option>
                {!! blade_html_options(['options' => $_conf_company, 'selected' => $search_condition['syoutai_company_id']]) !!}
              </select>

            </div>
            </div>

            <div class="col-md-4 pl-1">
              <div class="msr_text_01">
                <label>企業名</label>
                <input type="text" name="syoutai_kigyou_name" value="{{ $search_condition['syoutai_kigyou_name'] }}" style="width:50%;" />を含む
              </div>
            </div>

            <div class="col-md-4 pl-1">
              <div class="msr_text_01">
                <label>企業名カナ</label>
                <input type="text" name="syoutai_kigyou_name_kana" value="{{ $search_condition['syoutai_kigyou_name_kana'] }}" style="width:50%;" />を含む
              </div>
            </div>

            <!-- {{-- <div class="col-md-4 pl-1">
              <div class="msr_text_01">
                <label>SANSANID</label>
                <input type="text" name="syoutai_sansan_id" value="{{ $search_condition['syoutai_sansan_id'] }}" style="width:50%;" />を含む
              </div>
            </div> --}} -->
          </div>

          <!-- ***************************** -->
          <div class="row">
            <div class="col-md-4 pl-1">
              <div class="msr_text_01">
                <label>氏名</label>
                <input type="text" name="syoutai_name" value="{{ $search_condition['syoutai_name'] }}"  style="width:50%;"/>を含む
              </div>
            </div>

            <div class="col-md-4 pl-1">
              <div class="msr_text_01">
                <label>氏名カナ</label>
                <input type="text" name="syoutai_name_kana" value="{{ $search_condition['syoutai_name_kana'] }}"  style="width:50%;"/>を含む
              </div>
            </div>

            <div class="col-md-4 pl-1">
              <div class="msr_text_01">
                <label>メールアドレス(PC)</label>
                <input type="text" name="syoutai_mail" value="{{ $search_condition['syoutai_mail'] }}" style="width:50%;" />を含む
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-4 pl-1">
              <div class="msr_text_01">
                <label>役職</label>
                <input type="text" name="syoutai_yakusyoku" value="{{ $search_condition['syoutai_yakusyoku'] }}" style="width:50%;"/>を含む
                <p class="small">※スペース区切りでOR検索が行えます</p>
              </div>
            </div>
            <div class="col-md-4 pl-1">
              <div class="msr_text_01">
                <label>タグ文字列（来場者マスタ）</label>
                <input type="text" name="syoutai_tag" value="{{ $search_condition['syoutai_tag'] }}" style="width:50%;"/>を含む
              </div>
            </div>
          </div>

          <!-- ***************************** -->
          <div class="row">
            <div class="col-md-4 pl-1">
              <div class="msr_pulldown_01" style="width:400px;">
                <label>招待したイベントと担当者と所属</label><br>
                <select name="join_event_id" style="width:300px;">
                  <option value="">選択してください</option>
                  {!! blade_html_options(['options' => $_conf_join_event, 'selected' => $search_condition['join_event_id']]) !!}
                </select><br>

                <label>招待した担当者<span class="searchBtn" style="margin-left:10px;" onClick="_winAdminOpen();"></span></label>
                <span id="tantousya_lbl">@if($search_condition['admin_name']!="")<a href="javascript:_tantousyaDel();void(0);" style="color:red;">[×]</a> {{ $search_condition['syozoku_name'] }}&nbsp;{{ $search_condition['admin_name'] }} が担当 @endif</span>
                <input type="hidden" name="join_admin_id" value="{{ $search_condition['join_admin_id'] }}">

                <br>
                <table>
                  <tr>
                    <td style="width:90px;vertical-align:top;">
                      <label>招待した所属<br>所属グループ</label>
                    </td>
                    <td style="vertical-align:top;">
                      <input type="text" name="syozoku_filter" placeholder="選択肢をフィルター" onKeyup="syozokuFilter();">
                      <!-- {{-- ****************** 所属フィルター ****************** --}} -->
                      <select id="hidden_syozoku_id_select" style="display:none;">
                        <option value="">選択してください</option>
                        {!! blade_html_options(['options' => $_conf_syozoku, 'selected' => $search_condition['admin_syozoku_id']]) !!}
                      </select>
                      @verbatim
                      <script>
                        function syozokuFilter(){
                          var filter = $('input[name="syozoku_filter"]').val();
                          if(filter==''){
                            $('select[name="admin_syozoku_id"]').html( $("#hidden_syozoku_id_select").html() );
                          }else{
                            $('select[name="admin_syozoku_id"]').html('');
                            $('select[name="admin_syozoku_id"]').append($("<option>").val('').text( '選択してください' ));
                            $("#hidden_syozoku_id_select option").each(function(i){
                              if($(this).text().indexOf(filter) != -1){
                                $('select[name="admin_syozoku_id"]').append($("<option>").val($(this).val()).text( $(this).text() ));
                              }
                            });
                          }
                        }
                      </script>
                      @endverbatim
                      <!-- {{-- ****************** /所属フィルター ****************** --}} -->
                      <select name="admin_syozoku_id" style="width:300px;" onClick="_syozokuClcik();">
                        <option value="">選択してください</option>
                        {!! blade_html_options(['options' => $_conf_syozoku, 'selected' => $search_condition['admin_syozoku_id']]) !!}
                      </select><br>の所属グループ
                    </td>
                  </tr>
                </table>

              </div>
            </div>

            <div class="col-md-4 pl-1">
              <div class="msr_pulldown_01">
                <label>招待していないイベント</label>
                <select name="not_join_event_id" style="width:300px;">
                  <option value="">選択してください</option>
                  {!! blade_html_options(['options' => $_conf_join_event, 'selected' => $search_condition['not_join_event_id']]) !!}
                </select>
              </div>
            </div>


            <div class="col-md-4 pl-1">
              <div class="msr_pulldown_01">
                <label>並び順</label>
                <select name="order_by" style="width: 100% !important;">
                  <option value="">選択してください</option>
                  {!! blade_html_options(['options' => $order_by_arr, 'selected' => $search_condition['order_by']]) !!}
                </select>
              </div>
            </div>

          </div>

          <div class="row">
            <div class="update ml-auto">
              <button type="button" class="btn btn-primary-S btn-round" onClick="_regist('search');">検　索</button>
              <button type="button" class="btn btn-primary-S btn-round" onClick="location.href='?page=syoutai_list';">リセット</button>
              @if($login['admin_master_kengen'] == 1)
              <button type="button" class="btn btn-primary-S btn-round" style="width:300px;" onClick="_regist('csv_download');">上記条件でCSVダウンロード</button>                　　
               @endif
            </div>
          </div>
        </form>
      </div><!-- <div class="card card-user"> -->
    </div><!-- <div class="col-md-12"> -->
  </div><!-- <div class="row"> -->

  @if($login['admin_kyouryoku_kigyou_flg'] != 1)
  <button type="button" class="btn btn-primary-S btn-round" style="width:340px;" onClick="_regist('csv_edit_download');">【一括編集用】フォームダウンロード</button>
  <button type="button" onClick="location.href='./?page={!! $page !!}&exec=syoutai_form_download';" class="btn btn-primary-S btn-round" style="width:380px;">【一括新規登録用】フォームダウンロード</button>
  <button type="button" onClick="location.href='?page=syoutai_ikkatsu_hensyuu';" class="btn btn-primary-S2 btn-round" style="width:400px;">【一括登録・編集用】CSVアップロード</button>
   @else 
  <button type="button" class="btn btn-primary-S btn-round" style="width:340px;" onClick="_regist('csv_edit_download');">【一括編集用】フォームダウンロード</button>
<button type="button" onClick="location.href='?page=syoutai_ikkatsu_hensyuu';" class="btn btn-primary-S2 btn-round" style="width:400px;">【一括登録・編集用】CSVアップロード</button>
   @endif
  <button type="button" onClick="location.href='?page=syoutai_edit&from_page={{ $page }}';" class="btn btn-primary-S btn-round">新規登録</button>
  <button type="button" onClick="_syoutai_delete();" class="btn btn-danger btn-round" style="width: 200px;">来場者一括削除</button>
  <!--  　s
  <button type="button" onClick="location.href='?page=syoutai_ikkatsu_touroku';" class="btn btn-primary-S btn-round" style="width:200px;">一括新規登録</button>
-->

  <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-body">
            @if($count==0)
              <br><br>条件に合うデータがありませんでした。<br><br><br>
             @else 
              @include('page_navi_syoutai')
              <div class="table-responsive">
                <table class="table">
                  <thead class="text-primary-S">
                    <tr>
                      <th><input type="checkbox" id="all_check" onclick="_toggleAllCheck(this);"/><label for="all_check">削除</label></th>
                      <th>大分類</th>
                      <th>企業名</th>
                      <th>部署</th>
                      <th>役職</th>
                      <th>氏名</th>
                      <th style="padding-left:25px;">　 詳細</th>
                    </tr>
                  </thead>
                  <tbody>
                  <form name="syoutai_delete" action="./" method="post" onsubmit="return false;">
                    <input type="hidden" name="page" value="{{ $page }}">
                    <input type="hidden" name="exec" value="syoutai_delete">
                  @foreach($main_recs as $rec)
                    <tr>
                      <td>
                        <input type="checkbox" class="largechkbox" id="syoutai_delete_flg" name="syoutai_delete_ids[]" value="{!! $rec['syoutai_id'] !!}" />
                      </td>
                      <td>
                        {{ $rec['disp_big_cate'] }}
                      </td>
                      <td>
                        {{ $rec['syoutai_company_name'] }}
                      </td>
                      <td>
                        {{ $rec['syoutai_busyo'] }}
                      </td>
                      <td>
                        {{ $rec['syoutai_yakusyoku'] }}
                      </td>
                      <td>
                        {{ $rec['syoutai_name'] }}
                      </td>
                      <td>
                      <button type="button" onClick="location.href='?page=syoutai_edit&id={{ $rec['syoutai_id'] }}&from_page={{ $page }}';" class="btn btn-primary-S btn-round">詳細</button>
                      </td>
                    </tr>
                   @endforeach
                  </form>
                  </tbody>
                </table>
              </div>
              @include('page_navi_syoutai')
             @endif
          </div>
        </div>
      </div>
    </div>