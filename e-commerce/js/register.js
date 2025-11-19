// Registration form validation and submission
document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.getElementById('register-form');
    const submitBtn = document.getElementById('submitBtn');
    
    // Ensure form exists
    if (!registerForm) {
        console.error('Registration form not found!');
        return;
    }
    
    // Regular expressions for validation based on database requirements
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d@$!%*?&]{8,}$/;
    const phoneRegex = /^[\+]?[0-9\s\-\(\)]{7,15}$/;
    const nameRegex = /^[a-zA-Z\s\-'\.]{2,100}$/;
    
    // Form validation function
    function validateForm(formData) {
        const errors = [];
        
        // Validate name 
        if (!formData.name.trim()) {
            errors.push('Full name is required');
        } else if (formData.name.trim().length > 100) {
            errors.push('Full name must be less than 100 characters');
        } else if (!nameRegex.test(formData.name.trim())) {
            errors.push('Full name must contain only letters, spaces, hyphens, and apostrophes');
        }
        
        // Validate email 
        if (!formData.email.trim()) {
            errors.push('Email is required');
        } else if (formData.email.trim().length > 50) {
            errors.push('Email must be less than 50 characters');
        } else if (!emailRegex.test(formData.email.trim())) {
            errors.push('Please enter a valid email address');
        }
        
        // Validate password 
        if (!formData.password) {
            errors.push('Password is required');
        } else if (!passwordRegex.test(formData.password)) {
            errors.push('Password must be at least 8 characters long with at least one uppercase letter, one lowercase letter, and one number');
        }
        
        // Validate confirm password
        if (!formData.confirmPassword) {
            errors.push('Please confirm your password');
        } else if (formData.password !== formData.confirmPassword) {
            errors.push('Passwords do not match');
        }
        
        // Validate country 
        if (!formData.country || formData.country.trim() === 'select country' || formData.country.trim() === '') {
            errors.push('Please select a country');
        } else if (formData.country.trim().length > 30) {
            errors.push('Country must be less than 30 characters');
        }
        
        // Validate city 
        if (!formData.city.trim()) {
            errors.push('City is required');
        } else if (formData.city.trim().length > 30) {
            errors.push('City must be less than 30 characters');
        }
        
        // Validate contact 
        if (!formData.contact.trim()) {
            errors.push('Contact number is required');
        } else if (formData.contact.trim().length > 15) {
            errors.push('Contact number must be less than 15 characters');
        } else if (!phoneRegex.test(formData.contact.trim())) {
            errors.push('Please enter a valid contact number');
        }
        
        return errors;
    }
    
    //loading state
    function setLoadingState(isLoading) {
        if (submitBtn) {
            if (isLoading) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Registering...';
                submitBtn.classList.add('btn-secondary');
                submitBtn.classList.remove('btn-primary');
            } else {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-user-plus me-2"></i>Register';
                submitBtn.classList.add('btn-primary');
                submitBtn.classList.remove('btn-secondary');
            }
        }
    }
    
    //error msgs
    function displayErrors(errors) {
        const errorContainer = document.getElementById('errorMessages');
        if (errorContainer) {
            errorContainer.innerHTML = '';
            if (errors.length > 0) {
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-danger alert-dismissible fade show';
                alertDiv.innerHTML = `
                    <strong>oops</strong>
                    <ul class="mb-0 mt-2">
                        ${errors.map(error => `<li>${error}</li>`).join('')}
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                errorContainer.appendChild(alertDiv);
                
                // Scroll to error message
                errorContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
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
            
            // Scroll to success message
            successContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
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
        });
    }
    
    //password validation
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirmPassword');
    
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            const passwordFeedback = document.getElementById('passwordFeedback');
            
            if (passwordFeedback) {
                if (password && !passwordRegex.test(password)) {
                    passwordFeedback.innerHTML = '<small class="text-danger"><i class="fas fa-times-circle me-1"></i>Password must be at least 8 characters with uppercase, lowercase, and number</small>';
                    passwordInput.classList.add('is-invalid');
                    passwordInput.classList.remove('is-valid');
                } else if (password) {
                    passwordFeedback.innerHTML = '<small class="text-success"><i class="fas fa-check-circle me-1"></i>Password strength: Good</small>';
                    passwordInput.classList.add('is-valid');
                    passwordInput.classList.remove('is-invalid');
                } else {
                    passwordFeedback.innerHTML = '';
                    passwordInput.classList.remove('is-valid', 'is-invalid');
                }
            }
            
            //validate confirm password if it has a value
            if (confirmPasswordInput && confirmPasswordInput.value) {
                confirmPasswordInput.dispatchEvent(new Event('input'));
            }
        });
    }
    
    if (confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', function() {
            const confirmPassword = this.value;
            const password = passwordInput ? passwordInput.value : '';
            const confirmPasswordFeedback = document.getElementById('confirmPasswordFeedback');
            
            if (confirmPasswordFeedback) {
                if (confirmPassword && password !== confirmPassword) {
                    confirmPasswordFeedback.innerHTML = '<small class="text-danger"><i class="fas fa-times-circle me-1"></i>Passwords do not match</small>';
                    confirmPasswordInput.classList.add('is-invalid');
                    confirmPasswordInput.classList.remove('is-valid');
                } else if (confirmPassword && password === confirmPassword) {
                    confirmPasswordFeedback.innerHTML = '<small class="text-success"><i class="fas fa-check-circle me-1"></i>Passwords match</small>';
                    confirmPasswordInput.classList.add('is-valid');
                    confirmPasswordInput.classList.remove('is-invalid');
                } else {
                    confirmPasswordFeedback.innerHTML = '';
                    confirmPasswordInput.classList.remove('is-valid', 'is-invalid');
                }
            }
        });
    }
    
    // Form submission handler - SINGLE EVENT LISTENER
    registerForm.addEventListener('submit', async function(e) {
        // PREVENT DEFAULT FORM SUBMISSION
        e.preventDefault();
        e.stopPropagation();
        
        console.log('Form submitted via AJAX - preventing default form submission');
        
        clearMessages();
        
        // Get form data
        const formData = {
            name: document.getElementById('name').value,
            email: document.getElementById('email').value,
            password: document.getElementById('password').value,
            confirmPassword: document.getElementById('confirmPassword').value,
            country: document.getElementById('country').value,
            city: document.getElementById('city').value,
            contact: document.getElementById('contact').value,
            user_role: 2 // Default customer role
        };
        
        console.log('Form data prepared:', { ...formData, password: '[HIDDEN]', confirmPassword: '[HIDDEN]' });
        
        // Validate form
        const errors = validateForm(formData);
        
        if (errors.length > 0) {
            displayErrors(errors);
            return false;
        }
        
        // Set loading state
        setLoadingState(true);
        
        try {
            // Remove confirmPassword from data sent to server
            const serverData = { ...formData };
            delete serverData.confirmPassword;
            
            console.log('Sending registration request...');
            
            // Send registration request - try different path
            const response = await fetch('../actions/register_customer_action.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(serverData)
            });
            
            console.log('Response received, status:', response.status);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const textResponse = await response.text();
                console.error('Server response was not JSON:', textResponse);
                throw new Error('Server did not return JSON response');
            }
            
            const result = await response.json();
            console.log('Registration result:', result);
            
            if (result.status) {
                // Success
                displaySuccess(result.message + ' Redirecting to login page...');
                
                // Clear form
                registerForm.reset();
                
                // Clear form validation classes
                const inputs = registerForm.querySelectorAll('.form-control, .form-select');
                inputs.forEach(input => {
                    input.classList.remove('is-valid', 'is-invalid');
                });
                
                // Clear feedback messages
                const feedbacks = registerForm.querySelectorAll('[id$="Feedback"]');
                feedbacks.forEach(feedback => {
                    feedback.innerHTML = '';
                });
                
                // Redirect to login page after 3 seconds
                setTimeout(() => {
                    console.log('Redirecting to login page...');
                    window.location.href = result.redirect || '../login/login.php';
                }, 3000);
                
            } else {
                // Error
                displayErrors([result.message || 'Registration failed']);
            }
            
        } catch (error) {
            console.error('Registration error:', error);
            displayErrors(['An error occurred during registration. Please try again.']);
        } finally {
            setLoadingState(false);
        }
        
        return false; // Prevent any form submission
    });
    
    // Debug: Log when script loads
    console.log('Registration script loaded successfully');
});