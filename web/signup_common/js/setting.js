$(function(){
//START

//LOADER ++++++++++++++++++++++++++++++++++++++++++++++++++
if($("#content").length){
$("#content").imagesLoaded( { background: true },function(){
setTimeout(function(){   
$("#loading").fadeOut(600,"easeInQuad");
},1000);});
}

//DEVICE CHECK ++++++++++++++++++++++++++++++++++++++++++++++++++
if (navigator.userAgent.indexOf("iPhone") > 0) {
$("body").addClass("iPhone");
}
if (navigator.userAgent.indexOf("iPad") > 0) {
$("body").addClass("iPad");
}
if (navigator.userAgent.indexOf("Android") > 0) {
$("body").addClass("Android");
}

//ios対応 ++++++++++++++++++++++++++++++++++++++++++++++++++
var ua = navigator.userAgent;
if ( ua.indexOf( "iPhone" ) > 0 || ua.indexOf( "iPad" ) > 0) {
$(".for-ios-ba").css({
"cssText":"background-attachment:scroll !important;"
});
}

//END
});

// HAMBURGER MENU +++++++++++++++++++++++++
$(function(){
//START
var scrollpos;
var scrollpos_o1;

$(".menu").click(function () {
$(".menu").toggleClass("active");
$(".menu").toggleClass("h-open");
if($(".menu").hasClass("h-open")){
scrollpos = $(window).scrollTop();
$('body').addClass('fixed').css({'top': -scrollpos});
$("#g-menu > div").css({'overflow': 'auto'});
$("#g-menu").fadeIn(500);
}else{
$("#g-menu").fadeOut(500);
$('body').removeClass('fixed').css({'top': 0});
window.scrollTo( 0 , scrollpos );
$("#g-menu > div").css({'overflow': 'hidden'});
}

});

// SEARCH NAVI +++++++++++++++++++++++++
$(".search-button button,#overlay-search .close").click(function () {
var button_num = $(".search-button button").index(this);
$(".search-button button").toggleClass("overlay-search-open");
if($(".search-button button").hasClass("overlay-search-open")){
$("#overlay-search nav").removeClass("show");
$("#overlay-search").removeClass("show");
scrollpos_o1 = $(window).scrollTop();
$('body').addClass('fixed').css({'top': -scrollpos_o1});
//$('#overlay-search nav').css({'overflow': 'auto'});
$("#overlay-search").fadeToggle(400);
$("#overlay-search nav").eq(button_num).addClass("show");
$("#overlay-search").addClass("show");
}else{
$("#overlay-search").fadeOut(500);
$('body').removeClass('fixed').css({'top': 0});
window.scrollTo( 0 , scrollpos_o1 );
//$('#overlay-search nav').css({'overflow': 'hidden'});
}
});
//END
});

// SEARCH NAVI ACCORDION +++++++++++++++++++++++++
$(function(){
$(".accordion-button").click(function(){
var num = $(".accordion-button").index(this);
if($(this).hasClass("open")){
$(".accordion-button").eq(num).removeClass("open");
}else{
$(".accordion-button").removeClass("open");
$(".accordion-button").eq(num).addClass("open");
}
$(".accordion").addClass("opened-1"); 
$(".accordion").eq(num).removeClass("opened-1");

$(".accordion").eq(num).slideToggle(200,"easeOutQuad");
$(".opened-1").slideUp(200,"easeOutQuad");

});
});


//PAGE TOP BUTTON ++++++++++++++++++++++++++++++++++++++++++++++++++
$(document).ready(function() {
$(window).on("scroll", function() {

if ($(this).scrollTop() > 100) {
$("#scroll-top").addClass("is--visible");
} else {
$("#scroll-top").removeClass("is--visible");
}

});
$("#scroll-top a").click(function() {
$("html,body").animate({ scrollTop: 0 }, 800,"easeInOutCubic");
});
});

//TELNUMBER LINK SETTING ++++++++++++++++++++++++++++++++++++++++++++++++++
$(document).ready(function() {
var ua = navigator.userAgent.toLowerCase();
var isMobile = /iphone/.test(ua)||/android(.+)?mobile/.test(ua);

if (!isMobile) {
$("a[href^='tel:']").on("click", function(e) {
e.preventDefault();
});
}
});

//HEIGHT ADAPT ++++++++++++++++++++++++++++++++++++++++++++++++++
$(function () {
//$(".c-menu .event").matchHeight({byRow: false});
});


//INVIEW ++++++++++++++++++++++++++++++++++++++++++++++++++
$.fn.acs = function(options) {

var elements = this;
var defaults = {
screenPos: 1,
className: "IN-animation"
};
var setting = $.extend(defaults, options);
add_class_in_scrolling();
$(window).on("load scroll",function() {
add_class_in_scrolling();
});

function add_class_in_scrolling() {
var winScroll = $(window).scrollTop();
var winHeight = $(window).height();
var scrollPos = winScroll + (winHeight * setting.screenPos);

elements.each(function(index) {
if($(this).offset().top < scrollPos) {
$(this).addClass(setting.className);
}else{$(this).removeClass(setting.className);}
});
}

};

$(function() {

$(".animation-up").acs();
$(".animation-down").acs();
$(".animation-opacity").acs();
$(".animation-scale").acs();
$(".animation-slide").acs();
});

//HEIGHT ADAPT+++++++++++++++++++++++++

$(function () {

$(".product-list dd .category").matchHeight();
$(".product-list dd h4").matchHeight();
$(".product-list dd p").matchHeight();
//$(".unit h4 strong").matchHeight({byRow: false});
});


//CATEGORY SLIDER SETTING +++++++++++++++++++++++++
$(function(){

var $_t = $(".category-slide-wrap");

$_t.slick({
slidesToShow:7,
slidesToScroll:1,
arrows: false, 
autoplay: true,
autoplaySpeed: 3000,
speed:1000,
infinite: true,
centerMode:true,
centerPadding:'8%',
dots:false,
fade:false, 
waitForAnimate:false,
focusOnSelect: true,
responsive: [
{
breakpoint: 2000,
settings:{ 
slidesToShow:6,
slidesToScroll:1,
centerPadding:'8%',

}
},
{
breakpoint: 1700,
settings:{ 
slidesToShow:5,
slidesToScroll:1,

}
},{
breakpoint: 768,
settings: {
slidesToShow:2,
slidesToScroll:1,
centerPadding:'4%',
swipe:true,
touchThreshold:8,
swipeToSlide: true,
}
}

]
});
});

$(document).ready(function(){
$('body').each(function(i){
var $_t = $(this);
//$_t.find('.slider').addClass("animation-opacity");
$_t.find('.slider').addClass('.slider'+i).slick({
slidesToShow:3,
slidesToScroll:3,
arrows: false, 
autoplay: true,
autoplaySpeed: 3000,
speed:1000,
infinite: true,
dots:true,
fade:false, 
responsive: [
{
breakpoint: 5000,
//settings: "unslick" 
},
{
breakpoint: 768,
settings: {
slidesToShow:1,
slidesToScroll:1,
centerMode:true,
centerPadding:'13%',
swipe:true,
touchThreshold:8,
swipeToSlide: true,
focusOnSelect: true,
waitForAnimate:false,
}
}
]
});

});

});
