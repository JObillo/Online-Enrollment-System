document.addEventListener("DOMContentLoaded", function() {
    const dropdown = document.querySelector(".dropdown");
    const dropdownToggle = dropdown.querySelector(".dropdown-toggle");
    const dropdownContent = dropdown.querySelector(".dropdown-content");
    const dropdownItems = dropdownContent.querySelectorAll("a"); // Get all the links in the dropdown

    // When you click the dropdown toggle, it will open or close the dropdown
    dropdownToggle.addEventListener("click", function(event) {
        event.preventDefault(); // Prevent the default action of the anchor tag
        dropdown.classList.toggle("active"); // Toggle 'active' class to control dropdown visibility
        dropdownContent.style.display = dropdownContent.style.display === "block" ? "none" : "block"; // Toggle display
    });

    // Prevent dropdown from closing when clicking on the dropdown items (Pre-enrolled, Enrolled, Rejected)
    dropdownItems.forEach(item => {
        item.addEventListener("click", function(event) {
            event.stopPropagation(); // Prevent the event from propagating and closing the dropdown
            // Optional: Do something when one of the items is clicked, e.g., navigate to the respective page
            console.log("Item clicked: " + item.textContent);
        });
    });
});

// Sidebar.js

function setActive(element) {
    // Remove "active" class from all menu items
    let items = document.querySelectorAll('.menu-item');
    items.forEach(item => item.classList.remove('active'));
    
    // Add "active" class to the clicked menu item
    element.classList.add('active');
}

function toggleDropdown() {
    // Toggle the dropdown visibility
    const dropdownContent = document.querySelector('.dropdown-content');
    dropdownContent.classList.toggle('show');
}