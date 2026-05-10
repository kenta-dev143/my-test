<?php


require_once(SYS_ROOT . "/lib/class/common/AbstractDefaultAction.class.php" );
require_once(SYS_ROOT . "/lib/class/common/Config.class.php" );
require_once(SYS_ROOT . "/lib/class/common/Utils.class.php" );
require_once(SYS_ROOT . '/tcpdf/lang/jpn.php');
require_once(SYS_ROOT .'/tcpdf/tcpdf.php');
//require_once(SYS_ROOT .'/Fpdi/FpdfTpl.php');
// require_once(SYS_ROOT .'/tcpdf/PdfParser/Tokenizer.php');
// require_once(SYS_ROOT .'/tcpdf/PdfParser/PdfParser.php');
// require_once(SYS_ROOT .'/tcpdf/PdfReader/PdfReader.php');
// require_once(SYS_ROOT .'/tcpdf/PdfParser/StreamReader.php');
// require_once(SYS_ROOT .'/tcpdf/FpdiTrait.php');
// require_once(SYS_ROOT .'/tcpdf/Tcpdf/Fpdi.php');
require_once(SYS_ROOT .'/tcpdf/autoload.php');
require_once(SYS_ROOT . "/lib/class/common/StaticValues.class.php" );

use setasign\Fpdi\Tcpdf\Fpdi;

//class Pdf extends FPDI {
class Pdf extends Fpdi {


    private $settingName;
    private $templateDir;
    public $config;
    private $orientation;
    private $pageCount;
    public $cb = '';
    public $code = array();
    public $cd = 0;
    public $bar = array();
    private $page_w; //2018/11/12 Add
    private $page_h; //2018/11/12 Add

    public function __construct($settingName, $templateDir,$diskcache=false) {

        define("_FLNM_THROUGH",true);

        $this->settingName = $settingName;
        $this->templateDir = $templateDir;

        $this->pageCount = 0;

        $f = $this->templateDir . "/" . $settingName . "/setting.ini";
        if(file_exists($f)) {
            $this->config = parse_ini_file($f, true);
        }else{
            die($f." not found!");
        }


        //$orientation  'P' 縦  'L' 横;
        $this->orientation = $this->config['SETTING']['orientation']?$this->config['SETTING']['orientation']:'P';

        //2018/11/12 Add ------ Start ----
        //用紙サイズ ※印刷が横向きでも、用紙縦の場合のサイズを指定 (指定が無い場合A4の幅と高さ)
        $this->page_w = $this->config['SETTING']['page_w']?$this->config['SETTING']['page_w']:210;
        $this->page_h = $this->config['SETTING']['page_h']?$this->config['SETTING']['page_h']:297;
        //2018/11/12 Add ------ End ----

        //parent::__construct($orientation);
        parent::__construct($orientation, 'mm', 'A4', true, 'UTF-8', $diskcache, false);

        mb_internal_encoding("UTF8");
        mb_regex_encoding("UTF8");


        //case 'mm': {
        //$this->k = $this->dpi / 25.4; -->2.834
        //case 'px':
        //case 'pt': {
        //$this->k = 1;
        //break;
        //$dpi = 72;

        $this->Open();
        $this->setPrintHeader( false );    // 余計な横線を消す
        $this->setPrintFooter( false );    // 余計な横線を消す
        $this->setFooterMargin(0);
        $this->setMargins(0, 0, 0);
        $this->setAutoPageBreak(false);

        if($this->config['SETTING']['title']!=""){
            $this->SetTitle($this->config['SETTING']['title']);
            $this->SetSubject($this->config['SETTING']['title']);
        }


        //parent::__construct($orientation='P', $unit='mm', $format='A4', $unicode=true, $encoding='UTF-8', $diskcache=false, $pdfa=false)
    }

    public function WriteInfo($tpl_id,$info) {
        if($this->config == null) {
            return;
        }

        //テンプレ情報取得
        foreach($this->config as $key => $setting_val){
            if($key==$tpl_id){
                $_tmplate_info = $this->config[$key];
                break;
            }
        }

        $pageNum = $this->setSourceFile($this->templateDir . "/" . $this->settingName . "/" . $_tmplate_info['tpl_name']);

        //$this->AddFont('msgothicp','','msgothicp.ttf',true);


        if($_tmplate_info['list']=="yes" || $_tmplate_info['list']==1){
            $recs = $info['list'];
            $header_info = $info;
            unset($header_info['list']);
            if(_count($info['list2']) > 0){
                $recs2 = $info['list2'];
                unset($header_info['list2']);
            }
            $rows_per_page = $_tmplate_info['rows'];
        }else{
            $recs = array();
            $recs[] = $info;
            $rows_per_page = 1;
        }


        for($page = 1; $page <= $_tmplate_info['pages'] ; $page++){

            $iIndex = $this->importPage($page);

            $_cur_page_count = 0;

            if($_tmplate_info['list']=="yes" || $_tmplate_info['list']==1){
                $_all_page_count = ceil( _count($recs) / $_tmplate_info['rows'] );
            }

            $y_offset_sum = 0; //2019/02/20 Add

            for($r=0;$r<_count($recs);$r++){

                $write_recs = array();

                if( ($r % $rows_per_page)  ==0){

                    //2018/11/12 Mod ------------ Before -----------
                    // //改ページ
                    // $this->AddPage($this->orientation);
                    // $this->pageCount++;
                    // $this->Bookmark( $this->pageCount );
                    //
                    // $_cur_page_count++;
                    //
                    // if($this->orientation=="P"){
                    //     $_h = 297;
                    //     $_w = 210;
                    //     $debug_max_x = 190;
                    //     $debug_max_y = 290;
                    // }else{
                    //     $_h = 210;
                    //     $_w = 297;
                    //     $debug_max_x = 290;
                    //     $debug_max_y = 190;
                    // }
                    // $this->useTemplate($iIndex,0,0,$_w,$_h,false);//A4サイズをmmで指定
                    //2018/11/12 Mod ------------ After -----------
                    if($this->orientation=="P"){
                        $_w = $this->page_w;
                        $_h = $this->page_h;
                        $debug_max_x = $_w - 20;
                        $debug_max_y = $_h - 7;
                    }else{
                        $_w = $this->page_h;
                        $_h = $this->page_w;
                        $debug_max_x = $_w - 7;
                        $debug_max_y = $_h - 20;
                    }

                    $this->AddPage($this->orientation,array($_w,$_h));
                    $this->pageCount++;
                    $this->Bookmark( $this->pageCount );
                    $_cur_page_count++;

                    $this->useTemplate($iIndex);
                    //2018/11/12 Mod ------------ End -----------

                    //debug時のルーラー描画
                    if($this->config['SETTING']['debug']){
                        for($x=10;$x<=$debug_max_x;$x=$x+10){
                            $str = $x;
                            $y = 0;
                            $w = 0;
                            $align = "L";
                            $this->_write($str, $x-1, $y, $w ,0, $align, $font_name , 9 , '',0, -1 );
                            $this->_write("|", $x, $y+2.5, $w ,0, $align, $font_name , 9 , '',0, -1 );
                            $this->_write("|", $x, $y+122, $w ,0, $align, $font_name , 9 , '',0, -1 );
                        }
                        for($y=10;$y<=$debug_max_y;$y=$y+10){
                            $str = $y;
                            $x = 0;
                            $w = 0;
                            $align = "L";
                            $this->_write($str, $x, $y, $w ,0, $align, $font_name , 9 , '',0, -1 );
                            $this->_write("-", $x+5.5, $y, $w ,0, $align, $font_name , 9 , '',0, -1 );
                        }
                    }

                    if($_tmplate_info['list']=="yes" || $_tmplate_info['list']==1){
                        $header_info['offset_y'] = 0;
                        $y_offset_sum = 0; //2019/02/20 Add
                        $write_recs[] = $header_info;
                    }
                }

                $recs[$r]['offset_y'] = ($r % $rows_per_page) * $_tmplate_info['height'];
                if(_count($recs2)>0){
                    $recs2[$r]['offset_y'] = ($r % $rows_per_page) * $_tmplate_info['height'];
                }

                $write_recs[] = $recs[$r];
                if(_count($recs2)>0){
                    $write_recs[] = $recs2[$r];
                }

                //2019/02/19 Add --- Strat ---
                // $save_offset_y = $recs[$r]['offset_y'];
                // $y_offset_sum = 0;
                //2019/02/19 Add --- End ---

                for($wrt_idx=0;$wrt_idx<_count($write_recs);$wrt_idx++){


                    $rec = $write_recs[$wrt_idx];
#                     foreach($this->config as $key => $setting_val){
#
#                         if($key=="SETTING" || substr($key,0,9)=="TEMPLATE_") continue;
#                         if($setting_val['tpl']!=$tpl_id) continue;
#
#                         if(!isset($rec[$key])) continue;

                    //2019/02/19 Add --- Strat ---
                    $rec['offset_y'] = $rec['offset_y'] + $y_offset_sum;
                    //2019/02/19 Add --- End ---

                    foreach($rec as $key => $val){

                        //2019/02/19 Add --- Strat ---
                        if($key == "y_offset"){
                            $rec['offset_y'] += $val;
                            $y_offset_sum += $val;
                            continue;
                        }
                        //2019/02/19 Add --- End ---

                        $setting_val = $this->config[$key];

                        //2013/10/10 Add
                        if($setting_val['tpl']!=$tpl_id) continue;

                        if(_count($setting_val) == 0) continue;

                        if($setting_val['type']!="cur_page_count" && $setting_val['type']!="all_page_count" && $setting_val['type']!="rect"){
                            //特殊タイプでない場合で、値が空ならスキップ
                            if(trim($rec[$key])=="") continue;
                        }

                        //１ページ目指定がある場合は、１ページ時のみ印字
                        if($setting_val['firstpage']=="1"){
                            if($_cur_page_count!=1){
                                continue;
                            }
                        }

                        //最終ページ指定がある場合は、最終ページ時のみ印字
                        if($setting_val['lastpage']=="1"){
                            if($_cur_page_count!=$_all_page_count){
                                continue;
                            }
                        }

                        switch($setting_val['type']){
                            case "line" :
                                $x1 = $setting_val['x1']?$setting_val['x1']:0;
                                $y1 = $setting_val['y1']?$setting_val['y1']:0;
                                $x2 = $setting_val['x2']?$setting_val['x2']:0;
                                $y2 = $setting_val['y2']?$setting_val['y2']:0;

                                //2017/11/28 Mod ---- Before ----------
                                //$this->Line($x1, $y1, $x2, $y2);
                                //2017/11/28 Mod ---- After ----------
                                if(substr($setting_val['border_color'],0,1)=="#" && strlen($setting_val['border_color'])==7){
                                    $border_color = $this->Hex2RGB($setting_val['border_color']);
                                }else{
                                    $border_color = array(0, 0, 0);
                                }
                                if($setting_val['lw']){
                                    $lw = $setting_val['lw'];
                                }else{
                                    $lw = 0.3;
                                }
                                $style = array('width' => $lw, 'color' => $border_color);
                                $this->Line($x1, $y1, $x2, $y2, $style);
                                //2017/11/28 Mod ---- End ----------

                                break;

                            case "list_line" :
                                $x1 = $setting_val['x1']?$setting_val['x1']:0;
                                $y1 = $setting_val['y1']?$setting_val['y1']:0;
                                $x2 = $setting_val['x2']?$setting_val['x2']:0;
                                $y2 = $setting_val['y2']?$setting_val['y2']:0;

                                //2017/11/28 Mod ---- Before ----
                                // if($setting_val['lw']){
                                //     $style = array('width' => $setting_val['lw']);
                                // }else{
                                //     $style = array('width' => 0.3);
                                // }
                                //2017/11/28 Mod ---- After ----
                                if(substr($setting_val['border_color'],0,1)=="#" && strlen($setting_val['border_color'])==7){
                                    $border_color = $this->Hex2RGB($setting_val['border_color']);
                                }else{
                                    $border_color = array(0, 0, 0);
                                }
                                if($setting_val['lw']){
                                    $lw = $setting_val['lw'];
                                }else{
                                    $lw = 0.3;
                                }
                                $style = array('width' => $lw, 'color' => $border_color);
                                //2017/11/28 Mod ---- End ----
                                $this->Line($x1, $y1 + $rec['offset_y'], $x2, $y2 + $rec['offset_y'], $style);

                                break;

                            case "rect" :
                                $x = $setting_val['x']?$setting_val['x']:0;
                                $y = $setting_val['y']?$setting_val['y']:0;
                                $w = $setting_val['w']?$setting_val['w']:0;
                                $h = $setting_val['h']?$setting_val['h']:0;
                                //$style = array('width' => 0.2, 'cap' => 'round', 'join' => 'rounded', 'dash' => '5', 'color' => array(0, 0, 0));
                                if($setting_val['lw']){
                                    $lw = $setting_val['lw'];
                                }else{
                                    $lw = 0.3;
                                }

                                //2017/11/28 Mod -------- Before ----------
                                // $style = array('width' => $lw, 'cap' => 'round', 'join' => 'rounded', 'color' => array(0, 0, 0));
                                // $this->Rect($x, $y, $w, $h, 'D', array('all' => $style) );
                                //2017/11/28 Mod -------- After ----------
                                if(substr($setting_val['border_color'],0,1)=="#" && strlen($setting_val['border_color'])==7){
                                    $border_color = $this->Hex2RGB($setting_val['border_color']);
                                }else{
                                    $border_color = array(0, 0, 0);
                                }
                                $border_style = array('width' => $lw, 'cap' => 'round', 'join' => 'rounded', 'color' => $border_color);
                                //#ffffff形式で指定
                                if(substr($setting_val['fill_color'],0,1)=="#" && strlen($setting_val['fill_color'])==7){
                                    $fill_color = $this->Hex2RGB($setting_val['fill_color']);
                                    $DF_style = "DF";
                                }else{
                                    $fill_color = array();
                                    $DF_style = "D";
                                }
                                $this->Rect($x, $y, $w, $h, $DF_style, array('all' => $border_style) ,$fill_color);
                                //2017/11/28 Mod -------- End ----------



                                break;

                            case "list_rect" :
                            case "list_rect_noborder" :// 2018/12/12  Add
                                $x = $setting_val['x']?$setting_val['x']:0;
                                $y = $setting_val['y']?$setting_val['y']:0;
                                $w = $setting_val['w']?$setting_val['w']:0;
                                $h = $setting_val['h']?$setting_val['h']:0;
                                //$style = array('width' => 0.2, 'cap' => 'round', 'join' => 'rounded', 'dash' => '5', 'color' => array(0, 0, 0));
                                if($setting_val['lw']){
                                    $lw = $setting_val['lw'];
                                }else{
                                    $lw = 0.3;
                                }

                                //2017/11/28 Mod -------- Before ----------
                                // $style = array('width' => $lw, 'cap' => 'round', 'join' => 'rounded', 'color' => array(0, 0, 0));
                                // $this->Rect($x, $y + $rec['offset_y'], $w, $h, 'D', array('all' => $style) );
                                //2017/11/28 Mod -------- After ----------

                                // 2018/12/12  Add ------ Start ------
                                if($setting_val['type'] != "list_rect_noborder"){
                                    if(substr($setting_val['border_color'],0,1)=="#" && strlen($setting_val['border_color'])==7){
                                        $border_color = $this->Hex2RGB($setting_val['border_color']);
                                    }else{
                                        $border_color = array(0, 0, 0);
                                    }
                                    $border_style = array('width' => $lw, 'cap' => 'round', 'join' => 'rounded', 'color' => $border_color);
                                }else{
                                    //2019/02/20 Add
                                    $border_style = array();
                                }
                                // 2018/12/12  Add ------ End ------
                                //#ffffff形式で指定

                                if(substr($setting_val['fill_color'],0,1)=="#" && strlen($setting_val['fill_color'])==7){
                                    $fill_color = $this->Hex2RGB($setting_val['fill_color']);
                                    $DF_style = "DF";
                                }else{
                                    $fill_color = array();
                                    $DF_style = "D";
                                }
                                $this->Rect($x, $y + $rec['offset_y'], $w, $h, $DF_style, array('all' => $border_style) ,$fill_color);
                                //2017/11/28 Mod -------- End ----------

                                break;

                            case "tel" :
                                $arr = explode("-",$rec[$key]);

                                $font_name = $setting_val['font_name']?$setting_val['font_name']:"msgothicp";
                                $font_size = $setting_val['font_size']?$setting_val['font_size']:null;
                                $border =  $setting_val['border']?$setting_val['border']:0;
                                for($i=0;$i<3;$i++){
                                    $str = $arr[$i];
                                    $x = $setting_val['x'.($i+1)]?$setting_val['x'.($i+1)]:0;
                                    $y = $setting_val['y'.($i+1)]?$setting_val['y'.($i+1)]:0;
                                    $w = $setting_val['w'.($i+1)]?$setting_val['w'.($i+1)]:0;
                                    $align = $setting_val['align'.($i+1)]?$setting_val['align'.($i+1)]:"L";
                                    $fontStyle = $setting_val['font_style'.($i+1)]?$setting_val['font_style'.($i+1)]:'';
                                    $this->_write($str, $x, $y + $rec['offset_y'], $w ,0, $align, $font_name , $font_size , $fontStyle, $border );
                                }
                                break;

                            case "date" :
                            case "wareki_date" :
                                $repl =str_replace("-","/", $rec[$key]);
                                $arr = explode("/",$repl);

                                $arr[0] = intval($arr[0]);
                                $arr[1] = intval($arr[1]);
                                $arr[2] = intval($arr[2]);

                                if($setting_val['type']=="wareki_date"){
                                    $arr[0] = $this->_seireki2warekiYear($repl);
                                }

                                $font_name = $setting_val['font_name']?$setting_val['font_name']:"msgothicp";
                                $font_size = $setting_val['font_size']?$setting_val['font_size']:null;
                                $border =  $setting_val['border']?$setting_val['border']:0;
                                for($i=0;$i<3;$i++){
                                    $str = $arr[$i];
                                    $x = $setting_val['x'.($i+1)]?$setting_val['x'.($i+1)]:0;
                                    $y = $setting_val['y'.($i+1)]?$setting_val['y'.($i+1)]:0;
                                    $w = $setting_val['w'.($i+1)]?$setting_val['w'.($i+1)]:0;
                                    $align = $setting_val['align'.($i+1)]?$setting_val['align'.($i+1)]:"L";
                                    $fontStyle = $setting_val['font_style'.($i+1)]?$setting_val['font_style'.($i+1)]:'';
                                    $this->_write($str, $x, $y + $rec['offset_y'], $w ,0, $align, $font_name , $font_size ,$fontStyle, $border );
                                }

                                break;

                            case 'cur_page_count' :
                            case 'print_page_count' :
                            case 'all_page_count' :
                                if($setting_val['type']=="cur_page_count"){
                                    $str = $_cur_page_count;
                                }elseif($setting_val['type']=="print_page_count"){
                                    // $str = $this->pageCount;
                                    // 2019/02/20
                                    $str = $setting_val['page_prefix'].$this->pageCount;
                                }else{
                                    $str = $_all_page_count;
                                }
                                $x = $setting_val['x']?$setting_val['x']:0;
                                $y = $setting_val['y']?$setting_val['y']:0;
                                $w = $setting_val['w']?$setting_val['w']:0;
                                $align = $setting_val['align']?$setting_val['align']:"L";
                                $font_name = $setting_val['font_name']?$setting_val['font_name']:"msgothicp";
                                $font_size = $setting_val['font_size']?$setting_val['font_size']:null;
                                $underline =  $setting_val['underline']?$setting_val['underline']:"";
                                $border =  $setting_val['border']?$setting_val['border']:0;
                                $fontStyle =  $setting_val['font_style']?$setting_val['font_style']:"";
                                $this->_write($str, $x, $y + $rec['offset_y'], $w ,0, $align, $font_name , $font_size , $fontStyle, $underline, $border );

                                break;

                            case 'textarea' :
                                $str = $rec[$key];
                                $x = $setting_val['x']?$setting_val['x']:0;
                                $y = $setting_val['y']?$setting_val['y']:0;
                                $w = $setting_val['w']?$setting_val['w']:0;
                                $h = $setting_val['h']?$setting_val['h']:0;
                                $align = $setting_val['align']?$setting_val['align']:"L";
                                $valign = $setting_val['valign']?$setting_val['valign']:"T"; //2017/10/17 Add
                                $maxh = $setting_val['maxh']?$setting_val['maxh']:0; //2017/10/17 Add
                                $font_name = $setting_val['font_name']?$setting_val['font_name']:"msgothicp";
                                $font_size = $setting_val['font_size']?$setting_val['font_size']:null;
                                $underline =  $setting_val['underline']?$setting_val['underline']:"";
                                $border =  $setting_val['border']?$setting_val['border']:0;
                                $fontStyle =  $setting_val['font_style']?$setting_val['font_style']:"";
                                //$this->_write($str, $x, $y + $rec['offset_y'], $w ,$h, $align, $font_name , $font_size , $fontStyle, $underline, $border );
                                //2017/10/17 Mod
                                // $this->setCellHeightRatio(0.9); //[K-Cre]行間設定 2019/02/19 Add
                                $this->setCellHeightRatio(1.0); //[K-Cre]行間設定 2019/02/19 Add
                                $this->_write($str, $x, $y + $rec['offset_y'], $w ,$h, $align, $font_name , $font_size , $fontStyle, $underline, $border, $maxh, $valign );
                                $this->setCellHeightRatio(K_CELL_HEIGHT_RATIO); //[K-Cre]行間戻し 2019/02/19 Add

                                break;

                            case 'image' :
                                $str = $rec[$key];
                                $x = $setting_val['x']?$setting_val['x']:0;
                                $y = $setting_val['y']?$setting_val['y']:0;
                                $w = $setting_val['w']?$setting_val['w']:0;
                                $h = $setting_val['h']?$setting_val['h']:0;
                                $resize = $setting_val['resize']?$setting_val['resize']:false;
                                $this->Image($str,$x, $y + $rec['offset_y'], $w ,$h, "", "" , "" , $resize );

                                break;

                            case 'customer_barcode' : //郵便用カスタマバーコード
                                $str = $rec[$key];
                                $x = $setting_val['x']?$setting_val['x']:0;
                                $y = $setting_val['y']?$setting_val['y']:0;
                                $this->_customerBarcode($x, $y + $rec['offset_y'], $str );

                                break;

                            case "barcode_code39" :
                            case "barcode_code39+" :

                                $str = $rec[$key];
                                $x = $setting_val['x']?$setting_val['x']:0;
                                $y = $setting_val['y']?$setting_val['y']:0;
                                $w = $setting_val['w']?$setting_val['w']:0;
                                $h = $setting_val['h']?$setting_val['h']:0;
                                $style = array(
                                    'position' => 'S',
                                    'border' => false,
                                    'padding' => 4,
                                    'fgcolor' => array(0,0,0), //0～255三元色
                                    'bgcolor' => false,
                                    'text' => true, //下に値を出す
                                    'font' => 'msgothic',
                                    'fontsize' => 8,
                                    'stretchtext' => 4
                                );

                                if($setting_val['type']=="barcode_code39") $bc_type="C39";
                                if($setting_val['type']=="barcode_code39+") $bc_type="C39+";
                                $this->write1DBarcode($str, $bc_type, $x, $y + $rec['offset_y'], $w, $h, 0.4, $style, 'N');

                                break;

                            case "barcode_ean128" :

                                $str = $rec[$key];
                                $x = $setting_val['x']?$setting_val['x']:0;
                                $y = $setting_val['y']?$setting_val['y']:0;
                                $w = $setting_val['w']?$setting_val['w']:0;
                                $h = $setting_val['h']?$setting_val['h']:0;
                                $font_name = $setting_val['font_name']?$setting_val['font_name']:"msgothicp";
                                $font_size = $setting_val['font_size']?$setting_val['font_size']:8;
                                $style = array(
                                    'position' => 'S',
                                    'border' => false,
                                    'padding' => 4,
                                    'fgcolor' => array(0,0,0), //0～255三元色
                                    'bgcolor' => false,
                                    'text' => false, //下に値を出す
                                    'font' => $font_name,
                                    'fontsize' => $font_size,
                                    'stretchtext' => 4
                                );

                                //$bc_type="C128";
                                //$this->write1DBarcode($str, $bc_type, $x, $y + $rec['offset_y'], $w, $h, 0.4, $style, 'N');
                                //2019/05/18 これでGS1-128になるか？
                                $bc_type="C128C";
                                $str = chr(241).$str;
                                $this->write1DBarcode($str, $bc_type, $x, $y + $rec['offset_y'], $w, $h, 0.4, $style, 'N');

                                break;

                            case "qrcode" :

                                $str = $rec[$key];
                                $x = $setting_val['x']?$setting_val['x']:0;
                                $y = $setting_val['y']?$setting_val['y']:0;
                                $w = $setting_val['w']?$setting_val['w']:0;
                                $h = $setting_val['h']?$setting_val['h']:0;
                                $font_name = $setting_val['font_name']?$setting_val['font_name']:"msgothicp";
                                $font_size = $setting_val['font_size']?$setting_val['font_size']:8;
                                $style = array(
                                    'position' => 'S',
                                    'border' => false,
                                    'padding' => 0,
                                    'fgcolor' => array(0,0,0), //0～255三元色
                                    'bgcolor' => false,
                                    'text' => false, //下に値を出す
                                    'font' => $font_name,
                                    'fontsize' => $font_size,
                                    'stretchtext' => 4
                                );
                                $bc_type="QRCODE,".$setting_val['qrtype']."";
                                $this->write2DBarcode($str, $bc_type, $x, $y + $rec['offset_y'], $w, $h, $style, 'N');

                                break;

                            default : // "normal"
                                $str = $rec[$key];
                                $x = $setting_val['x']?$setting_val['x']:0;
                                $y = $setting_val['y']?$setting_val['y']:0;
                                $w = $setting_val['w']?$setting_val['w']:0;
                                $align = $setting_val['align']?$setting_val['align']:"L";
                                $font_name = $setting_val['font_name']?$setting_val['font_name']:"msgothicp";
                                $font_size = $setting_val['font_size']?$setting_val['font_size']:null;
                                $underline =  $setting_val['underline']?$setting_val['underline']:"";
                                $border =  $setting_val['border']?$setting_val['border']:0;
                                $fontStyle =  $setting_val['font_style']?$setting_val['font_style']:"";

                                if(substr($setting_val['color'],0,1)=="#" && strlen($setting_val['color'])==7){
                                    $color = $this->Hex2RGB($setting_val['color']);
                                }else{
                                    $color = array(0, 0, 0);
                                }
                                $this->SetTextColor($color[0],$color[1],$color[2]);

                                $this->_write($str, $x, $y + $rec['offset_y'], $w ,0, $align, $font_name , $font_size , $fontStyle, $underline, $border );

                                break;

                        }
                    }
                }
            }
        }
    }

    // private function _write($str, $x, $y, $w = 0, $h = 0, $align="L", $fontName = 'msgothicp', $fontSize = null, $fontStyle='', $underline = "", $border = 0) {
    //2017/10/17 Mod
    private function _write($str, $x, $y, $w = 0, $h = 0, $align="L", $fontName = 'msgothicp', $fontSize = null, $fontStyle='', $underline = "", $border = 0, $maxh=0, $valign='T') {
        if($str === null) {
            $str = "";
        }
        if($fontSize == null) {
            $fontSize = $this->getFontSize();
        }

        if($border == "") {
            $border = 0;
        }

        if($border == -1){
            $border = 0;
        }elseif($this->config['SETTING']['debug']){
            $border = 1;
        }

        //$h = 0;
        //kozgopromedium
        //$this->SetFont('cid0jp', '', $fontSize);

        if($fontName!="msgothic"){
            //msgothic以外ならフォントを埋め込み指示を出す
            $this->setFontSubsetting(false);
        }
        $this->SetFont($fontName, $fontStyle, $fontSize);

        $strLng = $this->GetStringWidth($str);
        $now_font_size = $fontSize;

        if($h == 0){
            if($w > 0){
                while($strLng > $w){
                    if(( $now_font_size - 1) > 0){
                        $now_font_size = $now_font_size - 1;
                        $this->SetFontSize($now_font_size);
                    }else{
                        $str = mb_substr($str,0,mb_strlen($str)-1);
                    }

                    $strLng = $this->GetStringWidth($str);
                }
            }
        }

        $fill = 0;
        $link = '';

        //denug時は赤文字に
        if($this->config['SETTING']['debug']){
            $this->SetTextColor(255,0,0);
        }

        if($h == 0){
            $this->SetXY($x, $y);
            $this->Cell($w, $h, $str, $border, $ln, $align, $fill, $link);
        }else{
            // $this->MultiCell($w, $h, $str, $border, $align, $fill, 1, $x, $y);
            //2017/10/17 Mod
            $stretch = 0; //テキストの伸縮(ストレッチ)モード 0 = なし、1 = 必要に応じて水平伸縮、2 = 水平伸縮、3 = 必要に応じてスペース埋め、4 = スペース埋め
            $ishtml = false;
            if($ishtml){
                $str = htmlspecialchars($str);
                $str = str_replace(" ", "&nbsp;", $str);
                $str = str_replace("　", "&nbsp;&nbsp;", $str);
                $str = nl2br($str);
            }
            $autopadding = true;
            $this->MultiCell($w, $h, $str, $border, $align, $fill, 1, $x, $y, true, $stretch, $ishtml, $autopadding, $maxh, $valign);
        }

        if($underline==1){
            $line_y = $this->GetStringHeight($w,$str) + $y;
            $line_x2 = $this->GetStringWidth($str) + $x + 5;
            $this->Line($x, $line_y, $line_x2, $line_y);
        }
    }

    //和暦変換用の関数
    // 引数：19910202 or 1991/02/02 or 1991-02-02 の形式のみ変換します。
    //変換できれば："昭和 43" といったような元号付き和年を返します
    //変換でなければ：西暦年をそのまま返します
    private function _seireki2warekiYear($ymd){
        if(strlen($ymd)==8){
            $y = substr($ymd,0,4);
            $m = substr($ymd,4,2);
            $d = substr($ymd,6,2);
        }elseif(strlen($ymd)==10){
            $y = substr($ymd,0,4);
            $m = substr($ymd,5,2);
            $d = substr($ymd,8,2);
        }else{
            return "";
        }

        $ymd = sprintf("%04d%02d%02d", $y, $m, $d);
        if ($ymd <= "19120729") {
            $gg = "明治";
            $yy = $y - 1867;
        } elseif ($ymd >= "19120730" && $ymd <= "19261224") {
            $gg = "大正";
            $yy = $y - 1911;
        } elseif ($ymd >= "19261225" && $ymd <= "19890107") {
            $gg = "昭和";
            $yy = $y - 1925;
        } elseif ($ymd >= "19890108" && $ymd <= "20190430") {
            $gg = "平成";
            $yy = $y - 1988;
        } elseif ($ymd >= "20190501") {
            $gg = "令和";
            $yy = $y - 2018;
        }else{
            $gg = "";
            $yy = $y;
        }

        if($yy==1){
            $yy = "元";
        }
        $wareki = $gg." ".$yy;

        return $wareki;
    }

    public function Output($name='', $dest=''){
        //2019/05/25 Add ---- Start ---
        global $_PDF_OUTPUTED_TO_DISPLAY;
        if(strtoupper($dest)=="I"){
            //画面表示
            $_PDF_OUTPUTED_TO_DISPLAY = true;
        }
        //2019/05/25 Add ---- End ---

        if(strtoupper($dest)=="D"){
            $pdf_output = parent::Output($name,"S");

            header( "Pragma: public" );
            header( "Expires: 0 ");
            header( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );
            header( "Content-Transfer-Encoding: binary" );
            header( "Content-Type: application/octet-streams" );
            header( "Content-Disposition: attachment; filename=\"{$name}\"" );
            print $pdf_output;
            exit;
        }

        $moto_name = $name; //2018/08/30 Add

        $is_rfc2231 = false;

        // ブラウザを判定する
        $ua = $_SERVER['HTTP_USER_AGENT'];

        $browser = 'unknown';
        if (strstr($ua, 'MSIE') && !strstr($ua, 'Opera')) {
            $browser = 'msie';
        } elseif (strstr($ua, 'Opera')) {
            $browser = 'opera';
        } elseif (strstr($ua, 'Firefox')) {
            $browser = 'firefox';
        } elseif (strstr($ua, "Chrome")) {
            $browser = 'chrome';
        } elseif (strstr($ua, "Safari")) {
            $browser = 'safari';
        }

        if($dest!="F"){
            // 英数字だけかを判定する
            $ascii = mb_convert_encoding($name, "US-ASCII", "UTF-8");
            if ( $ascii == $name ) {
                $browser = 'ascii';
            }

            // ブラウザに応じた処理
            switch( $browser ){
                // urlencode する
                case 'ascii':
                    $name = rawurlencode($name);
                    break;

                // RFC2231形式を使用する
                case 'firefox':
                case 'chrome':
                case 'opera':
                    $name = "utf-8'ja'".rawurlencode($name);
                    $is_rfc2231 = true;
                    break;

                // UTF-8 のまま
                case 'safari':
                    break;

                // SJIS に変換する
                default:
                    //'msie'など
                    $name = mb_convert_encoding($name, "SJIS-WIN", "UTF-8");
                    break;

            }
        }

        parent::Output($name,$dest, _FLNM_THROUGH, $is_rfc2231 );

    }

    function _customerBarcode($x,$y,$cb)
    {
        $this->SetXY($x,$y);
        $this->cb = strtolower($cb);
        $this->code = $this->_calc($this->cb);
        $this->cd = $this->_checkDigit($this->code);
        $this->_barcode();
        $this->_draw();
    }

    // 入力コードを変換
    function _calc($cb)
    {
        if ($cb == false || !is_string($cb)) {
            return false;
        }

        $r = array();

        while(strlen($cb) > 0) {
            $c = substr($cb, 0, 1);
            $cb = substr($cb, 1);

            switch ($c) {
                case '0':
                    $r[] = '0';
                    break;
                case '1':
                    $r[] = '1';
                    break;
                case '2':
                    $r[] = '2';
                    break;
                case '3':
                    $r[] = '3';
                    break;
                case '4':
                    $r[] = '4';
                    break;
                case '5':
                    $r[] = '5';
                    break;
                case '6':
                    $r[] = '6';
                    break;
                case '7':
                    $r[] = '7';
                    break;
                case '8':
                    $r[] = '8';
                    break;
                case '9':
                    $r[] = '9';
                    break;
                case '-':
                    $r[] = '-';
                    break;
                case 'a':
                    $r[] = 'CC1';
                    $r[] = '0';
                    break;
                case 'b':
                    $r[] = 'CC1';
                    $r[] = '1';
                    break;
                case 'c':
                    $r[] = 'CC1';
                    $r[] = '2';
                    break;
                case 'd':
                    $r[] = 'CC1';
                    $r[] = '3';
                    break;
                case 'e':
                    $r[] = 'CC1';
                    $r[] = '4';
                    break;
                case 'f':
                    $r[] = 'CC1';
                    $r[] = '5';
                    break;
                case 'g':
                    $r[] = 'CC1';
                    $r[] = '6';
                    break;
                case 'h':
                    $r[] = 'CC1';
                    $r[] = '7';
                    break;
                case 'i':
                    $r[] = 'CC1';
                    $r[] = '8';
                    break;
                case 'j':
                    $r[] = 'CC1';
                    $r[] = '9';
                    break;
                case 'k':
                    $r[] = 'CC2';
                    $r[] = '0';
                    break;
                case 'l':
                    $r[] = 'CC2';
                    $r[] = '1';
                    break;
                case 'm':
                    $r[] = 'CC2';
                    $r[] = '2';
                    break;
                case 'n':
                    $r[] = 'CC2';
                    $r[] = '3';
                    break;
                case 'o':
                    $r[] = 'CC2';
                    $r[] = '4';
                    break;
                case 'p':
                    $r[] = 'CC2';
                    $r[] = '5';
                    break;
                case 'q':
                    $r[] = 'CC2';
                    $r[] = '6';
                    break;
                case 'r':
                    $r[] = 'CC2';
                    $r[] = '7';
                    break;
                case 's':
                    $r[] = 'CC2';
                    $r[] = '8';
                    break;
                case 't':
                    $r[] = 'CC2';
                    $r[] = '9';
                    break;
                case 'u':
                    $r[] = 'CC3';
                    $r[] = '0';
                    break;
                case 'v':
                    $r[] = 'CC3';
                    $r[] = '1';
                    break;
                case 'w':
                    $r[] = 'CC3';
                    $r[] = '2';
                    break;
                case 'x':
                    $r[] = 'CC3';
                    $r[] = '3';
                    break;
                case 'y':
                    $r[] = 'CC3';
                    $r[] = '4';
                    break;
                case 'z':
                    $r[] = 'CC3';
                    $r[] = '5';
                    break;
                default:
                    return false;
            }
        }

        if (_count($r) < 20) {
            do {
                $r[] = 'CC4';
            } while (_count($r) < 20);
        } else {
            while (_count($r) > 20) {
                array_pop($r);
            }
        }

        return $r;
    }

    // チェックディジットを計算
    function _checkDigit($cb)
    {
        if (_count($cb) != 20) {
            return false;
        }

        $i = 0;

        foreach ($cb as $c) {
            switch ($c) {
                case '0':
                    $i += 0;
                    break;
                case '1':
                    $i += 1;
                    break;
                case '2':
                    $i += 2;
                    break;
                case '3':
                    $i += 3;
                    break;
                case '4':
                    $i += 4;
                    break;
                case '5':
                    $i += 5;
                    break;
                case '6':
                    $i += 6;
                    break;
                case '7':
                    $i += 7;
                    break;
                case '8':
                    $i += 8;
                    break;
                case '9':
                    $i += 9;
                    break;
                case '-':
                    $i += 10;
                    break;
                case 'CC1':
                    $i += 11;
                    break;
                case 'CC2':
                    $i += 12;
                    break;
                case 'CC3':
                    $i += 13;
                    break;
                case 'CC4':
                    $i += 14;
                    break;
                case 'CC5':
                    $i += 15;
                    break;
                case 'CC6':
                    $i += 16;
                    break;
                case 'CC7':
                    $i += 17;
                    break;
                case 'CC8':
                    $i += 18;
                    break;
                default:
                    return false;
            }
        }

        $i = ($i == 0) ? 0 : 19 - $i % 19;
        switch (true) {
            case ($i >= 0 && $i <= 9):
                return '' . $i;
                break;
            case ($i == 10):
                return '-';
                break;
            case ($i == 11):
                return 'CC1';
                break;
            case ($i == 12):
                return 'CC2';
                break;
            case ($i == 13):
                return 'CC3';
                break;
            case ($i == 14):
                return 'CC4';
                break;
            case ($i == 15):
                return 'CC5';
                break;
            case ($i == 16):
                return 'CC6';
                break;
            case ($i == 17):
                return 'CC7';
                break;
            case ($i == 18):
                return 'CC8';
                break;
            default:
                return false;
        }
    }

    // バーの種類を特定
    function _barcode()
    {
        $t = array();
        $this->bar = array();

        $t[] = 'STC'; // start code
        $t = array_merge($t, $this->code);
        $t[] = $this->_checkDigit($this->code);
        $t[] = 'SPC'; // stop code

        foreach ($t as $val) {
            switch ($val) {
                case '1':
                    $this->bar[] = 1;
                    $this->bar[] = 1;
                    $this->bar[] = 4;
                    break;
                case '2':
                    $this->bar[] = 1;
                    $this->bar[] = 3;
                    $this->bar[] = 2;
                    break;
                case '3':
                    $this->bar[] = 3;
                    $this->bar[] = 1;
                    $this->bar[] = 2;
                    break;
                case '4':
                    $this->bar[] = 1;
                    $this->bar[] = 2;
                    $this->bar[] = 3;
                    break;
                case '5':
                    $this->bar[] = 1;
                    $this->bar[] = 4;
                    $this->bar[] = 1;
                    break;
                case '6':
                    $this->bar[] = 3;
                    $this->bar[] = 2;
                    $this->bar[] = 1;
                    break;
                case '7':
                    $this->bar[] = 2;
                    $this->bar[] = 1;
                    $this->bar[] = 3;
                    break;
                case '8':
                    $this->bar[] = 2;
                    $this->bar[] = 3;
                    $this->bar[] = 1;
                    break;
                case '9':
                    $this->bar[] = 4;
                    $this->bar[] = 1;
                    $this->bar[] = 1;
                    break;
                case '0':
                    $this->bar[] = 1;
                    $this->bar[] = 4;
                    $this->bar[] = 4;
                    break;
                case '-':
                    $this->bar[] = 4;
                    $this->bar[] = 1;
                    $this->bar[] = 4;
                    break;
                case 'CC1':
                    $this->bar[] = 3;
                    $this->bar[] = 2;
                    $this->bar[] = 4;
                    break;
                case 'CC2':
                    $this->bar[] = 3;
                    $this->bar[] = 4;
                    $this->bar[] = 2;
                    break;
                case 'CC3':
                    $this->bar[] = 2;
                    $this->bar[] = 3;
                    $this->bar[] = 4;
                    break;
                case 'CC4':
                    $this->bar[] = 4;
                    $this->bar[] = 3;
                    $this->bar[] = 2;
                    break;
                case 'CC5':
                    $this->bar[] = 2;
                    $this->bar[] = 4;
                    $this->bar[] = 3;
                    break;
                case 'CC6':
                    $this->bar[] = 4;
                    $this->bar[] = 2;
                    $this->bar[] = 3;
                    break;
                case 'CC7':
                    $this->bar[] = 4;
                    $this->bar[] = 4;
                    $this->bar[] = 1;
                    break;
                case 'CC8':
                    $this->bar[] = 1;
                    $this->bar[] = 1;
                    $this->bar[] = 1;
                    break;
                case 'STC':
                    $this->bar[] = 1;
                    $this->bar[] = 3;
                    break;
                case 'SPC':
                    $this->bar[] = 3;
                    $this->bar[] = 1;
                    break;
                default:
                    return false;
            }
        }
    }

    // 描画
    function _draw()
    {
        // Y軸の設定
        $y = array(
                $this->getY(), $this->getY() + 1.2, // Y
                1.2, 2.4, 3.6 // height
            );
        // X軸の設定
        $x = array(0.6, 0.6); //[0] width, [1] space

        foreach ($this->bar as $val) {
            switch ($val) {
                case 1:
                    $_y = $y[0];
                    $_h = $y[4];
                    break;
                case 2:
                    $_y = $y[0];
                    $_h = $y[3];
                    break;
                case 3:
                    $_y = $y[1];
                    $_h = $y[3];
                    break;
                case 4:
                    $_y = $y[1];
                    $_h = $y[2];
                    break;
                default:
                    return false;
            }

            $this->Rect($this->getX(), $_y, $x[0], $_h, "F", array(), array(0, 0, 0));
            $this->setX($this->getX() + $x[0] + $x[1]);
        }

        return true;
    }

    //2017/11/28 Add --------- Strat --------
    function Hex2RGB($color){
        $color = str_replace('#', '', $color);
        if (strlen($color) != 6){ return array(0,0,0); }
        $rgb = array();
        for ($x=0;$x<3;$x++){
            $rgb[$x] = hexdec(substr($color,(2*$x),2));
        }
        return $rgb;
    }
    //2017/11/28 Add --------- End --------

}


?>
