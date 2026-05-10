@if($page_flg != 0)
<div style="font-size: 12px;">
  @if($page_back == 1)
    <a href="index.php?page={!! $page !!}&offset={!! $offset_first !!}">&lt;&lt;最初へ</a>&nbsp;
    <a href="index.php?page={!! $page !!}&offset={!! $offset_back !!}">&lt;前へ</a>
   @endif
  @if($start_dot == 1)<a href="index.php?page={!! $page !!}&offset={!! $offset_back_page !!}">…</a> @endif
  @foreach($page_navi as $navi)
    @if($navi['link'] != 0)
      <a href="index.php?page={!! $page !!}&offset={!! $navi['offset'] !!}">{!! $navi['page_num'] !!}</a>
     @else 
      {!! $navi['page_num'] !!}
     @endif
    @if($navi['separator'] == 1)
      |
     @endif
   @endforeach
  @if($end_dot == 1)<a href="index.php?page={!! $page !!}&offset={!! $offset_next_page !!}">…</a> @endif
  @if($page_next == 1)
    <a href="index.php?page={!! $page !!}&offset={!! $offset_next !!}">次へ&gt;</a>&nbsp;
    <a href="index.php?page={!! $page !!}&offset={!! $offset_last !!}">最後へ&gt;&gt;</a>
   @endif
  <br>
  ({!! $count !!}件中　{!! $st_count !!}件～{!! $en_count !!}件目)
</div>
 @endif
