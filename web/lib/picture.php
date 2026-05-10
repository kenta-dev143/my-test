<?php

    // アップ画像サイズ(横幅)
    define("_UP_IMG_W", 1024);
    // アップ画像サイズ(高さ)
    define("_UP_IMG_H", 1024);

    //---------------------------------------------------------
    //ファイル名から拡張子を取得 : _get_extension
    //
    //     ※ファイル名からでなく、実ファイルパスから拡張子(ファイル種類)
    //       を得る場合は「_getImageData」を使用すること
    //
    //        引数：なし
    //        戻値：$_REQUEST
    //---------------------------------------------------------
    function _get_extension($filename)
    {
        preg_match('/([^\.]*?)\.([^\/\.]*)$/i', $filename, $matches);
        return  strtolower($matches[2]);
    }

    //---------------------------------------------------------
    // イメージファイルデータ取得 : _getImageData
    //        引数：ファイルパス
    //        戻値：情報配列
    //---------------------------------------------------------
    function _getImageData($_file){

        if( $_file == "none" || $_file == ""){
            return;
        }
        if( ! _file_exists($_file) ){
            return;
        }

        $_array_img_info = @ GetImageSize($_file);

        $_array_ret['width']  = $_array_img_info[0];
        $_array_ret['height'] = $_array_img_info[1];
        switch($_array_img_info[2]){
            case 1: $_array_ret['img_type'] = 'gif'; break;
            case 2: $_array_ret['img_type'] = 'jpg'; break;
            case 3: $_array_ret['img_type'] = 'png'; break;
            case 4: $_array_ret['img_type'] = 'swf'; break;
            case 5: $_array_ret['img_type'] = 'psd'; break;
            case 6: $_array_ret['img_type'] = 'bmp'; break;
            case 7: $_array_ret['img_type'] = 'tiff_ii'; break;
            case 8: $_array_ret['img_type'] = 'tiff_mm'; break;
            case 9: $_array_ret['img_type'] = 'jpc'; break;
            case 10: $_array_ret['img_type'] = 'jp2'; break;
            case 11: $_array_ret['img_type'] = 'jpx'; break;
            default: $_array_ret['img_type'] = 'unknown TypeNo=' . $_array_img_info[2]; break;
        }
        $_array_ret['mime'] = $_array_img_info['mime'];
        $_array_ret['tag'] = $_array_img_info[3];

        return($_array_ret);

    }

    // --------------------------------
    // 表示用IMGオプション作成
    // --------------------------------
    function _getDispImgOption($filepath,$dstW,$dstH){
        $ret = "";
        $imginfo = _getImageData($filepath);
        $srcW = $imginfo['width'];
        $srcH = $imginfo['height'];

        $ret = _ImgOptionCalc($srcW,$srcH,$dstW,$dstH);
        return $ret;
    }

    function _ImgOptionCalc($srcW,$srcH,$dstW,$dstH){
        if( $srcW > $dstW || $srcH > $dstH ){
            //幅高さどちらか一方でも表示サイズ超えていれば....

            if( $srcW > $srcH ){
                //高さより幅のほうが広い
                $ans = $srcH * $dstW / $srcW;
                if( $ans > $dstH ){
                    //幅を狭めても、その比率で狭めても高さがまだ超えてしまうので高さ調整
                    $ret = "height=" . $dstH;
                }else{
                    //幅を狭めれば表示エリアに入るので、幅調整
                    $ret = "width=" . $dstW;
                }
            }else{
                //幅より高さのほうが高い
                $ans = $srcW * $dstH / $srcH;
                if( $ans > $dstW ){
                    //高さを狭めても、その比率で狭めても幅がまだ超えてしまうので幅調整
                    $ret = "width=" . $dstW;
                }else{
                    //高さを狭めれば表示エリアに入るので、高さ調整
                    $ret = "height=" . $dstH;
                }
            }
        }

        return $ret;
    }

    // --------------------------------
    // 画像ファイルを指定した幅高内に収める縮小処理
    // --------------------------------
    function _imgSizeConert($src_file_path,$dst_file_path,$dstW,$dstH,$extension = null){

        $imginfo = _getImageData($src_file_path);
        $srcW = $imginfo['width'];
        $srcH = $imginfo['height'];

        $pic = new clsPicture($src_file_path);
        if($srcW > $dstW || $srcH > $dstH){
            // 保存サイズを超過した場合
            if($srcW > $srcH){
                //高さより幅のほうが広い
                $ans = $srcH * $dstW / $srcW;
                if( $ans > $dstH ){
                    //幅を狭めても、その比率で狭めても高さがまだ超えてしまうので高さ調整
                    $pic->h($dstH,_PICTURE_MAX);
                }else{
                    //幅を狭めれば表示エリアに入るので、幅調整
                    $pic->w($dstW,_PICTURE_MAX);
                }
            }else{

                //幅より高さのほうが高い
                $ans = $srcW * $dstH / $srcH;
                if( $ans > $dstW ){
                    //高さを狭めても、その比率で狭めても幅がまだ超えてしまうので幅調整
                    $pic->w($dstW,_PICTURE_MAX);
                }else{
                    //高さを狭めれば表示エリアに入るので、高さ調整
                    $pic->h($dstH,_PICTURE_MAX);
                }
            }
        }

        if($extension==null){
            $pic->save($dst_file_path);
        }else{
            $pic->convert($extension,$dst_file_path);
        }
    }

    // --------------------------------
    // 2014/06/11 Add 画像ファイルを指定した幅高内に収める縮小処理(※いずれか一方ははみ出してもよいバージョン：保存用ファイル作成のための関数)
    // --------------------------------
    function _imgSizeConertForSave($src_file_path,$dst_file_path,$dstW,$dstH,$extension = null){

        $imginfo = _getImageData($src_file_path);
        $srcW = $imginfo['width'];
        $srcH = $imginfo['height'];

        $pic = new clsPicture($src_file_path);

        if($srcW > $dstW && $srcH > $dstH){
            //幅も高さも超えている場合のみ処理

            //横幅を希望の幅に縮めた場合の高さ計算
            $ans = $srcH * $dstW / $srcW;
            if( $ans > $dstH ){
                //幅を狭めたら、その比率での新しい高さがまだ超えてしまうが、横は縮まるのでOK
                $pic->w($dstW,_PICTURE_MAX);
            }else{
                //幅を狭めれば高さが希望の高さより低くなるので高さを縮める
                $pic->h($dstH,_PICTURE_MAX);
            }
            if($extension==null){
                $pic->save($dst_file_path);
            }else{
                $pic->convert($extension,$dst_file_path);
            }
        }else{
            //幅or高さのいずれかが、範囲内であれば、そのままコピー
            copy($src_file_path, $dst_file_path);
        }

    }


    // ************************************************************************
    // 指定したサイズの正方形のサムネイル画像を作成する関数
    // ************************************************************************
    function _squareThumbMake($orgFile, $saveFile, $new_pic_width, $new_pic_height){

        // 画像のピクセルサイズ情報を取得
        $imginfo = getimagesize( $orgFile );

        switch($imginfo[2]){
        case 1:
            $ImageResource = @ImageCreateFromGIF($orgFile);
            break;
        case 2:
            $ImageResource = @ImageCreateFromJPEG($orgFile);
            break;
        case 3:
            $ImageResource = @ImageCreateFromPNG($orgFile);
            break;
        }

        // イメージリソースから、横、縦ピクセルサイズ取得
        $width  = imagesx( $ImageResource );    // 横幅
        $height = imagesy( $ImageResource );    // 縦幅

        if ( $width >= $height ) {
            // 横長の画像の時
            $side = $height;
            $x = floor( ( $width - $height ) / 2 );
            $y = 0;
            $width = $side;
        } else {
            // 縦長の画像の時
            $side = $width;
            $y = floor( ( $height - $width ) / 2 );
            $x = 0;
            $height = $side;
        }


        switch ( $imginfo[2] ) {

            // jpeg
            case 2:

                // 出力ピクセルサイズで新規画像作成
                $square_width  = $new_pic_width;
                $square_height = $new_pic_height;
                $square_new = imagecreatetruecolor( $square_width, $square_height );
                imagecopyresized( $square_new, $ImageResource, 0, 0, $x, $y, $square_width, $square_height, $width, $height );
                imagejpeg($square_new, $saveFile, 100);
                break;

            // gif
            case 1:

                // 出力ピクセルサイズで新規画像作成
                $square_width  = $new_pic_width;
                $square_height = $new_pic_height;
                $square_new = imagecreatetruecolor( $square_width, $square_height );
                imagecopyresampled($square_new, $ImageResource, 0, 0, $x, $y, $square_width, $square_height, $width, $height);
                imagegif($square_new, $saveFile, 100);
                break;

            // png
            case 3:

                // 出力ピクセルサイズで新規画像作成
                $square_width  = $new_pic_width;
                $square_height = $new_pic_height;
                $square_new = imagecreatetruecolor( $square_width, $square_height );
                imagealphablending($square_new, false);        // アルファブレンディングを無効
                imageSaveAlpha($square_new, true);             // アルファチャンネルを有効
                $transparent = imagecolorallocatealpha($square_new, 0, 0, 0, 127); // 透明度を持つ色を作成
                imagefill($square_new, 0, 0, $transparent);    // 塗りつぶす
                imagecopyresampled($square_new, $ImageResource, 0, 0, $x, $y, $square_width, $square_height, $width, $height);
                imagepng($square_new, $saveFile);
                break;

            // デフォルト
            Default:
                break;
        }        
    }

    //***************************************************************************************
    // clsPictureクラス
    //***************************************************************************************
    define("_PICTURE_NORMAL", 0);
    define("_PICTURE_MAX", 1);
    define("_PICTURE_MIN", 2);

    class clsPicture
    {
        var $im = NULL;
        var $im_dst = NULL;
        var $params = array();
        var $header = '';

        function clsPicture($file = NULL)
        {
            $this->params['quality'] = 100;
            $this->params['ncolors'] = 256;
            if($file)
                $this->load($file);
        }

        function set($data)
        {
            $this->im = imagecreatefromstring($data);
            if($this->im)
            {
                $this->params['w'] = ImageSX($this->im);
                $this->params['h'] = ImageSY($this->im);

                return TRUE;
            }
            return    FALSE;
        }

        function load($file)
        {
            $this->header = '';
            $param['extension'] = _get_extension($file);

            $flText = $this->fileLoad($file);
            $this->im = @ImageCreateFromString($flText);
            if($this->im && !$param['extension'])
            {
                $this->param['type'] = @exif_imagetype($file);
            }
            if(!$this->im)
                switch(strtolower($this->params['extension']))
                {
                case 'gif':
                    $this->im = @ImageCreateFromGIF($file);
                    break;
                case 'jpg':
                case 'jpeg':
                    $this->im = @ImageCreateFromJPEG($file);
                    break;
                case 'png':
                    $this->im = @ImageCreateFromPNG($file);
                    break;
                case 'wbmp':
                    $this->im = @ImageCreateFromWBMP($file);
                    break;
                }
            if(!$this->im)    $this->im = @ImageCreateFromJPEG($file);
            if(!$this->im)    $this->im = @ImageCreateFromGIF($file);
            if(!$this->im)    $this->im = @ImageCreateFromWBMP($file);
            if(!$this->im)    $this->im = @ImageCreateFromPNG($file);
            if($this->im)
            {
                $this->params['w'] = ImageSX($this->im);
                $this->params['h'] = ImageSY($this->im);

                return TRUE;
            }
            return    FALSE;
        }

        function fileLoad($filename)
        {

            if(!is_file($filename))
            {
                return FALSE;
            }

            $filetext = '';
            //ファイルを開く.
            $count = 3;
            while($count>=0)
            {
                $fh = @fopen($filename, 'r');    //読取専用でオープン.
                if(!$fh)
                {
                    if(($count--)<=1)
                    {
                        return    FALSE;    //失敗.
                    }
                    sleep(1);
                }
                else
                {
                    @flock($fh, LOCK_SH);        //読み込みロック.
                    @fseek($fh, SEEK_SET, 0);    //ファイルポインターを最前列に.
                    while (!feof($fh))
                    {
                        $filetext .= fread($fh, 4096);
                    }
                    @flock($fh, LOCK_UN);        //アンロック.
                    fclose($fh);        //ファイルを閉じる.
                    $count = -1;
                }
            }

            return    $filetext;
        }

        function w($w = NULL, $flag = _PICTURE_NORMAL)
        {
            if(!is_null($w))
            {
                switch($flag)
                {
                case _PICTURE_MAX:
                    if($this->params['w'] < $w)
                        $this->params['wt'] = $this->params['w'];
                    else
                        $this->params['wt'] = $w;
                    break;

                case _PICTURE_MIN:
                    if($w < $this->params['w'])
                        $this->params['wt'] = $this->params['w'];
                    else
                        $this->params['wt'] = $w;
                    break;

                default:
                    $this->params['wt'] = $w;
                }
            }

            return $this->params['w'];
        }

        function quality($q = NULL)
        {
            $qt = $this->params['quality'];
            if(!is_null($q))
                $this->params['quality'] = $q;

            return $qt;
        }

        function h($h = NULL, $flag = _PICTURE_NORMAL)
        {
            if(!is_null($h))
            {
                switch($flag)
                {
                case _PICTURE_MAX:
                    if($this->params['h'] < $h)
                        $this->params['ht'] = $this->params['h'];
                    else
                        $this->params['ht'] = $h;
                    break;

                case _PICTURE_MIN:
                    if($h < $this->params['h'])
                        $this->params['ht'] = $this->params['h'];
                    else
                        $this->params['ht'] = $h;
                    break;

                default:
                    $this->params['ht'] = $h;
                }
            }

            return $this->params['h'];
        }


        function save($file)
        {
            $kind = _get_extension($file);
            if(!$kind)
            {
                switch($this->param['type'])
                {
                case IMAGETYPE_GIF:
                    $kind = 'gif';
                    break;
                case IMAGETYPE_JPEG:
                    $kind = 'jpeg';
                    break;
                case IMAGETYPE_PNG:
                    $kind = 'png';
                    break;
                }
            }

            $this->convert($kind, $file);
        }

        function convert($kind, $file = '')
        {
            $sw = $this->params['w'];
            $sh = $this->params['h'];
            $dw = $sw;
            $dh = $sh;
            if($this->params['wt'] != 0 && $this->params['ht'] == 0)
            {
                $dw = $this->params['wt'];
                $dh = (int)($sh * $this->params['wt'] / $sw);
            }
            else if($this->params['wt'] == 0 && $this->params['ht'] != 0)
            {
                $dh = $this->params['ht'];
                $dw = (int)($sw * $this->params['ht'] / $sh);
            }
            else if($this->params['wt'] != 0 && $this->params['ht'] != 0)
            {
                $dh = $this->params['ht'];
                $dw = $this->params['wt'];
            }
            set_time_limit(3600);
            switch(strtolower($kind))
            {
            case 'gif':
                if(imagetypes() & IMG_GIF)
                {
                    $this->im_dst = @ImageCreateTrueColor($dw, $dh);

                    //2015/08/07 Add 透過GIFの場合の処理
                    $this->transmissionGifProc($this->im,$this->im_dst,$sw,$sh);

                    # -------------------- 2010/12/16 mod -----------------------------------
#                     @imagecopyresized($this->im_dst, $this->im, 0, 0, 0, 0, $dw, $dh, $sw, $sh);
                    if(function_exists("imagecopyresampled")){
                        @imagecopyresampled($this->im_dst, $this->im, 0, 0, 0, 0, $dw, $dh, $sw, $sh);
                    } else {
                        @imagecopyresized($this->im_dst, $this->im, 0, 0, 0, 0, $dw, $dh, $sw, $sh);
                    }
                    # -------------------- 2010/12/16 mod -----------------------------------
                    @ImageTrueColorToPalette($this->im_dst, TRUE, $this->params['ncolors']);
                    $this->header = $this->header($kind);
                    if(!$file)
                    {
                        header($this->header());
                        @imageGIF($this->im_dst);
                    }
                    else
                        @imageGIF($this->im_dst, $file);
                }
                break;

            case 'jpg':
            case 'jpeg':
                if(imagetypes() & IMG_JPG)
                {
                    $this->im_dst = @ImageCreateTrueColor($dw, $dh);
                    # -------------------- 2010/12/16 mod -----------------------------------
#                     @imagecopyresized($this->im_dst, $this->im, 0, 0, 0, 0, $dw, $dh, $sw, $sh);
                    if(function_exists("imagecopyresampled")){
                        @imagecopyresampled($this->im_dst, $this->im, 0, 0, 0, 0, $dw, $dh, $sw, $sh);
                    } else {
                        @imagecopyresized($this->im_dst, $this->im, 0, 0, 0, 0, $dw, $dh, $sw, $sh);
                    }
                    # -------------------- 2010/12/16 mod -----------------------------------
                    $this->header = $this->header($kind);
                    if(!$file)
                    {
                        header($this->header());
                        @ImageJPEG($this->im_dst, '', $this->params['quality']);
                    }
                    else
                        @ImageJPEG($this->im_dst, $file, $this->params['quality']);
                }
                break;

            case 'png':
                if(imagetypes() & IMG_PNG)
                {
                    //$this->im_dst = @ImageCreate($dw, $dh);
                    //2016/05/31 Mod
                    $this->im_dst = @ImageCreateTrueColor($dw, $dh);
                    
                    //2017/09/04 Mod ------- Before --------
                    //$black = ImageColorAllocateAlpha($this->im_dst, 255, 255, 255, 127);
                    //2017/09/04 Mod ------- After --------
                    //ブレンドモードを無効にする
                    imagealphablending($this->im_dst, false);
                    //完全なアルファチャネル情報を保存するフラグをonにする
                    imagesavealpha($this->im_dst, true);
                    //2017/09/04 Mod ------- End --------

                    # -------------------- 2010/12/16 mod -----------------------------------
#                     @imagecopyresized($this->im_dst, $this->im, 0, 0, 0, 0, $dw, $dh, $sw, $sh);
                    if(function_exists("imagecopyresampled")){
                        @imagecopyresampled($this->im_dst, $this->im, 0, 0, 0, 0, $dw, $dh, $sw, $sh);
                    } else {
                        @imagecopyresized($this->im_dst, $this->im, 0, 0, 0, 0, $dw, $dh, $sw, $sh);
                    }
                    # -------------------- 2010/12/16 mod -----------------------------------

                    //2017/09/04 Del
                    //@imagetruecolortopalette($this->im_dst, TRUE, $this->params['ncolors']);

                    $this->header = $this->header($kind);
                    if(!$file)
                    {
                        header($this->header());
                        //ImagePNG($this->im_dst);
                        //2016/05/31 Mod
                        ImagePNG($this->im_dst,null,0);
                    }
                    else{
                        //ImagePNG($this->im_dst, $file);
                        //2016/05/31 Mod
                        ImagePNG($this->im_dst, $file,0);
                    }
                }
                break;

            }
        }

        //2015/08/07 Add --------- Start ---------
        //透過GIFの場合の処理
        function transmissionGifProc(&$src,&$dst,$srcW,$srcH){

            for($sx=0; $sx<$srcW; $sx++) {
              for($sy=0; $sy<$srcH; $sy++) {
                $rgb = imagecolorat($src, $sx, $sy);
                $idx = imagecolorsforindex($src, $rgb);
                if($idx["alpha"] !== 0){
                  $tp = $idx;
                  break;
                }
              }
              if(!isset($tp) || $tp!== null) break;
            }
            // 透過GIF
            if(isset($tp) && is_array($tp)){
              // 画像で使用する色を透過度を指定して作成
              $bgcolor = imagecolorallocatealpha($dst,
                                                  $tp["red"],
                                                  $tp["green"],
                                                  $tp["blue"],
                                                  $tp["alpha"]);

              // 塗り潰す
              imagefill($dst, 0, 0, $bgcolor);
              // 透明色を定義
              imagecolortransparent($dst,$bgcolor);

              return true;
            }else{
                // 透過GIFではない
                return false;
            }
        }
        //2015/08/07 Add --------- End ---------

        function header($kind = '')
        {
            if($kind)
                switch(strtolower($kind))
                {
                case 'gif':
                    return    'Content-type: image/gif';
                case 'jpg':
                case 'jpeg':
                    return    'Content-type: image/jpeg';
                case 'png':
                    return    'Content-type: image/png';
                }

            return $this->header;
        }
    }

    //---------------------------------------------------------
    //画像表示 : _disp_img
    //
    //        引数：$_id          対象管理者ID
    //        　　  $_filename    ファイル名の形式
    //        　　　$_disp_w      画像サイズの幅
    //        　　　$_disp_h      画像サイズの高さ
    //        戻値：ファイルデータ['file_name']
    //        　　　　　　　　　　['img_opt']
    //---------------------------------------------------------
    function _disp_img($_dir_id, $_tagname, &$_sess, $_disp_w, $_disp_h ){
        $_filename = $_sess[$_tagname];
        //イメージのフォルダIDを返す
        $_sess[$_tagname . '_id'] = $_dir_id;

        if($_dir_id==""){
            $_dir_id = "new_tmp";
        }

        // 格納先ディレクトリ
        $_fil_dir = _SYSTEM_ROOT_DIR . '/upfile/' . $_dir_id . '/';

        // 格納先ディレクトリが無い場合は、作成する
        @mkdir($_fil_dir, 0777);
        // 既にディレクトリがあるが権限が777以外の場合用(?)
        @chmod($_fil_dir, 0777); //2009/10/07 Add

        if( _is_file($_fil_dir . $_filename) ){
            // ファイル有り
            $_sess[$_tagname] = $_filename;
        }else{
            // ファイル無し
            return;
        }

        // ファイルサイズを取得
        $_sess[$_tagname.'_size'] = ""; //2007/07/02 Add
        $ret = _getDispImgOption($_fil_dir . $_sess[$_tagname], $_disp_w, $_disp_h);
        if($ret != ""){
            $_sess[$_tagname.'_size'] = $ret;
        }
        return;
    }

    //---------------------------------------------------------
    //画像表示 : _disp_confirmimg
    //
    //        引数：$_id          対象管理者ID
    //        　　  $_filename    ファイル名
    //        　　　$_tagname     fileタグ名
    //        　　　$_del_flg     削除フラグ
    //        　　　$_disp_w      画像サイズの幅
    //        　　　$_disp_h      画像サイズの高さ
    //           $_save_w      実際に保存するサイズの幅
    //           $_save_h      実際に保存するサイズの高さ
    //           $_need_chk_str  必須チェックさせたい場合にエラーメッセージに使う項目名を指定する
    //        戻値：ファイル情報
    //---------------------------------------------------------
    //function _disp_confirmimg($_id, $_filename, $_tagname, $_del_flg = "", $_disp_w=_UP_IMG_W, $_disp_h=_UP_IMG_H){
    //function _disp_confirmimg($_mode, $_dir_id, $_tagname, &$_sess, $_disp_w=_UP_IMG_W, $_disp_h=_UP_IMG_H){
    //2014/06/11 Mod
    //function _disp_confirmimg($_mode, $_dir_id, $_tagname, &$_sess, $_disp_w=_UP_IMG_W, $_disp_h=_UP_IMG_H, $_save_w=_UP_IMG_W, $_save_h=_UP_IMG_H){
    //2014/07/28 Mod
    function _disp_confirmimg($_mode, $_dir_id, $_tagname, &$_sess, $_disp_w=_UP_IMG_W, $_disp_h=_UP_IMG_H, $_save_w=_UP_IMG_W, $_save_h=_UP_IMG_H,$_need_chk_str=""){
        //2014/07/28 Add ---------- Start ----------
        global $_conf_msg;
        global $_lang;
        if($_lang==''){ $_lang='J'; }
        //2014/07/28 Add ---------- End ----------


        $_filename = $_sess[$_tagname];
        $_del_flg = $_sess[$_tagname . "_del"];

        $_ret_err_msg = array();
        //イメージのフォルダIDを返す
        $_sess[$_tagname . "_id"] = $_dir_id;

        if($_dir_id==""){
            $_dir_id = "new_tmp";
        }

        // 格納先ディレクトリ
        $_fil_dir = _SYSTEM_ROOT_DIR . '/upfile/' . $_dir_id . '/';

        @mkdir($_fil_dir,0777);
        @chmod($_fil_dir, 0777); //2009/10/07 Add

        if(is_uploaded_file($_FILES[$_tagname]['tmp_name']) ){
            // 削除表示はしない
            $_sess[$_tagname . "_del"] = "";

            if($_mode != "delete"){
                // 拡張子の取得
                $extension = '.'._get_extension($_FILES[$_tagname]['name']);
                if($extension==".jpeg") $extension = ".jpg"; //2015/06/09 Add
                $extension = strtolower($extension); //2015/06/09 Add

                // 拡張子チェック
                if(strtolower($extension)==".gif" || strtolower($extension)==".png" || strtolower($extension)==".jpg"){ //2014/06/27 png追加
                    // UPされたファイルをシステムに見れる場所にコピーする
                    $_uptmp_path = $_fil_dir . rand() . $extension;
                    move_uploaded_file($_FILES[$_tagname]['tmp_name'], $_uptmp_path );

                    //2015/06/09 Add ---------- Start ----------
                    if(strtolower($extension)==".jpg"){
                        _OrientationTopLeft($_uptmp_path);
                    }
                    //2015/06/09 Add ---------- End ----------


                    // コンバートファイル作成
                    $_tmp_path = $_fil_dir . rand() . $extension;

                    //if($extension == ".gif" || $extension == ".png" ){ //2014/06/27 png追加
                    //    // コピー
                    //    copy($_uptmp_path, $_tmp_path);
                    //}else{
                    //    // コンバート
                    //    //_imgSizeConert($_uptmp_path, $_tmp_path, _UP_IMG_W, _UP_IMG_H, "jpg");
                    //    //2014/06/11
                    //    _imgSizeConertForSave($_uptmp_path, $_tmp_path, $_save_w, $_save_h, "jpg");
                    //}
                    //2015/08/07 Mod ------------ Strat --------------
                    $ext = substr($extension,1); //ピリオドなし
                    // コンバート 幅高さどちらもはみ出さないバージョン

                    _imgSizeConert($_uptmp_path, $_tmp_path, $_save_w, $_save_h, $ext);
                    //2014/06/11 画像ファイルを指定した幅高内に収める縮小処理(※いずれか一方ははみ出してもよいバージョン：保存用ファイル作成のための関数)
                    //_imgSizeConertForSave($_uptmp_path, $_tmp_path, $_save_w, $_save_h, $ext);
                    //2015/08/07 Mod ------------ End --------------

                    // UPファイルからのTMPファイルを削除
                    unlink($_uptmp_path);

                    // 画像データ
                    $_FH = fopen($_tmp_path,"rb");
                    $_sess[$_tagname . '_tmp_data'] = base64_encode(fread($_FH,_filesize($_tmp_path)));

                    fclose($_FH);
                    // 画像サイズ
                    $imginfo = _getImageData($_tmp_path);
                    $_w = $imginfo['width'];
                    $_h = $imginfo['height'];
                    // mime-type
                    $_sess[$_tagname . '_tmp_data_mime'] = $imginfo['mime'];
                    // 拡張子
                    $_sess[$_tagname . '_tmp_data_ext'] = $extension;
                    // TMPファイルを削除
                    unlink($_tmp_path);

                    // ファイルサイズを取得
                    $_sess[$_tagname . '_size'] = _ImgOptionCalc($_w,$_h,$_disp_w,$_disp_h);

                }else{
                    // 「GIF」「PNG」「JPG」以外のファイルはエラー
                    //$_ret_err_msg[] = "アップロードできる画像は「GIF」「PNG」「JPG」のみです。"; //2014/06/27 png追加
                    //2014/07/28 Mod
                    $_ret_err_msg[] = $_conf_msg[$_lang]['pic'][1];
                    return $_ret_err_msg;
                }
            }else{
                if($_sess[$_tagname . '_tmp_data']!=""){
                    $_sess[$_tagname . "_del"]="";
                    $_del_flg="";
                }
                $_sess[$_tagname . '_tmp_data'] = "";
                $_sess[$_tagname . '_tmp_data_mime'] = "";
                $_sess[$_tagname . '_tmp_data_ext'] = "";
                $_sess[$_tagname . '_size'] = ""; //2007/07/02 Add
                $_sess[$_tagname] = ""; //2018/09/09 Add

                _disp_img($_dir_id, $_tagname, $_sess, $_disp_w, $_disp_h);

                //2015/02/18 Add kcre ---------- Strat --------------
                if($_need_chk_str!=""){
                    if($_sess[$_tagname]=="" && $this_sess[$_tagname.'_tmp_data']==""){
                        $_ret_err_msg[] = sprintf($_conf_msg[$_lang]['pic'][2],$_need_chk_str); //xxxxxを指定してください。
                    }
                }
                //2015/02/18 Add kcre ---------- End ----------------
                return $_ret_err_msg;
            }
        }else{
            if($_del_flg!=""){
                if($_sess[$_tagname . '_tmp_data']!=""){
                    $_sess[$_tagname . "_del"]="";
                    $_del_flg="";
                }
                $_sess[$_tagname . '_tmp_data'] = "";
                $_sess[$_tagname . '_tmp_data_mime'] = "";
                $_sess[$_tagname . '_tmp_data_ext'] = "";
                $_sess[$_tagname . '_size'] = ""; //2007/07/02 Add
                $_sess[$_tagname] = ""; //2018/09/09 Add

                //2014/07/28 Add
                //2015/02/18 Del
                //if($_need_chk_str!="") $_ret_err_msg[] = sprintf($_conf_msg[$_lang]['pic'][2],$_need_chk_str); //xxxxxを指定してください。
            }
            _disp_img($_dir_id, $_tagname, $_sess, $_disp_w, $_disp_h);

            $_sess[$_tagname . "_del"] = $_del_flg;

            //2015/02/18 Add kcre ---------- Strat --------------
            if($_need_chk_str!=""){
                if($_sess[$_tagname]=="" && $this_sess[$_tagname.'_tmp_data']==""){
                    $_ret_err_msg[] = sprintf($_conf_msg[$_lang]['pic'][2],$_need_chk_str); //xxxxxを指定してください。
                }
            }
            //2015/02/18 Add kcre ---------- End ----------------

            return $_ret_err_msg;
        }

        //2015/02/18 Add kcre ---------- Strat --------------
        if($_need_chk_str!=""){
            if($_sess[$_tagname]=="" && $this_sess[$_tagname.'_tmp_data']==""){
                $_ret_err_msg[] = sprintf($_conf_msg[$_lang]['pic'][2],$_need_chk_str); //xxxxxを指定してください。
            }
        }
        //2015/02/18 Add kcre ---------- End ----------------
        return $_ret_err_msg;
    }

    //---------------------------------------------------------
    //画像表示 : _disp_saveimg
    //
    //        引数：$_id          対象管理者ID
    //        　　  $_ss_data     画像バイナリデータ
    //        　　  $_filename    ファイル名
    //        　　　$_ext         保存拡張子
    //        　　　$_del_flg     削除フラグ
    //        戻値：ファイルが変更・追加の場合はファイル名
    //        　　　ファイルが削除の場合は""
    //        　　　ファイルの変更が無い場合はfalse
    //---------------------------------------------------------
    //function _disp_saveimg($_id, $_ss_data, $_filename,  $_del_flg){
    function _disp_saveimg($_mode, $_id, $_tagname, &$_sess, $_file_base_name){

        // 格納先ディレクトリ
        $_fil_dir = _SYSTEM_ROOT_DIR . '/upfile/' . $_id . '/';

        //2019/10/10 Mod ---- Before ----
        // $_old_flnm = $_sess[$_tagname];             //2007/12/13 Add
        //2019/10/10 Mod ---- After ----
        if($_sess[$_tagname.'_del']!=""){
            $_old_flnm = $_sess['init_data'][$_tagname];
        }else{
            $_old_flnm = $_sess[$_tagname];
        }
        //2019/10/10 Mod ---- End ----
        $_old_file_path = $_fil_dir . $_old_flnm;   //2007/12/13 Add
        if( $_sess[$_tagname.'_tmp_data'] != "" ){
            $_sess[$_tagname] = $_file_base_name . $_sess[$_tagname.'_tmp_data_ext'];
        }

        if($_mode=="delete"){
            $_del_flg = "on";
        }else{
            $_del_flg = $_sess[$_tagname.'_del'];
        }

        // 格納先ディレクトリが無い場合は、作成する
        // @mkdir($_fil_dir, 0777);
        // @chmod($_fil_dir, 0777); //2009/10/07 Add
        $w_id = explode("/", $_id);
        $_wk_dir = _SYSTEM_ROOT_DIR. '/upfile/';
        for ($i=0; $i <_count($w_id) ; $i++) { 
            // 格納先ディレクトリ
            $_wk_dir .= '/' . $w_id[$i] . '/';
            _mkdir($_wk_dir);

        }

        // ファイルパス
        $_file_path = $_fil_dir . $_sess[$_tagname];
        if($_sess[$_tagname.'_tmp_data'] != ""){
            // 画像セッションデータがある
            if($_del_flg == ""){
                // 削除フラグが無い

                //2007/12/13 Add 旧ファイル名でファイルがあれば削除 ----- Strat ------
                if( $_old_flnm!="" && _is_file($_old_file_path) ){
                    // ファイルの削除
                    unlink($_old_file_path);
                }
                //2007/12/13 Add 旧ファイル名でファイルがあれば削除 ----- End ------

                //同一ファイルがあれば一旦削除
                if( _is_file($_file_path) ){
                    // ファイルの削除
                    unlink($_file_path);
                }
                // セッションデータを実ファイルに保存
                $_FH = fopen($_file_path, "wb");
                fwrite($_FH, base64_decode($_sess[$_tagname.'_tmp_data']));
                fclose($_FH);
                // 全てに読み込み、書き込み権限を与える
                @chmod($_file_path,0666);
            }
        }else if($_del_flg != ""){
            //2019/10/10 Mod ---- Before ----
            // if( _is_file($_file_path) ){
            //     // ファイルの削除
            //     unlink($_file_path);
            // }
            //2019/10/10 Mod ---- After ----
            if( _is_file($_old_file_path) ){
                // ファイルの削除
                unlink($_old_file_path);
            }
            //2019/10/10 Mod ---- End ----
            
            $_sess[$_tagname] = "";
        }

        return;
    }

    //2015/06/09 Add ---------- Start ----------
    function _OrientationTopLeft($_path){
        if ( function_exists('exif_read_data')){
            // php_exifモジュールが使える場合
            try{
                $exif = exif_read_data($_path);
            }catch (Exception $e){
                return;
            }
            // Orientation = 1 回転無し
            // Orientation = 2 左右反転
            // Orientation = 3 180°回転
            // Orientation = 4 上下反転
            // Orientation = 5 時計回りに90°回転した後、左右反転
            // Orientation = 6 時計回りに90°回転
            // Orientation = 7 反時計回りに90°回転した後、左右反転
            // Orientation = 8 反時計回りに90°回転
            if (isset($exif['Orientation'])){
                $orientation = $exif['Orientation'];

                if($orientation == 3){
                    $rotation = 180;
                }elseif ($orientation == 6){
                    $rotation = -90;
                }elseif ($orientation == 8){
                    $rotation = 90;
                }else{
                    $rotation = 0;
                }
                $source = imagecreatefromjpeg($_path);
                $rotate = imagerotate($source, $rotation, 0);
                imagejpeg($rotate, $_path, 100);
            }
        }else{
            // ImageMagick API ネイティブPHP拡張モジュールが使える場合
            if (class_exists('Imagick')) {
                $imagick = new Imagick();
                $imagick->readImage($_path);
                $format = strtolower($imagick->getImageFormat());

                if ($format === 'jpeg') {
                    $orientation = $imagick->getImageOrientation();
                    $isRotated = false;
                    if ($orientation === \Imagick::ORIENTATION_RIGHTTOP) {
                        $imagick->rotateImage('none', 90);
                        $isRotated = true;
                    } elseif ($orientation === \Imagick::ORIENTATION_BOTTOMRIGHT) {
                        $imagick->rotateImage('none', 180);
                        $isRotated = true;
                    } elseif ($orientation === \Imagick::ORIENTATION_LEFTBOTTOM) {
                        $imagick->rotateImage('none', 270);
                        $isRotated = true;
                    }
                    if ($isRotated) {
                        $imagick->setImageOrientation(\Imagick::ORIENTATION_TOPLEFT);
                    }
                }
                $imagick->destroy();
                $imagick = null;
            }
        }
    }
    //2015/06/09 Add ---------- End ----------


?>