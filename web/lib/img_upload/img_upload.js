var msie=navigator.appVersion.toLowerCase();
var chrome=(msie.indexOf('chrome')>-1)?1:0;
var msie=(msie.indexOf('msie')>-1)?parseInt(msie.replace(/.*msie[ ]/,'').match(/^[0-9]+/)):0;

function _picSelect(fldName){
        var $elm = $('input[name='+fldName+']');
        if (document.createEvent) {
            var e = document.createEvent('MouseEvents');
            e.initEvent('click', true, true );
            $elm.get(0).dispatchEvent(e);
        }
        else {
            $elm.trigger("click");
        }
        return false;
}
function _picDel(fldName){
    _picServerPost(fldName,1);
}
function _picPost(fldName){
    _picServerPost(fldName,0);
}
function _picServerPost(fldName,del_flg){

    var wform = $('input[name='+fldName+']').parents('form');
    if( (msie>10)||(msie==0) ){
        var fileFldVal = wform.find('input[name='+fldName+']').val();
        if(fileFldVal=='' && del_flg==0){
            return false;
        }
        wform.attr('target' , '_self' );
        if(del_flg==1){
            wform.find('input[name=exec]').val('img_upload_del'); 
        }else{
            wform.find('input[name=exec]').val('img_upload'); 
        }
        wform.find('input[name=img_upload_fldname]').val(fldName);
        //wform.find('input[name=img_upload_br]').val('new'); 
        fd = new FormData(wform[0]);
        $('#'+fldName+'_loading_gif').show();
        $.ajax(
                'index.php?page='+this_page+'&rand='+Math.random(),
            {
            type: 'post',
            processData: false,
            contentType: false,
            data: fd,
            dataType: "text",
            success: function(data) {
                wform.find('input[name=exec]').val(''); 
                $('input[name='+fldName+']').val('');
                //wform.find('input[name=img_upload_br]').val(''); 
                if(data.substring(0,2) == 'DE'){ //Del OK
                    $('#'+fldName+'_tag').attr({'width':'auto','height':DISP_IMG_SIZE[fldName].h,'src':_SYSTEM_ROOT_URLS+'/lib/img_upload/img/img_dummy.jpg'});
                    $('#'+fldName+'_img_del_btn').hide();
                    $('#'+fldName+'_tag').on("load", function(){
                        $('#'+fldName+'_loading_gif').hide();
                        $('#'+fldName+'_img_del_btn').hide(); //2017/11/24 Add
                    });
                }else if(data.substring(0,2) == 'OK'){
                    if( data.substring(3,4)=='h'){
                        $('#'+fldName+'_tag').attr({'width':'auto','height':DISP_IMG_SIZE[fldName].h,'src':_SYSTEM_ROOT_URLS+'/lib/tmpimgdisp.php?project_name_prefix='+project_name_prefix+'&page='+this_page+'&id='+fldName+'&rand='+Math.random()});
                        $('#'+fldName+'_err').hide();
                    }else{
                        $('#'+fldName+'_tag').attr({'width':DISP_IMG_SIZE[fldName].w,'height':'auto','src':_SYSTEM_ROOT_URLS+'/lib/tmpimgdisp.php?project_name_prefix='+project_name_prefix+'&page='+this_page+'&id='+fldName+'&rand='+Math.random()});
                        $('#'+fldName+'_err').hide();
                    }
                    $('#'+fldName+'_tag').on("load", function(){
                        $('#'+fldName+'_loading_gif').hide();
                        $('#'+fldName+'_img_del_btn').show(); //2017/11/24 Add
                    });
                }else{
                    $('#'+fldName+'_loading_gif').hide();
                    alert( data );
                }
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                $('#'+fldName+'_loading_gif').hide();
                wform.find('input[name=exec]').val(''); 
                //wform.find('input[name=img_upload_br]').val(''); 
                $('input[name='+fldName+']').val('');;
                alert( "画像が投稿できませんでした。" );
            }
        });
        return false;
    }else{
        //IE10以下
        $('#'+fldName+'_loading_gif').show();
        wform.attr('target' , 'hid_frame1' );
        if(del_flg==1){
            wform.find('input[name=exec]').val('img_upload_del'); 
        }else{
            wform.find('input[name=exec]').val('img_upload'); 
        }
        wform.find('input[name=img_upload_fldname').val(fldName);
        //wform.find('input[name=img_upload_br]').val('old'); 
        wform.submit();
    }
}

function imageReturn(fldName,data){
    var wform = $('input[name='+fldName+']').parents('form');
    wform.find('input[name=exec]').val(''); 
    $('input[name='+fldName+']').val('');
    //wform.find('input[name=img_upload_br]').val(''); 
    if(data.substring(0,2) == 'DE'){ //Del OK
        $('#'+fldName+'_tag').attr({'width':'auto','height':DISP_IMG_SIZE[fldName].h,'src':_SYSTEM_ROOT_URLS+'/lib/img_upload/img/img_dummy.jpg'});
        $('#'+fldName+'_img_del_btn').hide();
        $('#'+fldName+'_tag').on("load", function(){
            $('#'+fldName+'_loading_gif').hide();
            $('#'+fldName+'_img_del_btn').hide(); //2017/11/24 Add
        });
    }else if(data.substring(0,2) == 'OK'){
        if( data.substring(3,4)=='h'){
            $('#'+fldName+'_tag').attr({'width':'auto','height':DISP_IMG_SIZE[fldName].h,'src':_SYSTEM_ROOT_URLS+'/lib/tmpimgdisp.php?project_name_prefix='+project_name_prefix+'&page=shop_edit&id='+fldName+'&rand='+Math.random()});
        }else{
            $('#'+fldName+'_tag').attr({'width':DISP_IMG_SIZE[fldName].w,'height':'auto','src':_SYSTEM_ROOT_URLS+'/lib/tmpimgdisp.php?project_name_prefix='+project_name_prefix+'&page=shop_edit&id='+fldName+'&rand='+Math.random()});
        }
        $('#'+fldName+'_err').hide();

        $('#'+fldName+'_tag').on("load", function(){
            $('#'+fldName+'_loading_gif').hide();
            $('#'+fldName+'_img_del_btn').show(); //2017/11/24 Add
        });
    }else{
        $('#'+fldName+'_loading_gif').hide();
        alert( data );
    }
}


function imageerrReturn(fldName,data){
    var wform = $('input[name='+fldName+']').parents('form');
    wform.find('input[name=exec]').val(''); 
    $('input[name='+fldName+']').val('');
    //wform.find('input[name=img_upload_br]').val(''); 
    $('#'+fldName+'_err').html( data );
    $('#'+fldName+'_err').show();
}

