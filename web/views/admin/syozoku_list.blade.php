
@verbatim
<script>
  function _regist(exec){
    document.search_form.exec.value=exec;
    document.search_form.submit();
  }

  function _downloadCsv(){
    document.search_form.exec.value='download_csv';
    document.search_form.sess_no_init.value = "1"
    document.search_form.submit();
  }

  function _uploadCsvSelect() {
    const fileElem = document.getElementById("file_select");
    if (fileElem) {
      fileElem.click();
    }
  }

  function _uploadCsv() {
    document.upload_form.exec.value = 'upload_csv';
    document.upload_form.submit();
  }

  function _uploadCodeCsvSelect() {
      const fileElem = document.getElementById("code_file_select");
      if (fileElem) {
          fileElem.click();
      }
  }

  function _uploadCodeCsv() {
      document.upload_form.exec.value = 'upload_code_csv';
      document.upload_form.submit();
  }
</script>
@endverbatim

<div class="row">
  <div class="col-md-12">
    @if($err_msg['0'] !='')
    <div class="errArea">
      @foreach($err_msg as $msg)
      {{ $msg }}<br>
       @endforeach
    </div><br>
    @if($line_err['0'] !='')
    <div class="card card-user" style="padding:20px;">

      <span style="font-weight:bold;">取り込み結果</span>
      <hr>
      @foreach($line_err as $err)
      <span style="color:red;">{{ $err }}</span>
       @endforeach
      {{ $success_msg }}

    </div><!-- <div class="card card-user"> -->
     @endif
    @elseif($success_msg!="")
    <div class="successArea">{!! nl2br($success_msg) !!}</div>
     @endif
    <div class="card card-user">
      <form name="search_form" action="./" method="post">
        <input type="hidden" name="page" value="{{ $page }}">
        <input type="hidden" name="exec" value="search">
        <input type="hidden" name="set_pattern" value="{{ $set_pattern }}">
        <input type="hidden" name="sess_no_init" value="">

        <br>

        <div class="row">
          <div class="col-md-4 pl-1">
            <div class="msr_text_01">
              <label>支店・部署名</label>
              <input type="text" name="syozoku_name" value="{{ $search_condition['syozoku_name'] }}"  style="width:50%;"/>を含む
            </div>
          </div>
          <div class="col-md-4 pl-1">
            <div class="msr_text_01">
              <label>表示状態でフィルタ</label>
              <select name="hidden_flg">
                {!! blade_html_options(['options' => $select_hidden, 'selected' => $search_condition['hidden_flg']]) !!}
              </select>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="update ml-auto mr-auto">
            <button type="button" class="btn btn-primary btn-round" onClick="_regist('search');">検　索</button>
            <button type="button" class="btn btn-primary btn-round" onClick="location.href='?page={{ $page }}';">リセット</button>
          </div>
        </div>

      </form>
    </div><!-- <div class="card card-user"> -->
  </div><!-- <div class="col-md-12"> -->
</div><!-- <div class="row"> -->

<button type="button" onClick="location.href='?page=syozoku_edit';" class="btn btn-primary btn-round">新規登録</button>
<button type="button" onClick="_downloadCsv();" class="btn btn-primary btn-round" style="width:200px;">CSVダウンロード</button>
<!--button type="button" onClick="_uploadCsvSelect();" class="btn btn-primary btn-round" style="width:200px;">CSVアップロード</button-->
<button type="button" onClick="_uploadCodeCsvSelect();" class="btn btn-primary btn-round" style="width:300px;">CSVアップロード</button>

<form name="upload_form" action="./" method="post" enctype="multipart/form-data" id="form_csv_upload">
  <input type="hidden" name="page" value="{{ $page }}">
  <input type="hidden" name="exec" value="upload_csv">
  <input type="file" name="csv_file" class="d-none" id="file_select" onchange="_uploadCsv();">
  <input type="file" name="code_csv_file" class="d-none" id="code_file_select" onchange="_uploadCodeCsv();">
</form>

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
                <th style="width:200px;">
                  コード
                </th>
                <th style="width:300px;">
                  所属(支店・部署)名
                </th>
                <th style="width:300px;">
                  所属グループ名
                </th>
                <th style="width:300px;">
                  エリア
                </th>
              </thead>
              <tbody>
              @foreach($main_recs as $rec)
                <tr>
                  <td>
                    {{ $rec['syozoku_code'] }}
                  </td>
                  <td>
                    {{ $rec['syozoku_name'] }}
                  </td>
                  <td>
                    {{ $rec['szkgrp_name'] }}
                  </td>
                  <td>
                    {{ $rec['tanarea_name'] }}
                  </td>
                  <td>
                  <button type="button" onClick="location.href='?page=syozoku_edit&id={{ $rec['syozoku_id'] }}';" class="btn btn-primary btn-round">詳細</button>
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

