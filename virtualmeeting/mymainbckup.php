<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Virtual Meeting</title>
  <link rel="stylesheet" href="virtualmeeting.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
</head>



<body onload="fetchNotifications()">
  <?php include '../includes/navbar.php'; ?>

  <div class="page-title">
    <h1 style="font-size: 30px;">Virtual Meeting</h1>
  </div>

  <div class="breadcrumb">
    <ul id="breadcrumb-list"></ul>
  </div>

  <div class="virtual-carousel">
    <div class="virtual-header">
      <div class="title">
        <h1 style="font-size: 25px;">My Meeting</h1>
      </div>
      <button class="add-virtual-toggle" onclick="openModal()">+ Create Meeting</button>
    </div>
  </div>

  <div id="meetingsContainer"></div>

  <!-- Create Meeting Modal -->
  <div id="meetingModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closeModal()">&times;</span>

      <!-- Calendar -->
      <div class="calendar">
        <div class="calendar-header">
          <button class="nav-btn" onclick="changeMonth(-1)">&#8592;</button>
          <h3 id="monthYear"></h3>
          <button class="nav-btn" onclick="changeMonth(1)">&#8594;</button>
        </div>
        <div class="calendar-grid" id="calendarGrid"></div>
      </div>

      <!-- Event Form -->
      <div class="form-container">
        <h2>Create Meeting</h2>
        <form id="meetingForm">
          <label>Invite Participants:</label>
          <div class="search-wrapper" style="position: relative;">
            <input type="text" id="searchInput" placeholder="Search participants...">
            <div class="search-results" id="searchResults"></div>
          </div>
          <h3>Invited Participants:</h3>
          <ul id="invitedList"></ul>

          <label>Date:</label>
          <input type="text" id="eventDate" readonly>

          <label>Start Time:</label>
          <input type="time" id="startTime">

          <label>End Time:</label>
          <input type="time" id="endTime">

          <label>Title:</label>
          <input type="text" id="title">

          <label>Agenda:</label>
          <textarea id="agenda"></textarea>

          <div class="form-buttons">
            <button type="submit" class="btn-save">Save</button>
            <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>


  <!-- Join Meeting Modal -->
  <div id="joinMeetingModal" class="modal">
    <div class="modal-content">
      <!-- Participants area -->
      <div class="participants">
        <div class="active-speaker participant-tile">
          <video autoplay playsinline></video>
          <div class="label" id="localUserLabel"></div>
        </div>
        <div id="remoteContainer-join" class="participants"></div>
      </div>

      <!-- Toolbar -->
      <div class="toolbar">
        <button class="ctrl" onclick="toggleMic()">
          <i class="fas fa-microphone"></i>
          <span>Audio</span>
        </button>
        <button class="ctrl" onclick="toggleCamera()">
          <i class="fas fa-video"></i>
          <span>Video</span>
        </button>
        <button class="ctrl" onclick="openChat(currentMeetingId)">
          <i class="fas fa-comment"></i>
          <span>Chat</span>
        </button>
        <button class="ctrl" onclick="startScreenShare()">
          <i class="fas fa-desktop"></i>
          <span>Share</span>
        </button>
        <button id="recordBtn2" class="ctrl" onclick="toggleRecording()">
          <i class="fas fa-circle"></i>
          <span>Record</span>
        </button>
        <button class="ctrl end" onclick="closeModal('joinMeetingModal')">
          <i class="fas fa-sign-out-alt"></i>
          <span>Leave</span>
        </button>
      </div>

      <!-- Chat Panel -->
      <div id="chatPanel" class="chat-panel">
        <div id="chatBox" class="chat-box"></div>
        <input id="chatInput" type="text" placeholder="Type a message...">
        <button onclick="sendChat(currentMeetingId)">Send</button>
      </div>
    </div>
  </div>

  <!-- Meeting Modal For Testing -->
  <div id="testMeetingModal" class="modal">
    <div class="modal-content">
      <!-- Participants area -->
      <div id="remoteContainer-test" class="participants">
        <div class="active-speaker participant-tile">
          <video autoplay playsinline></video>
          <div class="label" id="localUserLabel"></div>
        </div>
      </div>

      <!-- Toolbar -->
      <div class="toolbar">
        <button class="ctrl" onclick="toggleMic()">
          <i class="fas fa-microphone"></i>
          <span>Audio</span>
        </button>
        <button class="ctrl" onclick="toggleCamera()">
          <i class="fas fa-video"></i>
          <span>Video</span>
        </button>
        <button class="ctrl" onclick="openChat('test')">
          <i class="fas fa-comment"></i>
          <span>Chat</span>
        </button>
        <button class="ctrl" onclick="startScreenShare()">
          <i class="fas fa-desktop"></i>
          <span>Share</span>
        </button>
        <button id="recordBtn" class="ctrl" onclick="toggleRecording()">
          <i class="fas fa-circle"></i>
          <span>Record</span>
        </button>
        <button class="ctrl end" onclick="closeModal('testMeetingModal')">
          <i class="fas fa-sign-out-alt"></i>
          <span>End</span>
        </button>
      </div>

      <!-- Chat Panel -->
      <div id="chatPanel-test" class="chat-panel">
        <div id="chatBox-test" class="chat-box"></div>
        <input id="chatInput-test" type="text" placeholder="Type a message...">
        <button onclick="sendChat('test')">Send</button>
      </div>
    </div>
  </div>


  <!-- Edit Meeting Modal -->
  <div id="editMeetingModal" class="modal" style="display:none;">
    <div class="modal-content">
      <span onclick="closeEditMeetingModal()" class="close">&times;</span>
      <h2>Edit Meeting</h2>

      <label>Title:</label>
      <input type="text" id="editTitle">

      <label>Agenda:</label>
      <textarea id="editAgenda"></textarea>

      <label>Date:</label>
      <input type="date" id="editDate">

      <label>Start Time:</label>
      <input type="time" id="editStartTime">

      <label>End Time:</label>
      <input type="time" id="editEndTime">

      <h3>Participants</h3>
      <div id="editInvitedParticipants"></div>

      <button onclick="updateMeeting()">Save Changes</button>
      <button onclick="closeEditMeetingModal()">Cancel</button>
    </div>
  </div>

  <!-- Delete Confirmation -->
  <div id="deleteConfirmBox" class="popup-overlay" style="display: none;">
    <div class="popup-content">
      <h3>Are you sure you want to delete?</h3>
      <div class="popup-actions">
        <button id="deleteYes" class="btn-yes">Yes</button>
        <button id="deleteNo" class="btn-no">No</button>
      </div>
    </div>
  </div>

  <!-- Microphone Notification -->
  <div id="toast"></div>

  <!-- Save Confirmation -->
  <div id="confirmPopup" class="popup-overlay" style="display:none;">
    <div class="popup-content">
      <h3>Are you sure you want to save this meeting?</h3>
      <div class="popup-actions">
        <button id="confirmYes" class="btn-yes">Yes</button>
        <button id="confirmNo" class="btn-no">No</button>
      </div>
    </div>
  </div>

  <!-- Success Notification -->
  <div id="successPopup" class="popup-overlay" style="display: none;">
    <div class="popup-content">
      <h3 id="successMessage">Meeting has been successfully deleted!</h3>
      <div class="popup-actions">
        <button id="successOk" class="btn-yes">OK</button>
      </div>
    </div>
  </div>

  <div id="saveConfirmBox" class="popup-overlay" style="display:none;">
    <div class="popup-content">
      <h3 id="saveMessage">Meeting created successfully!</h3>
      <div class="popup-actions">
        <button id="saveOk" class="btn-yes">OK</button>
      </div>
    </div>
  </div>

  <script>
    // Calendar
    let currentMonth = new Date().getMonth();
    let currentYear = new Date().getFullYear();

    function renderCalendar(month, year) {
      const monthYear = document.getElementById("monthYear");
      const grid = document.getElementById("calendarGrid");
      grid.innerHTML = "";
      monthYear.innerText = new Date(year, month).toLocaleString("default", {
        month: "long",
        year: "numeric"
      });

      const firstDay = new Date(year, month, 1).getDay();
      const daysInMonth = new Date(year, month + 1, 0).getDate();

      for (let i = 0; i < firstDay; i++) grid.innerHTML += `<div></div>`;

      for (let d = 1; d <= daysInMonth; d++) {
        let dayElem = document.createElement("div");
        dayElem.classList.add("day");
        dayElem.textContent = d;

        let today = new Date();
        let thisDate = new Date(year, month, d);

        if (thisDate < new Date(today.getFullYear(), today.getMonth(), today.getDate())) {
          dayElem.classList.add("disabled");
        } else {
          dayElem.onclick = () => {
            document.getElementById("eventDate").value = `${String(month+1).padStart(2,"0")}/${String(d).padStart(2,"0")}/${year}`;
            document.querySelectorAll(".day").forEach(el => el.classList.remove("selected"));
            dayElem.classList.add("selected");
          };
        }
        grid.appendChild(dayElem);
      }
    }

    function changeMonth(delta) {
      currentMonth += delta;
      if (currentMonth < 0) {
        currentMonth = 11;
        currentYear--;
      }
      if (currentMonth > 11) {
        currentMonth = 0;
        currentYear++;
      }
      renderCalendar(currentMonth, currentYear);
    }

    renderCalendar(currentMonth, currentYear);

    // Participants Search
    let invitedParticipants = [];

    document.getElementById("searchInput").addEventListener("input", function() {
      let query = this.value;
      let resultsDiv = document.getElementById("searchResults");
      if (query.length > 0) {
        fetch("search_participants.php?q=" + encodeURIComponent(query))
          .then(res => res.json())
          .then(data => {
            resultsDiv.innerHTML = "";
            if (data.length > 0) {
              data.forEach(item => {
                let div = document.createElement("div");
                div.classList.add("search-item");
                div.textContent = item.name + " (" + item.email + ")";
                div.onclick = () => {
                  addToInvited(item);
                  resultsDiv.innerHTML = "";
                  resultsDiv.style.display = "none";
                  document.getElementById("searchInput").value = "";
                };
                resultsDiv.appendChild(div);
              });
              resultsDiv.style.display = "block";
            } else resultsDiv.style.display = "none";
          });
      } else {
        resultsDiv.innerHTML = "";
        resultsDiv.style.display = "none";
      }
    });

    function addToInvited(item) {
      let invitedList = document.getElementById("invitedList");
      if (document.getElementById("invited-" + item.user_id)) {
        alert(item.name + " is already invited.");
        return;
      }

      let li = document.createElement("li");
      li.id = "invited-" + item.user_id;
      li.textContent = item.name + " (" + item.email + ") ";
      let removeBtn = document.createElement("button");
      removeBtn.textContent = "Remove";
      removeBtn.style.marginLeft = "10px";
      removeBtn.style.cursor = "pointer";
      removeBtn.onclick = () => li.remove();
      li.appendChild(removeBtn);
      invitedList.appendChild(li);
    }

    // Create Meeting 
    document.getElementById("meetingForm").addEventListener("submit", function(e) {
      e.preventDefault();
      document.getElementById("confirmPopup").style.display = "flex";
    });

    document.getElementById("confirmYes").onclick = function() {
      document.getElementById("confirmPopup").style.display = "none";

      let invited = [];
      document.querySelectorAll("#invitedList li").forEach(li => invited.push(li.id.replace("invited-", "")));

      let rawDate = document.getElementById("eventDate").value;
      let parts = rawDate.split("/");
      let formattedDate = `${parts[2]}-${parts[0]}-${parts[1]}`;

      let data = {
        title: document.getElementById("title").value,
        agenda: document.getElementById("agenda").value,
        meeting_date: formattedDate,
        start_time: document.getElementById("startTime").value,
        end_time: document.getElementById("endTime").value,
        invited: invited
      };

      fetch("save_meeting.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json"
          },
          body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(response => {
          document.getElementById("saveMessage").innerText = response.success ? "Meeting created successfully!" : "Error: " + response.message;
          document.getElementById("saveConfirmBox").style.display = "flex";
          if (response.success) {
            closeModal();
            document.getElementById("meetingForm").reset();
            document.getElementById("invitedList").innerHTML = "";
            loadMeetings();
          }
        }).catch(err => console.error(err));
    };

    document.getElementById("saveOk").onclick = function() {
      document.getElementById("saveConfirmBox").style.display = "none";
    };

    // Edit Meeting 
    let currentMeetingId = null;

    function openEditMeeting(meeting) {
      currentMeetingId = meeting.meeting_id;
      document.getElementById("editTitle").value = meeting.title;
      document.getElementById("editAgenda").value = meeting.agenda;
      document.getElementById("editDate").value = meeting.meeting_date;
      document.getElementById("editStartTime").value = meeting.start_time;
      document.getElementById("editEndTime").value = meeting.end_time;

      invitedParticipants = [...meeting.participants];
      renderInvitedParticipants("editInvitedParticipants");

      document.getElementById("editMeetingModal").style.display = "block";
    }

    function renderInvitedParticipants(containerId) {
      const container = document.getElementById(containerId);
      container.innerHTML = "";
      invitedParticipants.forEach(id => {
        let span = document.createElement("span");
        span.textContent = id;
        container.appendChild(span);
      });
    }

    function updateMeeting() {
      const updatedData = {
        meeting_id: currentMeetingId,
        title: document.getElementById("editTitle").value,
        agenda: document.getElementById("editAgenda").value,
        date: document.getElementById("editDate").value,
        start_time: document.getElementById("editStartTime").value,
        end_time: document.getElementById("editEndTime").value,
        participants: invitedParticipants
      };

      fetch("update_meeting.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json"
          },
          body: JSON.stringify(updatedData)
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            alert("Meeting updated successfully!");
            closeEditMeetingModal();
            loadMeetings();
          } else {
            alert("Update failed: " + data.message);
          }
        });
    }

    function closeEditMeetingModal() {
      document.getElementById("editMeetingModal").style.display = "none";
    }

    const currentUserId = <?php echo $_SESSION['id']; ?>;
    const currentUsername = "<?php echo htmlspecialchars($_SESSION['full_name']); ?>";

    // Load Meetings 
    function loadMeetings() {
      fetch("get_meetings.php")
        .then(res => res.json())
        .then(response => {
          if (!response.success) {
            alert("Error loading meetings: " + response.message);
            return;
          }
          let container = document.getElementById("meetingsContainer");
          container.innerHTML = "";
          response.data.forEach(meeting => {
            let participants = meeting.participants.map(p => p.name).join(", ");
            let dateObj = new Date(meeting.meeting_date);
            let formattedDate = dateObj.toLocaleDateString("en-GB", {
              day: "2-digit",
              month: "long",
              year: "numeric"
            });

            function formatTime(time) {
              let [h, m] = time.split(":");
              h = parseInt(h);
              let ampm = h >= 12 ? "PM" : "AM";
              h = h % 12 || 12;
              return h + ":" + m + " " + ampm;
            }
            let formattedStart = formatTime(meeting.start_time),
              formattedEnd = formatTime(meeting.end_time);

            function calculateDuration(start, end) {
              let [sh, sm] = start.split(":").map(Number);
              let [eh, em] = end.split(":").map(Number);
              let diff = (eh * 60 + em) - (sh * 60 + sm);
              if (diff < 0) diff += 1440;
              return Math.floor(diff / 60) + " hr " + (diff % 60) + " min";
            }
            let estimatedTime = calculateDuration(meeting.start_time, meeting.end_time);

            container.innerHTML += `
            <div class="meeting-card">
              <h2 class="meeting-title">${meeting.title}</h2>
              <h4 class="meeting-agenda">${meeting.agenda}</h4>
              <table class="meeting-info">
                <tr><td><strong>Date:</strong></td><td>${formattedDate}</td></tr>
                <tr><td><strong>Start Time:</strong></td><td>${formattedStart}</td></tr>
                <tr><td><strong>End Time:</strong></td><td>${formattedEnd}</td></tr>
                <tr><td><strong>Estimated Time:</strong></td><td>${estimatedTime}</td></tr>
                <tr><td><strong>Participants:</strong></td><td>${participants}</td></tr>
                <tr><td><strong>Status:</strong></td><td><span class="status scheduled">Scheduled</span></td></tr>
                <tr><td><strong>Created By:</strong></td><td>${meeting.created_by}</td></tr>
              </table>
              <div class="meeting-actions">
                <button class="btn delete" data-id ="${meeting.meeting_id}">Delete</button>
                <button class="btn edit" onclick='openEditMeeting(${JSON.stringify(meeting)})'>Edit</button>
                <button class="btn test" onclick="openTestMeeting()">Test</button>
                <button class="btn join" onclick='openJoinMeeting(${JSON.stringify(meeting)})'>Join Meeting</button>
              </div>
            </div>`;
          });
        }).catch(err => console.error(err));
    }

    loadMeetings();

    // Delete Meeting
    let meetingToDelete = null,
      cardToDelete = null;
    document.addEventListener("click", function(e) {
      if (e.target.classList.contains("delete")) {
        meetingToDelete = e.target.getAttribute("data-id");
        cardToDelete = e.target.closest(".meeting-card");
        document.getElementById("deleteConfirmBox").style.display = "flex";
      }
    });

    document.getElementById("deleteYes").onclick = function() {
      document.getElementById("deleteConfirmBox").style.display = "none";
      if (!meetingToDelete) return;
      fetch("function_meeting.php?action=delete&id=" + meetingToDelete, {
          method: "GET"
        })
        .then(res => res.text())
        .then(data => {
          document.getElementById("successMessage").innerText = data;
          document.getElementById("successPopup").style.display = "flex";
          if (cardToDelete) cardToDelete.remove();
          meetingToDelete = null;
          cardToDelete = null;
        })
        .catch(err => console.error("Error:", err));
    };

    document.getElementById("deleteNo").onclick = function() {
      document.getElementById("deleteConfirmBox").style.display = "none";
      meetingToDelete = null;
      cardToDelete = null;
    };
    document.getElementById("successOk").onclick = function() {
      document.getElementById("successPopup").style.display = "none";
    };

    // Add Remote Participant Video
    function addRemoteParticipant(userId, userName, stream) {
      const container = currentModal === "test" ? document.getElementById("remoteContainer-test") : document.getElementById("remoteContainer-join");
      if (document.getElementById("participant-" + userId)) return;

      const div = document.createElement("div");
      div.classList.add("participant-tile");
      div.id = "participant-" + userId;

      const video = document.createElement("video");
      video.autoplay = true;
      video.playsInline = true;
      if (stream) video.srcObject = stream;

      const label = document.createElement("div");
      label.classList.add("label");
      label.textContent = userName;

      div.appendChild(video);
      div.appendChild(label);
      container.appendChild(div);
    }

    // Function to join the meeting
    async function openJoinMeeting(meeting) {
      currentModal = "join";
      currentMeetingId = meeting.meeting_id;
      const userId = parseInt(currentUserId);
      const creatorId = parseInt(meeting.created_by_id);

      // Check if user is invited
      if (userId !== creatorId && !meeting.participants.some(p => parseInt(p.id) === userId)) {
        alert("You are not invited to this meeting.");
        return;
      }

      document.getElementById("joinMeetingModal").style.display = "block";

      // Get local camera + mic
      localStream = await navigator.mediaDevices.getUserMedia({
        video: true,
        audio: true
      });
      const myVideo = document.querySelector("#joinMeetingModal .active-speaker video");
      myVideo.srcObject = localStream;

      // Update label
      document.getElementById("localUserLabel").textContent = currentUsername + " (You)";

      // Create peers for existing participants
      const container = document.getElementById("remoteContainer-join");
      container.innerHTML = ""; // clear old
      meeting.participants.forEach(p => {
        if (parseInt(p.id) !== parseInt(currentUserId)) createPeer(p.id);
      });

      // Notify others via signaling server
      signalingSendJoin(currentMeetingId, currentUserId);
    }

    // Create Peer Connection
    function createPeer(remoteUserId) {
      if (peers[remoteUserId]) return peers[remoteUserId];

      const pc = new RTCPeerConnection(config);

      // Add local tracks
      localStream.getTracks().forEach(track => pc.addTrack(track, localStream));

      // Remote track
      pc.ontrack = (event) => {
        addRemoteParticipant(remoteUserId, "User " + remoteUserId, event.streams[0]);
      };

      // ICE candidate
      pc.onicecandidate = (event) => {
        if (event.candidate) signalingSendCandidate(remoteUserId, event.candidate);
      };

      peers[remoteUserId] = pc;
      return pc;
    }


    // Virtual Meeting Test
    let localStream = null;
    let screenStream = null;
    let peers = {}; // Peer connections
    let recording = false;
    let recorder = null;
    let recordedChunks = [];
    let currentModal = null; // "test" or "join"

    // STUN server config
    const config = {
      iceServers: [{
        urls: "stun:stun.l.google.com:19302"
      }]
    };

    // Open modal
    async function openTestMeeting(meeting) {
      currentModal = "test";
      currentMeetingId = meeting?.meeting_id || null;
      document.getElementById("testMeetingModal").style.display = "block";

      // Get local camera + mic
      localStream = await navigator.mediaDevices.getUserMedia({
        video: true,
        audio: true
      });
      const myVideo = document.querySelector("#testMeetingModal .active-speaker video");
      myVideo.srcObject = localStream;

      // Update label
      document.getElementById("localUserLabel").textContent = currentUsername + " (You)";

      // If there are participants already, create peers
      meeting?.participants.forEach(p => {
        if (parseInt(p.id) !== parseInt(currentUserId)) createPeer(p.id);
      });

      // Notify others via signaling server (placeholder)
      signalingSendJoin(currentMeetingId, currentUserId);
    }

    // Show toast message
    function showToast(message, duration = 1500) {
      const toast = document.getElementById('toast');
      toast.textContent = message;
      toast.classList.add('show');

      setTimeout(() => {
        toast.classList.remove('show');
      }, duration);
    }

    // Toggle Mic
    function toggleMic() {
      const track = localStream.getAudioTracks()[0];
      track.enabled = !track.enabled;
      showToast(track.enabled ? "Microphone On" : "Microphone Off");
    }


    // Toggle Camera
    function toggleCamera() {
      const track = localStream.getVideoTracks()[0];
      track.enabled = !track.enabled;
      showToast(track.enabled ? "Camera On" : "Camera Off");
    }


    // Screen Share
    async function startScreenShare() {
      if (!localStream) return;
      try {
        screenStream = await navigator.mediaDevices.getDisplayMedia({
          video: true
        });
        const screenTrack = screenStream.getVideoTracks()[0];

        // Replace track for all peers
        for (let peerId in peers) {
          const sender = peers[peerId].getSenders().find(s => s.track.kind === "video");
          if (sender) sender.replaceTrack(screenTrack);
        }

        // Update local video
        const myVideo = document.querySelector(`#${currentModal}MeetingModal .active-speaker video`);
        myVideo.srcObject = screenStream;

        screenTrack.onended = () => stopScreenShare();
      } catch (err) {
        alert("Screen share error: " + err);
      }
    }

    function stopScreenShare() {
      const videoTrack = localStream.getVideoTracks()[0];
      for (let peerId in peers) {
        const sender = peers[peerId].getSenders().find(s => s.track.kind === "video");
        if (sender) sender.replaceTrack(videoTrack);
      }
      const myVideo = document.querySelector(`#${currentModal}MeetingModal .active-speaker video`);
      myVideo.srcObject = localStream;

      if (screenStream) {
        screenStream.getTracks().forEach(track => track.stop());
        screenStream = null;
      }
    }


    // Recording
    async function toggleRecording() {
      const btn = currentModal === "test" ? document.getElementById("recordBtn") : document.getElementById("recordBtn2");

      if (!recording) {
        recorder = new MediaRecorder(localStream);
        recordedChunks = [];

        recorder.ondataavailable = e => {
          if (e.data.size > 0) recordedChunks.push(e.data);
        };

        recorder.start();
        btn.innerHTML = '<i class="fas fa-stop"></i><span>Stop</span>';
        recording = true;

      } else {
        recorder.stop();
        btn.innerHTML = '<i class="fas fa-circle"></i><span>Record</span>';
        recording = false;

        recorder.onstop = async () => {
          const blob = new Blob(recordedChunks, {
            type: "video/webm"
          });
          const url = URL.createObjectURL(blob);
          const a = document.createElement("a");
          a.href = url;
          a.download = `meeting_recording_${Date.now()}.webm`;
          a.click();
          showToast("Recording saved!");
        };
      }
    }

    // Placeholder Signaling
    function signalingSendJoin(meetingId, userId) {
      // Implement via WebSocket
      console.log("User joined:", meetingId, userId);
    }

    function signalingSendCandidate(remoteUserId, candidate) {
      // Implement via WebSocket
      console.log("Send ICE candidate to", remoteUserId, candidate);
    }

    // Chat
    async function openChat(meetingId) {
      const panel = document.getElementById("chatPanel");
      panel.classList.toggle("open");

      if (panel.classList.contains("open")) {
        loadChat(meetingId);
        if (!panel.dataset.intervalSet) {
          setInterval(() => loadChat(meetingId), 1000);
          panel.dataset.intervalSet = true;
        }
      }
    }

    async function sendChat(meetingId) {
      const input = document.getElementById("chatInput");
      const message = input.value.trim();
      if (!message) return;

      await fetch("save_chat.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json"
        },
        body: JSON.stringify({
          meeting_id: meetingId,
          message
        })
      });

      input.value = "";
      loadChat(meetingId);
    }

    async function loadChat(meetingId) {
      const chatBox = document.getElementById("chatBox");
      const res = await fetch("get_chat.php?meeting_id=" + meetingId);
      const data = await res.json();

      if (data.success) {
        chatBox.innerHTML = "";
        data.messages.forEach(msg => {
          const msgEl = document.createElement("div");
          msgEl.textContent = msg.full_name + ": " + msg.message;
          chatBox.appendChild(msgEl);
        });
        chatBox.scrollTop = chatBox.scrollHeight;
      }
    }

    // Close modal
    function closeModal(id) {
      const modal = document.getElementById(id);
      if (modal) {
        modal.style.display = "none";
      }
      // stop streams if available
      if (localStream) localStream.getTracks().forEach(track => track.stop());
      if (screenStream) screenStream.getTracks().forEach(track => track.stop());
    }

    // Breadcrumbs
    let breadcrumbs = JSON.parse(sessionStorage.getItem('breadcrumbs')) || [];
    let currentPageUrl = window.location.pathname;
    let currentPageName = document.title.trim();
    if (!breadcrumbs.some(b => b.url === currentPageUrl)) {
      breadcrumbs.push({
        name: currentPageName,
        url: currentPageUrl
      });
      sessionStorage.setItem('breadcrumbs', JSON.stringify(breadcrumbs));
    }

    let breadcrumbList = document.getElementById("breadcrumb-list");
    breadcrumbList.innerHTML = breadcrumbs.map(b => `<li><a href="${b.url}">${b.name}</a></li>`).join(" > ");

    // Modal Helpers 
    function openModal() {
      document.getElementById("meetingModal").style.display = "block";
    }
  </script>
</body>

</html>