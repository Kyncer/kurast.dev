document.addEventListener('DOMContentLoaded', function () {
    // Get the current URL path
    var currentPath = window.location.pathname;
  
    // Get all navigation links
    var navLinks = document.querySelectorAll('.nav-link');
  
    // Debugging output
    console.log('Current Path:', currentPath);
  
    // Check if the link's href matches the current path and add 'active' class
    navLinks.forEach(function (link) {
      console.log('Link Href:', link.getAttribute('href'));
  
      if (link.getAttribute('href') === currentPath) {
        link.classList.add('active');
      }
    });
  });

  function showImagePreview(event, element) {
    // Get the image URL from the data-image attribute
    var imageUrl = element.getAttribute("data-image");

    // Get the image preview container
    var imagePreview = document.getElementById("imagePreview");

    // Set the image source and display the preview
    imagePreview.innerHTML = `<img src="${imageUrl}" alt="Preview Image">`;

    // Set the position of the image preview above and centered on the cursor
    var offsetX = 10; // Adjust this value to control the horizontal offset
    var offsetY = -300; // Adjust this value to control the vertical offset

    imagePreview.style.left = (event.pageX + offsetX) + 'px';
    imagePreview.style.top = (event.pageY + offsetY - imagePreview.offsetHeight) + 'px';

    // Apply smooth transition by adding a class
    imagePreview.classList.add("show-preview");

    imagePreview.style.display = "block";
  }

  function hideImagePreview() {
    // Get the image preview container
    var imagePreview = document.getElementById("imagePreview");

    // Remove the class to smoothly transition back
    imagePreview.classList.remove("show-preview");

    // Hide the image preview after the transition
    setTimeout(function () {
      imagePreview.style.display = "none";
    }, 300); // Adjust the delay to match the transition duration
  }

//darkmode

  var checkbox = document.querySelector('input[name=mode]');

  checkbox.addEventListener('change', function() {
      if(this.checked) {
          trans()
          document.documentElement.setAttribute('data-theme', 'dark')
      } else {
          trans()
          document.documentElement.setAttribute('data-theme', 'light')
      }
  })

  let trans = () => {
      document.documentElement.classList.add('transition');
      window.setTimeout(() => {
          document.documentElement.classList.remove('transition');
      }, 1000)
  }