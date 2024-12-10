document.addEventListener("DOMContentLoaded", function () {
    const dropdowns = document.querySelectorAll(".dropdown");

    dropdowns.forEach((dropdown) => {
        const dropdownToggle = dropdown.querySelector(".dropdown-toggle");
        const dropdownContent = dropdown.querySelector(".dropdown-content");
        const dropdownIcon = dropdownToggle.querySelector(".dropdown-icon");
        const dropdownItems = dropdownContent.querySelectorAll("a");

        // Toggle dropdown visibility
        dropdownToggle.addEventListener("click", function (event) {
            event.preventDefault();

            // Close all other dropdowns
            dropdowns.forEach((d) => {
                if (d !== dropdown) {
                    d.classList.remove("active");
                    d.querySelector(".dropdown-content").style.display = "none";
                    d.querySelector(".dropdown-icon").style.transform = "rotate(0deg)";
                }
            });

            // Toggle the clicked dropdown
            const isActive = dropdown.classList.contains("active");
            dropdown.classList.toggle("active", !isActive);
            dropdownContent.style.display = isActive ? "none" : "block";
            dropdownIcon.style.transform = isActive ? "rotate(0deg)" : "rotate(180deg)";
        });

        // Prevent dropdown from closing when clicking an item
        dropdownItems.forEach((item) => {
            item.addEventListener("click", function (event) {
                event.stopPropagation(); // Prevent closing the dropdown
                console.log("Item clicked: " + item.textContent);
            });
        });
    });

    // Restore dropdown state on page load
    const activeDropdownState = localStorage.getItem("dropdownState");
    dropdowns.forEach((dropdown) => {
        const dropdownContent = dropdown.querySelector(".dropdown-content");
        const dropdownIcon = dropdown.querySelector(".dropdown-icon");

        if (dropdown.id === activeDropdownState) {
            dropdown.classList.add("active");
            dropdownContent.style.display = "block";
            dropdownIcon.style.transform = "rotate(180deg)";
        } else {
            dropdown.classList.remove("active");
            dropdownContent.style.display = "none";
            dropdownIcon.style.transform = "rotate(0deg)";
        }
    });
});

function setActive(item) {
    // Remove active class from all menu items
    document.querySelectorAll(".menu-item").forEach((menu) => {
        menu.classList.remove("active");
    });

    // Add active class to the clicked menu item
    item.classList.add("active");

    // Check if the clicked item is part of a dropdown
    const dropdown = item.closest(".dropdown");
    if (dropdown) {
        // Persist the dropdown state in localStorage
        localStorage.setItem("dropdownState", dropdown.id); // Ensure the dropdown has an ID
    } else {
        // Clear dropdown state when clicking non-dropdown items
        localStorage.removeItem("dropdownState");
    }
}
