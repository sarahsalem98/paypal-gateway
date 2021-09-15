<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <?php if(!empty($response['code'])) { ?>
                <div class="alert alert-<?php echo $response['code']; ?>">
                    <?php echo $response['message']; ?>
                </div>
                <?php } ?>
                <div class="panel panel-default">
                    <div class="panel-heading">Laravel PayPal Demo</div>
                    <div class="panel-body">
                    
                            
                             <button><a href="{{route('paypal_chechout')}}">checkout for 15$ </a></button>
                 
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
    