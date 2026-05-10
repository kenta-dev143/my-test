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

<div class="text-right">
  @if($login['admin_master_kengen'] == 1)
  <button type="button" onClick="location.href='?page=company_request_approve';" class="btn btn-primary btn-round">承認処理</button>
   @endif
  <button type="button" onClick="location.href='?page=company_request_edit';" class="btn btn-primary btn-round">新規登録</button>
</div>

<div class="row">
  @if($err_msg['0'] !='')
  <div class="errArea">
    @foreach($err_msg as $msg)
    {{ $msg }}<br>
     @endforeach
  </div><br>
  @elseif($success_msg!="")
  <div class="successArea">{{ $success_msg }}</div>
  @elseif($flash_message!="")
  <div class="successArea">{{ $flash_message }}</div>
   @endif

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
                  申請日時
                </th>
                <th>
                  企業名
                </th>
                <th>
                  法人格
                </th>
                <th>
                  ステータス
                </th>
                <th>
                  却下理由
                </th>
              </thead>
              <tbody>
              @foreach($main_recs as $rec)
                <tr>
                  <td>
                    {!! blade_date_format($rec['tcr_insert_date'], "%Y年%m月%d日 %H:%M") !!}
                  </td>
                  <td>
                    {{ $rec['tcr_name'] }}
                  </td>
                  <td>
                    {{ $rec['tcr_legal_personality'] }}
                  </td>
                  <td>
                    @if($rec['tcr_status']==0)
                    <span class="rounded px-2 py-1" style="background: #F39C12; color: white">未承認</span>
                    @elseif($rec['tcr_status']==1)
                    <span class="rounded px-2 py-1" style="background: #2FCC71; color: white">承認済</span>
                    @elseif($rec['tcr_status']==2)
                    <span class="rounded px-2 py-1" style="background: #E74C3D; color: white">却下</span>
                     @endif
                  </td>
                  <td>
                    {{ $rec['tcr_reject_reason'] }}
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

