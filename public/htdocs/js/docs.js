hljs.initHighlightingOnLoad();
$(document).ready(function(){
    $('.langList > button').click(
        function(){
            $('.langList > ul').toggle();
        });

    $('.developeBtn > button').click(
        function(){
            $('.developeBtn > ul').toggle();
        });    
    $('.codeView').each(function(){
        console.log('codeView', $(this).css('height'), $(this).height());
        if($(this).find('code').height() > 200){
            $(this).addClass('codeExpendable').click(function(){
                // TODO : modal 연동(해당소스값 긁어서 모달창에 뿌리기)
                alert(111);
            });
        }
    });
});