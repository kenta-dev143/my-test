@if($page_flg != 0)
<div style="font-size: 12px;">
  @if($page_back == 1)
    <a class="a-syoutai" href="javascript:_pageChange('{!! $offset_first !!}');void(0);">&lt;&lt;最初へ</a>&nbsp;
    <a class="a-syoutai" href="javascript:_pageChange('{!! $offset_back !!}');void(0);">&lt;前へ</a>
   @endif
  @if($start_dot == 1)<a class="a-syoutai" href="javascript:_pageChange('{!! $offset_back_page !!}');void(0);">…</a> @endif
  @foreach($page_navi as $navi)
    @if($navi['link'] != 0)
      <a class="a-syoutai" href="javascript:_pageChange('{!! $navi['offset'] !!}');void(0);">{!! $navi['page_num'] !!}</a>
     @else 
      {!! $navi['page_num'] !!}
     @endif
    @if($navi['separator'] == 1)
      |
     @endif
   @endforeach
  @if($end_dot == 1)<a class="a-syoutai" href="index.php?page={!! $page !!}&offset={!! $offset_next_page !!}">…</a> @endif
  @if($page_next == 1)
    <a class="a-syoutai" href="javascript:_pageChange('{!! $offset_next !!}');void(0);">次へ&gt;</a>&nbsp;
    <a class="a-syoutai" href="javascript:_pageChange('{!! $offset_last !!}');void(0);">最後へ&gt;&gt;</a>
   @endif
  <br>
  ({!! $count !!}件中　{!! $st_count !!}件～{!! $en_count !!}件目)
</div>
 @endif
