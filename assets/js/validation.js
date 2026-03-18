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

// ========== ADD THE NEW CATEGORY VALIDATION CODE HERE ==========

// Validate category form before submission
function validateCategoryForm() {
  const nameInput = document.getElementById("categoryName");
  const errorElement = document.getElementById("categoryError");

  // Check if element exists
  if (!nameInput || !errorElement) {
    return true; // If elements don't exist, don't block submission
  }

  const value = nameInput.value.trim();

  // Check if empty
  if (value === "") {
    errorElement.textContent = "❌ Category name cannot be empty";
    errorElement.style.color = "#f44336";
    nameInput.style.borderColor = "#f44336";
    return false;
  }

  // Check if just quotes
  if (value === '""' || value === "''") {
    errorElement.textContent = "❌ Please enter a valid category name";
    errorElement.style.color = "#f44336";
    nameInput.style.borderColor = "#f44336";
    return false;
  }

  // Check minimum length
  if (value.length < 2) {
    errorElement.textContent = "❌ Category name must be at least 2 characters";
    errorElement.style.color = "#f44336";
    nameInput.style.borderColor = "#f44336";
    return false;
  }

  // Check if only numbers
  if (/^\d+$/.test(value)) {
    errorElement.textContent = "❌ Category name cannot be only numbers";
    errorElement.style.color = "#f44336";
    nameInput.style.borderColor = "#f44336";
    return false;
  }

  return true;
}

// Real-time validation as user types
document.addEventListener("DOMContentLoaded", function () {
  const categoryInput = document.getElementById("categoryName");
  const errorElement = document.getElementById("categoryError");

  if (categoryInput && errorElement) {
    categoryInput.addEventListener("keyup", function () {
      const value = this.value.trim();

      if (value === "") {
        errorElement.textContent = "❌ Category name cannot be empty";
        errorElement.style.color = "#f44336";
        this.style.borderColor = "#f44336";
      } else if (value === '""' || value === "''") {
        errorElement.textContent = "❌ Please enter a valid category name";
        errorElement.style.color = "#f44336";
        this.style.borderColor = "#f44336";
      } else if (value.length < 2) {
        errorElement.textContent =
          "❌ Category name must be at least 2 characters";
        errorElement.style.color = "#f44336";
        this.style.borderColor = "#f44336";
      } else if (/^\d+$/.test(value)) {
        errorElement.textContent = "❌ Category name cannot be only numbers";
        errorElement.style.color = "#f44336";
        this.style.borderColor = "#f44336";
      } else {
        errorElement.textContent = "✅ Valid category name";
        errorElement.style.color = "#4CAF50";
        this.style.borderColor = "#4CAF50";
      }
    });
  }
});

// ========== END OF NEW CODE ==========

// Modal functions (if they exist in this file)
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
// Validate category name in real-time
function validateCategoryName(input) {
  const value = input.value.trim();
  const errorElement = document.getElementById("categoryError");
  const saveBtn = document.getElementById("saveCategoryBtn");

  if (value === "") {
    errorElement.textContent = "❌ Category name cannot be empty";
    if (saveBtn) saveBtn.disabled = true;
    return false;
  } else if (value === '""' || value === "''") {
    errorElement.textContent = "❌ Please enter a valid category name";
    if (saveBtn) saveBtn.disabled = true;
    return false;
  } else if (value.length < 2) {
    errorElement.textContent = "❌ Category name must be at least 2 characters";
    if (saveBtn) saveBtn.disabled = true;
    return false;
  } else if (/^\d+$/.test(value)) {
    errorElement.textContent = "❌ Category name cannot be only numbers";
    if (saveBtn) saveBtn.disabled = true;
    return false;
  } else {
    errorElement.textContent = "✅ Valid category name";
    errorElement.style.color = "#4CAF50";
    if (saveBtn) saveBtn.disabled = false;
    return true;
  }
}

// Validate edit category name
function validateEditCategoryName(input) {
  const value = input.value.trim();
  const errorElement = document.getElementById("editCategoryError");

  if (value === "") {
    errorElement.textContent = "❌ Category name cannot be empty";
    return false;
  } else if (value === '""' || value === "''") {
    errorElement.textContent = "❌ Please enter a valid category name";
    return false;
  } else if (value.length < 2) {
    errorElement.textContent = "❌ Category name must be at least 2 characters";
    return false;
  } else if (/^\d+$/.test(value)) {
    errorElement.textContent = "❌ Category name cannot be only numbers";
    return false;
  } else {
    errorElement.textContent = "✅ Valid category name";
    errorElement.style.color = "#4CAF50";
    return true;
  }
}
// Validate edit category form before submission
function validateEditCategoryForm() {
    const nameInput = document.getElementById('editCategoryName');
    const errorElement = document.getElementById('editCategoryError');
    
    // Check if elements exist
    if(!nameInput || !errorElement) {
        return true;
    }
    
    const value = nameInput.value.trim();
    
    // Check if empty
    if(value === '') {
        errorElement.textContent = '❌ Category name cannot be empty';
        errorElement.style.color = '#f44336';
        nameInput.style.borderColor = '#f44336';
        return false;
    }
    
    // Check if just quotes
    if(value === '""' || value === "''") {
        errorElement.textContent = '❌ Please enter a valid category name';
        errorElement.style.color = '#f44336';
        nameInput.style.borderColor = '#f44336';
        return false;
    }
    
    // Check minimum length
    if(value.length < 2) {
        errorElement.textContent = '❌ Category name must be at least 2 characters';
        errorElement.style.color = '#f44336';
        nameInput.style.borderColor = '#f44336';
        return false;
    }
    
    // Check if only numbers
    if(/^\d+$/.test(value)) {
        errorElement.textContent = '❌ Category name cannot be only numbers';
        errorElement.style.color = '#f44336';
        nameInput.style.borderColor = '#f44336';
        return false;
    }
    
    return true;
}

// Real-time validation for edit category
document.addEventListener('DOMContentLoaded', function() {
    // Add this inside your existing DOMContentLoaded
    const editCategoryInput = document.getElementById('editCategoryName');
    const editErrorElement = document.getElementById('editCategoryError');
    
    if(editCategoryInput && editErrorElement) {
        editCategoryInput.addEventListener('keyup', function() {
            const value = this.value.trim();
            
            if(value === '') {
                editErrorElement.textContent = '❌ Category name cannot be empty';
                editErrorElement.style.color = '#f44336';
                this.style.borderColor = '#f44336';
            }
            else if(value === '""' || value === "''") {
                editErrorElement.textContent = '❌ Please enter a valid category name';
                editErrorElement.style.color = '#f44336';
                this.style.borderColor = '#f44336';
            }
            else if(value.length < 2) {
                editErrorElement.textContent = '❌ Category name must be at least 2 characters';
                editErrorElement.style.color = '#f44336';
                this.style.borderColor = '#f44336';
            }
            else if(/^\d+$/.test(value)) {
                editErrorElement.textContent = '❌ Category name cannot be only numbers';
                editErrorElement.style.color = '#f44336';
                this.style.borderColor = '#f44336';
            }
            else {
                editErrorElement.textContent = '✅ Valid category name';
                editErrorElement.style.color = '#4CAF50';
                this.style.borderColor = '#4CAF50';
            }
        });
    }
});