@verbatim
<style>
  .table-bordered th {
    border: 1px solid #dee2e6 !important;
  }
</style>

<script>
  function _downloadCsv(){
    document.search_form.exec.value='download_csv';
    document.search_form.submit();
  }  
</script>
@endverbatim

@php($zero_divide_value = '0%')

<div class="row" style="margin-top:10px;">
  <div class="col-md-12">
    <div class="card">
    @if($error_pattern == "")
      <form name="search_form" action="?page=kaijyou_syuukei_hikaku" method="post">
        <input type="hidden" name="exec" value="search">
        <div>
          <h4>{{ $select_event_rec['event_pulldown_name'] }}</h4>
          <select name="select_ymd">
            {!! blade_html_options(['options' => $select_ymd_arr, 'selected' => $search_condition['select_ymd']]) !!}
          </select>
        </div>
        <div>
          <h4>{{ $compare_event['event_pulldown_name'] }}</h4>
          <select name="compare_ymd">
            {!! blade_html_options(['options' => $compare_ymd_arr, 'selected' => $search_condition['compare_ymd']]) !!}
          </select>
        </div>
        <div class="row">
          <div class="update ml-auto mr-auto">
            <button type="button" class="btn btn-primary btn-round" onclick="document.search_form.exec.value='search';document.search_form.submit();">表　示</button>
            <button type="button" class="btn btn-primary btn-round" onclick="location.href='?page=kaijyou_syuukei_hikaku';">リセット</button>
            <button type="button" onClick="_downloadCsv();" class="btn btn-primary btn-round" style="width:270px;">上記条件でCSVダウンロード</button>
          </div>
        <div>
      </form>
    @elseif($error_pattern == 1)
      @include('parts.select_event')
    @elseif($error_pattern == 2)
      <div style="padding-left: 5em;">
        <br><br><br><br>
        <span>比較するイベントを設定してください</span>
        <br><br>
        <button type="button" onClick="location.href='?page=event_edit&id={!! $select_event_rec['event_id'] !!}';" class="btn btn-primary btn-round" style="width:200px;">設定を行う</button>
        <br><br><br><br><br>
      </div>
     @endif
    </div>
  </div>
</div>

@if(count($event_summary) > 0)
<div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="card-header">
        <h4 class="card-title">大分類別-入場者数</h4>
      </div>

      <div class="card-body">
        <h5>入場者_合計（①招待者 + ③メーカー（出展））</h5>
        <div class="table-responsive">
          <table class="table table-bordered">
            <thead class="text-primary">
            <tr>
              <th style="text-align: center"></th>
              @foreach($_conf_big_cate1 as $k => $v)
              <th style="text-align: center" colspan="3">{{ $v }}</th>
               @endforeach
              <th style="text-align: center" colspan="3">招待者_合計</th>
              <th style="text-align: center" colspan="3">{{ $_conf_big_cate2[5] }}</th>
              <th style="text-align: center" colspan="3">合計</th>
            </tr>
            <tr>
              <th style="text-align: center">時間帯</th>
              @foreach($_conf_big_cate1 as $k => $v)
              <th style="text-align: left">{{ $event_summary['select']['name'] }}</th>
              <th style="text-align: left">{{ $event_summary['compare']['name'] }}</th>
              <th style="text-align: center">前回比</th>
               @endforeach
              <th style="text-align: left">{{ $event_summary['select']['name'] }}</th>
              <th style="text-align: left">{{ $event_summary['compare']['name'] }}</th>
              <th style="text-align: center">前回比</th>
              <th style="text-align: left">{{ $event_summary['select']['name'] }}</th>
              <th style="text-align: left">{{ $event_summary['compare']['name'] }}</th>
              <th style="text-align: center">前回比</th>
              <th style="text-align: left">{{ $event_summary['select']['name'] }}</th>
              <th style="text-align: left">{{ $event_summary['compare']['name'] }}</th>
              <th style="text-align: center">前回比</th>
            </tr>
            </thead>
            <tbody>
            @foreach($event_summary['select']['summary'] as $k => $row)
            <tr>
              <td>
                @if($loop->last)
                招待者_合計
                 @else 
                {{ $row['time'] }}
                 @endif
              </td>
              @foreach($_conf_big_cate1 as $bk => $bv)
              <td>{{ $row[$bk] }}</td>
              <td>{{ $event_summary['compare']['summary'][$k][$bk] }}</td>
              <td>
                @if($event_summary['compare']['summary'][$k][$bk] == 0)
                {!! $zero_divide_value !!}
                 @else 
                @php($calc = ($row[$bk] / $event_summary['compare']['summary'][$k][$bk]) * 100)
                {!! $calc !!}%
                 @endif
              </td>
               @endforeach
              <td>{{ $row['syoutai_total'] }}</td>
              <td>{{ $event_summary['compare']['summary'][$k]['syoutai_total'] }}</td>
              <td>
                @if($event_summary['compare']['summary'][$k]['syoutai_total'] == 0)
                {!! $zero_divide_value !!}
                 @else 
                @php($calc = ($row['syoutai_total'] / $event_summary['compare']['summary'][$k]['syoutai_total']) * 100)
                {!! $calc !!}%
                 @endif
              </td>
              <td>{{ $row[5] }}</td>
              <td>{{ $event_summary['compare']['summary'][$k][5] }}</td>
              <td>
                @if($event_summary['compare']['summary'][$k][5] == 0)
                {!! $zero_divide_value !!}
                 @else 
                @php($calc = ($row[5] / $event_summary['compare']['summary'][$k][5]) * 100)
                {!! $calc !!}%
                 @endif
              </td>
              @php($total_compare = $row['syoutai_total'] + $row[5])
              @php($total_current = $event_summary['compare']['summary'][$k]['syoutai_total'] + $event_summary['compare']['summary'][$k][5])
              <td>{{ $total_compare }}</td>
              <td>{{ $total_current }}</td>
              <td>
                @if($total_compare == 0 || $total_current == 0)
                {!! $zero_divide_value !!}
                 @else 
                @php($calc = ($total_compare / $total_current) * 100)
                {!! $calc !!}%
                 @endif
              </td>
            </tr>
             @endforeach

            </tbody>
          </table>
        </div>
      </div>

      <div class="card-body">
        <h5>入場者_合計（①招待者+②来場者）</h5>
        <div class="table-responsive">
          <table class="table table-bordered">
            <thead class="text-primary">
              <tr>
                <th></th>
                <th colspan="3" style="text-align: center">入場者_合計</th>
              </tr>
              <tr>
                <th style="text-align: left">時間帯</th>
                <th style="text-align: left">{{ $event_summary['select']['name'] }}</th>
                <th style="text-align: left">{{ $event_summary['compare']['name'] }}</th>
                <th style="text-align: center">前回比</th>
              </tr>
            </thead>
            <tbody>
              @foreach($event_summary['select']['summary'] as $k => $row)
                <tr>
                  <td>
                    @if($loop->last)
                      入場者_合計
                     @else 
                      {{ $row['time'] }}
                     @endif
                  </td>
                  <td>{{ $row['total'] }}</td>
                  <td>{{ $event_summary['compare']['summary'][$k]['total'] }}</td>
                  <td>
                    @if($event_summary['compare']['summary'][$k]['total'] == 0)
                      {!! $zero_divide_value !!}
                     @else 
                      @php($calc = ($row['total'] / $event_summary['compare']['summary'][$k]['total']) * 100)
                      {!! $calc !!}%
                     @endif
                  </td>
                </tr>
               @endforeach
            </tbody>
          </table>
        </div>
      </div>

      <div class="card-body">
        <h5>①招待者</h5>
        <div class="table-responsive">
          <table class="table table-bordered">
            <thead class="text-primary">
              <tr>
                <th style="text-align: center"></th>
                @foreach($_conf_big_cate1 as $k => $v)
                  <th style="text-align: center" colspan="3">{{ $v }}</th>
                 @endforeach
                <th style="text-align: center" colspan="3">招待者_合計</th>
              </tr>
              <tr>
                <th style="text-align: center">時間帯</th>
                @foreach($_conf_big_cate1 as $k => $v)
                  <th style="text-align: left">{{ $event_summary['select']['name'] }}</th>
                  <th style="text-align: left">{{ $event_summary['compare']['name'] }}</th>
                  <th style="text-align: center">前回比</th>
                 @endforeach
                <th style="text-align: left">{{ $event_summary['select']['name'] }}</th>
                <th style="text-align: left">{{ $event_summary['compare']['name'] }}</th>
                <th style="text-align: center">前回比</th>
              </tr>
            </thead>
            <tbody>
              @foreach($event_summary['select']['summary'] as $k => $row)
                <tr>
                  <td>
                    @if($loop->last)
                      招待者_合計
                     @else 
                      {{ $row['time'] }}
                     @endif
                  </td>
                  @foreach($_conf_big_cate1 as $bk => $bv)
                    <td>{{ $row[$bk] }}</td>
                    <td>{{ $event_summary['compare']['summary'][$k][$bk] }}</td>
                    <td>
                      @if($event_summary['compare']['summary'][$k][$bk] == 0)
                        {!! $zero_divide_value !!}
                       @else 
                        @php($calc = ($row[$bk] / $event_summary['compare']['summary'][$k][$bk]) * 100)
                        {!! $calc !!}%
                       @endif
                    </td>
                   @endforeach
                  <td>{{ $row['syoutai_total'] }}</td>
                  <td>{{ $event_summary['compare']['summary'][$k]['syoutai_total'] }}</td>
                  <td>
                    @if($event_summary['compare']['summary'][$k]['syoutai_total'] == 0)
                      {!! $zero_divide_value !!}
                     @else 
                      @php($calc = ($row['syoutai_total'] / $event_summary['compare']['summary'][$k]['syoutai_total']) * 100)
                      {!! $calc !!}%
                     @endif
                  </td>
                </tr>
               @endforeach
            </tbody>
          </table>
        </div>
      </div>

      <div class="card-body">
        <h5>②来場者</h5>
        <div class="table-responsive">
          <table class="table table-bordered">
            <thead class="text-primary">
              <tr>
                <th style="text-align: center"></th>
                @foreach($_conf_big_cate2 as $k => $v)
                  <th style="text-align: center" colspan="3">{{ $v }}</th>
                 @endforeach
                <th style="text-align: center" colspan="3">来場者_合計</th>
              </tr>
              <tr>
                <th style="text-align: center">時間帯</th>
                @foreach($_conf_big_cate2 as $k => $v)
                  <th style="text-align: left">{{ $event_summary['select']['name'] }}</th>
                  <th style="text-align: left">{{ $event_summary['compare']['name'] }}</th>
                  <th style="text-align: center">前回比</th>
                 @endforeach
                <th style="text-align: left">{{ $event_summary['select']['name'] }}</th>
                <th style="text-align: left">{{ $event_summary['compare']['name'] }}</th>
                <th style="text-align: center">前回比</th>
              </tr>
            </thead>
            <tbody>
              @foreach($event_summary['select']['summary'] as $k => $row)
                <tr>
                  <td>
                    @if($loop->last)
                      来場者_合計
                     @else 
                      {{ $row['time'] }}
                     @endif
                  </td>
                  @foreach($_conf_big_cate2 as $bk => $bv)
                    <td>{{ $row[$bk] }}</td>
                    <td>{{ $event_summary['compare']['summary'][$k][$bk] }}</td>
                    <td>
                      @if($event_summary['compare']['summary'][$k][$bk] == 0)
                        {!! $zero_divide_value !!}
                       @else 
                        @php($calc = ($row[$bk] / $event_summary['compare']['summary'][$k][$bk]) * 100)
                        {!! $calc !!}%
                       @endif
                    </td>
                   @endforeach
                  <td>{{ $row['raijyou_total'] }}</td>
                  <td>{{ $event_summary['compare']['summary'][$k]['raijyou_total'] }}</td>
                  <td>
                    @if($event_summary['compare']['summary'][$k]['raijyou_total'] == 0)
                      {!! $zero_divide_value !!}
                     @else 
                      @php($calc = ($row['raijyou_total'] / $event_summary['compare']['summary'][$k]['raijyou_total']) * 100)
                      {!! $calc !!}%
                     @endif
                  </td>
                </tr>
               @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <h4 class="card-title">担当エリア別-入場者数（招待者）</h4>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          @php($criterion = 6)
          <table class="table table-bordered">
            <thead class="text-primary">
              <tr>
                <th style="text-align: center"></th>
                @foreach($m_tantou_area as $k => $v)
                  @if($k >= $criterion)
                    @continue
                   @endif
                  <th style="text-align: center" colspan="3">{{ $v }}</th>
                 @endforeach
              </tr>
              <tr>
                <th style="text-align: center">時間帯</th>
                @foreach($m_tantou_area as $k => $v)
                  @if($k >= $criterion)
                    @continue
                   @endif
                  <th style="text-align: left">{{ $area_summary['select']['name'] }}</th>
                  <th style="text-align: left">{{ $area_summary['compare']['name'] }}</th>
                  <th style="text-align: center">前回比</th>
                 @endforeach
              </tr>
            </thead>
            <tbody>
              @foreach($area_summary['select']['summary'] as $k => $row)
                <tr>
                  <td>
                    @if($loop->last)
                      エリア別招待者_合計
                     @else 
                      {{ $row['time'] }}
                     @endif
                  </td>
                  @foreach($m_tantou_area as $tk => $tv)
                    @if($tk >= $criterion)
                      @continue
                     @endif
                    <td>{{ $row[$tk] }}</td>
                    <td>{{ $area_summary['compare']['summary'][$k][$tk] }}</td>
                    <td>
                      @if($area_summary['compare']['summary'][$k][$tk] == 0)
                        {!! $zero_divide_value !!}
                       @else 
                        @php($calc = ($row[$tk] / $area_summary['compare']['summary'][$k][$tk]) * 100)
                        {!! $calc !!}%
                       @endif
                    </td>
                   @endforeach
                </tr>
               @endforeach
            </tbody>
          </table>
        </div>

        <div class="table-responsive">
          @php($criterion = 5)
          <table class="table table-bordered">
            <thead class="text-primary">
              <tr>
                <th style="text-align: center"></th>
                @foreach($m_tantou_area as $k => $v)
                  @if($k <= $criterion)
                    @continue
                   @endif
                  <th style="text-align: center" colspan="3">{{ $v }}</th>
                 @endforeach
                <th style="text-align: center" colspan="3">担当エリア別_合計</th>
              </tr>
              <tr>
                <th style="text-align: center">時間帯</th>
                @foreach($m_tantou_area as $k => $v)
                  @if($k <= ($criterion - 1))
                    @continue
                   @endif
                  <th style="text-align: left">{{ $area_summary['select']['name'] }}</th>
                  <th style="text-align: left">{{ $area_summary['compare']['name'] }}</th>
                  <th style="text-align: center">前回比</th>
                 @endforeach
              </tr>
            </thead>
            <tbody>
              @foreach($area_summary['select']['summary'] as $k => $row)
                <tr>
                  <td>
                    @if($loop->last)
                      エリア別招待者_合計
                     @else 
                      {{ $row['time'] }}
                     @endif
                  </td>
                  @foreach($m_tantou_area as $tk => $tv)
                    @if($tk <= $criterion)
                      @continue
                     @endif
                    <td>{{ $row[$tk] }}</td>
                    <td>{{ $area_summary['compare']['summary'][$k][$tk] }}</td>
                    <td>
                      @if($area_summary['compare']['summary'][$k][$tk] == 0)
                        {!! $zero_divide_value !!}
                       @else 
                        @php($calc = ($row[$tk] / $area_summary['compare']['summary'][$k][$tk]) * 100)
                        {!! $calc !!}%
                       @endif
                    </td>
                   @endforeach
                  <td>{{ $area_summary['select']['summary'][$k]['total'] }}</td>
                  <td>{{ $area_summary['compare']['summary'][$k]['total'] }}</td>
                  <td>
                    @if($area_summary['compare']['summary'][$k]['total'] == 0)
                      {!! $zero_divide_value !!}
                     @else 
                      @php($calc = ($area_summary['select']['summary'][$k]['total'] / $area_summary['compare']['summary'][$k]['total']) * 100)
                      {!! $calc !!}%
                     @endif
                  </td>
                </tr>
               @endforeach
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>
</div>
 @endif