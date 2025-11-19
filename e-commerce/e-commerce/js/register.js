// Registration form validation and submission
document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.getElementById('register-form');
    const submitBtn = document.getElementById('submitBtn');
    
    // Regular expressions for validation based on database requirements
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d@$!%*?&]{8,}$/;
    const phoneRegex = /^[\+]?[0-9\s\-\(\)]{7,15}$/;
    const nameRegex = /^[a-zA-Z\s\-'\.]{2,100}$/;
    
    // Form validation function
    function validateForm(formData) {
        const errors = [];
        
        // Validate name (required, max 100 chars, only letters and spaces)
        if (!formData.name.trim()) {
            errors.push('Full name is required');
        } else if (formData.name.trim().length > 100) {
            errors.push('Full name must be less than 100 characters');
        } else if (!nameRegex.test(formData.name.trim())) {
            errors.push('Full name must contain only letters, spaces, hyphens, and apostrophes');
        }
        
        // Validate email (required, max 50 chars, valid format)
        if (!formData.email.trim()) {
            errors.push('Email is required');
        } else if (formData.email.trim().length > 50) {
            errors.push('Email must be less than 50 characters');
        } else if (!emailRegex.test(formData.email.trim())) {
            errors.push('Please enter a valid email address');
        }
        
        // Validate password (required, strong password)
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
        
        // Validate country (required, max 30 chars)
        if (!formData.country.trim()) {
            errors.push('Country is required');
        } else if (formData.country.trim().length > 30) {
            errors.push('Country must be less than 30 characters');
        }
        
        // Validate city (required, max 30 chars)
        if (!formData.city.trim()) {
            errors.push('City is required');
        } else if (formData.city.trim().length > 30) {
            errors.push('City must be less than 30 characters');
        }
        
        // Validate contact (required, max 15 chars, valid format)
        if (!formData.contact.trim()) {
            errors.push('Contact number is required');
        } else if (formData.contact.trim().length > 15) {
            errors.push('Contact number must be less than 15 characters');
        } else if (!phoneRegex.test(formData.contact.trim())) {
            errors.push('Please enter a valid contact number');
        }
        
        return errors;
    }
    
    // Show/hide loading state
    function setLoadingState(isLoading) {
        if (submitBtn) {
            if (isLoading) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Registering...';
            } else {
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Register';
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
                    <strong>Please fix the following errors:</strong>
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
    
    // Check email availability asynchronously
    async function checkEmailAvailability(email) {
        try {
            const response = await fetch('/e-commerce/actions/register_customer_action.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ email: email })
            });
            
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            
            const result = await response.json();
            return result;
        } catch (error) {
            // console.error('Error checking email availability:', error);
            return { status: false, message: 'Error checking email availability' };
        }
    }
    
    // Real-time email validation
    const emailInput = document.getElementById('email');
    if (emailInput) {
        let emailTimeout;
        emailInput.addEventListener('input', function() {
            const email = this.value.trim();
            const emailFeedback = document.getElementById('emailFeedback');
            
            // Clear previous timeout
            clearTimeout(emailTimeout);
            
            if (email && emailRegex.test(email)) {
                // Show checking state
                if (emailFeedback) {
                    emailFeedback.innerHTML = '<small class="text-info"><i class="fas fa-spinner fa-spin me-1"></i>Checking availability...</small>';
                }
                
                // Debounce the email check
                emailTimeout = setTimeout(async () => {
                    const result = await checkEmailAvailability(email);
                    
                    if (emailFeedback) {
                        if (result.status && result.exists) {
                            emailFeedback.innerHTML = '<small class="text-danger"><i class="fas fa-times-circle me-1"></i>This email is already registered</small>';
                            emailInput.classList.add('is-invalid');
                            emailInput.classList.remove('is-valid');
                        } else if (result.status && !result.exists) {
                            emailFeedback.innerHTML = '<small class="text-success"><i class="fas fa-check-circle me-1"></i>Email is available</small>';
                            emailInput.classList.add('is-valid');
                            emailInput.classList.remove('is-invalid');
                        } else {
                            emailFeedback.innerHTML = '';
                            emailInput.classList.remove('is-valid', 'is-invalid');
                        }
                    }
                }, 500); // 500ms debounce
            } else if (email) {
                if (emailFeedback) {
                    emailFeedback.innerHTML = '<small class="text-danger"><i class="fas fa-times-circle me-1"></i>Invalid email format</small>';
                }
                emailInput.classList.add('is-invalid');
                emailInput.classList.remove('is-valid');
            } else {
                if (emailFeedback) {
                    emailFeedback.innerHTML = '';
                }
                emailInput.classList.remove('is-valid', 'is-invalid');
            }
        });
    }
    
    // Real-time password validation
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
            
            // Also validate confirm password if it has a value
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
    
    // Form submission handler
    if (registerForm) {
        registerForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
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
            
            // Validate form
            const errors = validateForm(formData);
            
            if (errors.length > 0) {
                displayErrors(errors);
                return;
            }
            
            // Check email availability before submitting
            const emailCheck = await checkEmailAvailability(formData.email);
            if (emailCheck.status && emailCheck.exists) {
                displayErrors(['This email is already registered']);
                return;
            }
            
            // Set loading state
            setLoadingState(true);
            
           registerForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    setLoadingState(true); // Show loading

    try {
        // Copy form data and remove confirmPassword
        const formData = Object.fromEntries(new FormData(registerForm));
        delete formData.confirmPassword;

        // Send registration request
        const response = await fetch('../actions/register_customer_action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData)
        });

        const result = await response.json();

        if (result.status) {
            displaySuccess(result.message + ' Redirecting to login page...');
            
            registerForm.reset(); // Clear form

            // Redirect after 2s
            setTimeout(() => {
                window.location.href = 'login.php';
            }, 2000);
        } else {
            displayErrors([result.message]);
        }

    } catch (err) {
        console.error('Registration error:', err);
        displayErrors(['An error occurred. Please try again.']);
    } finally {
        setLoadingState(false); // Hide loading
    }
});

        });
    }
});