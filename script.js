//SELECT DROPDOWN
function toggleDropdown() {
  const dropdownOptions = document.getElementById("dropdownOptions");
  dropdownOptions.style.display = dropdownOptions.style.display === "none" ? "block" : "none";
}

function selectOption(option) {
  const dropdownSelect = document.querySelector(".dropdown-select");
  dropdownSelect.textContent = option;
  toggleDropdown();

  // You can add code here to handle the sorting or other actions based on the selected option
  // For demonstration purposes, let's log the selected option to the console
  console.log("Selected Option:", option);
}

// Close the dropdown if the user clicks outside of it
window.onclick = function (event) {
  if (!event.target.matches('.dropdown-select')) {
    const dropdownOptions = document.getElementById("dropdownOptions");
    if (dropdownOptions.style.display === "block") {
      dropdownOptions.style.display = "none";
    }
  }
}

//FOR DROPDOWN LOGIC
function reorderSkills() {
  const skillsContainer = document.querySelector('.skills-contain');
  const skillsList = Array.from(skillsContainer.children);

  // Define the order of classes
  const order = ['superior', 'advanced', 'familiar', 'learning'];

  // Sort the skillsList based on the order
  skillsList.sort((a, b) => order.indexOf(a.classList[1]) - order.indexOf(b.classList[1]));

  // Clear existing skills and append the reordered ones
  skillsContainer.innerHTML = '';
  skillsList.forEach(skill => skillsContainer.appendChild(skill));
}

// Function to handle selecting an option
function selectOption(option) {
  const dropdownSelect = document.querySelector(".dropdown-select");
  dropdownSelect.textContent = option;

  if (option === "Sort: Default") {
    // Do not sort, maintain the default order
    resetOrder();
  } else if (option === "By Rank") {
    // Sort skills based on the default CSS styles
    reorderSkills();
  }

  toggleDropdown();
}

// Store the original order when the page loads
document.addEventListener('DOMContentLoaded', function () {
  const skillsContainer = document.querySelector('.skills-contain');
  skillsContainer.dataset.defaultOrder = skillsContainer.innerHTML;
});

// Global variable to store the original order of skills
let originalSkillsOrder = null;

// Function to reset the order to the default state
function resetOrder() {
  const skillsContainer = document.querySelector('.skills-contain');

  // Ensure the original order is stored
  if (!originalSkillsOrder) {
    originalSkillsOrder = Array.from(skillsContainer.children);
  }

  // Clear the existing content of the skills container
  skillsContainer.innerHTML = '';

  // Restore the original order
  originalSkillsOrder.forEach(skill => {
    skillsContainer.appendChild(skill.cloneNode(true));
  });
}

// Function to handle selecting an option
function selectOption(option) {
  const dropdownSelect = document.querySelector(".dropdown-select");
  dropdownSelect.textContent = option;

  if (option === "Sort: Default") {
    // Do not sort, maintain the default order
    resetOrder();
  } else if (option === "By Rank") {
    // Sort skills based on the default CSS styles
    reorderSkills();
  }

  toggleDropdown();
}

// Store the original order when the page loads
document.addEventListener('DOMContentLoaded', function () {
  const skillsContainer = document.querySelector('.skills-contain');
  originalSkillsOrder = Array.from(skillsContainer.children);
});


//GO TO ANCHOR
function goToAnchor(anchorId) {
  const element = document.getElementById(anchorId);
  if (element) {
    element.scrollIntoView({ behavior: 'smooth' });
  }
}

function calculateVisibilityPercentage(element) {
  const rect = element.getBoundingClientRect();
  const windowHeight = window.innerHeight;
  const visibleHeight = Math.min(rect.bottom, windowHeight) - Math.max(rect.top, 0);
  return (visibleHeight / element.offsetHeight) * 100;
}

function setActiveSection() {
  const sections = document.querySelectorAll('.section');
  const navLinks = document.querySelectorAll('.nav-link');

  let maxVisibility = 0;
  let activeSection = null;

  sections.forEach(section => {
    const visibility = calculateVisibilityPercentage(section);

    if (visibility > maxVisibility) {
      maxVisibility = visibility;
      activeSection = section;
    }
  });

  // Toggle active class for sections
  sections.forEach(section => {
    const isActive = section === activeSection;
    section.classList.toggle('active', isActive);
  });

  // Toggle active class for nav links
  navLinks.forEach((link, index) => {
    // Check if corresponding section exists
    if (index < sections.length) {
      const correspondingSection = sections[index];
      const isActive = correspondingSection.classList.contains('active');
      link.classList.toggle('active', isActive);
    }
  });
}


document.addEventListener('scroll', setActiveSection);
document.addEventListener('DOMContentLoaded', setActiveSection);

//check what area is active for url = currently not in use

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
  // Check if the element and data-image attribute exist
  if (!element || !element.hasAttribute("data-image")) {
    console.error("Invalid element or missing data-image attribute");
    return;
  }

  // Get the image URL from the data-image attribute
  var imageUrl = element.getAttribute("data-image");

  // Check if imagePreview container exists
  var imagePreview = document.getElementById("imagePreview");
  if (!imagePreview) {
    console.error("Image preview container not found");
    return;
  }

  // Set the image source and display the preview
  imagePreview.innerHTML = `<img src="${imageUrl}" alt="Preview Image">`;

  // Set the position of the image preview above and centered on the cursor
  var offsetX = 10; // Adjust this value to control the horizontal offset
  var offsetY = -300; // Adjust this value to control the vertical offset

  // Check if event object and pageX/pageY properties exist
  if (event && event.pageX !== undefined && event.pageY !== undefined) {
    imagePreview.style.left = (event.pageX + offsetX) + 'px';
    imagePreview.style.top = (event.pageY + offsetY - imagePreview.offsetHeight) + 'px';
  } else {
    console.error("Invalid event object or missing pageX/pageY properties");
    return;
  }

  // Apply smooth transition by adding a class
  imagePreview.classList.add("show-preview");

  imagePreview.style.display = "block";
}

function hideImagePreview() {
  // Get the image preview container
  var imagePreview = document.getElementById("imagePreview");

  // Check if imagePreview container exists
  if (!imagePreview) {
    console.error("Image preview container not found");
    return;
  }

  // Remove the class to smoothly transition back
  imagePreview.classList.remove("show-preview");

  // Hide the image preview after the transition
  setTimeout(function () {
    imagePreview.style.display = "none";
  }, 10); // Adjust the delay to match the transition duration
}

//toggle extra to avoid not responding to clicks
// Function to toggle the dropdown visibility
function toggleDropdown() {
  const dropdownOptions = document.getElementById("dropdownOptions");
  const isDropdownVisible = dropdownOptions.style.display === "block";

  // Toggle the display based on the current state
  dropdownOptions.style.display = isDropdownVisible ? "none" : "block";
}

// Close the dropdown if the user clicks outside of it
window.onclick = function (event) {
  const dropdownOptions = document.getElementById("dropdownOptions");
  const dropdownSelect = document.querySelector(".dropdown-select");

  if (!event.target.matches('.dropdown-select') && !event.target.matches('.dropdown-option')) {
    dropdownOptions.style.display = "none";
  }

  // Automatically close the dropdown if an option is selected
  if (event.target.matches('.dropdown-option')) {
    dropdownOptions.style.display = "none";
    dropdownSelect.textContent = event.target.textContent;
  }
}


//DARKMODE

var checkbox = document.querySelector('input[name=mode]');

checkbox.addEventListener('change', function () {
  if (this.checked) {
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

//FOR TABS
function openTab(tabName) {
  var i, tabContent;

  // Hide all tab content
  tabContent = document.getElementsByClassName("tab-content");
  for (i = 0; i < tabContent.length; i++) {
    tabContent[i].style.display = "none";
  }

  // Remove "active" class from all tabs
  var tabs = document.getElementsByClassName("tab");
  for (i = 0; i < tabs.length; i++) {
    tabs[i].classList.remove("active");
  }

  // Show the clicked tab content and mark it as active
  document.getElementById(tabName).style.display = "block";
  event.currentTarget.classList.add("active");
}

//TOOLTIP
document.addEventListener('DOMContentLoaded', function () {
  var skills = document.querySelectorAll('.highlight-skills');

  skills.forEach(function (skill) {
    var tooltip = document.createElement('div');
    tooltip.classList.add('tooltip');
    tooltip.textContent = skill.getAttribute('data-title');
    skill.appendChild(tooltip);

    skill.removeAttribute('title'); // Remove the default title attribute

    skill.addEventListener('click', function () {
      tooltip.classList.toggle('show-tooltip');
    });

    skill.addEventListener('mouseleave', function () {
      tooltip.classList.remove('show-tooltip');
    });
  });
});

//FAQ
document.addEventListener("DOMContentLoaded", function () {
  const questions = document.querySelectorAll('.question');

  questions.forEach(function (question) {
    question.addEventListener('click', function () {
      const answer = this.nextElementSibling;
      answer.classList.toggle('active');
      question.classList.toggle('active');
    });
  });
});


//ARROW TOGGLE
document.addEventListener("DOMContentLoaded", function () {
  var faqItems = document.querySelectorAll('.faq-item');

  faqItems.forEach(function (item) {
    var question = item.querySelector('.question');
    var answer = item.querySelector('.answer');
    var arrowDown = item.querySelector('.arrow-down');
    var arrowUp = item.querySelector('.arrow-up');

    question.addEventListener('click', function () {
      answer.classList.toggle('hidden');
      arrowDown.classList.toggle('hidden');
      arrowUp.classList.toggle('nothidden');
    });
  });
});





