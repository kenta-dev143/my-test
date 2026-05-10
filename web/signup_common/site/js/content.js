//MAIN SLIDER SETTING +++++++++++++++++++++++++
$(function(){
$(".main-slider").slick({
arrows:false,
autoplay:true,
autoplaySpeed:5000,
dots:true,
focusOnSelect:true,
fade:true,
speed:1500,
//slickCurrentSlide:true
}); 
});


$(function(){
//COMPANY SLIDER SETTING +++++++++++++++++++++++++

var $_c = $(".company-bronze-slider");

$_c.slick({
slidesToShow:19,
slidesToScroll:1,
arrows: false, 
autoplay: true,
autoplaySpeed: 3000,
speed:1000,
infinite: true,
centerMode:true,
centerPadding:'2%',
swipeToSlide: true,
dots:false,
fade:false, 
waitForAnimate:false,
focusOnSelect: true,
responsive: [
{
breakpoint: 1700,
settings:{ 
slidesToShow:15,
slidesToScroll:1,
centerPadding:'4%',

}
},
{
breakpoint: 1400,
settings:{ 
slidesToShow:13,
slidesToScroll:1,
}
},
{
breakpoint: 992,
settings:{ 
slidesToShow:11,
slidesToScroll:1,
}
},
{
breakpoint: 768,
settings: {
slidesToShow:5,
slidesToScroll:1,
centerPadding:'4%',
swipe:true,
touchThreshold:8,
swipeToSlide: true,
}
}

]
});
$(window).on("resize load", function() {
//$_c.slick("slickSetOption", "slidesToScroll", 2);
});


});