@verbatim
<script>
  $(window).on('load', function() {
    setTimeout(function(){
      location.reload();
    },30000);
  });
</script>
@endverbatim
        <div class="row" style="margin-top:10px;">
          <div class="col-md-12">
            <div class="card">
            @if($select_event_id!="")
              <form name="form_list" action="?page=area_syuukei" method="post">
              <input type="hidden" name="exec" value="search">
                <div class="msr_pulldown_01">
                  <h4 class="card-title">日付</h4>
                  <select name="select_ymd" onchange="document.form_list.submit();">
                    {!! blade_html_options(['options' => $select_ymd_arr, 'selected' => $search_condition['select_ymd']]) !!}
                  </select>
                </div>
              </form>

              <div class="card-header">
                <h4 class="card-title">エリア別-滞在者数 (※30秒自動更新)</h4>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table">
                    <thead class=" text-primary">
                      @foreach($area_recs as $arec)
                        <th>{{ $arec['area_name'] }}</th>
                       @endforeach
                      <th style="color:#ff8888;font-weight:bold;">全エリア</th>
                    </thead>
                    <tbody>
                      <tr>
                        @foreach($taizai_recs as $rec)
                          <td @if($rec['cnt'] > $rec['max'])style="color:red;" @endif>
                            {{ $rec['cnt'] }} / {{ $rec['max'] }} 
                          </td>
                         @endforeach
                        <td style="color:#ff8888;font-weight:bold;">{{ $all_area_cnt }} / {{ $all_area_max }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              @if($login['admin_syuukei_etsuran_kengen'] != 1)
              <div class="card-header">
                <h4 class="card-title">エリア別-入場者数</h4>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table">
                    <thead class=" text-primary">
                      <th>
                        時刻
                      </th>
                      @foreach($area_recs as $arec)
                        <th>{{ $arec['area_name'] }}</th>
                       @endforeach
                      <th style="color:#ff8888;font-weight:bold;">全エリア</th>
                    </thead>
                    <tbody>
                      @foreach($main_recs as $rec)
                        <tr>
                          <td>
                            {{ $rec['time'] }}
                          </td>
                          @foreach($area_recs as $arec)
                          <td>
                            {{ $rec[$arec['area_id']] }}
                          </td>
                           @endforeach
                          <td style="color:#ff8888;font-weight:bold;">{{ $rec['all_area'] }}</td>
                        </tr>
                       @endforeach
                    </tbody>
                  </table>
                </div>
              </div>
               @endif
             @else 
              @include('parts.select_event')
             @endif
            </div>
          </div>



        </div>
