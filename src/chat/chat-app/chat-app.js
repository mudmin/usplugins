var chatTimer;

function ajax(url, data, callback, methodType){
  const method = methodType || 'GET';
  const formData = new FormData();
  for(const [key, value] of Object.entries(data)){
    formData.append(key,value);
  }
  const options = {
    method: method
  };
  if(method !== 'GET'){
    options['body'] = formData;
  }
  return fetch(url, options)
  .then((response)=>{return response.json()})
  .then(resp => {
    if(typeof callback === 'function'){
      callback(resp);
    }
  })
}

function addParticipants(resp){
  const previousActive = window.localStorage.getItem("activeChatParticipants");
  if(!previousActive || parseInt(previousActive, 10) !== parseInt(resp.active,10)){
    window.localStorage.setItem("activeChatParticipants", resp.active)
    const partList = document.getElementById('chat-app-participants-list');
    partList.innerHTML = "";
    resp.participants.forEach(part => {
      const item = document.createElement('li');
      item.className = part.session_active == 1? 'active' : '';
      item.innerText = `${part.fname} ${part.lname} ${part.id}`;
      partList.appendChild(item);
    });
  }
}

function retrieveChatMessages(){
  ajax('usersc/plugins/chat/chat-app/parsers/getChatMessages.php',{},(resp)=>{
    if(resp.success){
      addParticipants(resp);
      if(resp.lastId !== ''){
        const previousLastId = window.localStorage.getItem('lastChatMessageId');
        if(!previousLastId || parseInt(resp.lastId,10) > parseInt(previousLastId,10)){
          window.localStorage.setItem('chatMessages',JSON.stringify(resp.msgs));
          window.localStorage.setItem('lastChatMessageId',parseInt(resp.lastId,10));
          let msgs = resp.msgs;
          const reversedMsgs = msgs.reverse();
          document.getElementById('chat-app-msg-body').innerHTML = "";

          reversedMsgs.forEach(msg => {
            if(msg.type.toString() === '1'){
              const item = document.createElement('div');
              item.className = "msg-item notice";
              item.innerText = msg.msg;
              document.getElementById('chat-app-msg-body').appendChild(item);
            } else {
              const item = document.createElement('div');
              item.className = "msg-item";
              const iconWrap = document.createElement('div');
              if(msg.user_picture === '' || msg.user_picture === null || msg.user_picture === 'default.png' ){
                const firstInitial = msg.user_fname.substring(0,1);
                iconWrap.className = `initial-wrap initial-${firstInitial.toLowerCase()}`;
                iconWrap.innerText = firstInitial.toUpperCase();
              } else {
                iconWrap.className = "icon-wrap";
                const img = document.createElement('img');
                img.src = `../assets/user_icons/${msg.user_picture}`;
                iconWrap.appendChild(img);
              }
              const msgBody = document.createElement('div');
              const title = document.createElement('div');
              title.className = "title";
              title.innerText = `${msg.user_fname} ${msg.user_lname} ${msg.user_id}`;
              const time = document.createElement('span');
              const formattedDate = Intl.DateTimeFormat('en', {hour:"numeric",month:'short',day:"numeric",minute:"numeric","timeZone":"GMT"}).format(new Date(msg.created_at));
              time.innerText = formattedDate;
              title.appendChild(time);
              const msgDiv = document.createElement('div');
              msgDiv.className = "msg";
              msgDiv.innerText = msg.msg;
              msgBody.appendChild(title);
              msgBody.appendChild(msgDiv);
              item.appendChild(iconWrap);
              item.appendChild(msgBody);
              document.getElementById('chat-app-msg-body').appendChild(item);
            }

          });
          const scrollBody = document.getElementById('chat-app-msg-body');
          scrollBody.scrollTop = scrollBody.scrollHeight;
        }
      }
    }
  });
}

function chatPoll(){
  chatTimer = setInterval(function(){
    //only poll when chat window is open and browser window is in focus
    if(!document.hidden && document.getElementById('chatWindow').classList.contains('show')) {
      retrieveChatMessages();
    }
  },2000);
}

function submitChatMsg(evt){
  evt.preventDefault();
  const msg = document.getElementById('chat-msg-value').value;
  if(!evt.target.classList.contains('disabled') && msg){
    evt.target.classList.add('disabled');
    ajax('usersc/plugins/chat/chat-app/parsers/createChatMessage.php',{msg:msg},(resp) => {
      evt.target.classList.remove('disabled');
      if(resp.success){
        document.getElementById('chat-msg-value').value = "";
        clearInterval(chatTimer);
        retrieveChatMessages();
        chatPoll();
      }
    },'POST');
  }
}

function initChat(){
  window.localStorage.removeItem("lastChatMessageId");
  window.localStorage.removeItem("chatMessages");
  window.localStorage.removeItem("activeChatParticipants");
}

function closeChat(){
  ajax('usersc/plugins/chat/chat-app/parsers/save_session.php',{type:"close"}, (resp)=>{
    document.getElementById('chatWindow').classList.remove('show');
  },'POST');
}

function openChat(){
  initChat();
  ajax('usersc/plugins/chat/chat-app/parsers/save_session.php',{type:"open"}, (resp) => {
    document.getElementById('chatWindow').classList.add('show');
  }, "POST");
}

function submitWithEnter(e){
  if(e.charCode === 13){
    e.preventDefault();
    submitChatMsg(e);
  }
}

function dragHandleClicked(e){
  const el = document.getElementById('chatWindow');
  window.addEventListener('mousemove', dragHandleMove);
  window.addEventListener('mouseup', dragHandleReleased);
  let prevX = e.clientX;
  let prevY = e.clientY;
  const windowWidth = window.innerWidth;
  const windowHeight = window.innerHeight;
  function dragHandleMove(evt){
    let newX = prevX - evt.clientX;
    let newY = prevY - evt.clientY;
    const rect = el.getBoundingClientRect();
    let newLeft = rect.left - newX;
    const newRight = rect.right - newX;
    let newTop = rect.top - newY;
    const newBottom = rect.bottom - newY;
    if(newLeft < 0) newLeft = 0;
    if(newRight > windowWidth){
      newLeft = windowWidth - rect.width;
    }
    if(newTop < 0) newTop = 0;
    if(newBottom > windowHeight) newTop = windowHeight - rect.height;
    el.style.left = `${newLeft}px`;
    el.style.top = `${newTop}px`;
    prevX = evt.clientX;
    prevY = evt.clientY;
  }

  function dragHandleReleased(e){
    window.removeEventListener('mousemove', dragHandleMove);
    window.removeEventListener('mouseup', dragHandleReleased);
  }
}



window.addEventListener('load',function(){
  const picker = new EmojiButton({
    position:'auto', theme: 'light',rootElement:document.getElementById('chatWindow'),
    emojiSize: '24px', showVariants: true
  });
  const trigger = document.getElementById('emoji-trigger');
  const field = document.getElementById('chat-msg-value');

  picker.on('emoji', selection => {
    field.value += selection;
  })

  trigger.addEventListener('click', () => {
    picker.pickerVisible ? picker.hidePicker() : picker.showPicker(field);
  })

  initChat();
  chatPoll();
  document.getElementById('addMsgBtn').addEventListener('click', submitChatMsg);
  document.getElementById('toggleChatWindowBtn').addEventListener('click', (evt) => {
    evt.preventDefault();
    if(document.getElementById('chatWindow').classList.contains("show")){
      closeChat();
    } else {
      openChat();
    }
  });
  document.getElementById('chat-msg-value').addEventListener('focus', function(){
    addEventListener('keypress', submitWithEnter);
  });

  document.getElementById('chat-msg-value').addEventListener('blur',function(){
    removeEventListener('keypress', submitWithEnter)
  });

  const dragHandle = document.getElementById('chat-window-drag-handle');
  dragHandle.addEventListener('mousedown', dragHandleClicked);
});
