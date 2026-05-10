<script>
var select_event_id = '{{ $select_event_id }}';
var taisyou_cnt = {{ $sentakuTaisyouCnt }};
var main_recs_count = {{ $main_recs_count }};

function _winAdminOpen(){



  // ****

  var s_event_id = "{{ $select_event_id }}";

  @verbatim

  var syozoku_id = $('select[name="admin_syozoku_id"]').val();
  if ( syozoku_id != "" ){
    syozoku_id = '&admin_syozoku_id=' + syozoku_id;
  }

  modalDialogOpen( './index.php?page=admin_list_select_win&select_event_id='+s_event_id+syozoku_id );

}

function _adminSelect(id,busyo,name){
    $('input[name="admin_id"]').val(id);
    $('#tantousya_lbl').html('<a href="javascript:_tantousyaDel();void(0);" style="color:red;">[×]</a> '+busyo+'　'+name);
}

function _tantousyaDel(){
  $('input[name="admin_id"]').val('');
  $('#tantousya_lbl').text('');
}

function _pageChange(offset){
  document.search_form.offset.value = offset;
  document.search_form.submit();
}

function _userChk(idx,chk){
  if(chk){
    $('#user_chks_'+idx).val('1');
    taisyou_cnt++;
  }else{
    $('#user_chks_'+idx).val('0');
    taisyou_cnt--;
  }
  $('#taisyou_cnt_lbl').text( _numberFormat(taisyou_cnt) );
  $('#taisyou_cnt_btn').text( _numberFormat(taisyou_cnt) );
}

function _checkall(){
  // 全ての質問の値を取得 (true,false)
  var chkall_flg = $("#id_check_all").prop("checked");
  // タイトルのクラスに値を設定
  $(".chks").prop("checked",chkall_flg);

  if (chkall_flg){
    // $ (".hid_chks").val(1);
    for (var i = 0; i < main_recs_count; i++) {
      if(''+$('#user_chks_'+i).val()=='0'){
        $('#user_chks_'+i).val(1);
        taisyou_cnt++;
      }
    }
  } else {
    // $ (".hid_chks").val(0);
    for (var i = 0; i < main_recs_count; i++) {
      if(''+$('#user_chks_'+i).val()=='1'){
        $('#user_chks_'+i).val(0);
        taisyou_cnt--;
      }
    }
  }
  $('#taisyou_cnt_lbl').text( _numberFormat(taisyou_cnt) );
  $('#taisyou_cnt_btn').text( _numberFormat(taisyou_cnt) );
}

function _gotoDetail(id){
  document.search_form.exec.value = "gotoDetail";
  document.search_form.user_id.value = id;
  document.search_form.submit();
}

function _userKettei(){
  if(taisyou_cnt==0){
    alert('送信対象者が１人もいません。');
  }else{
    document.search_form.exec.value='user_kettei';
    document.search_form.submit();
  }
}

function _syoutaiAriChk(chk){
  if(chk){
    $('#sankasinai_nozoku').prop('disabled',false);
  }else{
    $('#sankasinai_nozoku').prop('disabled',true);
    $('#sankasinai_nozoku').prop('checked',false);
  }
}
</script>
　<br>
@endverbatim
  @if($err_msg['0'] !='')
    <div class="errArea">
      @foreach($err_msg as $msg)
        {{ $msg }}<br>
       @endforeach
    </div><br>
  @elseif($success_msg!="")
    <div class="successArea">{{ $success_msg }}</div>
   @endif

  @if($select_event_id!="")
            <form name="search_form" action="./" method="post" onSubmit="return false;">
              <input type="hidden" name="page" value="{{ $page }}">
              <input type="hidden" name="exec" value="">
              <input type="hidden" name="offset" value="">
              <input type="hidden" name="user_id" value="">

      <div class="row">
        <div class="col-md-12">
          <div class="card card-user" style="padding-top:10px;padding-left:20px;padding-right:20px;">
              <span style="font-weight:bold;color:#51cecb;font-size:18px;">Step1: 送信対象者の絞り込み</span>
              <!--
            <form name="search_form" action="./" method="post" onSubmit="return false;">
              <input type="hidden" name="page" value="{{ $page }}">
              <input type="hidden" name="exec" value="">
              <input type="hidden" name="offset" value="">
              <input type="hidden" name="taisyou_count" value="{{ $count }}">
            -->


              <!-- ***************************** -->
              <div class="row">
                <div style="width:100%;background-color:#eeeeee;padding:5px;margin-top:10px;">
                <span style="font-weight:bold;">基本条件</span>
                </div>
              </div>
              <!-- ***************************** -->
            
              <!-- ***************************** -->
              <div class="row">
                <div class="col-md-4 pl-1">
                  <div class="msr_pulldown_01">
                    <label>招待者・来場者</label>
                    <select name="syoutai_raijyou" style="width:300px;">
                      <option value="">全て</option>
                      {!! blade_html_options(['options' => $_conf_syoutai_raijyou, 'selected' => $search_condition['syoutai_raijyou']]) !!}
                    </select>
                  </div>
                </div>

                <div class="col-md-4 pl-1">
                  <div class="msr_pulldown_01">
                    <label style="width:350px;">マイページ設定URL通知メール送信状態</label>
                    <select name="mail_status" style="width:240px;">
                      {!! blade_html_options(['options' => $_conf_mail_status, 'selected' => $search_condition['mail_status']]) !!}
                    </select>
                  </div>
                </div>

                <div class="col-md-4 pl-1">
                  <div class="msr_pulldown_01">
                    <label style="width:350px;">パスワード設定状態</label>
                    <select name="pass_set" style="width:240px;">
                      {!! blade_html_options(['options' => $_conf_pass_set, 'selected' => $search_condition['pass_set']]) !!}
                    </select>
                  </div>
                </div>

              </div>

              <!-- ***************************** -->
              <div class="row">
                <div class="col-md-4 pl-1">
                  <label>現地展示会への招待の状態</label>
                  <div class="msr_radio_01">
                      <input type="checkbox" class="largechkbox" name="syoutai_yotei_time_ari" id="syoutai_yotei_time_ari" value="1" onClick="_syoutaiAriChk(this.checked);" @if($search_condition['syoutai_yotei_time_ari']=="1")checked @endif/>
                      <label for="syoutai_yotei_time_ari">現地展示会に招待されている(来場予定日時の設定がある)</label>
                      <br>　
                      <input type="checkbox" class="largechkbox" name="sankasinai_nozoku" id="sankasinai_nozoku" value="1" @if($search_condition['sankasinai_nozoku']=="1")checked @endif @if($search_condition['syoutai_yotei_time_ari']!="1")disabled @endif/>
                      <label for="sankasinai_nozoku">「参加しない」などの来場予定は除く</label>
                  </div>
                </div>

                <div class="col-md-4 pl-1">
                  <label>WEB展示会(ガイドブック)</label>
                  <div class="msr_radio_01">
                      <input type="checkbox" class="largechkbox" name="user_web" id="user_web" value="1" @if($search_condition['user_web']=="1")checked @endif/>
                      <label for="user_web">WEB展示会(ガイドブック)に招待されている</label>
                  </div>
                </div>

                <div class="col-md-3 pl-1">
                  <div class="msr_pulldown_01">
                    <label>サインアップの承認状態</label>
                    <select name="user_syounin_flg">
                      <option value="">選択してください</option>
                      {!! blade_html_options(['options' => $_conf_user_syounin_flg, 'selected' => $search_condition['user_syounin_flg']]) !!}
                    </select>
                  </div>
                </div>

              </div>

              <div class="row">
                <div class="col-md-4 pl-1">
                  <label>管理者</label>
                  <div class="msr_radio_01">
                      <input type="checkbox" class="largechkbox" name="include_admin" id="include_admin" value="1" @if($search_condition['include_admin']=="1")checked @endif/>
                      <label for="include_admin">管理画面ログイン権限あり</label>
                  </div>
                </div>
              </div>

              <!-- ***************************** -->
              <div class="row">
                <div style="width:100%;background-color:#eeeeee;padding:5px;">
                <span style="font-weight:bold;">詳細条件</span>
                </div>
              </div>
              <!-- ***************************** -->

              <div class="row">
                <div class="col-md-4 pl-1">
                  <div class="msr_text_01">
                    <label>企業名</label>
                    <input type="text" name="user_kigyou_name" value="{{ $search_condition['user_kigyou_name'] }}" style="width:50%;" />を含む
                  </div>
                </div>

                <div class="col-md-4 pl-1">
                  <div class="msr_text_01">
                    <label>企業名カナ</label>
                    <input type="text" name="user_kigyou_name_kana" value="{{ $search_condition['user_kigyou_name_kana'] }}" style="width:50%;" />を含む
                  </div>
                </div>

                <div class="col-md-4 pl-1">
                  <div class="msr_pulldown_01">
                    <label>VIP</label>
                    <select name="user_vip_flg">
                      <option value="">選択してください</option>
                      {!! blade_html_options(['options' => $_conf_vip, 'selected' => $search_condition['user_vip_flg']]) !!}
                    </select>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-4 pl-1">
                  <div class="msr_text_01">
                    <label>氏名</label>
                    <input type="text" name="user_name" value="{{ $search_condition['user_name'] }}"  style="width:50%;"/>を含む
                  </div>
                </div>
                
                <div class="col-md-4 pl-1">
                  <div class="msr_text_01">
                    <label>氏名カナ</label>
                    <input type="text" name="user_name_kana" value="{{ $search_condition['user_name_kana'] }}"  style="width:50%;"/>を含む
                  </div>
                </div>

                <div class="col-md-4 pl-1">
                  <div class="msr_text_01">
                    <label>メールアドレス(PC)</label>
                    <input type="text" name="user_mail" value="{{ $search_condition['user_mail'] }}" style="width:50%;" />を含む
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-4 pl-1">
                  <div class="msr_text_01">
                    <label>役職　</label>
                    <input type="text" name="user_yakusyoku" value="{{ $search_condition['user_yakusyoku'] }}" style="width:50%;"/>を含む
                    <p class="small">※スペース区切りでOR検索が行えます</p>
                  </div>
                </div>

                <div class="col-md-4 pl-1">
                  <div class="msr_text_01">
                    <label>中分類　</label>
                    <select name="user_mid_cate">
                      <option value="">選択してください</option>
                      {!! blade_html_options(['options' => $_conf_mid_cate, 'selected' => $search_condition['user_mid_cate']]) !!}
                    </select>
                  </div>
                </div>
              </div>

              <!-- ***************************** -->
              <div class="row">
                <div class="col-md-3 pl-1">
                  <div class="msr_pulldown_01">
                    <label>担当者エリア名</label>
                    <select name="admin_tanarea_id[]" multiple="multiple" class="select-multiple">
                      <option value="">選択してください</option>
                      {!! blade_html_options(['options' => $_conf_tanarea, 'selected' => $search_condition['admin_tanarea_id']]) !!}
                    </select>
                  </div>
                </div>

                <div class="col-md-3 pl-1">
                  <div class="msr_pulldown_01">
                    <label>担当者支店名・部署名</label>

                    <!-- {{-- ****************** 所属フィルター ****************** --}} -->
                    <input type="text" name="syozoku_filter" placeholder="選択肢をフィルター" onKeyup="syozokuFilter();">
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

                    <select name="admin_syozoku_id" style="width:300px;">
                      <option value="">選択してください</option>
                      {!! blade_html_options(['options' => $_conf_syozoku, 'selected' => $search_condition['admin_syozoku_id']]) !!}
                    </select>


                  </div>
                </div>

                <div class="col-md-3 pl-1">
                  <div class="msr_pulldown_01">
                    <label>担当者名</label><span class="searchBtn" style="margin-left:10px;" onClick="_winAdminOpen();"></span>
                    <div id="tantousya_lbl">@if($search_condition['admin_name']!="")<a href="javascript:_tantousyaDel();void(0);" style="color:red;">[×]</a> {{ $search_condition['syozoku_name'] }}　{{ $search_condition['admin_name'] }} @endif</div>
                    <input type="hidden" name="admin_id" value="{{ $search_condition['admin_id'] }}">
                  </div>
                </div>

                <div class="col-md-3 pl-1">
                  <div class="msr_text_01">
                    <label>担当者メールアドレス(PC)</label>
                    <input type="text" name="admin_mail" value="{{ $search_condition['admin_mail'] }}" style="width:50%;"/>
                  </div>
                </div>


                <div class="col-md-3 pl-1">
                  <div class="msr_pulldown_01">
                    <label style="width:350px;">来場日時</label>
                    <select name="raijyou_ymd[]" style="width:240px;" multiple="multiple" class="select-multiple">
                      <option value=""></option>
                      @foreach($_conf_raijyou_yotei_time as $ymd => $info)
                        @php($isChecked = false)
                        @foreach($search_condition['raijyou_ymd'] as $k => $i)
                          @if(!$isChecked && $ymd == $i)
                            @php($isChecked = true)
                           @endif
                         @endforeach
                        <option value="{!! $ymd !!}" @if($isChecked )selected @endif>{{ $info['disp_ymd'] }}</option>
                       @endforeach
                    </select>
                    <br>
                    <input type="text" name="raijyou_hi_st" value="{{ $search_condition['raijyou_hi_st'] }}" class="raijyouHi" placeholder="09:00" style="width:60px;">
                    〜
                    <input type="text" name="raijyou_hi_ed" value="{{ $search_condition['raijyou_hi_ed'] }}" class="raijyouHi" placeholder="18:00" style="width:60px;">
                    <br>に来場された方
                  </div>
                </div>

                <div class="col-md-3 pl-1">
                  <div class="msr_pulldown_01">
                    <label>事後アンケート回答有無</label>
                    <select name="jigo_ans">
                      {!! blade_html_options(['options' => $_conf_jigo_ans, 'selected' => $search_condition['jigo_ans']]) !!}
                    </select>
                  </div>
                </div>

                <div class="col-md-3 pl-1">
                  <div class="msr_text_01">
                    <label>タグ文字列</label>
                    <input type="text" name="user_tag" value="{{ $search_condition['user_tag'] }}" style="width:50%;"/>
                  </div>
                </div>

                <div class="col-md-3 pl-1">
                  <div class="msr_pulldown_01">
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
                  <button type="button" class="btn btn-primary btn-round" onClick="document.search_form.exec.value='search';document.search_form.submit();">検　索</button>
                  <button type="button" class="btn btn-primary btn-round" onClick="location.href='?page=user_mail_send';">リセット</button>
                </div>
              </div>

            <!-- </form> -->
          </div><!-- <div class="card card-user"> -->
        </div><!-- <div class="col-md-12"> -->
      </div><!-- <div class="row"> -->

      @if($list_disp=="1")

        <div class="row">
          <div class="col-md-12">
            <div class="card" style="padding-top:10px;">
              <span style="font-weight:bold;color:#51cecb;font-size:18px;">　Step2: 送信対象者の確認</span>
              　<br>
              <span style="font-weight:bold;font-size:16px;color:blue;">　送信対象者　<span id="taisyou_cnt_lbl">{!! $sentakuTaisyouCnt !!}</span>　名</span>

              <div class="card-body">
                @if($count==0)
                  <br><br>条件に合うデータがありませんでした。<br><br><br>
                 @else 
                  @include('page_navi_post')
                  <div class="table-responsive">
                    <table class="table">
                      <thead class=" text-primary">
                        <th style="text-align:center;">全てをﾁｪｯｸ<br><input type="checkbox" id="id_check_all" class="largechkbox" @if($allChkOff!="1")checked @endif onClick="_checkall();"></th>
                        <th>
                          大分類
                        </th>
                        <th>
                          企業名
                        </th>
                        <th>
                          部署
                        </th>
                        <th>
                          役職
                        </th>
                        <th>
                          氏名
                        </th>
                        <th>
                          予定日時
                        </th>
                        <th>
                          来場日時
                        </th>
                        <th>
                          ﾊﾟｽﾜｰﾄﾞ<br>設定状態
                        </th>
                        <th>
                          ﾏｲﾍﾟｰｼﾞ設定<br>URL通知
                        </th>
                        <th style="padding-left:25px;">
                          　 詳細
                        </th>
                      </thead>
                      <tbody>
                      @foreach($main_recs as $rec)
                        <tr>
                          <td style="text-align:center;">
                            &nbsp;
                            <input type="checkbox" name="chks[]" id="chks_{!! $loop->index !!}" class="chks largechkbox" onClick="_userChk({!! $loop->index !!},this.checked);"  value="{{ $rec['user_id'] }}" {!! $rec['checked'] !!}>
                            <input type="hidden" name="user_chks[]" class="hid_chks" id="user_chks_{!! $loop->index !!}" value="@if($rec['checked']=="")0 @else 1 @endif" {!! $rec['checked'] !!}>
                            <input type="hidden" name="user_ids[]" value="{{ $rec['user_id'] }}" {!! $rec['checked'] !!}>
                          </td>
                          <td>
                            {{ $rec['disp_big_cate'] }}
                          </td>
                          <td>
                            {{ $rec['user_kigyou_name'] }}
                          </td>
                          <td>
                            {{ $rec['user_busyo'] }}
                          </td>
                          <td>
                            {{ $rec['user_yakusyoku'] }}
                          </td>
                          <td>
                            {{ $rec['user_name'] }}
                          </td>
                          <td>
                            {{ $rec['disp_user_raijyou_yotei_time'] }}
                          </td>
                          <td>
                            {!! $rec['disp_min_kinout_time_in'] !!}
                          </td>
                          <td>
                            {!! $rec['pass_set'] !!}
                          </td>
                          <td>
                            @if($rec['user_mail_send_kbn']=="1")
                              <span style="color:blue;font-weight:bold;">済</span>
                            @elseif($rec['user_mail_send_kbn']=="2")
                              <span style="color:red;">NG</span>
                             @else 
                              <span style="color:black;">未</span>
                             @endif
                          </td>
                          <td>
                          <!-- <button type="button" onClick="location.href='?page=user_edit&id={{ $rec['user_id'] }}&from_page=user_mail_send';" class="btn btn-primary btn-round">詳細</button> -->
                          <button type="button" onClick="_gotoDetail('{{ $rec['user_id'] }}');" class="btn btn-primary btn-round">詳細</button>
                          </td>
                        </tr>
                       @endforeach
                      </tbody>
                    </table>
                  </div>
                  @include('page_navi_post')
                 @endif
              </div>
            </div>
          </div>
        </div>

        @if($count>0)
          <div class="card" style="padding-top:10px;">

            <span style="font-weight:bold;color:#51cecb;font-size:18px;">　Step3: 送信対象者決定</span>

            <div class="row">
              <div class="update _ml-auto _mr-auto">
                <button type="button" style="width:400px;margin-left:60px;" class="btn btn-primary btn-round" onClick="_userKettei();">上記送信対象者(<span id="taisyou_cnt_btn">{{ $sentakuTaisyouCnt }}</span>名)にメール送信する<br>（メール内容編集画面へ）</button>
              </div>
            </div>
          </div>
         @endif
       @endif
    </form>
   @else 
    <div class="col-md-12">
      <div class="card">
        <br><br><br><br>
        　　　　　イベントを選択してください
        <br><br><br><br><br>
      </div>
    </div>
   @endif