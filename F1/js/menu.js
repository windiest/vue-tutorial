/*$("button").click(function(){
  $("#menu").toggle();
});*/

/*$(document).ready(function() {
 $(".btn").click(function() {
 $("#menu").toggle();
 $(".content").animate({
 marginRight : "120px"
 });
 });
 $(".btn_left").click(function() {
 $("#menu").toggle(500);
 $(".content").animate({
 marginRight : ""
 });

 });

 // 点击图片缩小区域后返回

 $(".content").click(function() {
 $("#menu").toggle(500);
 $(".content").animate({
 marginRight : ""
 });

 });

 * $(".btn_left").click(function(){ $(".content").animate({right:'250px'});



 * $(".btn").click(function() { $("#btn_left").hide(); });

 });*/

/* fadeToggle(); */
//第二种效果
/*$("button").click(function(){
 $("#menu").toggle();
 });*/

$(document).ready(function() {
	$(".btn").click(function() {
		$("#menu").fadeIn();
		/*$(".menu_background_2").animate({right:'125px'});*/
		$("body").animate({right:'125px'});

	});

	// .btn_left .content点击按钮或者内容区域把导航栏隐藏
	$(".btn_left").click(function() {
		$("#menu").fadeOut();
		$("body").animate({right:''});

	});
	$(".content").click(function() {
		$("#menu").fadeOut();
		$("body").animate({right:''});
	});

	// var screenHeight = $(window).height();// 获取屏幕可视区域的高度。
	// animate({scrollTop: $(document).height()}, 300);是滚动到最底部。
	// animate({scrollTop: $(document).height()}, 300);是滚动一个屏幕高度。
	// $('html, body, .content').animate()原函数
	$(".bottomDiv").click(function() {
		$("html").animate({
			scrollTop : $(window).height()
		}, 300);
		$(".bottomDiv").hide();
		/* return false; */
	});

	/*
	 * $(".btn").click(function(){ $(".menu_img").animate({"left": "-=50px"},
	 * "slow"); });
	 */

	// 点击图片缩小区域后返回
	/*
	 * $(".content").click(function() { $("#menu").toggle(500);
	 * 
	 * });
	 */
	/*
	 * $(".btn_left").click(function(){ $(".content").animate({right:'250px'});
	 */

	/*
	 * $(".btn").click(function() { $("#btn_left").hide(); });
	 */
});
// var screenHeight = $(window).height();// 获取屏幕可视区域的高度。
/*
 * function scrollWindow() { window.scrollTo(screenHeight, screenHeight) }
 */

// 当用户滚动指定的元素时，会发生 scroll 事件。
/*
 * $("div").scroll(function() { $("span").text(x += 1); });
 */
/*
 * $(".bottomDiv").click(function() { $('html, body, .content').animate({
 * scrollTop : $(document).height() }, 300); return false; });
 */
/* fadeToggle(); */
// var screenWidth = $(window).width();// 获取屏幕可视区域的宽度。
var screenHeight = $(window).height();// 获取屏幕可视区域的高度。

$(window).scroll(function() {
	var scrollHeight = $(document).scrollTop();// 获取滚动条滚动的高度。
	// 判断是否滚下。如果是则渐出
	if (scrollHeight != 0) {
		$(".bottomDiv").fadeOut();
	}
	// 判断是否滚动到最顶。如果是则渐入
	if (scrollHeight == 0) {
		$(".bottomDiv").fadeIn();
	}
})
