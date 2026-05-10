<div class="row">
  <div class="col-md-12">
    @if($err_msg['0'] !='')
      <div class="errArea">
        @foreach($err_msg as $msg)
          {{ $msg }}<br>
         @endforeach
      </div><br>
    @elseif($success_msg!="")
      <div class="successArea">{{ $success_msg }}</div>
     @endif

  <div class="row">
    <div class="col-md-12">
      <div class="card card-user" style="padding:20px;">
        <span style="font-weight:bold;">取り込み結果</span>
        <hr>
        @foreach($line_err as $err)
          <span style="color:red;">{{ $err }}</span>
         @endforeach
        {{ $success_msg }}
      </div><!-- <div class="card card-user"> -->
    </div><!-- <div class="col-md-12"> -->
  </div><!-- <div class="row"> -->

    <div class="card card-user" style="padding:5px;">
      <table>
        <tr>
          <td style="vertical-align:top;">
              <br>
              <span style="font-weight:bold;">来場者マスタ一括登録・編集</span>
              <br>
              {!! $syoutai_layout !!}
          </td>
        </tr>

        <tr>
          <td style="vertical-align:top;">
            <hr>
            <form name="upload_form" action="./" method="post" enctype="multipart/form-data">
              <input type="hidden" name="page" value="{{ $page }}">
              <input type="hidden" name="exec" value="syoutai_csv_upload">

              <span class="text-danger">編集用CSVは来場者マスタの「一括編集用フォームダウンロード」からDLしてください。<br>【注意事項】一括での編集を行う際は、ご自身の担当単位でのダウンロード・編集・アップロードをお願い致します 。</span>
              <p>支店・部・課レベルでダウンロード・編集を行い、アップロードを実施すると後優先で更新されます。 <br>編集されていない事項が上書きされ、編集済の情報が初期値へ戻る可能性があります。</p>

              <!-- ***************************** -->
              <div class="row">
                <div class="col-md-4 pl-1">
                  <div class="msr_text_01">
                    <label>CSVファイル</label>
                    <input type="file" name="csv_file">
                  </div>
                </div>
              </div>

              <div class="row">
                <button type="submit" class="btn btn-primary-S btn-round" style="width:340px;">CSVアップロード</button>
              </div>
              <!-- ***************************** -->

            </form>
          </td>
        </tr>
      </table>
    </div><!-- <div class="card card-user"> -->
  </div><!-- <div class="col-md-12"> -->
</div><!-- <div class="row"> -->

