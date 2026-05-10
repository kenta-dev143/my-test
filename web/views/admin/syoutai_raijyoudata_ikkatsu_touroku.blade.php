      <div class="row" style="margin-top:10px;">
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
                    <span style="font-weight:bold;">来場者アップロードCSVレイアウト</span>
                    {!! $raijyou_layout !!}
                </td>
              </tr>
              <tr>
                <td style="vertical-align:top;">
                    <hr>
                    <form name="upload_form" action="./" method="post" enctype="multipart/form-data">

                      <input type="hidden" name="page" value="{{ $page }}">
                      <input type="hidden" name="exec" value="raijyou_csv_upload">
                    
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
                        <!-- <div class="update ml-auto mr-auto"> -->
                          <button type="submit" class="btn btn-primary btn-round" style="width:340px;">アップロードして一括登録・変更</button>
                        <!-- </div> -->
                      </div>

                    </form>
                </td>
              </tr>
            </table>

          </div><!-- <div class="card card-user"> -->

        </div><!-- <div class="col-md-12"> -->

      </div><!-- <div class="row"> -->

