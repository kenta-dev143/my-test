@verbatim
<script>
    function _regist(_mode) {
        document.form_edit.mode.value = _mode;
        document.form_edit.exec.value = 'save';
        document.form_edit.submit();
    }

    function _csv() {
        document.form_edit.exec.value = 'csv_download';
        document.form_edit.submit();
    }

</script>
@endverbatim

<div class="text-right">
    <button type="button" class="btn btn-primary btn-round" onClick="_csv();">
        承認履歴
    </button>
</div>


<div class="row">
    <h6>新規企業承認</h6>

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

</div>
<div class="row">
    <div class="col p-3 bg-light border-left border-primary rounded mr-2" style="border-left-width: 3px !important;">
        <div class="border-bottom mb-4">
            <p class="mb-0">
                申請ID：{{ $rec['tcr_id'] }}
            </p>
            <p class="text-right mb-0">
                申請者：{{ $admin_rec['admin_name'] }}
            </p>
            <p class="mb-0">
                申請日時：{!! blade_date_format($rec['tcr_insert_date'], "%Y年%m月%d日 %H:%M") !!}
            </p>
            <p class="text-right">
                所属：{{ $admin_rec['syozoku_name'] }}
            </p>
        </div>

        <h6>申請内容</h6>

        <div class="my-2">
            類似度の高い企業が既に登録されている可能性があります。右側の類似企業のリストを確認してください。
        </div>

        <div class="my-2">
            <label>企業名</label>
            <p class="rounded border bg-white p-1">{{ $rec['tcr_full_name'] }}&nbsp;</p>
        </div>

        <div class="my-2">
            <label>登録区分</label>
            <p class="rounded border bg-white p-1">{{ $_conf_big_cate[$rec['tcr_big_cate']] }}&nbsp;</p>
        </div>

        <div class="my-2">
            <label>住所</label>
            <p class="rounded border bg-white p-1">{{ $rec['tcr_address'] }}&nbsp;</p>
        </div>

        <div class="my-2">
            <label>電話番号</label>
            <p class="rounded border bg-white p-1">{{ $rec['tcr_tel'] }}&nbsp;</p>
        </div>

        <div class="my-2">
            <label>ホームページ</label>
            <p class="rounded border bg-white p-1">{{ $rec['tcr_url'] }}&nbsp;</p>
        </div>

        <div class="my-2">
            <label>申請者備考</label>
            <p class="rounded border bg-white p-1">{{ $rec['tcr_memo'] }}&nbsp;</p>
        </div>
    </div>
    <div class="col p-3 bg-light rounded ml-2" style="border-left-width: 3px !important;">
        <h6>類似企業検索結果</h6>
        企業名「{{ $rec['tcr_full_name'] }}」に類似した既存企業

        <div class="my-3" style="max-height: 400px; overflow-y: scroll;">
            @if(count($similarity_companies)==0)
            <br><br>条件に合うデータがありませんでした。<br><br><br>
             @else 
            @foreach($similarity_companies as $company)
            <div class="p-3 bg-light border-left border-danger rounded m-2" style="border-left-width: 3px !important;">
                <h6>{{ $company['company_name'] }}</h6>
                WEB登録区分: {!! $_conf_big_cate[$company['company_big_cate']] !!}<br/>
                登録日: {!! blade_date_format($company['company_insert_date'], "%Y年%m月%d日 %H:%M") !!}
            </div>
             @endforeach
             @endif
        </div>

        <form name="form_edit" action="./?page={{ $page }}" method="post" onSubmit="return false;">
            <input type="hidden" name="exec" value="save">
            <input type="hidden" name="mode">
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="id" value="{{ $id }}">
            <div class="my-2">
                <label>差し戻し理由</label>
                <textarea name="reject_reason" class="w-100 p-2" style="min-height: 100px;"
                          placeholder="差し戻し理由を入力してください">{{ $url }}</textarea>
            </div>
        </form>
    </div>
</div>
<div class="row my-2 py-4 bg-light">
    <div class="col">
        @if(isset($rec['tcr_id']))
        <a class="rounded px-3 py-2" style="background: #3ACC72; color: white"
           href="javascript:_regist('approve');void(0);">承認</a>
        <a class="rounded px-3 py-2" style="background: #E74C3C; color: white"
           href="javascript:_regist('reject');void(0);">却下</a>
         @endif
    </div>
    @if($next_id !== "")
    <div class="col text-right">
        <a class="rounded px-3 py-2" style="background: #3498DB; color: white" href="?page=company_request_approve&id={{ $next_id }}">次の申請へ-></a>
    </div>
     @endif
</div>
