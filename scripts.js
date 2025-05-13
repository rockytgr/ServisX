// scripts.js

function scrollToBooking() {
    const bookingSection = document.getElementById("services");
    if (bookingSection) {
      bookingSection.scrollIntoView({ behavior: "smooth" });
    }
  }
  
  // scripts.js

  document.addEventListener("DOMContentLoaded", () => {
    const boxes = document.querySelectorAll(".service-box");
    const title = document.getElementById("data-title");
    const desc = document.getElementById("data-description");
    const interval = document.getElementById("data-interval");
    const price = document.getElementById("data-price");
  
    // Iterate through each service box
    boxes.forEach(box => {
      box.addEventListener("mouseenter", () => {
        // Remove active class from all boxes first
        boxes.forEach(b => b.classList.remove("active")); // remove active from all boxes
        box.classList.add("active"); // add active to the hovered box
  
        // Update the left detail panel with the data attributes of the hovered box
        title.textContent = box.dataset.title || "Default Title";
        desc.textContent = box.dataset.description || "Default Description";
        interval.textContent = box.dataset.interval || "Default Interval";
        price.textContent = box.dataset.price || "Default Price";
      });
  
      box.addEventListener("mouseleave", () => {
        // Reset the left panel details when the cursor leaves the box
        // Optional: You can reset to default values or keep the last hovered details
        // Here we are resetting to the default value (or previous hovered details).
        if (!box.classList.contains("active")) {
          title.textContent = "Perkhidmatan Penyelenggaraan";
          desc.textContent = "Daripada penyelenggaraan enjin hingga penjagaan sistem penyejuk, kami memastikan setiap bahagian kereta anda beroperasi dengan lancar.";
          interval.textContent = "Setiap 5,000 – 10,000 km atau 3–6 bulan sekali.";
          price.textContent = "RM 100-1500";
        }
      });
    });
  });
  
  
  
  
  