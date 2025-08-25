// ==================== HOMEPAGE JAVASCRIPT FUNCTIONALITY ====================

// Text formatting and modal functions
function formatText(command) {
    document.execCommand(command, false, null);
}

function prepareSubmission() {
    const richContent = document.getElementById('richContent').innerHTML;
    document.getElementById('hiddenContent').value = richContent;
}

function openModal() {
    document.getElementById('announcementModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('announcementModal').style.display = 'none';
}

function previewImage(event) {
    const reader = new FileReader();
    reader.onload = function() {
        const output = document.getElementById('imagePreview');
        output.src = reader.result;
        output.style.display = 'block';
    };
    reader.readAsDataURL(event.target.files[0]);
}

// Initialize event listeners when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('openFormBtn')?.addEventListener('click', openModal);
});

// ==================== BREADCRUMB FUNCTIONALITY ====================

// Breadcrumbs management
let breadcrumbs = JSON.parse(sessionStorage.getItem('breadcrumbs')) || [];
let currentPageName = "Homepage";
let currentPageUrl = window.location.pathname;

if (!breadcrumbs.some(breadcrumb => breadcrumb.url === currentPageUrl)) {
    breadcrumbs.push({
        name: currentPageName,
        url: currentPageUrl
    });
    sessionStorage.setItem('breadcrumbs', JSON.stringify(breadcrumbs));
}

let breadcrumbList = document.getElementById('breadcrumb-list');
if (breadcrumbList) {
    breadcrumbList.innerHTML = '';
    breadcrumbs.forEach((breadcrumb, index) => {
        let breadcrumbItem = document.createElement('li');
        let link = document.createElement('a');
        link.href = breadcrumb.url;
        link.textContent = breadcrumb.name;
        breadcrumbItem.appendChild(link);
        breadcrumbList.appendChild(breadcrumbItem);

        if (index < breadcrumbs.length - 1) {
            let separator = document.createElement('span');
            separator.textContent = ' > ';
            breadcrumbList.appendChild(separator);
        }
    });
}

// ==================== CALENDAR FUNCTIONALITY ====================

// Calendar variables and functions
const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
let currentDate = new Date();

// Enhanced calendar generation with event highlighting
function generateCalendar() {
    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();

    document.getElementById('currentMonth').textContent = `${months[month]} ${year}`;

    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const daysInMonth = lastDay.getDate();
    const startingDay = firstDay.getDay();

    // Get saved events to check for highlights
    const savedEvents = JSON.parse(localStorage.getItem('calendarEvents') || '[]');

    // Create array of dates that have events for this month
    const datesWithEvents = savedEvents.filter(event => {
        const eventDate = new Date(event.date);
        return eventDate.getMonth() === month && eventDate.getFullYear() === year;
    }).map(event => new Date(event.date).getDate());

    let calendarHTML = `
        <div class="day-header">Sun</div>
        <div class="day-header">Mon</div>
        <div class="day-header">Tue</div>
        <div class="day-header">Wed</div>
        <div class="day-header">Thu</div>
        <div class="day-header">Fri</div>
        <div class="day-header">Sat</div>
    `;

    for (let i = 0; i < startingDay; i++) {
        calendarHTML += '<div class="day-cell empty"></div>';
    }

    for (let day = 1; day <= daysInMonth; day++) {
        const isToday = new Date().toDateString() === new Date(year, month, day).toDateString();
        const hasEvent = datesWithEvents.includes(day);

        // Add has-event class if there's an event on this day
        calendarHTML += `
            <div class="day-cell ${isToday ? 'today' : ''} ${hasEvent ? 'has-event' : ''}" data-date="${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}">${day}</div>
        `;
    }

    document.getElementById('calendarGrid').innerHTML = calendarHTML;

    // Add click event for showing events when clicking on a day
    document.querySelectorAll('.day-cell:not(.empty)').forEach(cell => {
        cell.addEventListener('click', function() {
            document.querySelectorAll('.day-cell').forEach(c => c.classList.remove('highlight'));
            this.classList.add('highlight');

            const dateStr = this.getAttribute('data-date');
            if (dateStr) {
                // Show events for this date if desired
                // For now we just highlight and prefill the form date if opened
            }
        });
    });
}

function prevMonth() {
    currentDate.setMonth(currentDate.getMonth() - 1);
    generateCalendar();
}

function nextMonth() {
    currentDate.setMonth(currentDate.getMonth() + 1);
    generateCalendar();
}

// ==================== EVENT MANAGEMENT FUNCTIONALITY ====================

// Event form functionality
function openEventForm() {
    // Use the existing modal from PHP
    const modal = document.getElementById('eventFormModal');
    if (!modal) return;

    // Pre-fill date if a day was clicked
    const selectedDay = document.querySelector('.day-cell.highlight');
    if (selectedDay) {
        const day = selectedDay.textContent;
        const month = currentDate.getMonth() + 1;
        const year = currentDate.getFullYear();
        document.getElementById('eventDate').value = `${year}-${month.toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;
    } else {
        // Default to today's date
        const today = new Date();
        const day = today.getDate();
        const month = today.getMonth() + 1;
        const year = today.getFullYear();
        document.getElementById('eventDate').value = `${year}-${month.toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;
    }

    // Show the modal
    modal.style.display = 'block';
}

// ==================== EVENT POPUP FUNCTIONALITY ====================

// Global function to show events for a specific date
window.showEventsForDate = function(dateString, displayDate) {
    // Get real events from localStorage
    const savedEvents = JSON.parse(localStorage.getItem('calendarEvents') || '[]');

    // Filter events for the selected date
    const eventsForDate = savedEvents.filter(event => event.date === dateString);

    // Update popup title with selected date
    document.getElementById('eventsPopupDate').textContent = `Events for ${displayDate}`;

    // Generate events HTML
    let eventsHTML = '';
    if (eventsForDate.length > 0) {
        eventsHTML = '<ul class="events-list">';
        eventsForDate.forEach(event => {
            // Format times for display
            const startTime = event.startTime.substr(0, 5);
            const endTime = event.endTime.substr(0, 5);

            eventsHTML += `
                <li class="event-item" data-event-id="${event.id}">
                    <div class="event-time">${startTime} - ${endTime}</div>
                    <div class="event-details">
                        <div class="event-title">${event.title}</div>
                        <div class="event-location"><i class="fas fa-map-marker-alt"></i> ${event.location || 'No location'}</div>
                        ${event.description ? `<div class="event-description">${event.description}</div>` : ''}
                    </div>
                    <div class="event-actions">
                        <button class="edit-event-btn" onclick="editEvent(${event.id})"><i class="fas fa-edit"></i></button>
                        <button class="delete-event-btn" onclick="deleteEvent(${event.id})"><i class="fas fa-trash-alt"></i></button>
                    </div>
                </li>
            `;
        });
        eventsHTML += '</ul>';
    } else {
        eventsHTML = '<div class="no-events">No events scheduled for this day.</div>';
    }

    // Update content
    document.getElementById('eventsContent').innerHTML = eventsHTML;

    // Show popup with smooth animation
    const overlay = document.getElementById('eventsPopupOverlay');
    const container = document.getElementById('eventsPopupContainer');

    // Make sure the elements are visible before animation
    overlay.style.display = 'block';
    container.style.display = 'block';

    // Force reflow to ensure transition works
    void overlay.offsetWidth;
    void container.offsetWidth;

    // Add active class to trigger animation
    overlay.classList.add('active');
    container.classList.add('active');
};

// Edit event function
window.editEvent = function(eventId) {
    // Get existing events
    const savedEvents = JSON.parse(localStorage.getItem('calendarEvents') || '[]');
    const eventToEdit = savedEvents.find(event => event.id === eventId);

    if (!eventToEdit) return;

    // Use the existing edit modal from PHP
    const modal = document.getElementById('editEventFormModal');
    if (!modal) return;

    // Fill the form with event data
    document.getElementById('editEventId').value = eventToEdit.id;
    document.getElementById('editEventTitle').value = eventToEdit.title;
    document.getElementById('editEventDate').value = eventToEdit.date;
    document.getElementById('editStartTime').value = eventToEdit.startTime;
    document.getElementById('editEndTime').value = eventToEdit.endTime;
    document.getElementById('editEventLocation').value = eventToEdit.location || '';
    document.getElementById('editEventDescription').value = eventToEdit.description || '';

    // Show the edit modal
    modal.style.display = 'block';
};

// Delete event function
window.deleteEvent = function(eventId) {
    if (confirm('Are you sure you want to delete this event?')) {
        // Get existing events
        let savedEvents = JSON.parse(localStorage.getItem('calendarEvents') || '[]');

        // Remove the event
        const filteredEvents = savedEvents.filter(event => event.id !== eventId);

        // Save updated events
        localStorage.setItem('calendarEvents', JSON.stringify(filteredEvents));

        // Close the events popup
        closeEventsPopup();

        // Regenerate calendar to update dots
        generateCalendar();

        // Update upcoming events
        displayEvents(filteredEvents);

        // Show success message
        alert('Event deleted successfully!');
    }
};

// Close events popup function
window.closeEventsPopup = function() {
    const overlay = document.getElementById('eventsPopupOverlay');
    const container = document.getElementById('eventsPopupContainer');

    // Remove active class to trigger fade-out animation
    overlay.classList.remove('active');
    container.classList.remove('active');

    // Wait for animation to complete before hiding elements
    setTimeout(() => {
        overlay.style.display = 'none';
        container.style.display = 'none';
    }, 300);
};

// ==================== UPCOMING EVENTS SLIDER FUNCTIONALITY ====================

// Slider variables
let currentSlide = 0;
let totalSlides = 0;

// Display events function with slider
window.displayEvents = function(events) {
    const eventsSlider = document.getElementById('events-slider');
    if (!eventsSlider) return;

    // Filter to show only upcoming events (today and future)
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    const upcomingEvents = events.filter(event => {
        const eventDate = new Date(event.date);
        eventDate.setHours(0, 0, 0, 0);
        return eventDate >= today;
    });

    // Sort by date (closest first)
    upcomingEvents.sort((a, b) => new Date(a.date) - new Date(b.date));

    if (upcomingEvents.length === 0) {
        eventsSlider.innerHTML = '<p class="no-events-message">No upcoming events.</p>';
        const prevBtn = document.querySelector('.prev-btn');
        const nextBtn = document.querySelector('.next-btn');
        if (prevBtn) prevBtn.disabled = true;
        if (nextBtn) nextBtn.disabled = true;
        return;
    }

    let html = '';

    // Create a slide for each event
    upcomingEvents.forEach((event, index) => {
        // Format date
        const eventDate = new Date(event.date);
        const formattedDate = eventDate.toLocaleDateString('en-US', {
            weekday: 'short',
            month: 'short',
            day: 'numeric'
        });

        html += `
            <div class="event-slide" data-index="${index}">
                <div class="event-item">
                    <div class="event-date">${formattedDate} • ${event.startTime.substr(0, 5)} - ${event.endTime.substr(0, 5)}</div>
                    <div class="event-title">${event.title}</div>
                    <div class="event-details">
                        ${event.location ? `<div class="event-location"><i class="fas fa-map-marker-alt"></i> ${event.location}</div>` : ''}
                        ${event.description ? `<div class="event-description">${event.description.substring(0, 100)}${event.description.length > 100 ? '...' : ''}</div>` : ''}
                    </div>
                </div>
            </div>
        `;
    });

    eventsSlider.innerHTML = html;

    // Reset slider position
    currentSlide = 0;
    totalSlides = upcomingEvents.length;

    // Update navigation buttons
    updateSliderButtons();
    updateSliderPosition();
};

function updateSliderPosition() {
    const eventsSlider = document.getElementById('events-slider');
    if (eventsSlider) {
        eventsSlider.style.transform = `translateX(-${currentSlide * 100}%)`;
    }
}

function updateSliderButtons() {
    const prevBtn = document.querySelector('.prev-btn');
    const nextBtn = document.querySelector('.next-btn');
    
    if (prevBtn) prevBtn.disabled = currentSlide === 0;
    if (nextBtn) nextBtn.disabled = currentSlide === totalSlides - 1 || totalSlides <= 1;
}

// ==================== DOM CONTENT LOADED EVENT HANDLERS ====================

// Main initialization function
document.addEventListener('DOMContentLoaded', function() {
    // Initialize calendar when page loads
    generateCalendar();

    // Set up new event button functionality
    const newEventBtn = document.querySelector('.new-event-btn');
    if (newEventBtn) {
        newEventBtn.addEventListener('click', function() {
            openEventForm();
        });
    }

    // Set up modal close functionality for existing modals
    const eventModal = document.getElementById('eventFormModal');
    const editEventModal = document.getElementById('editEventFormModal');
    
    if (eventModal) {
        const closeBtn = eventModal.querySelector('.close-modal');
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                eventModal.style.display = 'none';
            });
        }
        
        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target === eventModal) {
                eventModal.style.display = 'none';
            }
        });
    }
    
    if (editEventModal) {
        const closeBtn = editEventModal.querySelector('.close-modal');
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                editEventModal.style.display = 'none';
            });
        }
        
        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target === editEventModal) {
                editEventModal.style.display = 'none';
            }
        });
    }

    // Set up events popup close functionality
    const eventsPopupClose = document.querySelector('.events-popup-close');
    const eventsPopupOverlay = document.getElementById('eventsPopupOverlay');
    
    if (eventsPopupClose) {
        eventsPopupClose.addEventListener('click', closeEventsPopup);
    }
    if (eventsPopupOverlay) {
        eventsPopupOverlay.addEventListener('click', closeEventsPopup);
    }

    // Set up form submission handlers
    const eventForm = document.getElementById('eventForm');
    if (eventForm) {
        eventForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Get form values
            const eventTitle = document.getElementById('eventTitle').value;
            const eventDate = document.getElementById('eventDate').value;
            const startTime = document.getElementById('startTime').value;
            const endTime = document.getElementById('endTime').value;
            const eventLocation = document.getElementById('eventLocation').value;
            const eventDescription = document.getElementById('eventDescription').value;

            // Create new event object
            const newEvent = {
                id: Date.now(), // unique id using timestamp
                title: eventTitle,
                date: eventDate,
                startTime: startTime,
                endTime: endTime,
                location: eventLocation,
                description: eventDescription
            };

            // Get existing events from localStorage
            let savedEvents = JSON.parse(localStorage.getItem('calendarEvents') || '[]');

            // Add new event to the list
            savedEvents.push(newEvent);

            // Sort events by date
            savedEvents.sort((a, b) => new Date(a.date) - new Date(b.date));

            // Save back to localStorage
            localStorage.setItem('calendarEvents', JSON.stringify(savedEvents));

            // Close the modal
            document.getElementById('eventFormModal').style.display = 'none';

            // Clear form
            eventForm.reset();

            // Regenerate calendar to update event dots
            generateCalendar();

            // Update the upcoming events display
            displayEvents(savedEvents);

            // Show success message
            alert('Event created successfully!');
        });
    }

    const editEventForm = document.getElementById('editEventForm');
    if (editEventForm) {
        editEventForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const eventId = parseInt(document.getElementById('editEventId').value);
            const title = document.getElementById('editEventTitle').value;
            const date = document.getElementById('editEventDate').value;
            const startTime = document.getElementById('editStartTime').value;
            const endTime = document.getElementById('editEndTime').value;
            const location = document.getElementById('editEventLocation').value;
            const description = document.getElementById('editEventDescription').value;

            // Get all events
            let savedEvents = JSON.parse(localStorage.getItem('calendarEvents') || '[]');

            // Find and update the event
            const updatedEvents = savedEvents.map(event => {
                if (event.id === eventId) {
                    return {
                        ...event,
                        title,
                        date,
                        startTime,
                        endTime,
                        location,
                        description
                    };
                }
                return event;
            });

            // Save updated events
            localStorage.setItem('calendarEvents', JSON.stringify(updatedEvents));

            // Close modals
            document.getElementById('editEventFormModal').style.display = 'none';
            closeEventsPopup();

            // Regenerate calendar to update dots
            generateCalendar();

            // Update upcoming events
            displayEvents(updatedEvents);

            // Show success message
            alert('Event updated successfully!');
        });
    }

    // Set up slider navigation
    document.addEventListener('click', function(e) {
        if (e.target.closest('.prev-btn')) {
            if (currentSlide > 0) {
                currentSlide--;
                updateSliderPosition();
                updateSliderButtons();
            }
        } else if (e.target.closest('.next-btn')) {
            if (currentSlide < totalSlides - 1) {
                currentSlide++;
                updateSliderPosition();
                updateSliderButtons();
            }
        }
    });

    // Add event listener for clicks on day cells
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('day-cell') && !e.target.classList.contains('empty')) {
            // Remove highlight from all cells
            document.querySelectorAll('.day-cell').forEach(cell => {
                cell.classList.remove('highlight');
            });

            // Add highlight to clicked cell
            e.target.classList.add('highlight');

            // Get the date of the clicked day
            const day = e.target.textContent;
            const month = currentDate.getMonth() + 1;
            const year = currentDate.getFullYear();
            const formattedDate = `${year}-${month.toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;

            // Only show events popup if this day has an event (has the has-event class)
            if (e.target.classList.contains('has-event')) {
                showEventsForDate(formattedDate, `${months[month - 1]} ${day}, ${year}`);
            }
        }
    });

    // Get existing events from localStorage and display them
    let savedEvents = JSON.parse(localStorage.getItem('calendarEvents') || '[]');
    displayEvents(savedEvents);
});
