@verbatim
<script>
  $(window).on('load', function() {
    setTimeout(function(){
      location.reload();
    },30000);
  });

  function _userlistCsv(){
    document.form_list.exec.value='area_csv';
    document.form_list.submit();
  }
</script>
@endverbatim
        <div class="row" style="margin-top:10px;">
          <div class="col-md-12">
            <div class="card">
            @if($select_event_id!="")
              @if($no_area == 1)
              <div class="card-header">
                <h3>選択中のイベントではエリアが設定されていません</h3>
              </div>
               @else 
              <form name="form_list" action="?page=kaijyou_area_syuukei" method="post">
              <input type="hidden" name="exec" value="search">
                <div class="msr_pulldown_01">
                  <h4 class="card-title">日付</h4>
                  <select name="select_ymd" onchange="document.form_list.submit();">
                    {!! blade_html_options(['options' => $select_ymd_arr, 'selected' => $search_condition['select_ymd']]) !!}
                  </select>
                </div>
              </form>

              <div class="card-header">
                <h3>エリア別-大分類別-滞在者数 (※30秒自動更新)</h3>
              </div>

              <div class="card-body">
                <div class="table-responsive">
                  <table class="table">
                    <thead class=" text-primary">
                    <th style="color:#000000;">
                      エリア
                    </th>
                    @foreach($_conf_big_cate as $big_cate_name)
                    <th>{{ $big_cate_name }}</th>
                     @endforeach
                    <th>
                      合計
                    </th>
                    </thead>
                    <tbody>
                    @foreach($area_names as $area_id => $area_name)
                    <tr>
                      <td>
                        {{ $area_name }}
                      </td>
                      @foreach($_conf_big_cate as $bcate_id => $bcate_name)
                      <td>
                        {{ ($taizai_recs[$area_id][$bcate_id]['cnt'] ?? '0') }}
                      </td>
                       @endforeach
                      <td>
                        {{ $area_total[$area_id] }}
                      </td>
                    </tr>
                     @endforeach
                    <tr>
                      <td>
                        全エリア
                      </td>
                      @foreach($_conf_big_cate as $bcate_id => $bcate_name)
                      <td>
                        {{ ($big_cate_total[$bcate_id] ?? '0') }}
                      </td>
                       @endforeach
                      <td>
                        {{ $all_total }}
                      </td>
                    </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <div class="card-header">
                <h3>エリア別-大分類別-入場者数</h3>
              </div>

              <div class="card-header row">
                <div class="update ml-auto mr-3">
                  <button type="button" onClick="_userlistCsv();" class="btn btn-primary btn-round" style="width:200px;">CSVダウンロード</button>
                </div>
              </div>

              @foreach($main_recs as $area_id => $rec)
              <div class="card-header">
                <h4 class="card-title">{{ $area_names[$area_id] }}</h4>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table">
                    <thead class=" text-primary">
                      <th style="color:#000000;">
                        時刻
                      </th>
                      @foreach($_conf_big_cate1 as $big_cate_name)
                        <th>{{ $big_cate_name }}</th>
                       @endforeach
                      <th>
                        合計(招待)
                      </th>
                      @foreach($_conf_big_cate2_html as $big_cate_name)
                        <th style="color:#6666ff;">{!! $big_cate_name !!}</th>
                       @endforeach
                      <th style="color:#000000;">
                        入場<br>合計
                      </th>
                      <th style="color:#ff7777;">
                        退場
                      </th>
                    </thead>
                    <tbody>
                      @foreach($rec as $area_rec)
                        <tr>
                          <td>
                            {{ $area_rec['time'] }}
                          </td>
                          @foreach($_conf_big_cate1 as $bcate_id => $bcate_name)
                          <td>
                            {{ $area_rec[$bcate_id] }}
                          </td>
                           @endforeach
                          <td>
                            {{ $area_rec['b_goukei'] }}
                          </td>
                          @foreach($_conf_big_cate2_html as $bcate_id => $bcate_name)
                          <td>
                            {{ $area_rec[$bcate_id] }}
                          </td>
                           @endforeach
                          <td>
                            {{ $area_rec['all_goukei'] }}
                          </td>
                          <td>
                            {{ $area_rec['taijyou_cnt'] }}
                          </td>
                        </tr>
                       @endforeach
                    </tbody>
                  </table>
                </div>
              </div>
               @endforeach
               @endif
             @else 
              @include('parts.select_event')
             @endif
            </div>
          </div>



        </div>