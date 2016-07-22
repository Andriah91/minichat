<?php require('script/chat.php'); ?>
<!doctype html>
<html ng-app="minichat">
<head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta charset="utf-8">

    <title>MiniChat</title>

    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.3.14/angular.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
    <script src="script/chat.js"> </script>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
    <link rel="stylesheet" href="chat.css">
</head>
<body ng-controller="minichatCtrl">
    <div class="container">
        <h2 class="hidden-xs">MY CHAT</h2>
        <div class="box box-success direct-chat direct-chat-success">
            <div class="box-header">
                <form ng-submit="saveMessage()">
                    <div class="input-group">
                        <input type="text" placeholder="Username" class="form-control" ng-model="me.username">
                        <input type="text" placeholder="Message" autofocus="autofocus" class="form-control" ng-model="me.message" ng-enter="saveMessage()">
                        <button type="submit" class="btn btn-success btn-flat">Send</button>
                    </div>
                </form>
            </div>
            <div class="box-body">
                <div class="direct-chat-messages">
                    <div class="direct-chat-msg" ng-repeat="message in messages" ng-if="historyFromId < message.id" ng-class="{'right':!message.me}">
                        <div class="direct-chat-info clearfix">
                            <span class="direct-chat-name" ng-class="{'pull-left':message.me, 'pull-right':!message.me}">{{ message.username }}</span>
                            <span class="direct-chat-timestamp " ng-class="{'pull-left':!message.me, 'pull-right':message.me}">{{ message.date }}</span>
                        </div>
                        <img class="direct-chat-img" src="http://upload.wikimedia.org/wikipedia/en/e/ee/Unknown-person.gif" alt="">
                        <div class="direct-chat-text right">
                            <span>{{ message.message }}</span>
                        </div>
                    </div>
                </div> 
            </div>
        </div>
    </div>

</body>
</html>
