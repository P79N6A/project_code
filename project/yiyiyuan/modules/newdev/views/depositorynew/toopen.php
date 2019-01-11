<?php if(!$base_form){  ?>
    <script type="text/javascript">
        alert('开户失败！');
        setTimeout(function () {
            window.myObj.closeHtml();
            function closeHtml() {
            }
        });
    </script>
<?php }else{ ?>
    <script type="text/javascript">
        var url = '<?php echo $base_form;?>';
        window.location = url;
    </script>
<?php }?>

