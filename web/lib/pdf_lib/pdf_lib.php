<?php

//settingファイルの項目記載ルール
//******************************************************************************************************
// 項目設定
//******************************************************************************************************
//
//        [項目ID]
//        tpl=TEMPLATE_x
//        type= normal      : 通常テキスト(1行テキスト)
//              textarea    : 複数行テキスト
//              tel         : xx-xxxx-xxxx形式を３フィールドに分割して印字
//              date        : yyyy/mm/dd or yyyy-mm-dd 形式を３フィールドに分割して印字
//              wareki_date : yyyy/mm/dd or yyyy-mm-dd 形式を３フィールドに分割して年のみ和暦で「平成26」に変換して印字
//              rect        : 罫線で四角形を印字
//              line        : 罫線
//              list_rect   : リスト用罫線で四角形を印字
//              list_rect_noborder   : リスト用罫線で四角形を印字(borderなし)
//              list_line   : リスト用罫線
//              cur_page_count : 1回のWriteInfoでの現在ページ番号
//              all_page_count : 1回のWriteInfoでの全ページ数
//              print_page_count : PDF全体でみたページ数
//
//        x = x位置数値 (※3フィールドに分割するtypeの場合はx1,x2,x3で指定)
//        y = y位置数値 (※3フィールドに分割するtypeの場合はy1,y2,y3で指定)
//        w = 幅数値 (※3フィールドに分割するtypeの場合はw1,w2,w3で指定) (0だと幅指定なしだが文字数多いと印刷枠からはみ出す可能性あるので注意）
//        h = typeが "rect" or "textarea" の場合の高さ
//        maxh = typeが "textarea" の場合のMAXの高さ（これ以上枠が高くならない※下にはみ出た文字は見えない）
//        lw = 線の太さ
//        font_name=msgothicp
//        font_size=数値 (wを指定した場合幅に収まらなければ自動でサイズダウンされます)
//        font_style= 太字の場合「B」、イタリックの場合「I」、取り消し「D」複数指定可
//        align = L or R or C (左寄せ、右寄せ、中央)  (※3フィールドに分割するtypeの場合はalign1,align2,align3で指定)
//        valign = T or M or B (上寄せ、中央寄せ、下寄せ)  (※type=textareaの場合に指定可能)
//        underline = 0 or 1 (下線付けるなら1)
//        border = 0 or 1 (枠線付けるなら1)
//        border_color = #ffffff形式で
//        fill_color = #ffffff形式で
//        firstpage=yes or no (WriteInfo単位で、最初のページのみ印字なら指定)
//        lastpage=yes or no (WriteInfo単位で、最終ページのみ印字なら指定)
//        qrtype=L,M,Q,H


if( strpos(dirname(__FILE__),"\\")!==FALSE ){
    define("__DIR__", str_replace("\\","/", dirname(__FILE__)) . "/tcpdf" );
}else{
    if (version_compare(PHP_VERSION, '5.3.0') < 0) {
        //5.3.0より前なので__DIR__がない
        define("__DIR__", dirname(__FILE__)."/tcpdf" );
    }
}

if ( !function_exists('sys_get_temp_dir')) {
  function sys_get_temp_dir() {
    if (!empty($_ENV['TMP'])) { return realpath($_ENV['TMP']); }
    if (!empty($_ENV['TMPDIR'])) { return realpath( $_ENV['TMPDIR']); }
    if (!empty($_ENV['TEMP'])) { return realpath( $_ENV['TEMP']); }
    $tempfile=tempnam(__FILE__,'');
    if (file_exists($tempfile)) {
      unlink($tempfile);
      return realpath(dirname($tempfile));
    }
    return null;
  }
}

if ( !function_exists('array_fill_keys')) {
    function array_fill_keys($target, $value = '') {
        if(is_array($target)) {
            foreach($target as $key => $val) {
                $filledArray[$val] = is_array($value) ? $value[$key] : $value;
            }
        }
        return $filledArray;
    }
}

define("SYS_ROOT", dirname(__FILE__));
define("DOC_ROOT", dirname(__FILE__));
require_once(SYS_ROOT . "/lib/class/common/Config.class.php" );
require_once(SYS_ROOT . "/common/class/pdf/Pdf.class.php" );
require_once(SYS_ROOT . "/lib/class/common/AbstractDefaultAction.class.php" );
