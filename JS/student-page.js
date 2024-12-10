document.addEventListener('DOMContentLoaded', function () {
    const monthNames = [
        "January", "February", "March", "April", "May", "June",
        "July", "August", "September", "October", "November", "December"
    ];
    let currentDate = new Date();
    let today = new Date();
  
    const calendarBody = document.getElementById('calendarBody');
    const monthYear = document.getElementById('monthYear');
    const prevMonth = document.getElementById('prevMonth');
    const nextMonth = document.getElementById('nextMonth');
  
    function renderCalendar() {
        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();
  
        // Set the header
        monthYear.textContent = `${monthNames[month]} ${year}`;
  
        // Clear previous cells
        calendarBody.innerHTML = '';
  
        // Calculate first day and total days in month
        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
  
        let date = 1;
        for (let i = 0; i < 6; i++) {
            const row = document.createElement('tr');
  
            for (let j = 0; j < 7; j++) {
                const cell = document.createElement('td');
  
                if (i === 0 && j < firstDay) {
                    cell.textContent = ''; // Empty cell before the first day
                } else if (date > daysInMonth) {
                    break; // Stop after the last day
                } else {
                    cell.textContent = date;
  
                    // Check if the date is today
                    if (
                        date === today.getDate() &&
                        month === today.getMonth() &&
                        year === today.getFullYear()
                    ) {
                        cell.classList.add('today'); // Add a special class for today
                    }
  
                    date++;
                }
                row.appendChild(cell);
            }
            calendarBody.appendChild(row);
        }
    }
  
    // Navigation buttons
    prevMonth.addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() - 1);
        renderCalendar();
    });
  
    nextMonth.addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() + 1);
        renderCalendar();
    });
  
    renderCalendar(); // Initialize the calendar on page load
  });
  
  
  
  