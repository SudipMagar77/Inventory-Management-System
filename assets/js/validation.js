// Validate username (should not be only numbers)
function validateUsername(input) {
  const errorElement = document.getElementById("usernameError");
  const value = input.value.trim();

  if (value === "") {
    errorElement.textContent = "Username is required";
    return false;
  } else if (/^\d+$/.test(value)) {
    errorElement.textContent = "Username cannot be only numbers";
    return false;
  } else {
    errorElement.textContent = "";
    return true;
  }
}

// Validate password (minimum 5 words)
function validatePassword(input) {
  const errorElement = document.getElementById("passwordError");
  const value = input.value.trim();

  if (value === "") {
    errorElement.textContent = "Password is required";
    return false;
  } else if (value.split(/\s+/).length < 5) {
    errorElement.textContent = "Password must have at least 5 words";
    return false;
  } else {
    errorElement.textContent = "";
    return true;
  }
}

// Validate confirm password
function validateConfirmPassword(input) {
  const errorElement = document.getElementById("confirmPasswordError");
  const password = document.getElementById("password").value;
  const value = input.value;

  if (value === "") {
    errorElement.textContent = "Please confirm your password";
    return false;
  } else if (value !== password) {
    errorElement.textContent = "Passwords do not match";
    return false;
  } else {
    errorElement.textContent = "";
    return true;
  }
}

// Validate email
function validateEmail(input) {
  const errorElement = document.getElementById("emailError");
  const value = input.value.trim();
  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  if (value === "") {
    errorElement.textContent = "Email is required";
    return false;
  } else if (!emailPattern.test(value)) {
    errorElement.textContent = "Please enter a valid email (must contain @)";
    return false;
  } else {
    errorElement.textContent = "";
    return true;
  }
}

// Validate phone number (only numbers)
function validatePhone(input) {
  const errorElement = document.getElementById("phoneError");
  const value = input.value.trim();
  const phonePattern = /^\d+$/;

  if (value === "") {
    errorElement.textContent = "Phone number is required";
    return false;
  } else if (!phonePattern.test(value)) {
    errorElement.textContent = "Phone number must contain only digits";
    return false;
  } else if (value.length < 10) {
    errorElement.textContent = "Phone number must be at least 10 digits";
    return false;
  } else {
    errorElement.textContent = "";
    return true;
  }
}

// Validate price (only numbers and decimal)
function validatePrice(input) {
  const errorElement = document.getElementById("priceError");
  const value = input.value.trim();
  const pricePattern = /^\d+(\.\d{1,2})?$/;

  if (value === "") {
    errorElement.textContent = "Price is required";
    return false;
  } else if (!pricePattern.test(value)) {
    errorElement.textContent = "Please enter a valid price (numbers only)";
    return false;
  } else {
    errorElement.textContent = "";
    return true;
  }
}

// Add real-time validation for forms
document.addEventListener("DOMContentLoaded", function () {
  // Login form validation
  const loginForm = document.getElementById("loginForm");
  if (loginForm) {
    loginForm.addEventListener("submit", function (e) {
      const username = document.getElementById("username");
      const password = document.getElementById("password");

      if (!validateUsername(username) || !validatePassword(password)) {
        e.preventDefault();
      }
    });
  }

  // Signup form validation
  const signupForm = document.getElementById("signupForm");
  if (signupForm) {
    signupForm.addEventListener("submit", function (e) {
      const username = document.getElementById("username");
      const password = document.getElementById("password");
      const confirmPassword = document.getElementById("confirm_password");

      if (
        !validateUsername(username) ||
        !validatePassword(password) ||
        !validateConfirmPassword(confirmPassword)
      ) {
        e.preventDefault();
      }
    });
  }
});

// Modal functions
function openModal(id) {
  document.getElementById(id).style.display = "block";
}

function closeModal(id) {
  document.getElementById(id).style.display = "none";
}

// Image preview
function previewImage(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function (e) {
      document.getElementById("preview").src = e.target.result;
    };
    reader.readAsDataURL(input.files[0]);
  }
}

function removeImage() {
  document.getElementById("preview").src =
    "../assets/images/default-product.jpg";
  document.querySelector('input[name="product_image"]').value = "";
}
