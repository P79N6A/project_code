<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title>GAP</title>
        <style type="text/css">  
        .contact{  
            padding:5px;  
            border-bottom:1px solid gray;  
        }  
    </style> 
    <script type="text/javascript" src="/js/cordova.js"></script>
  <script type="text/javascript" src="/js/jquery-1.7.2.min.js"></script>
    <script type="text/javascript"></script>
        <script type="text/javascript" charset="utf-8">  
      
      
      
    $(function(){  
    // Wait for PhoneGap to load  
    //  
    document.addEventListener("deviceready", onDeviceReady, false);  
  
    // PhoneGap is ready  
    //  
    function onDeviceReady() {  
        var options = new ContactFindOptions();  
        var fields = ["displayName", "phoneNumbers"];  
        navigator.contacts.find(fields, onSuccess, onError);  
    }  
  
    var list = $("#contacts");  
    function onSuccess(contacts) {  
        for (var i=0; i<contacts.length; i++) {  
           /*  console.log("Display Name = " + contacts[i].displayName);*/  
          $("<p class='contact'/>").text(contacts[i].displayName)  
          .data("name", contacts[i].displayName)  
          .data("number", contacts[i].phoneNumbers[0].value)  
          .click(function(){  
              $("#name").text($(this).data("name"));  
              $("#number").text($(this).data("number"));  
              $("#detail").show();  
              $("#contacts").hide();  
          })  
          .appendTo(list);  
        }  
    }  
  
    // onError: Failed to get the contacts  
    //  
    function onError(contactError) {  
        alert('onError!');  
    }  
      
    $("#back").click(function(){  
        $("#detail").hide();  
        $("#contacts").show();  
    });  
      
    });  
      
    </script>  
</head>
<body>
<?= $content ?>
</body>
</html>