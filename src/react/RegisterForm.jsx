import React, { useState, useEffect } from 'react';

function RegisterForm({ csrfToken }) {
  const [name, setName] = useState('');
  const [phone, setPhone] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  
  const [errors, setErrors] = useState({});
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [submitError, setSubmitError] = useState('');

  // Regex patterns (reuse from original script)
  const patterns = {
    name: /^[A-Za-z\s'-]{2,60}$/,
    phone: /^\d{10}$/,
    email: /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/, // More robust email regex
    password: /^(?=.*[A-Za-z])(?=.*\d).{8,}$/
  };

  const validateField = (fieldName, value) => {
    let error = '';
    switch (fieldName) {
      case 'name':
        if (!patterns.name.test(value)) {
          error = 'Name must be 2-60 characters and contain only letters, spaces, hyphens, apostrophes.';
        }
        break;
      case 'phone':
        if (!patterns.phone.test(value)) {
          error = 'Phone number must be exactly 10 digits.';
        }
        break;
      case 'email':
        if (!patterns.email.test(value)) {
          error = 'Please enter a valid email address.';
        }
        break;
      case 'password':
        if (!patterns.password.test(value)) {
          error = 'Password must be at least 8 characters with at least one letter and one number.';
        }
        break;
      default:
        break;
    }
    setErrors(prevErrors => ({ ...prevErrors, [fieldName]: error }));
    return !error; 
  };

  // --- Event Handlers ---
  const handleNameChange = (e) => {
    const value = e.target.value;
    setName(value);
    validateField('name', value);
  };

  const handlePhoneChange = (e) => {
    const value = e.target.value;
    setPhone(value);
    validateField('phone', value);
  };

  const handleEmailChange = (e) => {
    const value = e.target.value;
    setEmail(value);
    validateField('email', value);
  };

  const handlePasswordChange = (e) => {
    const value = e.target.value;
    setPassword(value);
    validateField('password', value);
  };

  // --- Form Submission ---
  const handleSubmit = async (e) => {
    e.preventDefault(); 
    setSubmitError(''); 
    
    const isNameValid = validateField('name', name);
    const isPhoneValid = validateField('phone', phone);
    const isEmailValid = validateField('email', email);
    const isPasswordValid = validateField('password', password);

    if (!isNameValid || !isPhoneValid || !isEmailValid || !isPasswordValid) {
        console.log("Form validation failed on submit.");
        return; 
    }

    setIsSubmitting(true);
    
    const formData = new FormData();
    formData.append('name', name);
    formData.append('phone', phone);
    formData.append('email', email);
    formData.append('password', password);
    formData.append('csrf_token', csrfToken); 

    try {
      // IMPORTANT: Adjust '/register-post' if your backend endpoint is different
      const response = await fetch('/api/register', { // Corrected URL
        method: 'POST',
        // body: formData, // Sending as form data, adjust if backend expects JSON - REMOVED
        body: JSON.stringify({ 
            name: name,
            phone: phone,
            email: email,
            password: password,
            // Assuming CSRF token needs to be sent if applicable; how it's obtained (prop?) needs confirmation
            // csrf_token: csrfToken 
        }),
        headers: {
            // Add 'Accept': 'application/json' if your backend responds with JSON
            'Accept': 'application/json', // Added Accept header
            'Content-Type': 'application/json' // Set Content-Type to JSON
        }
      });

      if (response.ok) {
        // Option 1: Redirect (if backend handles redirect)
        if (response.redirected) {
           window.location.href = response.url;
        } else {
           // Option 2: Manually redirect or show success message
           // Check if backend sends a specific success indicator
           const result = await response.json(); // Assuming backend sends JSON
           if(result.success) {
              window.location.href = result.redirectUrl || '/login'; // Redirect to login or specified URL
           } else {
              setSubmitError(result.message || 'Registration failed. Please try again.');
           }
        }
      } else {
        let errorMessage = `Registration failed. Status: ${response.status}`;
        try {
           const errorData = await response.json(); 
           errorMessage = errorData.message || errorMessage; 
        } catch (jsonError) {
           console.error("Could not parse error response:", jsonError);
        }
        setSubmitError(errorMessage);
      }
    } catch (error) {
      console.error('Registration request failed:', error);
      setSubmitError('An unexpected error occurred. Please check your connection and try again.');
    } finally {
      setIsSubmitting(false);
    }
  };

  // --- JSX Structure (based on register.php) ---
  return (
    <div className="row">
      <div className="col-md-6 mx-auto">
        <div className="card">
          <div className="card-header bg-primary text-white">
            <h1 className="h4 mb-0">Register</h1>
          </div>
          <div className="card-body">
            {submitError && (
              <div className="alert alert-danger">{submitError}</div>
            )}

            <form onSubmit={handleSubmit} noValidate>
              {/* Name Field */}
              <div className="mb-3">
                <label htmlFor="name" className="form-label">Name</label>
                <input
                  type="text"
                  className={`form-control ${errors.name ? 'is-invalid' : ''}`}
                  id="name"
                  name="name"
                  value={name}
                  onChange={handleNameChange}
                  required
                  aria-describedby="name-help name-feedback"
                />
                {errors.name && (
                  <div id="name-feedback" className="invalid-feedback">
                    {errors.name}
                  </div>
                )}
                 <div id="name-help" className="form-text">Letters, spaces, hyphens and apostrophes only.</div>
              </div>

              {/* Phone Field */}
              <div className="mb-3">
                <label htmlFor="phone" className="form-label">Phone</label>
                <input
                  type="tel"
                  className={`form-control ${errors.phone ? 'is-invalid' : ''}`}
                  id="phone"
                  name="phone"
                  value={phone}
                  onChange={handlePhoneChange}
                  required
                  aria-describedby="phone-help phone-feedback"
                />
                {errors.phone && (
                   <div id="phone-feedback" className="invalid-feedback">
                    {errors.phone}
                  </div>
                )}
                 <div id="phone-help" className="form-text">10 digits only, no spaces or symbols.</div>
              </div>

              {/* Email Field */}
              <div className="mb-3">
                <label htmlFor="email" className="form-label">Email</label>
                <input
                  type="email"
                   className={`form-control ${errors.email ? 'is-invalid' : ''}`}
                  id="email"
                  name="email"
                  value={email}
                  onChange={handleEmailChange}
                  required
                  aria-describedby="email-feedback"
                />
                 {errors.email && (
                   <div id="email-feedback" className="invalid-feedback">
                    {errors.email}
                  </div>
                )}
              </div>

              {/* Password Field */}
              <div className="mb-3">
                <label htmlFor="password" className="form-label">Password</label>
                <input
                  type="password"
                  className={`form-control ${errors.password ? 'is-invalid' : ''}`}
                  id="password"
                  name="password"
                  value={password}
                  onChange={handlePasswordChange}
                  required
                  minLength="8"
                  aria-describedby="password-help password-feedback"
                />
                {errors.password && (
                  <div id="password-feedback" className="invalid-feedback">
                    {errors.password}
                  </div>
                )}
                 <div id="password-help" className="form-text">At least 8 characters, including at least one letter and one number.</div>
              </div>

              {/* CSRF Token (Hidden - Passed as prop) */}
              {/* The hidden input is removed as we send it via fetch */}

              {/* Submit Button & Link */}
              <div className="d-flex justify-content-between align-items-center">
                <button type="submit" className="btn btn-primary" disabled={isSubmitting}>
                  {isSubmitting ? 'Registering...' : 'Register'}
                </button>
                <a href="/login" className="text-decoration-none">Already have an account? Login</a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  );
}

export default RegisterForm; 