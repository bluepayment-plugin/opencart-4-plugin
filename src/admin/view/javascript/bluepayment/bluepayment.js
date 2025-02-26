$(document).ready(function(){
    $(".currencies-list > li").map(function(){ $(this).removeClass("active"); });
    $(".currencies-list > li:first").addClass("active");

    $(".currencies-panels > div").map(function(){ $(this).removeClass("active"); });
    $(".currencies-panels > div:first").addClass("active");
});
