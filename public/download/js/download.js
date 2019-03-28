function check() {
    if (/(iPhone|iPad|iPod|iOS)/i.test(navigator.userAgent)) {  //判断iPhone|iPad|iPod|iOS
        $('.Android').hide();
        $('.iphone').show();
        var userAgent = navigator.userAgent;
        if (userAgent.indexOf("Safari") > -1) {
            $('.iphone > .fit').hide();
            $('.iphone > .tip').hide();
        } else {

        }


    }
    if (/(Android)/i.test(navigator.userAgent)) {
        $('.Android').show();
        $('.iphone').hide();
        var ua = navigator.userAgent.toLowerCase();
        var isWeixin = ua.indexOf('micromessenger') != -1;
        if (isWeixin) {

        }else  {
            $('.Android > .fit').hide();
            $('.Android > .tip').hide();
        }
    }
}
function zz() {
    $('.fit2').show();
    $('.fit-cont').show();
}
function uu() {
    $('.fit2').hide();
    $('.fit-cont').hide();
}
