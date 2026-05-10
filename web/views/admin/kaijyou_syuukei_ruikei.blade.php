        <div class="row" style="margin-top:10px;">
          <div class="col-md-12">
            <div class="card">
            @if($select_event_id!="")
              @if(count($event_syoutai_yotei_ymd_array) > 0)
              <div class="card-header">
                <h4 class="card-title">累計期間</h4>
                <ul class="list-group list-group-horizontal mb-4">
                  @foreach($event_syoutai_yotei_ymd_array as $ymd)
                  <li class="h5 my-1">{{ $ymd }}@if(!$loop->last)、 @endif</li>
                   @endforeach
                </ul>
              </div>
            </div>

            <div class="card">
              <div class="card-header">
                <h4 class="card-title">大分類別-入場者数</h4>
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
                      @foreach($main_recs as $rec)
                        <tr>
                          <td>
                            {{ $rec['time'] }}
                          </td>
                          @foreach($_conf_big_cate1 as $bcate_id => $bcate_name)
                          <td>
                            {{ $rec[$bcate_id] }}
                          </td>
                           @endforeach
                          <td>
                            {{ $rec['b_goukei'] }}
                          </td>
                          @foreach($_conf_big_cate2_html as $bcate_id => $bcate_name)
                          <td>
                            {{ $rec[$bcate_id] }}
                          </td>
                           @endforeach
                          <td>
                            {{ $rec['all_goukei'] }}
                          </td>
                          <td>
                            {{ $rec['taijyou_cnt'] }}
                          </td>
                        </tr>
                       @endforeach
                    </tbody>
                  </table>
                </div>
              </div>

              <div class="card-header">
                <h4 class="card-title">担当エリア別-入場者数（招待者）</h4>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table">
                    <thead class="text-primary">
                      <tr>
                        <th>時間帯</th>
                        @foreach($m_tantou_area as $k => $v)
                          <th>{{ $v }}</th>
                         @endforeach
                        <th>担当エリア別_合計</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($area_summary as $k => $row)
                        <tr>
                          <td>
                            @if($loop->last)
                              エリア別招待者_合計
                             @else 
                              {{ $row['time'] }}
                             @endif
                          </td>
                          @foreach($m_tantou_area as $tk => $tv)
                            <td>{{ $row[$tk] }}</td>
                           @endforeach
                          <td>{{ $row['total'] }}</td>
                        </tr>
                       @endforeach
                    </tbody>
                  </table>
                </div>
              </div>

               @else 
                <div style="padding-left: 5em;">
                  <br><br><br><br>
                  <span>イベント管理から(招待者)来場予定日時を設定してください</span>
                  <br><br>
                  <button type="button" onClick="location.href='?page=event_edit&id={!! $select_event_rec['event_id'] !!}';" class="btn btn-primary btn-round" style="width:200px;">設定を行う</button>
                  <br><br><br><br><br>
                </div>
               @endif
             @else 
              @include('parts.select_event')
             @endif
            </div>
          </div>



        </div>