@extends('layouts.layouts')
@section('additionalcss')
  <style type="text/css">
    input[type="range"] {
      display: ;
    }
  </style>
@endsection
@section('content')
<section class="content-header">
      <h1>
        Audio Streaming
      </h1>
      <ol class="breadcrumb">
        <li><a href="/home"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Audio Streaming</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
       <div class="box box-primary">
         <div class="box-body">
          <div class="col-md-12">
            <input type="text" id="room-id" value="abcdef" autocorrect=off autocapitalize=off size=20>
            <button id="open-room">Open Room</button>
            <button id="join-room">Join Room</button>
            <button id="open-or-join-room">Auto Open Or Join Room</button>
            <div id="room-urls" style="text-align: center;display: none;background: #F1EDED;margin: 15px -10px;border: 1px solid rgb(189, 189, 189);border-left: 0;border-right: 0;"></div>
          </div>
          <div class="col-md-12" style="margin: 0 auto">
            <div style="text-align: center;" id="audio-container"></div>
          </div>
         </div>
       </div>
    </section>
@endsection
@section('javas')
<!-- date-range-picker -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.11.2/moment.min.js"></script>
<script src="../../plugins/daterangepicker/daterangepicker.js"></script>
<!-- bootstrap datepicker -->
<script src="../../plugins/datepicker/bootstrap-datepicker.js"></script>

<script>
    window.enableAdapter = true; // enable adapter.js

    // ......................................................
    // .......................UI Code........................
    // ......................................................
    document.getElementById('open-room').onclick = function() {
        disableInputButtons();
        connection.open(document.getElementById('room-id').value, function() {
            showRoomURL(connection.sessionid);
        });
    };

    document.getElementById('join-room').onclick = function() {
        disableInputButtons();

        connection.sdpConstraints.mandatory = {
            OfferToReceiveAudio: true,
            OfferToReceiveVideo: true
        };
        connection.join(document.getElementById('room-id').value);
    };

    document.getElementById('open-or-join-room').onclick = function() {
        disableInputButtons();
        connection.openOrJoin(document.getElementById('room-id').value, function(isRoomExist, roomid) {
            if (!isRoomExist) {
                showRoomURL(roomid);
            }
            else {
                connection.sdpConstraints.mandatory = {
                    OfferToReceiveAudio: true,
                    OfferToReceiveVideo: true
                };
            }
        });
    };

    // ......................................................
    // ..................RTCMultiConnection Code.............
    // ......................................................

    var connection = new RTCMultiConnection();

    // by default, socket.io server is assumed to be deployed on your own URL
    connection.socketURL = 'http://10.151.253.223:9001/';

    // comment-out below line if you do not have your own socket.io server
    // connection.socketURL = 'https://rtcmulticonnection.herokuapp.com:443/';

    connection.socketMessageEvent = 'video-broadcast-demo';

    connection.session = {
        audio: true,
        video: true,
        oneway: true
    };

    connection.sdpConstraints.mandatory = {
        OfferToReceiveAudio: false,
        OfferToReceiveVideo: false
    };

    connection.audiosContainer = document.getElementById('audio-container');
    connection.onstream = function(event) {
        event.mediaElement.removeAttribute('src');
        event.mediaElement.removeAttribute('srcObject');

        var audio = document.createElement('audio');
        audio.controls = true;
        if(event.type === 'local') {
            audio.muted = true;
        }
        audio.srcObject = event.stream;

        var width = parseInt(connection.audiosContainer.clientWidth / 2) - 20;
        var mediaElement = getHTMLMediaElement(audio, {
            title: event.userid,
            // buttons: ['full-screen'],
            width: width,
            showOnMouseEnter: false
        });

        connection.audiosContainer.appendChild(mediaElement);

        setTimeout(function() {
            mediaElement.media.play();
        }, 5000);

        mediaElement.id = event.streamid;
    };

    connection.onstreamended = function(event) {
        var mediaElement = document.getElementById(event.streamid);
        if (mediaElement) {
            mediaElement.parentNode.removeChild(mediaElement);
        }
    };

    function disableInputButtons() {
        document.getElementById('open-or-join-room').disabled = true;
        document.getElementById('open-room').disabled = true;
        document.getElementById('join-room').disabled = true;
        document.getElementById('room-id').disabled = true;
    }

    // ......................................................
    // ......................Handling Room-ID................
    // ......................................................

    function showRoomURL(roomid) {
        var roomHashURL = '#' + roomid;
        var roomQueryStringURL = '?roomid=' + roomid;

        var html = '<h2>Unique URL for your audio streaming:</h2><br>';

        html += 'Hash URL: <a href="' + roomHashURL + '" target="_blank">' + roomHashURL + '</a>';
        html += '<br>';
        html += 'QueryString URL: <a href="' + roomQueryStringURL + '" target="_blank">' + roomQueryStringURL + '</a>';

        var roomURLsDiv = document.getElementById('room-urls');
        roomURLsDiv.innerHTML = html;

        roomURLsDiv.style.display = 'block';
    }

    (function() {
        var params = {},
            r = /([^&=]+)=?([^&]*)/g;

        function d(s) {
            return decodeURIComponent(s.replace(/\+/g, ' '));
        }
        var match, search = window.location.search;
        while (match = r.exec(search.substring(1)))
            params[d(match[1])] = d(match[2]);
        window.params = params;
    })();

    var roomid = '';
    if (localStorage.getItem(connection.socketMessageEvent)) {
        roomid = localStorage.getItem(connection.socketMessageEvent);
    } else {
        roomid = connection.token();
    }
    document.getElementById('room-id').value = roomid;
    document.getElementById('room-id').onkeyup = function() {
        localStorage.setItem(connection.socketMessageEvent, this.value);
    };

    var hashString = location.hash.replace('#', '');
    if (hashString.length && hashString.indexOf('comment-') == 0) {
        hashString = '';
    }

    var roomid = params.roomid;
    if (!roomid && hashString.length) {
        roomid = hashString;
    }

    if (roomid && roomid.length) {
        document.getElementById('room-id').value = roomid;
        localStorage.setItem(connection.socketMessageEvent, roomid);

        // auto-join-room
        (function reCheckRoomPresence() {
            connection.checkPresence(roomid, function(isRoomExist) {
                if (isRoomExist) {
                    connection.sdpConstraints.mandatory = {
                        OfferToReceiveAudio: true,
                        OfferToReceiveVideo: true
                    };
                    connection.join(roomid);
                    return;
                }

                setTimeout(reCheckRoomPresence, 5000);
            });
        })();

        disableInputButtons();
    }
</script>
@endsection

