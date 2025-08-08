// === OpenRouter AI Response with PBRA Context ===
async function getAIResponse(userMessage) {
  const response = await fetch("", {
    method: "POST",
    headers: {
      "Authorization": "",
      "Content-Type": "application/json",
      "HTTP-Referer": "http://localhost",
      "X-Title": "PBRA Support Assistant"
    },
    body: JSON.stringify({
      model: "openchat/openchat-3.5-0106",
      messages: [
        {
          role: "system",
          content: `You are a helpful assistant for the PBRA website, a staff portal at Politeknik Brunei. Respond based on how PBRA works, including role-based resources, announcements, task logs, and more.`
        },
        {
          role: "user",
          content: userMessage
        }
      ]
    })
  });

  if (!response.ok) {
    const errorText = await response.text();
    console.error("❌ AI API error:", response.status, errorText);
    throw new Error("Failed to fetch AI reply");
  }

  const data = await response.json();
  return data.choices[0].message.content;
}

document.addEventListener("DOMContentLoaded", () => {
  const chatBox = document.querySelector(".chat-messages");
  const userInput = document.getElementById("user-input");
  const chatForm = document.getElementById("chat-form");
  const attachBtn = document.querySelector(".attach-btn");
  const fileUpload = document.getElementById("file-upload");
  const historyList = document.querySelector(".history-box ul");

  const userKey = `chatHistory_user_${window.currentUserId}`;

  loadChatSidebar();

  chatForm.addEventListener("submit", function (e) {
    e.preventDefault();
    const msg = userInput.value.trim();
    if (msg === "") return;

    appendMessage("user", msg);
    saveToHistory("You", msg);

    userInput.value = "";

    const botBubble = appendMessage("bot", "Thinking...");

    getAIResponse(msg).then(reply => {
      botBubble.textContent = reply;
      saveToHistory("PBRA Bot", reply);
    }).catch(() => {
      botBubble.textContent = "Sorry, something went wrong.";
    });
  });

  function appendMessage(sender, msg) {
    const msgDiv = document.createElement("div");
    msgDiv.classList.add("message", sender === "user" ? "user-message" : "bot-message");
    msgDiv.textContent = msg;
    chatBox.appendChild(msgDiv);
    chatBox.scrollTop = chatBox.scrollHeight;
    return msgDiv;
  }

  function saveToHistory(sender, message) {
    const history = JSON.parse(localStorage.getItem(userKey) || "[]");
    const now = new Date();
    history.push({ sender, message, timestamp: now.toISOString() });
    localStorage.setItem(userKey, JSON.stringify(history));
    loadChatSidebar();
  }

  function loadChatSidebar() {
    const history = JSON.parse(localStorage.getItem(userKey) || "[]");
    const sessions = groupSessions(history);

    historyList.innerHTML = "";
    sessions.forEach(session => {
      const title = session.messages.find(m => m.sender === "You")?.message || "Chat Session";
      const li = document.createElement("li");
      li.textContent = `[${session.date}] ${title.substring(0, 30)}...`;
      li.style.cursor = "pointer";
      li.addEventListener("click", () => {
        chatBox.innerHTML = "";
        session.messages.forEach(msg => {
          appendMessage(msg.sender === "You" ? "user" : "bot", msg.message);
        });
      });
      historyList.appendChild(li);
    });
  }

  function groupSessions(history) {
    const sessions = [];
    let currentSession = [];
    let lastTime = null;
    const gap = 1000 * 60 * 10;

    history.forEach(item => {
      const time = new Date(item.timestamp);
      if (!lastTime || time - lastTime <= gap) {
        currentSession.push(item);
      } else {
        sessions.push({ messages: currentSession, date: new Date(currentSession[0].timestamp).toLocaleDateString() });
        currentSession = [item];
      }
      lastTime = time;
    });

    if (currentSession.length > 0) {
      sessions.push({ messages: currentSession, date: new Date(currentSession[0].timestamp).toLocaleDateString() });
    }

    return sessions.reverse();
  }

  attachBtn.addEventListener("click", () => fileUpload.click());
  fileUpload.addEventListener("change", e => {
    if (e.target.files.length > 0) {
      alert("📎 File selected: " + e.target.files[0].name);
    }
  });
});
// Fetch breadcrumbs from sessionStorage
let breadcrumbs = JSON.parse(sessionStorage.getItem('breadcrumbs')) || [];

// Get the current page name dynamically based on the current URL path
let currentPageUrl = window.location.pathname;

// Define page names based on the URL path
let currentPageName = '';
if (currentPageUrl.includes('homepage.php')) {
    currentPageName = 'Homepage';
} else if (currentPageUrl.includes('calendar.php')) {
    currentPageName = 'Calendar';
} else if (currentPageUrl.includes('distributetask.php')) {
    currentPageName = 'Distribute Task';
} else if (currentPageUrl.includes('events.php')) {
    currentPageName = ' Events';
  } else if (currentPageUrl.includes('feedback.php')) {
    currentPageName = 'Feedback ';
  } else if (currentPageUrl.includes('mail.php')) {
    currentPageName = ' Mail';
  } else if (currentPageUrl.includes('myrole.php')) {
    currentPageName = 'My Role ';
  } else if (currentPageUrl.includes('profile.php')) {
    currentPageName = 'Profile ';
  } else if (currentPageUrl.includes('report.php')) {
    currentPageName = 'Report ';
  } else if (currentPageUrl.includes('roles.php')) {
    currentPageName = 'Roles';
  } else if (currentPageUrl.includes('schedule.php')) {
    currentPageName = 'Schedule';
  } else if (currentPageUrl.includes('staff.php')) {
    currentPageName = 'Staff';
  } else if (currentPageUrl.includes('usersupport.php')) {
    currentPageName = 'usersupport';
} else {
    currentPageName = 'Unknown Page'; // Default if no match
}

// Check if this page is already in the breadcrumb trail
let pageExists = breadcrumbs.some(breadcrumb => breadcrumb.url === currentPageUrl);

// If the page isn't already in the breadcrumb trail, add it
if (!pageExists) {
    breadcrumbs.push({ name: currentPageName, url: currentPageUrl });
    sessionStorage.setItem('breadcrumbs', JSON.stringify(breadcrumbs));
}

// Render the breadcrumb list
let breadcrumbList = document.getElementById('breadcrumb-list');
breadcrumbList.innerHTML = '';  // Clear any existing breadcrumbs

// Loop through the breadcrumbs and render them with separators
breadcrumbs.forEach((breadcrumb, index) => {
    let breadcrumbItem = document.createElement('li');
    let link = document.createElement('a');
    
    link.href = breadcrumb.url;
    link.textContent = breadcrumb.name;

    // When a breadcrumb is clicked, we go back to that page and remove all breadcrumbs after it
    link.addEventListener('click', function (event) {
        event.preventDefault(); // Prevent default navigation
        let clickedIndex = index;
        
        // Update the breadcrumb trail by trimming after the clicked breadcrumb
        breadcrumbs = breadcrumbs.slice(0, clickedIndex + 1);
        sessionStorage.setItem('breadcrumbs', JSON.stringify(breadcrumbs));
        
        // Reload the page to reflect the updated breadcrumbs
        window.location.href = breadcrumb.url;
    });

    breadcrumbItem.appendChild(link);
    breadcrumbList.appendChild(breadcrumbItem);

    // Only add the separator if it's not the last breadcrumb item
    if (index < breadcrumbs.length - 1) {
        let separator = document.createElement('span');
        separator.textContent = ' > ';
        breadcrumbList.appendChild(separator);
    }
});

