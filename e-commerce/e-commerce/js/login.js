// Login form validation and submission
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const submitBtn = document.getElementById('loginBtn');
    
    //checking form existence
    if (!loginForm) {
        console.error('Login form not found!');
        return;
    }
    
    // Regular expressions for validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    
    // Form validation function
    function validateForm(formData) {
        const errors = [];
        
        // Validate email 
        if (!formData.email.trim()) {
            errors.push('Email is required');
        } else if (!emailRegex.test(formData.email.trim())) {
            errors.push('Please enter a valid email address');
        }
        
        // Validate password 
        if (!formData.password.trim()) {
            errors.push('Password is required');
        }
        
        return errors;
    }
    

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
    
    // Display error messages 
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
    
    //display success message 
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
    
    //email validation
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
    
    //password validation
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
    loginForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        console.log('Form submitted via AJAX - preventing default form submission');
        
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
            return false; 
        }
        
        // Set loading state
        setLoadingState(true);
        
        try {
            // Send login request via AJAX
            console.log('Sending AJAX request to login endpoint');
            
            const response = await fetch('actions/login_customer_action.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest' 
                },
                body: JSON.stringify(formData)
            });
            
            console.log('Response received, status:', response.status);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Server did not return JSON response');
            }
            
            const result = await response.json();
            console.log('Login result:', result);
            
            if (result.status) {
                // Success - handle redirect based on role
                let redirectMessage = 'Login successful!';
                
                if (result.role == 2 || result.role === 'admin') {
                    Location = '../admin/category.php';
                } else {
                    redirectMessage += ' Redirecting to customer dashboard...';
                }
                
                displaySuccess(redirectMessage);
                loginForm.reset();
                
             
                const inputs = loginForm.querySelectorAll('.form-control');
                inputs.forEach(input => {
                    input.classList.remove('is-valid', 'is-invalid');
                });
                
                // Clear feedback messages
                const feedbacks = loginForm.querySelectorAll('[id$="Feedback"]');
                feedbacks.forEach(feedback => {
                    feedback.innerHTML = '';
                });
                
                // Redirect to appropriate page after 2 seconds
                setTimeout(() => {
                    if (result.redirect) {
                        console.log('Redirecting to:', result.redirect);
                        window.location.href = result.redirect;
                    } else if (result.role == 2 || result.role === 'admin') {
                        console.log('Redirecting admin to category page');
                        window.location.href = '../admin/category.php';
                    } else {
                        console.log('Redirecting customer to dashboard');
                        window.location.href = '../customer/dashboard.php';
                    }
                }, 2000);
                
            } else {
                // Error
                displayErrors([result.message || 'Login failed']);
                
                // Add invalid class to inputs for visual feedback
                if (emailInput) emailInput.classList.add('is-invalid');
                if (passwordInput) passwordInput.classList.add('is-invalid');
            }
            
        } catch (error) {
            console.error('Login error:', error);
            displayErrors(['An error occurred during login. Please try again.']);
        } finally {
            setLoadingState(false);
        }
        
        return false;
    });
    
    loginForm.addEventListener('reset', function(e) {
        clearMessages();
        updateLoginButton();
    });
    

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
    
    // Debug: Log when script loads
    console.log('Login script loaded successfully');
});