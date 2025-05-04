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

    try {
      // Use the full path to the API endpoint
      const response = await fetch('/prin/x8m18/kill%20me/advanced-web-tech-final/public/api/register', {
        method: 'POST',
        body: JSON.stringify({
          name,
          phone,
          email,
          password,
          csrf_token: csrfToken
        }),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        }
      });

      // Parse the response as JSON
      let result;
      try {
        result = await response.json();
      } catch (error) {
        console.error('Failed to parse JSON response:', error);
        // If we can't parse JSON, try to get the text response
        const text = await response.text();
        console.error('Response text:', text);
        throw new Error('Invalid response format from server');
      }

      if (response.ok && result.success) {
        // Registration succeeded, redirect to login using URL from server if available
        window.location.href = result.redirectUrl || './login';
      } else {
        // Show the error message from the server
        setSubmitError(result.message || 'Registration failed. Please try again.');
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