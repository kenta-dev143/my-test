/*--------------------------------------------------------------------------*
*  
*  jQuery.footerFixed.js
*  
*  MIT-style license. 
*  
*  ported by @ginlime
*  https://gist.github.com/ginlime
*  
*  original written by:
*  2007 Kazuma Nishihata [to-R]
*  http://blog.webcreativepark.net
*  
*--------------------------------------------------------------------------*/

(function($){
var footerId = 'footer';
// 文字サイズ
var checkFontSize = function(func){
// 判定要素の追加
$('body').append($('<div></div>').attr('id','ff-node-temp').css({'position':'absolute','top':'0','visibility':'hidden'}).text('S'));
var defHeight = $('#ff-node-temp').prop('offsetHeight');
// 判定関数
var checkBoxSize = function(){
if(defHeight != $('#ff-node-temp').prop('offsetHeight')){
func();
defHeight = $('#ff-node-temp').prop('offsetHeight');
}
};
setInterval(checkBoxSize, 1000);
};
var footerFixed = function(){
// ドキュメントの高さ
var dh = $('body').prop('clientHeight');
// フッターのtopからの位置
$('#'+footerId).css('top','0');
var ft = $('#'+footerId).prop('offsetTop');
// フッターの高さ
var fh = $('#'+footerId).prop('offsetHeight');
// ウィンドウの高さ
var wh = $(window).prop('innerHeight');
if(ft+fh<wh){
$('#'+footerId).css({'position':'relative','top':(wh-fh-ft)+'px'});
}
};


$(window).on('load',function(e){
var windowWidth = window.innerWidth;

//if (windowWidth > 767) {
//if(!$('#btn-menu:hover a').hasClass('active-h')){
checkFontSize(footerFixed);
//}
//}
});
$(window).on('load resize',function(e){
var windowWidth = window.innerWidth;
//if (windowWidth > 767) {
//if(!$('#btn-menu:hover a').hasClass('active-h')){
footerFixed();
//}
//}
});


})(jQuery);