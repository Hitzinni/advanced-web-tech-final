import api from './api';

/** Register a new user */
export function register({ name, phone, email, password }) {
  return api.post('/register', { name, phone, email, password });
}

/** Log in existing user */
export function login(email, password, captcha) {
  return api.post('/login', { email, password, captcha });
}

/** Log out current user */
export function logout() {
  return api.post('/logout');
}

/** Place a new order */
export function placeOrder(productId) {
  return api.post('/order', { product_id: productId });
}
