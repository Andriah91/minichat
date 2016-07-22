(function() {
    var minichat = angular.module('minichat', []);

    minichat.directive('ngEnter', function () {
        return function (scope, element, attrs) {
            element.bind("keydown keypress", function (event) {
                if (event.which === 13) {
                    scope.$apply(function (){
                        scope.$eval(attrs.ngEnter);
                    });
                    event.preventDefault();
                }
            });
        };
    });

    minichat.controller('minichatCtrl', ['$scope', '$http', function($scope, $http) {

        $scope.urlListMessages = '?action=list';
        $scope.urlSaveMessage = '?action=save';

        $scope.pidMessages = null;

        $scope.messages = [];
        $scope.online = null;
        $scope.lastMessageId = null;
        $scope.historyFromId = null;

        $scope.me = {
            username: "",
            message: null
        };

        
        $scope.saveMessage = function(form, callback) {
            var data = $.param($scope.me);

            if (! ($scope.me.username && $scope.me.username.trim())) {
                return $scope.openModal();
            }

            if (! ($scope.me.message && $scope.me.message.trim() &&
                   $scope.me.username && $scope.me.username.trim())) {
                return;
            }
            $scope.me.message = '';
            return $http({
                method: 'POST',
                url: $scope.urlSaveMessage,
                data: data,
                headers: {'Content-Type': 'application/x-www-form-urlencoded'}
            }).success(function(data) {
                $scope.listMessages(true);
            });
        };

        $scope.replaceShortcodes = function(message) {
            var msg = '';
            msg = message.toString().replace(/(\[img])(.*)(\[\/img])/, "<img src='$2' />");
            msg = msg.toString().replace(/(\[url])(.*)(\[\/url])/, "<a href='$2'>$2</a>");
            return msg;
        };

        
        $scope.getLastMessage = function() {
            return $scope.messages[$scope.messages.length - 1];
        };

        $scope.listMessages = function(wasListingForMySubmission) {
            return $http.post($scope.urlListMessages, {}).success(function(data) {
                $scope.messages = [];
                angular.forEach(data, function(message) {
                    message.message = $scope.replaceShortcodes(message.message);
                    $scope.messages.push(message);
                });

                var lastMessage = $scope.getLastMessage();
                var lastMessageId = lastMessage && lastMessage.id;

                if ($scope.lastMessageId !== lastMessageId) {
                    $scope.onNewMessage(wasListingForMySubmission);
                }
                $scope.lastMessageId = lastMessageId;
            });
        };

        $scope.onNewMessage = function(wasListingForMySubmission) {
            if ($scope.lastMessageId && !wasListingForMySubmission) {
                $scope.notifyLastMessage();
            }

        };

        $scope.init = function() {
            $scope.listMessages();
            $scope.pidMessages = window.setInterval($scope.listMessages, 3000);
        };

        $scope.init();
    }]);
})();