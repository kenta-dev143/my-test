@verbatim
<script type="text/javascript">

function _regist(_mode){
    if(_mode =='delete'){
        if(window.confirm('本当に削除してもよろしいでしょうか？')){
            document.form_edit.mode.value=_mode;
            document.form_edit.submit();
        }
    }else{
        document.form_edit.mode.value=_mode;
        document.form_edit.submit();
    }
}

function _keishikiChk(obj, idx){
    switch (obj.value){
      case '2':
      case '3':
      case '4':
      case '5':
        document.getElementById('que'+idx).rowSpan = 4;
        document.getElementById('sentakushi'+idx).style.display = 'table-row';
        break;
      default:
        document.getElementById('que'+idx).rowSpan = 3;
        document.getElementById('sentakushi'+idx).style.display = 'none';
        break;
    }
}


</script>
@endverbatim
<div class="comTbl">
　<br>
@if($select_event_id!="")
    <p><span style="font-weight:bold;">※アンケート内容は回答者が既にいる場合、変更しないでください。</span></p>
    <p>必須項目は必ずご記入ください。</p>


  @if($err_msg['0'] !='')
    <div class="errArea">
      @foreach($err_msg as $msg)
        {{ $msg }}<br>
       @endforeach
    </div><br>
  @elseif($success_msg!="")
    <div class="successArea">{{ $success_msg }}</div>
   @endif


    <form  name="form_edit" action="?page={!! $page !!}" method="post" onSubmit="return false;" enctype="multipart/form-data">
    <input type="hidden" name="exec" value="save">
    <input type="hidden" name="mode">
    <input type="hidden" name="token" value="{{ $token }}">
    <input type="hidden" name="id" value="{{ $id }}">

    <div class="comTbl">
    <table class="list">
        <tr>
            <th colspan="2" style="background-color:#ea9c7a;">回答データダウンロード</th>
            <td style="background-color:#f3c2a6;">
                回答数：<span style="font-weight:bold;font-size:16px;">{!! $kaitou_suu !!}</span>件　<input type="button" onClick="@if($kaitou_suu==0)alert('まだ回答データがありません'); @else location.href='?page={!! $page !!}&exec=download'; @endif" value="回答データをダウンロードする">
            </td>
        </tr>
        <tr>
            <th colspan="2"><span class="must">必須</span>公開状態</th>
            <td>
                {!! blade_html_radios(['options' => $_conf_que_koukai_flg, 'name' => "que_koukai_flg", 'selected' => $que_koukai_flg, 'separator' => "&nbsp;&nbsp;&nbsp;"]) !!}
            </td>
        </tr>


        <!-- *************************************************************************** -->
        <tr>
            <th rowspan="{!! $row01 !!}" id="que01"><span class="must">必須</span>設問1</th>
            <th>設問文</th>
            <td>
                <input type="text" autocomplete="off" name="que_question_01" value="{{ $que_question_01 }}" style="width:400px;padding:4px;">
                ※設問文が空欄の場合、その設問は登録されません。
            </td>
        </tr>
        <tr>
            <th>入力</th>
            <td>
                <input type="radio" name="que_hissu_flg_01" value="1" @if($que_hissu_flg_01=="1")checked @endif>必須
                <input type="radio" name="que_hissu_flg_01" value="0" @if($que_hissu_flg_01=="0")checked @endif>なしでも可
            </td>
        </tr>
        <tr>
            <th>解答方式</th>
            <td>
                <input type="radio" name="que_keishiki_kbn_01" value="1" @if($que_keishiki_kbn_01=="1")checked @endif onChange="_keishikiChk(this,'01');"/>テキスト入力のみ<br />
                <input type="radio" name="que_keishiki_kbn_01" value="2" @if($que_keishiki_kbn_01=="2")checked @endif onChange="_keishikiChk(this,'01');"/>単一選択方式<br />
                <input type="radio" name="que_keishiki_kbn_01" value="3" @if($que_keishiki_kbn_01=="3")checked @endif onChange="_keishikiChk(this,'01');"/>単一選択方式＋テキスト入力<br />　　　テキスト入力コメント<input type="text" style="padding:4px;" autocomplete="off" name="que_keishiki_r_comment_01" value="{{ $que_keishiki_r_comment_01 }}" size="43"><br />
                <input type="radio" name="que_keishiki_kbn_01" value="4" @if($que_keishiki_kbn_01=="4")checked @endif onChange="_keishikiChk(this,'01');"/>チェックボックス方式<br />
                <input type="radio" name="que_keishiki_kbn_01" value="5" @if($que_keishiki_kbn_01=="5")checked @endif onChange="_keishikiChk(this,'01');"/>チェックボックス方式＋テキスト入力<br />　　　テキスト入力コメント<input type="text" style="padding:4px;" autocomplete="off" name="que_keishiki_c_comment_01" value="{{ $que_keishiki_c_comment_01 }}" size="43"><br />
            </td>
        </tr>
        <tr style="display:@if($que_keishiki_kbn_01 < '2' )none @else table-row @endif;" id="sentakushi01">
            <th>選択肢</th>
            <td>
                <textarea name="que_sentakushi_01" style="padding:4px;width:200px;height:100px;">{!! $que_sentakushi_01 !!}</textarea>
                ※選択肢は改行してリストを作成してください。
            </td>
        </tr>

        <!-- *************************************************************************** -->
        <tr>
            <th rowspan="{!! $row02 !!}" id="que02">設問2</th>
            <th>設問文</th>
            <td style="background-color:#F0F0F0;">
                <input type="text" autocomplete="off" name="que_question_02" value="{{ $que_question_02 }}" style="width:400px;padding:4px;">
            </td>
        </tr>
        <tr>
            <th>入力</th>
            <td style="background-color:#F0F0F0;">
                <input type="radio" name="que_hissu_flg_02" value="1" @if($que_hissu_flg_02=="1")checked @endif>必須
                <input type="radio" name="que_hissu_flg_02" value="0" @if($que_hissu_flg_02=="0")checked @endif>なしでも可
            </td>
        </tr>
        <tr>
            <th>解答方式</th>
            <td style="background-color:#F0F0F0;">
                <input type="radio" name="que_keishiki_kbn_02" value="1" @if($que_keishiki_kbn_02=="1")checked @endif onChange="_keishikiChk(this,'02');"/>テキスト入力のみ<br />
                <input type="radio" name="que_keishiki_kbn_02" value="2" @if($que_keishiki_kbn_02=="2")checked @endif onChange="_keishikiChk(this,'02');"/>単一選択方式<br />
                <input type="radio" name="que_keishiki_kbn_02" value="3" @if($que_keishiki_kbn_02=="3")checked @endif onChange="_keishikiChk(this,'02');"/>単一選択方式＋テキスト入力<br />　　　テキスト入力コメント<input type="text" style="padding:4px;" autocomplete="off" name="que_keishiki_r_comment_02" value="{{ $que_keishiki_r_comment_02 }}" size="43"><br />
                <input type="radio" name="que_keishiki_kbn_02" value="4" @if($que_keishiki_kbn_02=="4")checked @endif onChange="_keishikiChk(this,'02');"/>チェックボックス方式<br />
                <input type="radio" name="que_keishiki_kbn_02" value="5" @if($que_keishiki_kbn_02=="5")checked @endif onChange="_keishikiChk(this,'02');"/>チェックボックス方式＋テキスト入力<br />　　　テキスト入力コメント<input type="text" style="padding:4px;" autocomplete="off" name="que_keishiki_c_comment_02" value="{{ $que_keishiki_c_comment_02 }}" size="43"><br />
            </td>
        </tr>
        <tr style="display:@if($que_keishiki_kbn_02 < '2' )none @else table-row @endif;" id="sentakushi02">
            <th>選択肢</th>
            <td style="background-color:#F0F0F0;">
                <textarea name="que_sentakushi_02" style="padding:4px;width:200px;height:100px;">{!! $que_sentakushi_02 !!}</textarea>
                ※選択肢は改行してリストを作成してください。
            </td>
        </tr>


        <!-- *************************************************************************** -->
        <tr>
            <th rowspan="{!! $row03 !!}" id="que03">設問3</th>
            <th>設問文</th>
            <td>
                <input type="text" autocomplete="off" name="que_question_03" value="{{ $que_question_03 }}" style="width:400px;padding:4px;">
            </td>
        </tr>
        <tr>
            <th>入力</th>
            <td>
                <input type="radio" name="que_hissu_flg_03" value="1" @if($que_hissu_flg_03=="1")checked @endif>必須
                <input type="radio" name="que_hissu_flg_03" value="0" @if($que_hissu_flg_03=="0")checked @endif>なしでも可
            </td>
        </tr>
        <tr>
            <th>解答方式</th>
            <td>
                <input type="radio" name="que_keishiki_kbn_03" value="1" @if($que_keishiki_kbn_03=="1")checked @endif onChange="_keishikiChk(this,'03');"/>テキスト入力のみ<br />
                <input type="radio" name="que_keishiki_kbn_03" value="2" @if($que_keishiki_kbn_03=="2")checked @endif onChange="_keishikiChk(this,'03');"/>単一選択方式<br />
                <input type="radio" name="que_keishiki_kbn_03" value="3" @if($que_keishiki_kbn_03=="3")checked @endif onChange="_keishikiChk(this,'03');"/>単一選択方式＋テキスト入力<br />　　　テキスト入力コメント<input type="text" style="padding:4px;" autocomplete="off" name="que_keishiki_r_comment_03" value="{{ $que_keishiki_r_comment_03 }}" size="43"><br />
                <input type="radio" name="que_keishiki_kbn_03" value="4" @if($que_keishiki_kbn_03=="4")checked @endif onChange="_keishikiChk(this,'03');"/>チェックボックス方式<br />
                <input type="radio" name="que_keishiki_kbn_03" value="5" @if($que_keishiki_kbn_03=="5")checked @endif onChange="_keishikiChk(this,'03');"/>チェックボックス方式＋テキスト入力<br />　　　テキスト入力コメント<input type="text" style="padding:4px;" autocomplete="off" name="que_keishiki_c_comment_03" value="{{ $que_keishiki_c_comment_03 }}" size="43"><br />
            </td>
        </tr>
        <tr style="display:@if($que_keishiki_kbn_03 < '2' )none @else table-row @endif;" id="sentakushi03">
            <th>選択肢</th>
            <td>
                <textarea name="que_sentakushi_03" style="padding:4px;width:200px;height:100px;">{!! $que_sentakushi_03 !!}</textarea>
                ※選択肢は改行してリストを作成してください。
            </td>
        </tr>


        <!-- *************************************************************************** -->
        <tr>
            <th rowspan="{!! $row04 !!}" id="que04">設問4</th>
            <th>設問文</th>
            <td style="background-color:#F0F0F0;">
                <input type="text" autocomplete="off" name="que_question_04" value="{{ $que_question_04 }}" style="width:400px;padding:4px;">
            </td>
        </tr>
        <tr>
            <th>入力</th>
            <td style="background-color:#F0F0F0;">
                <input type="radio" name="que_hissu_flg_04" value="1" @if($que_hissu_flg_04=="1")checked @endif>必須
                <input type="radio" name="que_hissu_flg_04" value="0" @if($que_hissu_flg_04=="0")checked @endif>なしでも可
            </td>
        </tr>
        <tr>
            <th>解答方式</th>
            <td style="background-color:#F0F0F0;">
                <input type="radio" name="que_keishiki_kbn_04" value="1" @if($que_keishiki_kbn_04=="1")checked @endif onChange="_keishikiChk(this,'04');"/>テキスト入力のみ<br />
                <input type="radio" name="que_keishiki_kbn_04" value="2" @if($que_keishiki_kbn_04=="2")checked @endif onChange="_keishikiChk(this,'04');"/>単一選択方式<br />
                <input type="radio" name="que_keishiki_kbn_04" value="3" @if($que_keishiki_kbn_04=="3")checked @endif onChange="_keishikiChk(this,'04');"/>単一選択方式＋テキスト入力<br />　　　テキスト入力コメント<input type="text" style="padding:4px;" autocomplete="off" name="que_keishiki_r_comment_04" value="{{ $que_keishiki_r_comment_04 }}" size="43"><br />
                <input type="radio" name="que_keishiki_kbn_04" value="4" @if($que_keishiki_kbn_04=="4")checked @endif onChange="_keishikiChk(this,'04');"/>チェックボックス方式<br />
                <input type="radio" name="que_keishiki_kbn_04" value="5" @if($que_keishiki_kbn_04=="5")checked @endif onChange="_keishikiChk(this,'04');"/>チェックボックス方式＋テキスト入力<br />　　　テキスト入力コメント<input type="text" style="padding:4px;" autocomplete="off" name="que_keishiki_c_comment_04" value="{{ $que_keishiki_c_comment_04 }}" size="43"><br />
            </td>
        </tr>
        <tr style="display:@if($que_keishiki_kbn_04 < '2' )none @else table-row @endif;" id="sentakushi04">
            <th>選択肢</th>
            <td style="background-color:#F0F0F0;">
                <textarea name="que_sentakushi_04" style="padding:4px;width:200px;height:100px;">{!! $que_sentakushi_04 !!}</textarea>
                ※選択肢は改行してリストを作成してください。
            </td>
        </tr>


        <!-- *************************************************************************** -->
        <tr>
            <th rowspan="{!! $row05 !!}" id="que05">設問5</th>
            <th>設問文</th>
            <td>
                <input type="text" autocomplete="off" name="que_question_05" value="{{ $que_question_05 }}" style="width:400px;padding:4px;">
            </td>
        </tr>
        <tr>
            <th>入力</th>
            <td>
                <input type="radio" name="que_hissu_flg_05" value="1" @if($que_hissu_flg_05=="1")checked @endif>必須
                <input type="radio" name="que_hissu_flg_05" value="0" @if($que_hissu_flg_05=="0")checked @endif>なしでも可
            </td>
        </tr>
        <tr>
            <th>解答方式</th>
            <td>
                <input type="radio" name="que_keishiki_kbn_05" value="1" @if($que_keishiki_kbn_05=="1")checked @endif onChange="_keishikiChk(this,'05');"/>テキスト入力のみ<br />
                <input type="radio" name="que_keishiki_kbn_05" value="2" @if($que_keishiki_kbn_05=="2")checked @endif onChange="_keishikiChk(this,'05');"/>単一選択方式<br />
                <input type="radio" name="que_keishiki_kbn_05" value="3" @if($que_keishiki_kbn_05=="3")checked @endif onChange="_keishikiChk(this,'05');"/>単一選択方式＋テキスト入力<br />　　　テキスト入力コメント<input type="text" style="padding:4px;" autocomplete="off" name="que_keishiki_r_comment_05" value="{{ $que_keishiki_r_comment_05 }}" size="43"><br />
                <input type="radio" name="que_keishiki_kbn_05" value="4" @if($que_keishiki_kbn_05=="4")checked @endif onChange="_keishikiChk(this,'05');"/>チェックボックス方式<br />
                <input type="radio" name="que_keishiki_kbn_05" value="5" @if($que_keishiki_kbn_05=="5")checked @endif onChange="_keishikiChk(this,'05');"/>チェックボックス方式＋テキスト入力<br />　　　テキスト入力コメント<input type="text" style="padding:4px;" autocomplete="off" name="que_keishiki_c_comment_05" value="{{ $que_keishiki_c_comment_05 }}" size="43"><br />
            </td>
        </tr>
        <tr style="display:@if($que_keishiki_kbn_05 < '2' )none @else table-row @endif;" id="sentakushi05">
            <th>選択肢</th>
            <td>
                <textarea name="que_sentakushi_05" style="padding:4px;width:200px;height:100px;">{!! $que_sentakushi_05 !!}</textarea>
                ※選択肢は改行してリストを作成してください。
            </td>
        </tr>


        <!-- *************************************************************************** -->
        <tr>
            <th rowspan="{!! $row06 !!}" id="que06">設問6</th>
            <th>設問文</th>
            <td style="background-color:#F0F0F0;">
                <input type="text" autocomplete="off" name="que_question_06" value="{{ $que_question_06 }}" style="width:400px;padding:4px;">
            </td>
        </tr>
        <tr>
            <th>入力</th>
            <td style="background-color:#F0F0F0;">
                <input type="radio" name="que_hissu_flg_06" value="1" @if($que_hissu_flg_06=="1")checked @endif>必須
                <input type="radio" name="que_hissu_flg_06" value="0" @if($que_hissu_flg_06=="0")checked @endif>なしでも可
            </td>
        </tr>
        <tr>
            <th>解答方式</th>
            <td style="background-color:#F0F0F0;">
                <input type="radio" name="que_keishiki_kbn_06" value="1" @if($que_keishiki_kbn_06=="1")checked @endif onChange="_keishikiChk(this,'06');"/>テキスト入力のみ<br />
                <input type="radio" name="que_keishiki_kbn_06" value="2" @if($que_keishiki_kbn_06=="2")checked @endif onChange="_keishikiChk(this,'06');"/>単一選択方式<br />
                <input type="radio" name="que_keishiki_kbn_06" value="3" @if($que_keishiki_kbn_06=="3")checked @endif onChange="_keishikiChk(this,'06');"/>単一選択方式＋テキスト入力<br />　　　テキスト入力コメント<input type="text" style="padding:4px;" autocomplete="off" name="que_keishiki_r_comment_06" value="{{ $que_keishiki_r_comment_06 }}" size="43"><br />
                <input type="radio" name="que_keishiki_kbn_06" value="4" @if($que_keishiki_kbn_06=="4")checked @endif onChange="_keishikiChk(this,'06');"/>チェックボックス方式<br />
                <input type="radio" name="que_keishiki_kbn_06" value="5" @if($que_keishiki_kbn_06=="5")checked @endif onChange="_keishikiChk(this,'06');"/>チェックボックス方式＋テキスト入力<br />　　　テキスト入力コメント<input type="text" style="padding:4px;" autocomplete="off" name="que_keishiki_c_comment_06" value="{{ $que_keishiki_c_comment_06 }}" size="43"><br />
            </td>
        </tr>
        <tr style="display:@if($que_keishiki_kbn_06 < '2' )none @else table-row @endif;" id="sentakushi06">
            <th>選択肢</th>
            <td style="background-color:#F0F0F0;">
                <textarea name="que_sentakushi_06" style="padding:4px;width:200px;height:100px;">{!! $que_sentakushi_06 !!}</textarea>
                ※選択肢は改行してリストを作成してください。
            </td>
        </tr>


        <!-- *************************************************************************** -->
        <tr>
            <th rowspan="{!! $row07 !!}" id="que07">設問7</th>
            <th>設問文</th>
            <td>
                <input type="text" autocomplete="off" name="que_question_07" value="{{ $que_question_07 }}" style="width:400px;padding:4px;">
            </td>
        </tr>
        <tr>
            <th>入力</th>
            <td>
                <input type="radio" name="que_hissu_flg_07" value="1" @if($que_hissu_flg_07=="1")checked @endif>必須
                <input type="radio" name="que_hissu_flg_07" value="0" @if($que_hissu_flg_07=="0")checked @endif>なしでも可
            </td>
        </tr>
        <tr>
            <th>解答方式</th>
            <td>
                <input type="radio" name="que_keishiki_kbn_07" value="1" @if($que_keishiki_kbn_07=="1")checked @endif onChange="_keishikiChk(this,'07');"/>テキスト入力のみ<br />
                <input type="radio" name="que_keishiki_kbn_07" value="2" @if($que_keishiki_kbn_07=="2")checked @endif onChange="_keishikiChk(this,'07');"/>単一選択方式<br />
                <input type="radio" name="que_keishiki_kbn_07" value="3" @if($que_keishiki_kbn_07=="3")checked @endif onChange="_keishikiChk(this,'07');"/>単一選択方式＋テキスト入力<br />　　　テキスト入力コメント<input type="text" style="padding:4px;" autocomplete="off" name="que_keishiki_r_comment_07" value="{{ $que_keishiki_r_comment_07 }}" size="43"><br />
                <input type="radio" name="que_keishiki_kbn_07" value="4" @if($que_keishiki_kbn_07=="4")checked @endif onChange="_keishikiChk(this,'07');"/>チェックボックス方式<br />
                <input type="radio" name="que_keishiki_kbn_07" value="5" @if($que_keishiki_kbn_07=="5")checked @endif onChange="_keishikiChk(this,'07');"/>チェックボックス方式＋テキスト入力<br />　　　テキスト入力コメント<input type="text" style="padding:4px;" autocomplete="off" name="que_keishiki_c_comment_07" value="{{ $que_keishiki_c_comment_07 }}" size="43"><br />
            </td>
        </tr>
        <tr style="display:@if($que_keishiki_kbn_07 < '2' )none @else table-row @endif;" id="sentakushi07">
            <th>選択肢</th>
            <td>
                <textarea name="que_sentakushi_07" style="padding:4px;width:200px;height:100px;">{!! $que_sentakushi_07 !!}</textarea>
                ※選択肢は改行してリストを作成してください。
            </td>
        </tr>


        <!-- *************************************************************************** -->
        <tr>
            <th rowspan="{!! $row08 !!}" id="que08">設問8</th>
            <th>設問文</th>
            <td style="background-color:#F0F0F0;">
                <input type="text" autocomplete="off" name="que_question_08" value="{{ $que_question_08 }}" style="width:400px;padding:4px;">
            </td>
        </tr>
        <tr>
            <th>入力</th>
            <td style="background-color:#F0F0F0;">
                <input type="radio" name="que_hissu_flg_08" value="1" @if($que_hissu_flg_08=="1")checked @endif>必須
                <input type="radio" name="que_hissu_flg_08" value="0" @if($que_hissu_flg_08=="0")checked @endif>なしでも可
            </td>
        </tr>
        <tr>
            <th>解答方式</th>
            <td style="background-color:#F0F0F0;">
                <input type="radio" name="que_keishiki_kbn_08" value="1" @if($que_keishiki_kbn_08=="1")checked @endif onChange="_keishikiChk(this,'08');"/>テキスト入力のみ<br />
                <input type="radio" name="que_keishiki_kbn_08" value="2" @if($que_keishiki_kbn_08=="2")checked @endif onChange="_keishikiChk(this,'08');"/>単一選択方式<br />
                <input type="radio" name="que_keishiki_kbn_08" value="3" @if($que_keishiki_kbn_08=="3")checked @endif onChange="_keishikiChk(this,'08');"/>単一選択方式＋テキスト入力<br />　　　テキスト入力コメント<input type="text" style="padding:4px;" autocomplete="off" name="que_keishiki_r_comment_08" value="{{ $que_keishiki_r_comment_08 }}" size="43"><br />
                <input type="radio" name="que_keishiki_kbn_08" value="4" @if($que_keishiki_kbn_08=="4")checked @endif onChange="_keishikiChk(this,'08');"/>チェックボックス方式<br />
                <input type="radio" name="que_keishiki_kbn_08" value="5" @if($que_keishiki_kbn_08=="5")checked @endif onChange="_keishikiChk(this,'08');"/>チェックボックス方式＋テキスト入力<br />　　　テキスト入力コメント<input type="text" style="padding:4px;" autocomplete="off" name="que_keishiki_c_comment_08" value="{{ $que_keishiki_c_comment_08 }}" size="43"><br />
            </td>
        </tr>
        <tr style="display:@if($que_keishiki_kbn_08 < '2' )none @else table-row @endif;" id="sentakushi08">
            <th>選択肢</th>
            <td style="background-color:#F0F0F0;">
                <textarea name="que_sentakushi_08" style="padding:4px;width:200px;height:100px;">{!! $que_sentakushi_08 !!}</textarea>
                ※選択肢は改行してリストを作成してください。
            </td>
        </tr>


        <!-- *************************************************************************** -->
        <tr>
            <th rowspan="{!! $row09 !!}" id="que09">設問9</th>
            <th>設問文</th>
            <td>
                <input type="text" autocomplete="off" name="que_question_09" value="{{ $que_question_09 }}" style="width:400px;padding:4px;">
            </td>
        </tr>
        <tr>
            <th>入力</th>
            <td>
                <input type="radio" name="que_hissu_flg_09" value="1" @if($que_hissu_flg_09=="1")checked @endif>必須
                <input type="radio" name="que_hissu_flg_09" value="0" @if($que_hissu_flg_09=="0")checked @endif>なしでも可
            </td>
        </tr>
        <tr>
            <th>解答方式</th>
            <td>
                <input type="radio" name="que_keishiki_kbn_09" value="1" @if($que_keishiki_kbn_09=="1")checked @endif onChange="_keishikiChk(this,'09');"/>テキスト入力のみ<br />
                <input type="radio" name="que_keishiki_kbn_09" value="2" @if($que_keishiki_kbn_09=="2")checked @endif onChange="_keishikiChk(this,'09');"/>単一選択方式<br />
                <input type="radio" name="que_keishiki_kbn_09" value="3" @if($que_keishiki_kbn_09=="3")checked @endif onChange="_keishikiChk(this,'09');"/>単一選択方式＋テキスト入力<br />　　　テキスト入力コメント<input type="text" style="padding:4px;" autocomplete="off" name="que_keishiki_r_comment_09" value="{{ $que_keishiki_r_comment_09 }}" size="43"><br />
                <input type="radio" name="que_keishiki_kbn_09" value="4" @if($que_keishiki_kbn_09=="4")checked @endif onChange="_keishikiChk(this,'09');"/>チェックボックス方式<br />
                <input type="radio" name="que_keishiki_kbn_09" value="5" @if($que_keishiki_kbn_09=="5")checked @endif onChange="_keishikiChk(this,'09');"/>チェックボックス方式＋テキスト入力<br />　　　テキスト入力コメント<input type="text" style="padding:4px;" autocomplete="off" name="que_keishiki_c_comment_09" value="{{ $que_keishiki_c_comment_09 }}" size="43"><br />
            </td>
        </tr>
        <tr style="display:@if($que_keishiki_kbn_09 < '2' )none @else table-row @endif;" id="sentakushi09">
            <th>選択肢</th>
            <td>
                <textarea name="que_sentakushi_09" style="padding:4px;width:200px;height:100px;">{!! $que_sentakushi_09 !!}</textarea>
                ※選択肢は改行してリストを作成してください。
            </td>
        </tr>

        <!-- *************************************************************************** -->
        <tr>
            <th rowspan="{!! $row10 !!}" id="que10">設問10</th>
            <th>設問文</th>
            <td style="background-color:#F0F0F0;">
                <input type="text" autocomplete="off" name="que_question_10" value="{{ $que_question_10 }}" style="width:400px;padding:4px;">
            </td>
        </tr>
        <tr>
            <th>入力</th>
            <td style="background-color:#F0F0F0;">
                <input type="radio" name="que_hissu_flg_10" value="1" @if($que_hissu_flg_10=="1")checked @endif>必須
                <input type="radio" name="que_hissu_flg_10" value="0" @if($que_hissu_flg_10=="0")checked @endif>なしでも可
            </td>
        </tr>
        <tr>
            <th>解答方式</th>
            <td style="background-color:#F0F0F0;">
                <input type="radio" name="que_keishiki_kbn_10" value="1" @if($que_keishiki_kbn_10=="1")checked @endif onChange="_keishikiChk(this,'10');"/>テキスト入力のみ<br />
                <input type="radio" name="que_keishiki_kbn_10" value="2" @if($que_keishiki_kbn_10=="2")checked @endif onChange="_keishikiChk(this,'10');"/>単一選択方式<br />
                <input type="radio" name="que_keishiki_kbn_10" value="3" @if($que_keishiki_kbn_10=="3")checked @endif onChange="_keishikiChk(this,'10');"/>単一選択方式＋テキスト入力<br />　　　テキスト入力コメント<input type="text" style="padding:4px;" autocomplete="off" name="que_keishiki_r_comment_10" value="{{ $que_keishiki_r_comment_10 }}" size="43"><br />
                <input type="radio" name="que_keishiki_kbn_10" value="4" @if($que_keishiki_kbn_10=="4")checked @endif onChange="_keishikiChk(this,'10');"/>チェックボックス方式<br />
                <input type="radio" name="que_keishiki_kbn_10" value="5" @if($que_keishiki_kbn_10=="5")checked @endif onChange="_keishikiChk(this,'10');"/>チェックボックス方式＋テキスト入力<br />　　　テキスト入力コメント<input type="text" style="padding:4px;" autocomplete="off" name="que_keishiki_c_comment_10" value="{{ $que_keishiki_c_comment_10 }}" size="43"><br />
            </td>
        </tr>
        <tr style="display:@if($que_keishiki_kbn_10 < '2' )none @else table-row @endif;" id="sentakushi10">
            <th>選択肢</th>
            <td style="background-color:#F0F0F0;">
                <textarea name="que_sentakushi_10" style="padding:4px;width:200px;height:100px;">{!! $que_sentakushi_10 !!}</textarea>
                ※選択肢は改行してリストを作成してください。
            </td>
        </tr>

        <!-- *************************************************************************** -->
        <tr>
            <th rowspan="{!! $row11 !!}" id="que11">設問11</th>
            <th>設問文</th>
            <td>
                <input type="text" autocomplete="off" name="que_question_11" value="{{ $que_question_11 }}" style="width:400px;padding:4px;">
            </td>
        </tr>
        <tr>
            <th>入力</th>
            <td>
                <input type="radio" name="que_hissu_flg_11" value="1" @if($que_hissu_flg_11=="1")checked @endif>必須
                <input type="radio" name="que_hissu_flg_11" value="0" @if($que_hissu_flg_11=="0")checked @endif>なしでも可
            </td>
        </tr>
        <tr>
            <th>解答方式</th>
            <td>
                <input type="radio" name="que_keishiki_kbn_11" value="1" @if($que_keishiki_kbn_11=="1")checked @endif onChange="_keishikiChk(this,'11');"/>テキスト入力のみ<br />
                <input type="radio" name="que_keishiki_kbn_11" value="2" @if($que_keishiki_kbn_11=="2")checked @endif onChange="_keishikiChk(this,'11');"/>単一選択方式<br />
                <input type="radio" name="que_keishiki_kbn_11" value="3" @if($que_keishiki_kbn_11=="3")checked @endif onChange="_keishikiChk(this,'11');"/>単一選択方式＋テキスト入力<br />　　　テキスト入力コメント<input type="text" style="padding:4px;" autocomplete="off" name="que_keishiki_r_comment_11" value="{{ $que_keishiki_r_comment_11 }}" size="43"><br />
                <input type="radio" name="que_keishiki_kbn_11" value="4" @if($que_keishiki_kbn_11=="4")checked @endif onChange="_keishikiChk(this,'11');"/>チェックボックス方式<br />
                <input type="radio" name="que_keishiki_kbn_11" value="5" @if($que_keishiki_kbn_11=="5")checked @endif onChange="_keishikiChk(this,'11');"/>チェックボックス方式＋テキスト入力<br />　　　テキスト入力コメント<input type="text" style="padding:4px;" autocomplete="off" name="que_keishiki_c_comment_11" value="{{ $que_keishiki_c_comment_11 }}" size="43"><br />
            </td>
        </tr>
        <tr style="display:@if($que_keishiki_kbn_11 < '2' )none @else table-row @endif;" id="sentakushi11">
            <th>選択肢</th>
            <td>
                <textarea name="que_sentakushi_11" style="padding:4px;width:200px;height:100px;">{!! $que_sentakushi_11 !!}</textarea>
                ※選択肢は改行してリストを作成してください。
            </td>
        </tr>

        <!-- *************************************************************************** -->
        <tr>
            <th rowspan="{!! $row12 !!}" id="que12">設問12</th>
            <th>設問文</th>
            <td style="background-color:#F0F0F0;">
                <input type="text" autocomplete="off" name="que_question_12" value="{{ $que_question_12 }}" style="width:400px;padding:4px;">
            </td>
        </tr>
        <tr>
            <th>入力</th>
            <td style="background-color:#F0F0F0;">
                <input type="radio" name="que_hissu_flg_12" value="1" @if($que_hissu_flg_12=="1")checked @endif>必須
                <input type="radio" name="que_hissu_flg_12" value="0" @if($que_hissu_flg_12=="0")checked @endif>なしでも可
            </td>
        </tr>
        <tr>
            <th>解答方式</th>
            <td style="background-color:#F0F0F0;">
                <input type="radio" name="que_keishiki_kbn_12" value="1" @if($que_keishiki_kbn_12=="1")checked @endif onChange="_keishikiChk(this,'12');"/>テキスト入力のみ<br />
                <input type="radio" name="que_keishiki_kbn_12" value="2" @if($que_keishiki_kbn_12=="2")checked @endif onChange="_keishikiChk(this,'12');"/>単一選択方式<br />
                <input type="radio" name="que_keishiki_kbn_12" value="3" @if($que_keishiki_kbn_12=="3")checked @endif onChange="_keishikiChk(this,'12');"/>単一選択方式＋テキスト入力<br />　　　テキスト入力コメント<input type="text" style="padding:4px;" autocomplete="off" name="que_keishiki_r_comment_12" value="{{ $que_keishiki_r_comment_12 }}" size="43"><br />
                <input type="radio" name="que_keishiki_kbn_12" value="4" @if($que_keishiki_kbn_12=="4")checked @endif onChange="_keishikiChk(this,'12');"/>チェックボックス方式<br />
                <input type="radio" name="que_keishiki_kbn_12" value="5" @if($que_keishiki_kbn_12=="5")checked @endif onChange="_keishikiChk(this,'12');"/>チェックボックス方式＋テキスト入力<br />　　　テキスト入力コメント<input type="text" style="padding:4px;" autocomplete="off" name="que_keishiki_c_comment_12" value="{{ $que_keishiki_c_comment_12 }}" size="43"><br />
            </td>
        </tr>
        <tr style="display:@if($que_keishiki_kbn_12 < '2' )none @else table-row @endif;" id="sentakushi12">
            <th>選択肢</th>
            <td style="background-color:#F0F0F0;">
                <textarea name="que_sentakushi_12" style="padding:4px;width:200px;height:100px;">{!! $que_sentakushi_12 !!}</textarea>
                ※選択肢は改行してリストを作成してください。
            </td>
        </tr>

        <!-- *************************************************************************** -->
        <tr>
            <th rowspan="{!! $row13 !!}" id="que13">設問13</th>
            <th>設問文</th>
            <td>
                <input type="text" autocomplete="off" name="que_question_13" value="{{ $que_question_13 }}" style="width:400px;padding:4px;">
            </td>
        </tr>
        <tr>
            <th>入力</th>
            <td>
                <input type="radio" name="que_hissu_flg_13" value="1" @if($que_hissu_flg_13=="1")checked @endif>必須
                <input type="radio" name="que_hissu_flg_13" value="0" @if($que_hissu_flg_13=="0")checked @endif>なしでも可
            </td>
        </tr>
        <tr>
            <th>解答方式</th>
            <td>
                <input type="radio" name="que_keishiki_kbn_13" value="1" @if($que_keishiki_kbn_13=="1")checked @endif onChange="_keishikiChk(this,'13');"/>テキスト入力のみ<br />
                <input type="radio" name="que_keishiki_kbn_13" value="2" @if($que_keishiki_kbn_13=="2")checked @endif onChange="_keishikiChk(this,'13');"/>単一選択方式<br />
                <input type="radio" name="que_keishiki_kbn_13" value="3" @if($que_keishiki_kbn_13=="3")checked @endif onChange="_keishikiChk(this,'13');"/>単一選択方式＋テキスト入力<br />　　　テキスト入力コメント<input type="text" style="padding:4px;" autocomplete="off" name="que_keishiki_r_comment_13" value="{{ $que_keishiki_r_comment_13 }}" size="43"><br />
                <input type="radio" name="que_keishiki_kbn_13" value="4" @if($que_keishiki_kbn_13=="4")checked @endif onChange="_keishikiChk(this,'13');"/>チェックボックス方式<br />
                <input type="radio" name="que_keishiki_kbn_13" value="5" @if($que_keishiki_kbn_13=="5")checked @endif onChange="_keishikiChk(this,'13');"/>チェックボックス方式＋テキスト入力<br />　　　テキスト入力コメント<input type="text" style="padding:4px;" autocomplete="off" name="que_keishiki_c_comment_13" value="{{ $que_keishiki_c_comment_13 }}" size="43"><br />
            </td>
        </tr>
        <tr style="display:@if($que_keishiki_kbn_13 < '2' )none @else table-row @endif;" id="sentakushi13">
            <th>選択肢</th>
            <td>
                <textarea name="que_sentakushi_13" style="padding:4px;width:200px;height:100px;">{!! $que_sentakushi_13 !!}</textarea>
                ※選択肢は改行してリストを作成してください。
            </td>
        </tr>

        <!-- *************************************************************************** -->
        <tr>
            <th rowspan="{!! $row14 !!}" id="que14">設問14</th>
            <th>設問文</th>
            <td style="background-color:#F0F0F0;">
                <input type="text" autocomplete="off" name="que_question_14" value="{{ $que_question_14 }}" style="width:400px;padding:4px;">
            </td>
        </tr>
        <tr>
            <th>入力</th>
            <td style="background-color:#F0F0F0;">
                <input type="radio" name="que_hissu_flg_14" value="1" @if($que_hissu_flg_14=="1")checked @endif>必須
                <input type="radio" name="que_hissu_flg_14" value="0" @if($que_hissu_flg_14=="0")checked @endif>なしでも可
            </td>
        </tr>
        <tr>
            <th>解答方式</th>
            <td style="background-color:#F0F0F0;">
                <input type="radio" name="que_keishiki_kbn_14" value="1" @if($que_keishiki_kbn_14=="1")checked @endif onChange="_keishikiChk(this,'14');"/>テキスト入力のみ<br />
                <input type="radio" name="que_keishiki_kbn_14" value="2" @if($que_keishiki_kbn_14=="2")checked @endif onChange="_keishikiChk(this,'14');"/>単一選択方式<br />
                <input type="radio" name="que_keishiki_kbn_14" value="3" @if($que_keishiki_kbn_14=="3")checked @endif onChange="_keishikiChk(this,'14');"/>単一選択方式＋テキスト入力<br />　　　テキスト入力コメント<input type="text" style="padding:4px;" autocomplete="off" name="que_keishiki_r_comment_14" value="{{ $que_keishiki_r_comment_14 }}" size="43"><br />
                <input type="radio" name="que_keishiki_kbn_14" value="4" @if($que_keishiki_kbn_14=="4")checked @endif onChange="_keishikiChk(this,'14');"/>チェックボックス方式<br />
                <input type="radio" name="que_keishiki_kbn_14" value="5" @if($que_keishiki_kbn_14=="5")checked @endif onChange="_keishikiChk(this,'14');"/>チェックボックス方式＋テキスト入力<br />　　　テキスト入力コメント<input type="text" style="padding:4px;" autocomplete="off" name="que_keishiki_c_comment_14" value="{{ $que_keishiki_c_comment_14 }}" size="43"><br />
            </td>
        </tr>
        <tr style="display:@if($que_keishiki_kbn_14 < '2' )none @else table-row @endif;" id="sentakushi14">
            <th>選択肢</th>
            <td style="background-color:#F0F0F0;">
                <textarea name="que_sentakushi_14" style="padding:4px;width:200px;height:100px;">{!! $que_sentakushi_14 !!}</textarea>
                ※選択肢は改行してリストを作成してください。
            </td>
        </tr>

        <!-- *************************************************************************** -->
        <tr>
            <th rowspan="{!! $row15 !!}" id="que15">設問15</th>
            <th>設問文</th>
            <td>
                <input type="text" autocomplete="off" name="que_question_15" value="{{ $que_question_15 }}" style="width:400px;padding:4px;">
            </td>
        </tr>
        <tr>
            <th>入力</th>
            <td>
                <input type="radio" name="que_hissu_flg_15" value="1" @if($que_hissu_flg_15=="1")checked @endif>必須
                <input type="radio" name="que_hissu_flg_15" value="0" @if($que_hissu_flg_15=="0")checked @endif>なしでも可
            </td>
        </tr>
        <tr>
            <th>解答方式</th>
            <td>
                <input type="radio" name="que_keishiki_kbn_15" value="1" @if($que_keishiki_kbn_15=="1")checked @endif onChange="_keishikiChk(this,'15');"/>テキスト入力のみ<br />
                <input type="radio" name="que_keishiki_kbn_15" value="2" @if($que_keishiki_kbn_15=="2")checked @endif onChange="_keishikiChk(this,'15');"/>単一選択方式<br />
                <input type="radio" name="que_keishiki_kbn_15" value="3" @if($que_keishiki_kbn_15=="3")checked @endif onChange="_keishikiChk(this,'15');"/>単一選択方式＋テキスト入力<br />　　　テキスト入力コメント<input type="text" style="padding:4px;" autocomplete="off" name="que_keishiki_r_comment_15" value="{{ $que_keishiki_r_comment_15 }}" size="43"><br />
                <input type="radio" name="que_keishiki_kbn_15" value="4" @if($que_keishiki_kbn_15=="4")checked @endif onChange="_keishikiChk(this,'15');"/>チェックボックス方式<br />
                <input type="radio" name="que_keishiki_kbn_15" value="5" @if($que_keishiki_kbn_15=="5")checked @endif onChange="_keishikiChk(this,'15');"/>チェックボックス方式＋テキスト入力<br />　　　テキスト入力コメント<input type="text" style="padding:4px;" autocomplete="off" name="que_keishiki_c_comment_15" value="{{ $que_keishiki_c_comment_15 }}" size="43"><br />
            </td>
        </tr>
        <tr style="display:@if($que_keishiki_kbn_15 < '2' )none @else table-row @endif;" id="sentakushi15">
            <th>選択肢</th>
            <td>
                <textarea name="que_sentakushi_15" style="padding:4px;width:200px;height:100px;">{!! $que_sentakushi_15 !!}</textarea>
                ※選択肢は改行してリストを作成してください。
            </td>
        </tr>

        <!-- *************************************************************************** -->
        <tr>
            <th rowspan="{!! $row16 !!}" id="que16">設問16</th>
            <th>設問文</th>
            <td style="background-color:#F0F0F0;">
                <input type="text" autocomplete="off" name="que_question_16" value="{{ $que_question_16 }}" style="width:400px;padding:4px;">
            </td>
        </tr>
        <tr>
            <th>入力</th>
            <td style="background-color:#F0F0F0;">
                <input type="radio" name="que_hissu_flg_16" value="1" @if($que_hissu_flg_16=="1")checked @endif>必須
                <input type="radio" name="que_hissu_flg_16" value="0" @if($que_hissu_flg_16=="0")checked @endif>なしでも可
            </td>
        </tr>
        <tr>
            <th>解答方式</th>
            <td style="background-color:#F0F0F0;">
                <input type="radio" name="que_keishiki_kbn_16" value="1" @if($que_keishiki_kbn_16=="1")checked @endif onChange="_keishikiChk(this,'16');"/>テキスト入力のみ<br />
                <input type="radio" name="que_keishiki_kbn_16" value="2" @if($que_keishiki_kbn_16=="2")checked @endif onChange="_keishikiChk(this,'16');"/>単一選択方式<br />
                <input type="radio" name="que_keishiki_kbn_16" value="3" @if($que_keishiki_kbn_16=="3")checked @endif onChange="_keishikiChk(this,'16');"/>単一選択方式＋テキスト入力<br />　　　テキスト入力コメント<input type="text" style="padding:4px;" autocomplete="off" name="que_keishiki_r_comment_16" value="{{ $que_keishiki_r_comment_16 }}" size="43"><br />
                <input type="radio" name="que_keishiki_kbn_16" value="4" @if($que_keishiki_kbn_16=="4")checked @endif onChange="_keishikiChk(this,'16');"/>チェックボックス方式<br />
                <input type="radio" name="que_keishiki_kbn_16" value="5" @if($que_keishiki_kbn_16=="5")checked @endif onChange="_keishikiChk(this,'16');"/>チェックボックス方式＋テキスト入力<br />　　　テキスト入力コメント<input type="text" style="padding:4px;" autocomplete="off" name="que_keishiki_c_comment_16" value="{{ $que_keishiki_c_comment_16 }}" size="43"><br />
            </td>
        </tr>
        <tr style="display:@if($que_keishiki_kbn_16 < '2' )none @else table-row @endif;" id="sentakushi16">
            <th>選択肢</th>
            <td style="background-color:#F0F0F0;">
                <textarea name="que_sentakushi_16" style="padding:4px;width:200px;height:100px;">{!! $que_sentakushi_16 !!}</textarea>
                ※選択肢は改行してリストを作成してください。
            </td>
        </tr>

        <!-- *************************************************************************** -->
        <tr>
            <th rowspan="{!! $row17 !!}" id="que17">設問17</th>
            <th>設問文</th>
            <td>
                <input type="text" autocomplete="off" name="que_question_17" value="{{ $que_question_17 }}" style="width:400px;padding:4px;">
            </td>
        </tr>
        <tr>
            <th>入力</th>
            <td>
                <input type="radio" name="que_hissu_flg_17" value="1" @if($que_hissu_flg_17=="1")checked @endif>必須
                <input type="radio" name="que_hissu_flg_17" value="0" @if($que_hissu_flg_17=="0")checked @endif>なしでも可
            </td>
        </tr>
        <tr>
            <th>解答方式</th>
            <td>
                <input type="radio" name="que_keishiki_kbn_17" value="1" @if($que_keishiki_kbn_17=="1")checked @endif onChange="_keishikiChk(this,'17');"/>テキスト入力のみ<br />
                <input type="radio" name="que_keishiki_kbn_17" value="2" @if($que_keishiki_kbn_17=="2")checked @endif onChange="_keishikiChk(this,'17');"/>単一選択方式<br />
                <input type="radio" name="que_keishiki_kbn_17" value="3" @if($que_keishiki_kbn_17=="3")checked @endif onChange="_keishikiChk(this,'17');"/>単一選択方式＋テキスト入力<br />　　　テキスト入力コメント<input type="text" style="padding:4px;" autocomplete="off" name="que_keishiki_r_comment_17" value="{{ $que_keishiki_r_comment_17 }}" size="43"><br />
                <input type="radio" name="que_keishiki_kbn_17" value="4" @if($que_keishiki_kbn_17=="4")checked @endif onChange="_keishikiChk(this,'17');"/>チェックボックス方式<br />
                <input type="radio" name="que_keishiki_kbn_17" value="5" @if($que_keishiki_kbn_17=="5")checked @endif onChange="_keishikiChk(this,'17');"/>チェックボックス方式＋テキスト入力<br />　　　テキスト入力コメント<input type="text" style="padding:4px;" autocomplete="off" name="que_keishiki_c_comment_17" value="{{ $que_keishiki_c_comment_17 }}" size="43"><br />
            </td>
        </tr>
        <tr style="display:@if($que_keishiki_kbn_17 < '2' )none @else table-row @endif;" id="sentakushi17">
            <th>選択肢</th>
            <td>
                <textarea name="que_sentakushi_17" style="padding:4px;width:200px;height:100px;">{!! $que_sentakushi_17 !!}</textarea>
                ※選択肢は改行してリストを作成してください。
            </td>
        </tr>

        <!-- *************************************************************************** -->
        <tr>
            <th rowspan="{!! $row18 !!}" id="que18">設問18</th>
            <th>設問文</th>
            <td style="background-color:#F0F0F0;">
                <input type="text" autocomplete="off" name="que_question_18" value="{{ $que_question_18 }}" style="width:400px;padding:4px;">
            </td>
        </tr>
        <tr>
            <th>入力</th>
            <td style="background-color:#F0F0F0;">
                <input type="radio" name="que_hissu_flg_18" value="1" @if($que_hissu_flg_18=="1")checked @endif>必須
                <input type="radio" name="que_hissu_flg_18" value="0" @if($que_hissu_flg_18=="0")checked @endif>なしでも可
            </td>
        </tr>
        <tr>
            <th>解答方式</th>
            <td style="background-color:#F0F0F0;">
                <input type="radio" name="que_keishiki_kbn_18" value="1" @if($que_keishiki_kbn_18=="1")checked @endif onChange="_keishikiChk(this,'18');"/>テキスト入力のみ<br />
                <input type="radio" name="que_keishiki_kbn_18" value="2" @if($que_keishiki_kbn_18=="2")checked @endif onChange="_keishikiChk(this,'18');"/>単一選択方式<br />
                <input type="radio" name="que_keishiki_kbn_18" value="3" @if($que_keishiki_kbn_18=="3")checked @endif onChange="_keishikiChk(this,'18');"/>単一選択方式＋テキスト入力<br />　　　テキスト入力コメント<input type="text" style="padding:4px;" autocomplete="off" name="que_keishiki_r_comment_18" value="{{ $que_keishiki_r_comment_18 }}" size="43"><br />
                <input type="radio" name="que_keishiki_kbn_18" value="4" @if($que_keishiki_kbn_18=="4")checked @endif onChange="_keishikiChk(this,'18');"/>チェックボックス方式<br />
                <input type="radio" name="que_keishiki_kbn_18" value="5" @if($que_keishiki_kbn_18=="5")checked @endif onChange="_keishikiChk(this,'18');"/>チェックボックス方式＋テキスト入力<br />　　　テキスト入力コメント<input type="text" style="padding:4px;" autocomplete="off" name="que_keishiki_c_comment_18" value="{{ $que_keishiki_c_comment_18 }}" size="43"><br />
            </td>
        </tr>
        <tr style="display:@if($que_keishiki_kbn_18 < '2' )none @else table-row @endif;" id="sentakushi18">
            <th>選択肢</th>
            <td style="background-color:#F0F0F0;">
                <textarea name="que_sentakushi_18" style="padding:4px;width:200px;height:100px;">{!! $que_sentakushi_18 !!}</textarea>
                ※選択肢は改行してリストを作成してください。
            </td>
        </tr>

        <!-- *************************************************************************** -->
        <tr>
            <th rowspan="{!! $row19 !!}" id="que19">設問19</th>
            <th>設問文</th>
            <td>
                <input type="text" autocomplete="off" name="que_question_19" value="{{ $que_question_19 }}" style="width:400px;padding:4px;">
            </td>
        </tr>
        <tr>
            <th>入力</th>
            <td>
                <input type="radio" name="que_hissu_flg_19" value="1" @if($que_hissu_flg_19=="1")checked @endif>必須
                <input type="radio" name="que_hissu_flg_19" value="0" @if($que_hissu_flg_19=="0")checked @endif>なしでも可
            </td>
        </tr>
        <tr>
            <th>解答方式</th>
            <td>
                <input type="radio" name="que_keishiki_kbn_19" value="1" @if($que_keishiki_kbn_19=="1")checked @endif onChange="_keishikiChk(this,'19');"/>テキスト入力のみ<br />
                <input type="radio" name="que_keishiki_kbn_19" value="2" @if($que_keishiki_kbn_19=="2")checked @endif onChange="_keishikiChk(this,'19');"/>単一選択方式<br />
                <input type="radio" name="que_keishiki_kbn_19" value="3" @if($que_keishiki_kbn_19=="3")checked @endif onChange="_keishikiChk(this,'19');"/>単一選択方式＋テキスト入力<br />　　　テキスト入力コメント<input type="text" style="padding:4px;" autocomplete="off" name="que_keishiki_r_comment_19" value="{{ $que_keishiki_r_comment_19 }}" size="43"><br />
                <input type="radio" name="que_keishiki_kbn_19" value="4" @if($que_keishiki_kbn_19=="4")checked @endif onChange="_keishikiChk(this,'19');"/>チェックボックス方式<br />
                <input type="radio" name="que_keishiki_kbn_19" value="5" @if($que_keishiki_kbn_19=="5")checked @endif onChange="_keishikiChk(this,'19');"/>チェックボックス方式＋テキスト入力<br />　　　テキスト入力コメント<input type="text" style="padding:4px;" autocomplete="off" name="que_keishiki_c_comment_19" value="{{ $que_keishiki_c_comment_19 }}" size="43"><br />
            </td>
        </tr>
        <tr style="display:@if($que_keishiki_kbn_19 < '2' )none @else table-row @endif;" id="sentakushi19">
            <th>選択肢</th>
            <td>
                <textarea name="que_sentakushi_19" style="padding:4px;width:200px;height:100px;">{!! $que_sentakushi_19 !!}</textarea>
                ※選択肢は改行してリストを作成してください。
            </td>
        </tr>

        <!-- *************************************************************************** -->
        <tr>
            <th rowspan="{!! $row20 !!}" id="que20">設問20</th>
            <th>設問文</th>
            <td style="background-color:#F0F0F0;">
                <input type="text" autocomplete="off" name="que_question_20" value="{{ $que_question_20 }}" style="width:400px;padding:4px;">
            </td>
        </tr>
        <tr>
            <th>入力</th>
            <td style="background-color:#F0F0F0;">
                <input type="radio" name="que_hissu_flg_20" value="1" @if($que_hissu_flg_20=="1")checked @endif>必須
                <input type="radio" name="que_hissu_flg_20" value="0" @if($que_hissu_flg_20=="0")checked @endif>なしでも可
            </td>
        </tr>
        <tr>
            <th>解答方式</th>
            <td style="background-color:#F0F0F0;">
                <input type="radio" name="que_keishiki_kbn_20" value="1" @if($que_keishiki_kbn_20=="1")checked @endif onChange="_keishikiChk(this,'20');"/>テキスト入力のみ<br />
                <input type="radio" name="que_keishiki_kbn_20" value="2" @if($que_keishiki_kbn_20=="2")checked @endif onChange="_keishikiChk(this,'20');"/>単一選択方式<br />
                <input type="radio" name="que_keishiki_kbn_20" value="3" @if($que_keishiki_kbn_20=="3")checked @endif onChange="_keishikiChk(this,'20');"/>単一選択方式＋テキスト入力<br />　　　テキスト入力コメント<input type="text" style="padding:4px;" autocomplete="off" name="que_keishiki_r_comment_20" value="{{ $que_keishiki_r_comment_20 }}" size="43"><br />
                <input type="radio" name="que_keishiki_kbn_20" value="4" @if($que_keishiki_kbn_20=="4")checked @endif onChange="_keishikiChk(this,'20');"/>チェックボックス方式<br />
                <input type="radio" name="que_keishiki_kbn_20" value="5" @if($que_keishiki_kbn_20=="5")checked @endif onChange="_keishikiChk(this,'20');"/>チェックボックス方式＋テキスト入力<br />　　　テキスト入力コメント<input type="text" style="padding:4px;" autocomplete="off" name="que_keishiki_c_comment_20" value="{{ $que_keishiki_c_comment_20 }}" size="43"><br />
            </td>
        </tr>
        <tr style="display:@if($que_keishiki_kbn_20 < '2' )none @else table-row @endif;" id="sentakushi20">
            <th>選択肢</th>
            <td style="background-color:#F0F0F0;">
                <textarea name="que_sentakushi_20" style="padding:4px;width:200px;height:100px;">{!! $que_sentakushi_20 !!}</textarea>
                ※選択肢は改行してリストを作成してください。
            </td>
        </tr>

        <!-- *************************************************************************** -->
        <tr>
            <th rowspan="{!! $row21 !!}" id="que21">設問21</th>
            <th>設問文</th>
            <td>
                <input type="text" autocomplete="off" name="que_question_21" value="{{ $que_question_21 }}" style="width:400px;padding:4px;">
            </td>
        </tr>
        <tr>
            <th>入力</th>
            <td>
                <input type="radio" name="que_hissu_flg_21" value="1" @if($que_hissu_flg_21=="1")checked @endif>必須
                <input type="radio" name="que_hissu_flg_21" value="0" @if($que_hissu_flg_21=="0")checked @endif>なしでも可
            </td>
        </tr>
        <tr>
            <th>解答方式</th>
            <td>
                <input type="radio" name="que_keishiki_kbn_21" value="1" @if($que_keishiki_kbn_21=="1")checked @endif onChange="_keishikiChk(this,'21');"/>テキスト入力のみ<br />
                <input type="radio" name="que_keishiki_kbn_21" value="2" @if($que_keishiki_kbn_21=="2")checked @endif onChange="_keishikiChk(this,'21');"/>単一選択方式<br />
                <input type="radio" name="que_keishiki_kbn_21" value="3" @if($que_keishiki_kbn_21=="3")checked @endif onChange="_keishikiChk(this,'21');"/>単一選択方式＋テキスト入力<br />　　　テキスト入力コメント<input type="text" style="padding:4px;" autocomplete="off" name="que_keishiki_r_comment_21" value="{{ $que_keishiki_r_comment_21 }}" size="43"><br />
                <input type="radio" name="que_keishiki_kbn_21" value="4" @if($que_keishiki_kbn_21=="4")checked @endif onChange="_keishikiChk(this,'21');"/>チェックボックス方式<br />
                <input type="radio" name="que_keishiki_kbn_21" value="5" @if($que_keishiki_kbn_21=="5")checked @endif onChange="_keishikiChk(this,'21');"/>チェックボックス方式＋テキスト入力<br />　　　テキスト入力コメント<input type="text" style="padding:4px;" autocomplete="off" name="que_keishiki_c_comment_21" value="{{ $que_keishiki_c_comment_21 }}" size="43"><br />
            </td>
        </tr>
        <tr style="display:@if($que_keishiki_kbn_21 < '2' )none @else table-row @endif;" id="sentakushi21">
            <th>選択肢</th>
            <td>
                <textarea name="que_sentakushi_21" style="padding:4px;width:200px;height:100px;">{!! $que_sentakushi_21 !!}</textarea>
                ※選択肢は改行してリストを作成してください。
            </td>
        </tr>

        <!-- *************************************************************************** -->
        <tr>
            <th rowspan="{!! $row22 !!}" id="que22">設問22</th>
            <th>設問文</th>
            <td style="background-color:#F0F0F0;">
                <input type="text" autocomplete="off" name="que_question_22" value="{{ $que_question_22 }}" style="width:400px;padding:4px;">
            </td>
        </tr>
        <tr>
            <th>入力</th>
            <td style="background-color:#F0F0F0;">
                <input type="radio" name="que_hissu_flg_22" value="1" @if($que_hissu_flg_22=="1")checked @endif>必須
                <input type="radio" name="que_hissu_flg_22" value="0" @if($que_hissu_flg_22=="0")checked @endif>なしでも可
            </td>
        </tr>
        <tr>
            <th>解答方式</th>
            <td style="background-color:#F0F0F0;">
                <input type="radio" name="que_keishiki_kbn_22" value="1" @if($que_keishiki_kbn_22=="1")checked @endif onChange="_keishikiChk(this,'22');"/>テキスト入力のみ<br />
                <input type="radio" name="que_keishiki_kbn_22" value="2" @if($que_keishiki_kbn_22=="2")checked @endif onChange="_keishikiChk(this,'22');"/>単一選択方式<br />
                <input type="radio" name="que_keishiki_kbn_22" value="3" @if($que_keishiki_kbn_22=="3")checked @endif onChange="_keishikiChk(this,'22');"/>単一選択方式＋テキスト入力<br />　　　テキスト入力コメント<input type="text" style="padding:4px;" autocomplete="off" name="que_keishiki_r_comment_22" value="{{ $que_keishiki_r_comment_22 }}" size="43"><br />
                <input type="radio" name="que_keishiki_kbn_22" value="4" @if($que_keishiki_kbn_22=="4")checked @endif onChange="_keishikiChk(this,'22');"/>チェックボックス方式<br />
                <input type="radio" name="que_keishiki_kbn_22" value="5" @if($que_keishiki_kbn_22=="5")checked @endif onChange="_keishikiChk(this,'22');"/>チェックボックス方式＋テキスト入力<br />　　　テキスト入力コメント<input type="text" style="padding:4px;" autocomplete="off" name="que_keishiki_c_comment_22" value="{{ $que_keishiki_c_comment_22 }}" size="43"><br />
            </td>
        </tr>
        <tr style="display:@if($que_keishiki_kbn_22 < '2' )none @else table-row @endif;" id="sentakushi22">
            <th>選択肢</th>
            <td style="background-color:#F0F0F0;">
                <textarea name="que_sentakushi_22" style="padding:4px;width:200px;height:100px;">{!! $que_sentakushi_22 !!}</textarea>
                ※選択肢は改行してリストを作成してください。
            </td>
        </tr>

        <!-- *************************************************************************** -->
        <tr>
            <th rowspan="{!! $row23 !!}" id="que23">設問23</th>
            <th>設問文</th>
            <td>
                <input type="text" autocomplete="off" name="que_question_23" value="{{ $que_question_23 }}" style="width:400px;padding:4px;">
            </td>
        </tr>
        <tr>
            <th>入力</th>
            <td>
                <input type="radio" name="que_hissu_flg_23" value="1" @if($que_hissu_flg_23=="1")checked @endif>必須
                <input type="radio" name="que_hissu_flg_23" value="0" @if($que_hissu_flg_23=="0")checked @endif>なしでも可
            </td>
        </tr>
        <tr>
            <th>解答方式</th>
            <td>
                <input type="radio" name="que_keishiki_kbn_23" value="1" @if($que_keishiki_kbn_23=="1")checked @endif onChange="_keishikiChk(this,'23');"/>テキスト入力のみ<br />
                <input type="radio" name="que_keishiki_kbn_23" value="2" @if($que_keishiki_kbn_23=="2")checked @endif onChange="_keishikiChk(this,'23');"/>単一選択方式<br />
                <input type="radio" name="que_keishiki_kbn_23" value="3" @if($que_keishiki_kbn_23=="3")checked @endif onChange="_keishikiChk(this,'23');"/>単一選択方式＋テキスト入力<br />　　　テキスト入力コメント<input type="text" style="padding:4px;" autocomplete="off" name="que_keishiki_r_comment_23" value="{{ $que_keishiki_r_comment_23 }}" size="43"><br />
                <input type="radio" name="que_keishiki_kbn_23" value="4" @if($que_keishiki_kbn_23=="4")checked @endif onChange="_keishikiChk(this,'23');"/>チェックボックス方式<br />
                <input type="radio" name="que_keishiki_kbn_23" value="5" @if($que_keishiki_kbn_23=="5")checked @endif onChange="_keishikiChk(this,'23');"/>チェックボックス方式＋テキスト入力<br />　　　テキスト入力コメント<input type="text" style="padding:4px;" autocomplete="off" name="que_keishiki_c_comment_23" value="{{ $que_keishiki_c_comment_23 }}" size="43"><br />
            </td>
        </tr>
        <tr style="display:@if($que_keishiki_kbn_23 < '2' )none @else table-row @endif;" id="sentakushi23">
            <th>選択肢</th>
            <td>
                <textarea name="que_sentakushi_23" style="padding:4px;width:200px;height:100px;">{!! $que_sentakushi_23 !!}</textarea>
                ※選択肢は改行してリストを作成してください。
            </td>
        </tr>

        <!-- *************************************************************************** -->
        <tr>
            <th rowspan="{!! $row24 !!}" id="que24">設問24</th>
            <th>設問文</th>
            <td style="background-color:#F0F0F0;">
                <input type="text" autocomplete="off" name="que_question_24" value="{{ $que_question_24 }}" style="width:400px;padding:4px;">
            </td>
        </tr>
        <tr>
            <th>入力</th>
            <td style="background-color:#F0F0F0;">
                <input type="radio" name="que_hissu_flg_24" value="1" @if($que_hissu_flg_24=="1")checked @endif>必須
                <input type="radio" name="que_hissu_flg_24" value="0" @if($que_hissu_flg_24=="0")checked @endif>なしでも可
            </td>
        </tr>
        <tr>
            <th>解答方式</th>
            <td style="background-color:#F0F0F0;">
                <input type="radio" name="que_keishiki_kbn_24" value="1" @if($que_keishiki_kbn_24=="1")checked @endif onChange="_keishikiChk(this,'24');"/>テキスト入力のみ<br />
                <input type="radio" name="que_keishiki_kbn_24" value="2" @if($que_keishiki_kbn_24=="2")checked @endif onChange="_keishikiChk(this,'24');"/>単一選択方式<br />
                <input type="radio" name="que_keishiki_kbn_24" value="3" @if($que_keishiki_kbn_24=="3")checked @endif onChange="_keishikiChk(this,'24');"/>単一選択方式＋テキスト入力<br />　　　テキスト入力コメント<input type="text" style="padding:4px;" autocomplete="off" name="que_keishiki_r_comment_24" value="{{ $que_keishiki_r_comment_24 }}" size="43"><br />
                <input type="radio" name="que_keishiki_kbn_24" value="4" @if($que_keishiki_kbn_24=="4")checked @endif onChange="_keishikiChk(this,'24');"/>チェックボックス方式<br />
                <input type="radio" name="que_keishiki_kbn_24" value="5" @if($que_keishiki_kbn_24=="5")checked @endif onChange="_keishikiChk(this,'24');"/>チェックボックス方式＋テキスト入力<br />　　　テキスト入力コメント<input type="text" style="padding:4px;" autocomplete="off" name="que_keishiki_c_comment_24" value="{{ $que_keishiki_c_comment_24 }}" size="43"><br />
            </td>
        </tr>
        <tr style="display:@if($que_keishiki_kbn_24 < '2' )none @else table-row @endif;" id="sentakushi24">
            <th>選択肢</th>
            <td style="background-color:#F0F0F0;">
                <textarea name="que_sentakushi_24" style="padding:4px;width:200px;height:100px;">{!! $que_sentakushi_24 !!}</textarea>
                ※選択肢は改行してリストを作成してください。
            </td>
        </tr>

        <!-- *************************************************************************** -->
        <tr>
            <th rowspan="{!! $row25 !!}" id="que25">設問25</th>
            <th>設問文</th>
            <td>
                <input type="text" autocomplete="off" name="que_question_25" value="{{ $que_question_25 }}" style="width:400px;padding:4px;">
            </td>
        </tr>
        <tr>
            <th>入力</th>
            <td>
                <input type="radio" name="que_hissu_flg_25" value="1" @if($que_hissu_flg_25=="1")checked @endif>必須
                <input type="radio" name="que_hissu_flg_25" value="0" @if($que_hissu_flg_25=="0")checked @endif>なしでも可
            </td>
        </tr>
        <tr>
            <th>解答方式</th>
            <td>
                <input type="radio" name="que_keishiki_kbn_25" value="1" @if($que_keishiki_kbn_25=="1")checked @endif onChange="_keishikiChk(this,'25');"/>テキスト入力のみ<br />
                <input type="radio" name="que_keishiki_kbn_25" value="2" @if($que_keishiki_kbn_25=="2")checked @endif onChange="_keishikiChk(this,'25');"/>単一選択方式<br />
                <input type="radio" name="que_keishiki_kbn_25" value="3" @if($que_keishiki_kbn_25=="3")checked @endif onChange="_keishikiChk(this,'25');"/>単一選択方式＋テキスト入力<br />　　　テキスト入力コメント<input type="text" style="padding:4px;" autocomplete="off" name="que_keishiki_r_comment_25" value="{{ $que_keishiki_r_comment_25 }}" size="43"><br />
                <input type="radio" name="que_keishiki_kbn_25" value="4" @if($que_keishiki_kbn_25=="4")checked @endif onChange="_keishikiChk(this,'25');"/>チェックボックス方式<br />
                <input type="radio" name="que_keishiki_kbn_25" value="5" @if($que_keishiki_kbn_25=="5")checked @endif onChange="_keishikiChk(this,'25');"/>チェックボックス方式＋テキスト入力<br />　　　テキスト入力コメント<input type="text" style="padding:4px;" autocomplete="off" name="que_keishiki_c_comment_25" value="{{ $que_keishiki_c_comment_25 }}" size="43"><br />
            </td>
        </tr>
        <tr style="display:@if($que_keishiki_kbn_25 < '2' )none @else table-row @endif;" id="sentakushi25">
            <th>選択肢</th>
            <td>
                <textarea name="que_sentakushi_25" style="padding:4px;width:200px;height:100px;">{!! $que_sentakushi_25 !!}</textarea>
                ※選択肢は改行してリストを作成してください。
            </td>
        </tr>

        <!-- *************************************************************************** -->
        <tr>
            <th rowspan="{!! $row26 !!}" id="que26">設問26</th>
            <th>設問文</th>
            <td style="background-color:#F0F0F0;">
                <input type="text" autocomplete="off" name="que_question_26" value="{{ $que_question_26 }}" style="width:400px;padding:4px;">
            </td>
        </tr>
        <tr>
            <th>入力</th>
            <td style="background-color:#F0F0F0;">
                <input type="radio" name="que_hissu_flg_26" value="1" @if($que_hissu_flg_26=="1")checked @endif>必須
                <input type="radio" name="que_hissu_flg_26" value="0" @if($que_hissu_flg_26=="0")checked @endif>なしでも可
            </td>
        </tr>
        <tr>
            <th>解答方式</th>
            <td style="background-color:#F0F0F0;">
                <input type="radio" name="que_keishiki_kbn_26" value="1" @if($que_keishiki_kbn_26=="1")checked @endif onChange="_keishikiChk(this,'26');"/>テキスト入力のみ<br />
                <input type="radio" name="que_keishiki_kbn_26" value="2" @if($que_keishiki_kbn_26=="2")checked @endif onChange="_keishikiChk(this,'26');"/>単一選択方式<br />
                <input type="radio" name="que_keishiki_kbn_26" value="3" @if($que_keishiki_kbn_26=="3")checked @endif onChange="_keishikiChk(this,'26');"/>単一選択方式＋テキスト入力<br />　　　テキスト入力コメント<input type="text" style="padding:4px;" autocomplete="off" name="que_keishiki_r_comment_26" value="{{ $que_keishiki_r_comment_26 }}" size="43"><br />
                <input type="radio" name="que_keishiki_kbn_26" value="4" @if($que_keishiki_kbn_26=="4")checked @endif onChange="_keishikiChk(this,'26');"/>チェックボックス方式<br />
                <input type="radio" name="que_keishiki_kbn_26" value="5" @if($que_keishiki_kbn_26=="5")checked @endif onChange="_keishikiChk(this,'26');"/>チェックボックス方式＋テキスト入力<br />　　　テキスト入力コメント<input type="text" style="padding:4px;" autocomplete="off" name="que_keishiki_c_comment_26" value="{{ $que_keishiki_c_comment_26 }}" size="43"><br />
            </td>
        </tr>
        <tr style="display:@if($que_keishiki_kbn_26 < '2' )none @else table-row @endif;" id="sentakushi26">
            <th>選択肢</th>
            <td style="background-color:#F0F0F0;">
                <textarea name="que_sentakushi_26" style="padding:4px;width:200px;height:100px;">{!! $que_sentakushi_26 !!}</textarea>
                ※選択肢は改行してリストを作成してください。
            </td>
        </tr>

        <!-- *************************************************************************** -->
        <tr>
            <th rowspan="{!! $row27 !!}" id="que27">設問27</th>
            <th>設問文</th>
            <td>
                <input type="text" autocomplete="off" name="que_question_27" value="{{ $que_question_27 }}" style="width:400px;padding:4px;">
            </td>
        </tr>
        <tr>
            <th>入力</th>
            <td>
                <input type="radio" name="que_hissu_flg_27" value="1" @if($que_hissu_flg_27=="1")checked @endif>必須
                <input type="radio" name="que_hissu_flg_27" value="0" @if($que_hissu_flg_27=="0")checked @endif>なしでも可
            </td>
        </tr>
        <tr>
            <th>解答方式</th>
            <td>
                <input type="radio" name="que_keishiki_kbn_27" value="1" @if($que_keishiki_kbn_27=="1")checked @endif onChange="_keishikiChk(this,'27');"/>テキスト入力のみ<br />
                <input type="radio" name="que_keishiki_kbn_27" value="2" @if($que_keishiki_kbn_27=="2")checked @endif onChange="_keishikiChk(this,'27');"/>単一選択方式<br />
                <input type="radio" name="que_keishiki_kbn_27" value="3" @if($que_keishiki_kbn_27=="3")checked @endif onChange="_keishikiChk(this,'27');"/>単一選択方式＋テキスト入力<br />　　　テキスト入力コメント<input type="text" style="padding:4px;" autocomplete="off" name="que_keishiki_r_comment_27" value="{{ $que_keishiki_r_comment_27 }}" size="43"><br />
                <input type="radio" name="que_keishiki_kbn_27" value="4" @if($que_keishiki_kbn_27=="4")checked @endif onChange="_keishikiChk(this,'27');"/>チェックボックス方式<br />
                <input type="radio" name="que_keishiki_kbn_27" value="5" @if($que_keishiki_kbn_27=="5")checked @endif onChange="_keishikiChk(this,'27');"/>チェックボックス方式＋テキスト入力<br />　　　テキスト入力コメント<input type="text" style="padding:4px;" autocomplete="off" name="que_keishiki_c_comment_27" value="{{ $que_keishiki_c_comment_27 }}" size="43"><br />
            </td>
        </tr>
        <tr style="display:@if($que_keishiki_kbn_27 < '2' )none @else table-row @endif;" id="sentakushi27">
            <th>選択肢</th>
            <td>
                <textarea name="que_sentakushi_27" style="padding:4px;width:200px;height:100px;">{!! $que_sentakushi_27 !!}</textarea>
                ※選択肢は改行してリストを作成してください。
            </td>
        </tr>

        <!-- *************************************************************************** -->
        <tr>
            <th rowspan="{!! $row28 !!}" id="que28">設問28</th>
            <th>設問文</th>
            <td style="background-color:#F0F0F0;">
                <input type="text" autocomplete="off" name="que_question_28" value="{{ $que_question_28 }}" style="width:400px;padding:4px;">
            </td>
        </tr>
        <tr>
            <th>入力</th>
            <td style="background-color:#F0F0F0;">
                <input type="radio" name="que_hissu_flg_28" value="1" @if($que_hissu_flg_28=="1")checked @endif>必須
                <input type="radio" name="que_hissu_flg_28" value="0" @if($que_hissu_flg_28=="0")checked @endif>なしでも可
            </td>
        </tr>
        <tr>
            <th>解答方式</th>
            <td style="background-color:#F0F0F0;">
                <input type="radio" name="que_keishiki_kbn_28" value="1" @if($que_keishiki_kbn_28=="1")checked @endif onChange="_keishikiChk(this,'28');"/>テキスト入力のみ<br />
                <input type="radio" name="que_keishiki_kbn_28" value="2" @if($que_keishiki_kbn_28=="2")checked @endif onChange="_keishikiChk(this,'28');"/>単一選択方式<br />
                <input type="radio" name="que_keishiki_kbn_28" value="3" @if($que_keishiki_kbn_28=="3")checked @endif onChange="_keishikiChk(this,'28');"/>単一選択方式＋テキスト入力<br />　　　テキスト入力コメント<input type="text" style="padding:4px;" autocomplete="off" name="que_keishiki_r_comment_28" value="{{ $que_keishiki_r_comment_28 }}" size="43"><br />
                <input type="radio" name="que_keishiki_kbn_28" value="4" @if($que_keishiki_kbn_28=="4")checked @endif onChange="_keishikiChk(this,'28');"/>チェックボックス方式<br />
                <input type="radio" name="que_keishiki_kbn_28" value="5" @if($que_keishiki_kbn_28=="5")checked @endif onChange="_keishikiChk(this,'28');"/>チェックボックス方式＋テキスト入力<br />　　　テキスト入力コメント<input type="text" style="padding:4px;" autocomplete="off" name="que_keishiki_c_comment_28" value="{{ $que_keishiki_c_comment_28 }}" size="43"><br />
            </td>
        </tr>
        <tr style="display:@if($que_keishiki_kbn_28 < '2' )none @else table-row @endif;" id="sentakushi28">
            <th>選択肢</th>
            <td style="background-color:#F0F0F0;">
                <textarea name="que_sentakushi_28" style="padding:4px;width:200px;height:100px;">{!! $que_sentakushi_28 !!}</textarea>
                ※選択肢は改行してリストを作成してください。
            </td>
        </tr>

        <!-- *************************************************************************** -->
        <tr>
            <th rowspan="{!! $row29 !!}" id="que29">設問29</th>
            <th>設問文</th>
            <td>
                <input type="text" autocomplete="off" name="que_question_29" value="{{ $que_question_29 }}" style="width:400px;padding:4px;">
            </td>
        </tr>
        <tr>
            <th>入力</th>
            <td>
                <input type="radio" name="que_hissu_flg_29" value="1" @if($que_hissu_flg_29=="1")checked @endif>必須
                <input type="radio" name="que_hissu_flg_29" value="0" @if($que_hissu_flg_29=="0")checked @endif>なしでも可
            </td>
        </tr>
        <tr>
            <th>解答方式</th>
            <td>
                <input type="radio" name="que_keishiki_kbn_29" value="1" @if($que_keishiki_kbn_29=="1")checked @endif onChange="_keishikiChk(this,'29');"/>テキスト入力のみ<br />
                <input type="radio" name="que_keishiki_kbn_29" value="2" @if($que_keishiki_kbn_29=="2")checked @endif onChange="_keishikiChk(this,'29');"/>単一選択方式<br />
                <input type="radio" name="que_keishiki_kbn_29" value="3" @if($que_keishiki_kbn_29=="3")checked @endif onChange="_keishikiChk(this,'29');"/>単一選択方式＋テキスト入力<br />　　　テキスト入力コメント<input type="text" style="padding:4px;" autocomplete="off" name="que_keishiki_r_comment_29" value="{{ $que_keishiki_r_comment_29 }}" size="43"><br />
                <input type="radio" name="que_keishiki_kbn_29" value="4" @if($que_keishiki_kbn_29=="4")checked @endif onChange="_keishikiChk(this,'29');"/>チェックボックス方式<br />
                <input type="radio" name="que_keishiki_kbn_29" value="5" @if($que_keishiki_kbn_29=="5")checked @endif onChange="_keishikiChk(this,'29');"/>チェックボックス方式＋テキスト入力<br />　　　テキスト入力コメント<input type="text" style="padding:4px;" autocomplete="off" name="que_keishiki_c_comment_29" value="{{ $que_keishiki_c_comment_29 }}" size="43"><br />
            </td>
        </tr>
        <tr style="display:@if($que_keishiki_kbn_29 < '2' )none @else table-row @endif;" id="sentakushi29">
            <th>選択肢</th>
            <td>
                <textarea name="que_sentakushi_29" style="padding:4px;width:200px;height:100px;">{!! $que_sentakushi_29 !!}</textarea>
                ※選択肢は改行してリストを作成してください。
            </td>
        </tr>

        <!-- *************************************************************************** -->
        <tr>
            <th rowspan="{!! $row30 !!}" id="que30">設問30</th>
            <th>設問文</th>
            <td style="background-color:#F0F0F0;">
                <input type="text" autocomplete="off" name="que_question_30" value="{{ $que_question_30 }}" style="width:400px;padding:4px;">
            </td>
        </tr>
        <tr>
            <th>入力</th>
            <td style="background-color:#F0F0F0;">
                <input type="radio" name="que_hissu_flg_30" value="1" @if($que_hissu_flg_30=="1")checked @endif>必須
                <input type="radio" name="que_hissu_flg_30" value="0" @if($que_hissu_flg_30=="0")checked @endif>なしでも可
            </td>
        </tr>
        <tr>
            <th>解答方式</th>
            <td style="background-color:#F0F0F0;">
                <input type="radio" name="que_keishiki_kbn_30" value="1" @if($que_keishiki_kbn_30=="1")checked @endif onChange="_keishikiChk(this,'30');"/>テキスト入力のみ<br />
                <input type="radio" name="que_keishiki_kbn_30" value="2" @if($que_keishiki_kbn_30=="2")checked @endif onChange="_keishikiChk(this,'30');"/>単一選択方式<br />
                <input type="radio" name="que_keishiki_kbn_30" value="3" @if($que_keishiki_kbn_30=="3")checked @endif onChange="_keishikiChk(this,'30');"/>単一選択方式＋テキスト入力<br />　　　テキスト入力コメント<input type="text" style="padding:4px;" autocomplete="off" name="que_keishiki_r_comment_30" value="{{ $que_keishiki_r_comment_30 }}" size="43"><br />
                <input type="radio" name="que_keishiki_kbn_30" value="4" @if($que_keishiki_kbn_30=="4")checked @endif onChange="_keishikiChk(this,'30');"/>チェックボックス方式<br />
                <input type="radio" name="que_keishiki_kbn_30" value="5" @if($que_keishiki_kbn_30=="5")checked @endif onChange="_keishikiChk(this,'30');"/>チェックボックス方式＋テキスト入力<br />　　　テキスト入力コメント<input type="text" style="padding:4px;" autocomplete="off" name="que_keishiki_c_comment_30" value="{{ $que_keishiki_c_comment_30 }}" size="43"><br />
            </td>
        </tr>
        <tr style="display:@if($que_keishiki_kbn_30 < '2' )none @else table-row @endif;" id="sentakushi30">
            <th>選択肢</th>
            <td style="background-color:#F0F0F0;">
                <textarea name="que_sentakushi_30" style="padding:4px;width:200px;height:100px;">{!! $que_sentakushi_30 !!}</textarea>
                ※選択肢は改行してリストを作成してください。
            </td>
        </tr>


    </table>


    <p class="msr_btn15">
        @if($mode=='insert')
          <a href="javascript:_regist('insert');void(0);">上記内容で新規登録する</a>
         @else 
          <a href="javascript:_regist('update');void(0);">上記内容で更新する</a>
         @endif
    </p>

    </form>

 @else 
  <div class="col-md-12">
    <div class="card">
      <div class="card-body">
        @include('parts.select_event')
      </div>
    </div>
  </div>
 @endif

</div>
