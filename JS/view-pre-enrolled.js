// Get the modal
var modal = document.getElementById("imageModal");

// Get the modal image element
var modalImg = document.getElementById("modalImage");

// Get all clickable images
var images = document.querySelectorAll(".clickable-image");

// Add click event listeners to all images
images.forEach(function(img) {
    img.addEventListener("click", function() {
        modal.style.display = "block";
        modalImg.src = this.src; // Set the image in the modal to the clicked image's source
    });
});

// Close the modal when the user clicks the close button (x)
function closeModal() {
    modal.style.display = "none";
}
