<style>
.raijyouHi::placeholder {
  color: #bbbbbb;
}
</style>

<script>
var select_event_id = '{{ $select_event_id }}';
function qr_disp(uid,eid){
  var url = '../qr_print.pdf?user_id='+uid+'&event_id='+eid;
  var top = window.innerHeight / 2;
  var left = window.innerWidth / 2;
  window.open(url, 'qr_disp', 'top=' + top + ',left=' + left + ',width=1000,height=1000');
}

function qr_dl(uid,eid){
  var url = '../qr_print.pdf?user_id='+uid+'&event_id='+eid;
  location.href = url;
}

function syozokuChange(){
  var syozoku_id = $('select[name="admin_syozoku_id"]').val();
  var admin_id = $('select[name="admin_id"]').val();

  AJAXCall2json(
              "{!! $_SYSTEM_ROOT_URLS !!}/ajax_php/getAdminList.php"
              ,{'event_id':select_event_id, 'syozoku_id': syozoku_id }
          )
          .done(function (json) {

              if(!$.isEmptyObject( json )){
                  if(json.status=='NG'){
                      //NG
                      alert(json.error_message);
                  }else{
                      //OK
                      var data = json.data;

                      var html = '';
                      html += '<option value="">選択してください</option>';
                      for (var i = 0; i < data.admin_recs.length; i++) {
                          var syo_name = data.admin_recs[i].syozoku_name;
                          var name = data.admin_recs[i].admin_name;
                          var id = data.admin_recs[i].admin_id;
                          if(admin_id==id){
                            html += '<option value="'+id+'" selected>'+syo_name+' '+name+'</option>';
                          }else{
                            html += '<option value="'+id+'">'+syo_name+' '+name+'</option>';
                          }
                      }
                      $('select[name="admin_id"]').html(html);
                  }
              }
          })
          .fail(function (result, status, errors) {
              alert('通信エラーが発生しました。');
          })
  ;
}


var midCateArr = [];
@foreach($_conf_mid_cate as $m_id => $m_nm)
midCateArr[{!! $loop->index !!}] = { id: {!! $m_id !!}, name: '{{ $m_nm }}' };
 @endforeach
@verbatim

function bigCateChange(){
  var big_cate = $('select[name="user_big_cate[]"]').val();
  var mid_cate = $('select[name="user_mid_cate[]"]').val();

  big_cate = big_cate.length > 1 ? '' : big_cate[0];
  mid_cate = mid_cate.length > 0 ? mid_cate[0] : '';

  var html = '';
  html += '<option value="">選択してください</option>';
  for (var i = 0; i < midCateArr.length; i++) {
    var inFlg = false;
    if(big_cate==''){
      inFlg = true;
    }else if(parseInt(big_cate) <= 4){
      if(midCateArr[i].id < 100){
        inFlg = true;
      }
    }else{
      if(midCateArr[i].id >= 100){
        inFlg = true;
      }
    }
    if(inFlg){
      if(mid_cate==midCateArr[i].id){
        html += '<option value="'+midCateArr[i].id+'" selected>'+midCateArr[i].name+'</option>';
      }else{
        html += '<option value="'+midCateArr[i].id+'">'+midCateArr[i].name+'</option>';
      }
    }
  }
  $('select[name="user_mid_cate[]"]').html(html);

}

function _winAdminOpen(){
  @endverbatim
  var s_event_id = "{{ $select_event_id }}";
  @verbatim

  var syozoku_id = $('select[name="admin_syozoku_id"]').val();
  if ( syozoku_id != "" ){
    syozoku_id = '&admin_syozoku_id=' + syozoku_id;
  }

  //
  //window.open( './index.php?page=admin_list_select_win&select_event_id='+s_event_id+syozoku_id,'admin_list_select_win' ,'width=1750, height=800, menubar=no, toolbar=no, scrollbars=yes' );
  //
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

function _passSetUrlCsv(){
  if(confirm('「ご来場予定登録完了のご案内」メール送信フラグをONにしますか？') ){
    document.search_form.send_flg_on.value='1';
  }
  document.search_form.exec.value='pass_change_csv';
  document.search_form.submit();
}

function _userlistCsv(){
  var user_big_cate = $('select[name="user_big_cate"]').val();
  if ( user_big_cate == '' ){
    alert('来場者一覧CSVをダウンロードする場合、「大分類」を選択してください。');
    return;
  }

  document.search_form.exec.value='user_list_csv';
  document.search_form.submit();
}

//2021/11/08 Add ------- Start --------
function _userQrDl(){
  var chkCnt = 0;

  var sy_time_chk = $('input[name="user_syoutai_yotei_time[]"]');
  $.each(sy_time_chk, function(index1, w_chk1) {

    if(w_chk1.checked == true){
      chkCnt++;
    }

  })

  var ry_time_chk = $('input[name="user_raijyou_yotei_time[]"]');
  $.each(ry_time_chk, function(index2, w_chk2) {

    if(w_chk2.checked == true){
      chkCnt++;
    }

  })



  if ( chkCnt == 0 ){
    alert('QRコード一括ダウンロードする場合、「(招待者)予定日時」と「(来場者)予定日時」から１つ以上選択してください。');
    return;
  }

  document.search_form.exec.value='user_qr_dl';
  document.search_form.submit();
}
//2021/11/08 Add ------- End --------
function _szkgrp_qrzip_dl() {
  document.zip_form.szkgrp_id.value = '';
  document.zip_form.submit();
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

      <div class="row">
        <div class="col-md-12">
          <div class="card card-user">
            <form name="search_form" action="./" method="post" onSubmit="return false;">
              <input type="hidden" name="page" value="{{ $page }}">
              <input type="hidden" name="exec" value="">
              <input type="hidden" name="send_flg_on" value="">

              @if(isset($search_condition['user_ids']) && $search_condition['user_ids'] != '')
              <input type="hidden" name="user_ids" value="{{ $search_condition['user_ids'] }}">
               @endif

              <!-- ***************************** -->
              <div class="row">
                <div class="col-md-4 pl-1">
                  <div class="msr_pulldown_01">
                    <label>VIP</label><br>
                    <select name="user_vip_flg" style="width: 100% !important;">
                      <option value="">選択してください</option>
                      {!! blade_html_options(['options' => $_conf_vip, 'selected' => $search_condition['user_vip_flg']]) !!}
                    </select>
                  </div>
                </div>
                <div class="col-md-4 pl-1">
                  <div class="msr_pulldown_01">
                    <label>大分類（業種）</label>
                    <select name="user_big_cate[]" style="width:240px;" onChange="bigCateChange();" multiple="multiple" class="select-multiple">
                      <option value="">選択してください</option>
                      {!! blade_html_options(['options' => $_edited_big_cate, 'selected' => $search_condition['user_big_cate']]) !!}
                    </select>
                    <p class="small">※Ctrlキーで複数選択できます</p>
                  </div>
                </div>
                <div class="col-md-4 pl-1">
                  <div class="msr_pulldown_01">
                    <label>中分類（業態）</label>
                    <select name="user_mid_cate[]" style="width:240px;" multiple="multiple" class="select-multiple">
                      <option value="">選択してください</option>
                      {!! blade_html_options(['options' => $_conf_mid_cate, 'selected' => $search_condition['user_mid_cate']]) !!}
                    </select>
                    <p class="small">※Ctrlキーで複数選択できます</p>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-4 pl-1">
                  <div class="msr_text_01">
                    <label>企業名(マスタ)</label>

                    <!-- {{-- ****************** 企業フィルター ****************** --}} -->
                    <input type="text" name="company_filter" placeholder="選択肢をフィルター" onKeyup="companyFilter();">
                    <select id="hidden_company_id_select" style="display:none;">
                      <option value="">選択してください</option>
                      {!! blade_html_options(['options' => $_conf_company, 'selected' => $search_condition['user_company_id']]) !!}
                    </select>
                    @verbatim
                    <script>
                      function companyFilter(){
                        var filter = $('input[name="company_filter"]').val();
                        if(filter==''){
                          $('select[name="user_company_id"]').html( $("#hidden_company_id_select").html() );
                        }else{
                          $('select[name="user_company_id"]').html('');
                          $('select[name="user_company_id"]').append($("<option>").val('').text( '選択してください' ));
                          $("#hidden_company_id_select option").each(function(i){
                            if($(this).text().indexOf(filter) != -1){
                              $('select[name="user_company_id"]').append($("<option>").val($(this).val()).text( $(this).text() ));
                            }
                          });
                        }
                      }
                    </script>
                    @endverbatim
                    <!-- {{-- ****************** /企業フィルター ****************** --}} -->
                    <br>
                    <select name="user_company_id" style="width:460px;">
                      <option value="">選択してください</option>
                      {!! blade_html_options(['options' => $_conf_company, 'selected' => $search_condition['user_company_id']]) !!}
                    </select>

                  </div>
                </div>

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
              </div>

              <div class="row">
                <div class="col-md-4 pl-1">
                  <div class="msr_pulldown_01">
                    <label>予定日時設定状態</label>
                    <select name="yotei_set_jyoutai" style="width:240px;">
                      <option value="">選択してください</option>
                      {!! blade_html_options(['options' => $_conf_yotei_set_jyoutai, 'selected' => $search_condition['yotei_set_jyoutai']]) !!}
                    </select>
                  </div>
                </div>
              </div>

              <!-- ***************************** -->
              <div class="row">
                <div class="col-md-4 pl-1">
                  <label>(招待者)予定日時</label>
                  <div class="msr_radio_01">
                    @foreach($_conf_syoutai_yotei_time as $ymd => $info)
                      <label style="min-width: 7em;">{{ $info['disp_ymd'] }}</label>
                      @foreach($info['his'] as $hi_info)
                        <input type="checkbox" class="largechkbox" name="user_syoutai_yotei_time[]" value="{{ $ymd }} {{ $hi_info['hi'] }}" {{ $hi_info['checked'] }} id="syoutai_{{ $ymd }}{{ $hi_info['hi'] }}" />
                        <label for="syoutai_{{ $ymd }}{{ $hi_info['hi'] }}">{{ $hi_info['hi'] }} </label>
                       @endforeach
                      <br>
                     @endforeach
                  </div>
                </div>

                <div class="col-md-4 pl-1">
                  <label>(来場者)予定日時</label>
                  <div class="msr_radio_01">
                    @foreach($_conf_raijyou_yotei_time as $ymd => $info)
                    <label style="min-width: 7em;">{{ $info['disp_ymd'] }}</label>
                      @foreach($info['his'] as $hi_info)
                        <input type="checkbox" class="largechkbox" name="user_raijyou_yotei_time[]" value="{{ $ymd }} {{ $hi_info['hi'] }}" {{ $hi_info['checked'] }} id="raijyou_{{ $ymd }}{{ $hi_info['hi'] }}" />
                        <label for="raijyou_{{ $ymd }}{{ $hi_info['hi'] }}">{{ $hi_info['hi'] }} </label>
                       @endforeach
                      <br>
                     @endforeach
                  </div>
                </div>

                <div class="col-md-4 pl-1">
                  <label>WEB</label>
                  <div class="msr_radio_01">
                      <input type="checkbox" class="largechkbox" name="user_web" id="user_web" value="1" @if($search_condition['user_web']=="1")checked @endif/>
                      <label for="user_web">WEB展示会（ガイドブック）招待者</label>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-4 pl-1">
                  <label>未来場者</label>
                  <div class="msr_radio_01">
                      <input type="checkbox" class="largechkbox" name="user_miraijyou" id="user_miraijyou" value="1" @if($search_condition['user_miraijyou']=="1")checked @endif/>
                      <label for="user_miraijyou">未来場者を対象とする</label>
                  </div>
                </div>
              </div>

              <!-- 開閉用ボタンここから -->
              <input id="open" type="checkbox" class="openclose" @if($soyusai_cond_open=="1")checked @endif>
              <label for="open" class="open_btn">
                  <span>さらに表示する</span>
                  <span>閉じる</span>
              </label>
              <!-- 開閉用ボタンここまで -->

              <div class="open_content">

                  <!-- ***************************** -->
                  <div class="row">
                    <div class="col-md-3 pl-1">
                      <div class="msr_pulldown_01">
                        <label>担当者エリア名</label>
                        <select name="admin_tanarea_id[]" style="width: 100% !important;" multiple="multiple" class="select-multiple">
                          <option value="">選択してください</option>
                          {!! blade_html_options(['options' => $_conf_tanarea, 'selected' => $search_condition['admin_tanarea_id']]) !!}
                        </select>
                        <p class="small">※Ctrlキーで複数選択できます</p>
                      </div>
                    </div>

                    <div class="col-md-3 pl-1">
                      <div class="msr_pulldown_01">
                        <label>担当者支店名・部署名</label>
                        <!-- {{-- <select name="admin_syozoku_id" onChange="syozokuChange();"> --}} -->

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

                  </div>

                  <!-- ***************************** -->
                  <div class="row">

                    <div class="col-md-3 pl-1">
                      <div class="msr_text_01">
                        <label>タグ文字列（来場日時登録）</label>
                        <input type="text" name="user_tag" value="{{ $search_condition['user_tag'] }}" style="width:50%;"/>
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

                    <div class="col-md-3 pl-1">
                      <div class="msr_pulldown_01">
                        <label style="width:350px;">マイページ設定URL通知メール送信状態</label>
                        <select name="mail_status" style="width:240px;">
                          {!! blade_html_options(['options' => $_conf_mail_status, 'selected' => $search_condition['mail_status']]) !!}
                        </select>
                      </div>
                    </div>

                    <div class="col-md-3 pl-1">
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

                    <div class="col-md-3 pl-1">
                      <div class="msr_pulldown_01">
                        <label style="width:350px;">来場日時</label>
                        <select name="raijyou_ymd[]" style="width:240px;" multiple="multiple" class="select-multiple">
                          <option value=""></option>
                          @foreach($_conf_jitsu_raijyou_yotei_time as $ymd => $info)
                            @php($isChecked = false)
                            @foreach($search_condition['raijyou_ymd'] as $k => $i)
                              @if(!$isChecked && $ymd == $i)
                                @php($isChecked = true)
                               @endif
                             @endforeach
                            <option value="{!! $ymd !!}" @if($isChecked )selected @endif>{{ $info['disp_ymd'] }}</option>
                           @endforeach
                        </select>
                        <p class="small">※Ctrlキーで複数選択できます</p>
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
                      <div class="msr_pulldown_01">
                        <label>並び順</label>
                        <select name="order_by">
                          <option value="">選択してください</option>
                          {!! blade_html_options(['options' => $order_by_arr, 'selected' => $search_condition['order_by']]) !!}
                        </select>
                      </div>
                    </div>

                  </div>

              </div>

              <div class="row">
                <div class="update ml-auto">
                  <button type="button" class="btn btn-primary btn-round" onClick="document.search_form.exec.value='search';document.search_form.submit();">検　索</button>
                  <button type="button" class="btn btn-primary btn-round" onClick="location.href='?page=user_list';">リセット</button>
                  <!-- {{--
                  @if($login['admin_master_kengen'] == 1)
                    　　　　　　
                    <button type="button" class="btn btn-primary btn-round" style="width:400px;" onClick="_passSetUrlCsv();">パスワード設定URL案内CSVダウンロード</button>
                   @endif
                  --}} -->
                  @if($login['admin_user_kengen'] < 2)
                    <button type="button" onClick="_userlistCsv();" class="btn btn-primary btn-round" style="width:270px;">上記条件でCSVダウンロード</button>
                   @endif
                  <!-- {{-- 2021/11/08 Add ********* Start ******** --}} -->
                  <button type="button" onClick="_userQrDl();" class="btn btn-primary btn-round" style="width:320px;">上記条件でQR一括ダウンロード</button>
                  <!-- {{-- 2021/11/08 Add ********* End ******** --}} -->
                </div>
              </div>

            </form>
          </div><!-- <div class="card card-user"> -->
        </div><!-- <div class="col-md-12"> -->
      </div><!-- <div class="row"> -->

      @if($select_event_rec['event_archived_flg'] == '0')
      <button type="button" onClick="location.href='?page=syoutai_raijyoudata_make';" class="btn btn-primary btn-round" style="width:250px;">来場者マスタから招待する</button>
        @if($login['admin_master_kengen'] == 1)
        <button type="button" class="btn btn-primary btn-round" style="width:250px;" data-toggle="modal" data-target="#qrzipModal">QRZIPダウンロード</button>
         @else 
        <button type="button" onClick="_szkgrp_qrzip_dl();" class="btn btn-primary btn-round" style="width:250px;" >QRZIPダウンロード</button>
         @endif
       @endif
<!--
      @if($login['admin_master_kengen'] == 1)
        <button type="button" onClick="location.href='?page=user_edit';" class="btn btn-primary btn-round">新規登録</button>
        <button type="button" onClick="location.href='?page=user_ikkatsu_touroku';" class="btn btn-primary btn-round" style="width:200px;">来場者一括新規登録</button>        　
       @endif
-->

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
                          退場日時
                        </th>
                        <th>
                          ﾊﾟｽﾜｰﾄﾞ<br>設定状態
                        </th>
                        <th>
                          ﾏｲﾍﾟｰｼﾞ設定<br>URL通知
                        </th>
                        <th style="padding-left:50px;">
                          QR
                        </th>
                        <th style="padding-left:25px;">
                          　 詳細
                        </th>
                      </thead>
                      <tbody>
                      @foreach($main_recs as $rec)
                        <tr>
                          <td>
                            {{ $rec['disp_big_cate'] }}
                          </td>
                          <td>
                            @if(empty($rec['user_company_id']))
                              {{ $rec['user_kigyou_name'] }}
                             @else 
                              {{ $rec['user_company_name'] }}
                             @endif
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
                            {!! $rec['disp_min_kinout_time_out'] !!}
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
                            @if($rec['user_raijyou_yotei_time'] != '')
                            <button type="button" class="btn btn-primary btn-round" onClick="qr_dl('{{ $rec['user_id'] }}','{{ $rec['user_event_id'] }}');">QRDL</button>
                             @endif
                          </td>
                          <td>
  	                      <button type="button" onClick="location.href='?page=user_edit&id={{ $rec['user_id'] }}';" class="btn btn-primary btn-round">詳細</button>
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
     @else 
    <div class="col-md-12">
      <div class="card">
        <div class="card-body">
          @include('parts.select_event')
        </div>
      </div>
    </div>

     @endif
<!-- Modal -->
<div class="modal fade" id="qrzipModal" tabindex="-1" role="dialog" aria-labelledby="qrzipModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="qrzipModalLabel">ダウンロード対象閲覧部署グループ選択</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form name="zip_form" action="./" method="post">
        <input type="hidden" name="page" value="{{ $page }}">
        <input type="hidden" name="exec" value="szkgrp_qrzipdl">
        <div class="modal-body" style="height: 70vh; overflow-y: auto">
            <label>閲覧部署グループ名 絞り込み</label>
          <div class="mb-2">
            <input type="text" placeholder="名称" name="search_word" id="search_word">
          </div>

            <table class="table table-bordered">
              <thead>
              <tr>
                <th scope="col" class="align-middle text-center tableFixHeadTh1">名称</th>
              </tr>
              </thead>
              <tbody  id="szkgrps">
              @foreach($_conf_syozoku_grp as $k => $rec)
              <tr>
                <td class="search_words">
                  <input type="radio" id="{!! $k !!}" name="szkgrp_id" value="{!! $k !!}">
                  <label for="{!! $k !!}">{!! $rec !!}</label>
                </td>
              </tr>
               @endforeach
              </tbody>
            </table>

        </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" style="width:160px;" data-dismiss="modal">キャンセル</button>
        <button type="submit" class="btn btn-primary" style="width:160px;">ダウンロード</button>
      </div>
      </form>
    </div>
  </div>
</div>
<script>
  function search(target, values){
    for (var i = 0, len = values.length; i < len; ++i) {
      if (($(target).text().toLowerCase().indexOf(values[i]) > -1) == false) {
        return false;
      }
    }
    return true;
  }
  function toggle(value){
    value = value.replace(/　/g, ' ');
    var values = value.split(' ');
    $("#szkgrps tr .search_words").filter(function() {
      $(this).parent().toggle(
        search(this, values)
      )
    });
  }
  $("#search_word").on("keyup", function() {
    var value = $(this).val().toLowerCase();
    toggle(value);
  });
</script>