<script type="text/javascript">
    <?php 
    $as =  [
        'https://upload.',
        'http://upload.',
        'http://up.',
        'http://uploads.',
    ];
    ?>
    document.domain = "<?php echo str_replace($as,'',\Yii::$app->request->hostInfo);?>";
    window.name='<?php echo $jsonData;?>';
</script>