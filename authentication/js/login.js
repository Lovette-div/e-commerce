// Login form validation and submission
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('#loginForm');
    const submitBtn = document.getElementById('loginBtn');
    
    // Regular expressions for validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    
    // Form validation function
    function validateForm(formData) {
        const errors = [];
        
        // Validate email (required, valid format)
        if (!formData.email.trim()) {
            errors.push('Email is required');
        } else if (!emailRegex.test(formData.email.trim())) {
            errors.push('Please enter a valid email address');
        }
        
        // Validate password (required)
        if (!formData.password.trim()) {
            errors.push('Password is required');
        }
        
        return errors;
    }
    
    // Show/hide loading state
    function setLoadingState(isLoading) {
        if (submitBtn) {
            if (isLoading) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Signing in...';
            } else {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-sign-in-alt me-2"></i>Login';
            }
        }
    }
    
    // Display error messages using Bootstrap alerts
    function displayErrors(errors) {
        const errorContainer = document.getElementById('errorMessages');
        if (errorContainer) {
            errorContainer.innerHTML = '';
            if (errors.length > 0) {
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-danger alert-dismissible fade show';
                alertDiv.innerHTML = `
                    <strong>Login Failed:</strong>
                    <ul class="mb-0 mt-2">
                        ${errors.map(error => `<li>${error}</li>`).join('')}
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                errorContainer.appendChild(alertDiv);
            }
        }
    }
    
    // Display success message using Bootstrap alerts
    function displaySuccess(message) {
        const successContainer = document.getElementById('successMessage');
        if (successContainer) {
            successContainer.innerHTML = `
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>Success!</strong> ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
        }
    }
    
    // Clear all messages
    function clearMessages() {
        const errorContainer = document.getElementById('errorMessages');
        const successContainer = document.getElementById('successMessage');
        
        if (errorContainer) {
            errorContainer.innerHTML = '';
        }
        
        if (successContainer) {
            successContainer.innerHTML = '';
        }
    }
    
    // Real-time email validation
    const emailInput = document.getElementById('email');
    if (emailInput) {
        emailInput.addEventListener('input', function() {
            const email = this.value.trim();
            const emailFeedback = document.getElementById('emailFeedback');
            
            if (emailFeedback) {
                if (email && !emailRegex.test(email)) {
                    emailFeedback.innerHTML = '<small class="text-danger"><i class="fas fa-times-circle me-1"></i>Invalid email format</small>';
                    emailInput.classList.add('is-invalid');
                    emailInput.classList.remove('is-valid');
                } else if (email) {
                    emailFeedback.innerHTML = '<small class="text-success"><i class="fas fa-check-circle me-1"></i>Email format is valid</small>';
                    emailInput.classList.add('is-valid');
                    emailInput.classList.remove('is-invalid');
                } else {
                    emailFeedback.innerHTML = '';
                    emailInput.classList.remove('is-valid', 'is-invalid');
                }
            }
            
            // Update button state
            updateLoginButton();
        });
    }
    
    // Real-time password validation
    const passwordInput = document.getElementById('password');
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            const password = this.value.trim();
            const passwordFeedback = document.getElementById('passwordFeedback');
            
            if (passwordFeedback) {
                if (password) {
                    passwordFeedback.innerHTML = '<small class="text-success"><i class="fas fa-check-circle me-1"></i>Password entered</small>';
                    passwordInput.classList.add('is-valid');
                    passwordInput.classList.remove('is-invalid');
                } else {
                    passwordFeedback.innerHTML = '';
                    passwordInput.classList.remove('is-valid', 'is-invalid');
                }
            }
            
            // Update button state
            updateLoginButton();
        });
    }
    
    // Update login button state based on form validity
    function updateLoginButton() {
        if (submitBtn && emailInput && passwordInput) {
            const email = emailInput.value.trim();
            const password = passwordInput.value.trim();
            
            if (email && password && emailRegex.test(email)) {
                submitBtn.disabled = false;
                submitBtn.classList.remove('btn-secondary');
                submitBtn.classList.add('btn-primary');
            } else {
                submitBtn.disabled = true;
                submitBtn.classList.remove('btn-primary');
                submitBtn.classList.add('btn-secondary');
            }
        }
    }
    
    // Initialize button state
    updateLoginButton();
    
    // Form submission handler
    if (loginForm) {
        loginForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            clearMessages();
            
            // Get form data
            const formData = {
                email: document.getElementById('email').value,
                password: document.getElementById('password').value
            };
            
            // Validate form
            const errors = validateForm(formData);
            
            if (errors.length > 0) {
                displayErrors(errors);
                return;
            }
            
            // Set loading state
            setLoadingState(true);
            
            try {
                // Send login request
                const response = await fetch('/authentication/actions/login_customer_action.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(formData)
                });
                
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                
                const result = await response.json();
                
                if (result.status) {
                    // Success
                    displaySuccess(result.message + ' Redirecting to homepage...');
                    
                    // Clear form
                    loginForm.reset();
                    
                    // Clear form validation classes
                    const inputs = loginForm.querySelectorAll('.form-control');
                    inputs.forEach(input => {
                        input.classList.remove('is-valid', 'is-invalid');
                    });
                    
                    // Clear feedback messages
                    const feedbacks = loginForm.querySelectorAll('[id$="Feedback"]');
                    feedbacks.forEach(feedback => {
                        feedback.innerHTML = '';
                    });
                    
                    // Redirect to homepage after 2 seconds
                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 2000);
                    
                } else {
                    // Error
                    displayErrors([result.message]);
                    
                    // Add invalid class to inputs for visual feedback
                    emailInput.classList.add('is-invalid');
                    passwordInput.classList.add('is-invalid');
                }
                
            } catch (error) {
                console.error('Login error:', error);
                displayErrors(['An error occurred during login. Please try again.']);
            } finally {
                setLoadingState(false);
            }
        });
    }
    
    // Show/hide password functionality
    const togglePassword = document.getElementById('togglePassword');
    if (togglePassword) {
        togglePassword.addEventListener('click', function() {
            const passwordField = document.getElementById('password');
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            
            // Toggle icon
            const icon = this.querySelector('i');
            if (type === 'password') {
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    }
});