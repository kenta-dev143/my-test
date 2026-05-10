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
