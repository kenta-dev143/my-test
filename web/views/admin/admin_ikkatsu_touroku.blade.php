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

    <div class="card card-user" style="padding:10px;">
      <table>
        <tr>
          <td style="vertical-align:top;">
            <span style="font-weight:bold;">招待者アップロードCSVレイアウト</span>
            {!! $csv_layout !!}
          </td>
          <td>

          </td>
        </tr>
        <tr>
          <td style="vertical-align:top;">
            <br>
            <button type="button" onClick="location.href='./?page={!! $page !!}&exec=csv_tpl_download';" class="btn btn-primary btn-round" style="width:450px;">担当者 一括登録用<br>テンプレートダウンロード</button>
          </td>
          <td>

          </td>
        </tr>
        <tr>
          <td style="vertical-align:top;">
            <hr>
            <form name="upload_form" action="./" method="post" enctype="multipart/form-data">
              <input type="hidden" name="page" value="{{ $page }}">
              <input type="hidden" name="exec" value="csv_upload">
              <!-- <input type="hidden" name="dl_file" value="{!! $dl_file !!}"> -->
              <!-- <input type="hidden" name="dl_file_path" value="{!! $temp_file2_path !!}"> -->
              
              <div class="row">
                <div class="col-md-4 pl-1">
                  <div class="msr_text_01">
                    <label>CSVファイル</label>
                    <input type="file" name="csv_file">
                  </div>
                </div>
              </div>

              <div id="btn_showhide" style="{!! $NoDisplay !!}">
                <label for="id_mail_notice" style="color:red;">
                  <input type="checkbox" class="largechkbox" id="id_mail_notice" name="mail_notice" value="1" @if($mail_notice=="1")checked @endif/>&nbsp;パスワード設定URLを
                  <select name="mail_timing">
                    <option value="now" @if($mail_timing!="ato")selected @endif >今すぐ</option>
                    <option value="ato" @if($mail_timing=="ato")selected @endif >後から</option>
                  </select>
                  メール通知する
                </label>
                <p style="font-size:0.8571em;">※メール通知を希望しない方はチェックを外してください。</p>
              </div>
              
              <div class="row">
                <!-- <div class="update ml-auto mr-auto"> -->
                <button type="submit" style="width:300px;" onClick="document.upload_form.exec.value='csv_upload';document.upload_form.submit();" class="btn btn-primary btn-round" style="width:200px;">アップロードして<br>一括登録</button>
                <!-- </div> -->
              </div>
            </form>
          </td>

          <td style="vertical-align:top;">
            <hr>
            <form name="admin_download_form" action="./" method="post" enctype="multipart/form-data">
              <input type="hidden" name="page" value="{{ $page }}">
              <input type="hidden" name="exec" value="admin_download">
              
              <div class="row">
                <div class="col-md-4 pl-1">
                  <div class="msr_text_01">
                    <br><br>
                  </div>
                </div>
              </div>
              
              <div class="row">
                <button type="submit" style="width:300px;" onClick="document.admin_download_form.submit();" class="btn btn-primary btn-round" style="width:200px;">担当者一覧<br>ダウンロード</button>
              </div>
            </form>
          </td>

          <!-- {{-- <td style="vertical-align:top;"> --}} -->
            <!-- {{-- <hr> --}} -->
            <!-- {{-- <form name="download_form" action="./" method="post" enctype="multipart/form-data"> --}} -->
              <!-- {{-- <input type="hidden" name="page" value="{{ $page }}"> --}} -->
              <!-- {{-- <input type="hidden" name="exec" value="download"> --}} -->

              <!-- {{-- <div class="row"> --}} -->
                <!-- {{-- <div class="col-md-4 pl-1"> --}} -->
                  <!-- {{-- <div class="msr_text_01"> --}} -->
                    <!-- {{-- <label>初期パスワード情報ファイル</label> --}} -->
                    <!-- {{-- <select name="dl_file"> --}} -->
                      <!-- {{-- {!! blade_html_options(['options' => $downLoadFiles, 'selected' => $dl_file]) !!} --}} -->
                    <!-- {{-- </select> --}} -->
                  <!-- {{-- </div> --}} -->
                <!-- {{-- </div> --}} -->
              <!-- {{-- </div> --}} -->
              
              <!-- {{-- <div class="row"> --}} -->
                <!-- {{-- <div class="update ml-auto mr-auto"> --}} -->
                <!-- {{-- <button type="submit" style="width:300px;" onClick="document.download_form.submit();" class="btn btn-primary btn-round" style="width:200px;">初期パスワードファイル<br>ダウンロード</button> --}} -->
                <!-- {{-- </div> --}} -->
              <!-- {{-- </div> --}} -->
            <!-- {{-- </form> --}} -->
          <!-- {{-- </td> --}} -->
        </tr>
      </table>
    </div><!-- <div class="card card-user"> -->
  </div><!-- <div class="col-md-12"> -->
</div><!-- <div class="row"> -->
<!--
<div class="row">
  <div class="col-md-12">
    <div class="card card-user" style="padding:20px;">
      <span style="font-weight:bold;">取り込み結果</span>
      <hr>

      <!-- {{--
      @if($dl_file != '')
        <a href="javascript:void(0);" onClick="document.upload_form.exec.value='download';document.upload_form.submit();">パスワードファイル ダウンロード</a>
       @endif
      --}} 

      @foreach($line_err as $err)
        <span style="color:red;">{{ $err }}</span>
       @endforeach
      {{ $success_msg }}
    </div><!-- <div class="card card-user"> 
  </div><!-- <div class="col-md-12"> 
</div><!-- <div class="row"> -->
