import React from 'react';
import ReactDOM from 'react-dom/client';
import RegisterForm from './RegisterForm';

const container = document.getElementById('react-register-form');

if (container) {
  // Retrieve the CSRF token from the data attribute
  const csrfToken = container.dataset.csrfToken;
  
  const root = ReactDOM.createRoot(container);
  root.render(
    <React.StrictMode>
      <RegisterForm csrfToken={csrfToken} />
    </React.StrictMode>
  );
} else {
  console.error('Could not find the react-register-form container element.');
} 