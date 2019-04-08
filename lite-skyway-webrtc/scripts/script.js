const Peer = window.Peer;

(async function main() {
  const localVideo = document.getElementById('js-local-stream');
  const joinTrigger = document.getElementById('js-join-trigger');
  const leaveTrigger = document.getElementById('js-leave-trigger');
  const remoteVideos = document.getElementById('js-remote-streams');
//  const voicechatLabel = document.getElementById('js-voice-trigger-label');
  const voicechat = document.getElementById('js-voice-trigger');
  
  
//  const roomId = document.getElementById('js-room-id');
  const localText = document.getElementById('js-local-text');
  const sendTrigger = document.getElementById('js-send-trigger');
  const messages = document.getElementById('js-messages');
  
  const constraints = { audio: true, video: { frameRate :{ max: 15,min:10 }, width: { min: 240, ideal: 640, max: 1920 },height: { min: 120, ideal: 480, max: 1200 } }   };
   const constraints_voice = { audio: true  };

  const ja_messages = { unsupported:'サポート外のブラウザのためビデオ通話は利用できません。',voice_only:'音声のみ', join_btn:'参加', leave_btn:'退席',send_btn:'送る' ,join_mes:'参加しました' ,leave_mes:'退席しました',join:"参加しました",left:"退席しました" };
  const en_messages = { unsupported:'Video calls are not available due to an unsupported browser.',voice_only:'Voice only', join_btn:'Join', leave_btn:'leave',send_btn:'Send' ,join_mes:'You join' ,leave_mes:'You leave' ,join:"join",left:"left" };

  const localStream = await navigator.mediaDevices
    .getUserMedia(constraints)
    .catch(console.error);console.log(language);
  
  var language = (window.navigator.languages && window.navigator.languages[0]) ||
            window.navigator.language ||
            window.navigator.userLanguage ||
            window.navigator.browserLanguage;
var result = language.indexOf( 'ja' );
var joind_flg = false;



if(result !== -1) {
 var text_messages = ja_messages;
} else {
 var text_messages = en_messages;
}




joinTrigger.textContent = text_messages.join_btn;
leaveTrigger.textContent = text_messages.leave_btn;
sendTrigger.textContent = text_messages.send_btn;
//voicechatLabel.textContent = text_messages.voice_only;

var join_mes = text_messages.join_mes;
var leave_mes = text_messages.leave_mes;

    // サポート外ブラウザの場合何もしない
    if (!navigator.mediaDevices) {
        alert(text_messages.unsupported);
    return;
    }


  // Render local stream
  localVideo.muted = true;
  localVideo.srcObject = localStream;
  await localVideo.play().catch(console.error);

  const peer = new Peer({
    key: window.__SKYWAY_KEY__,
    debug: 3,
  });

  // Register join handler
  joinTrigger.addEventListener('click', () => {
    // Note that you need to ensure the peer has connected to signaling server
    // before using methods of peer instance.
    if(  joind_flg == true){
              return;
        }

    if (!peer.open) {
      messages.textContent += '=== Connection Failed !!!===\n';
      joind_flg = false ;
      return;
    }

    console.log(ROOM_ID);
    const room = peer.joinRoom(ROOM_ID, {
      mode: location.hash === ROOM_MODE ,
      stream: localStream,
    });
    console.log(room );
    room.once('open', () => {
      messages.textContent += '=== '+ join_mes + ' ===\n';
          joind_flg =true ;
    });
    room.on('peerJoin', peerId => {
      messages.textContent += `=== ` + text_messages.join+ ` ===\n`;
    });

    // Render remote stream for new peer join in the room
    room.on('stream', async stream => {
      const newVideo = document.createElement('video');
      newVideo.srcObject = stream;
      // mark peerId to find it later at peerLeave event
      newVideo.setAttribute('data-peer-id', stream.peerId);
      remoteVideos.append(newVideo);
      await newVideo.play().catch(console.error);
    });

    room.on('data', ({ data, src }) => {
      // Show a message sent to the room and who sent
           //ルームに送信されたメッセージと送信した人を表示する
      messages.textContent += `${data}\n`;

    });

    // for closing room members
    room.on('peerLeave', peerId => {
      const remoteVideo = remoteVideos.querySelector(
        `[data-peer-id=${peerId}]`
      );
      remoteVideo.srcObject.getTracks().forEach(track => track.stop());
      remoteVideo.srcObject = null;
      remoteVideo.remove();

      messages.textContent += `===`+text_messages.left+` ===\n`;
    });

    // for closing myself
    room.once('close', () => {
      sendTrigger.removeEventListener('click', onClickSend);
      messages.textContent += '== '+leave_mes +' ===\n';
      joind_flg = false ;
      Array.from(remoteVideos.children).forEach(remoteVideo => {
        remoteVideo.srcObject.getTracks().forEach(track => track.stop());
        remoteVideo.srcObject = null;
        remoteVideo.remove();
      });
    });

    sendTrigger.addEventListener('click', onClickSend);
    leaveTrigger.addEventListener('click', () => room.close(), { once: true });

    function onClickSend() {
      // Send message to all of the peers in the room via websocket
      room.send(DISPLAY_NAME+`:`+ localText.value);
      messages.textContent += DISPLAY_NAME+ `: ${localText.value}\n`;

//      messages.textContent += `${peer.id}: ${localText.value}\n`;
      localText.value = '';
    }
  });

  peer.on('error', console.error);
})();
